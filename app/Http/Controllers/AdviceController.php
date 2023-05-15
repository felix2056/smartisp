<?php

namespace App\Http\Controllers;
use App\DataTables\AdviceDataTable;
use App\models\Client;
use App\models\ClientService;
use App\models\GlobalSetting;
use App\models\Notice;
use App\models\SuspendClient;
use App\models\Template;
use App\Service\CommonService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\View;

class AdviceController extends BaseController
{


    public function __construct()
    {
//        $this->beforeFilter('auth');  //bloqueo de acceso
    }

    public function getIndex(AdviceDataTable $dataTable)
    {
        $id = Auth::user()->id;
        $level = Auth::user()->level;
        $perm = DB::table('permissions')->where('user_id', '=', $id)->get();
        $access = $perm[0]->access_clients;
        //control permissions only access super administrator (sa)
        if ($level == 'ad' || $access == true) {

            $global = GlobalSetting::all()->first();

            $permissions = array("clients" => $perm[0]->access_clients, "plans" => $perm[0]->access_plans, "routers" => $perm[0]->access_routers,
                "users" => $perm[0]->access_users, "system" => $perm[0]->access_system, "bill" => $perm[0]->access_pays,
                "template" => $perm[0]->access_templates, "ticket" => $perm[0]->access_tickets, "sms" => $perm[0]->access_sms,
                "reports" => $perm[0]->access_reports,
                "v" => $global->version, "st" => $global->status,
                "lv" => $global->license, "company" => $global->company,
                'permissions' => $perm->first(),
                // menu options
            );

            if (Auth::user()->level == 'ad')
                @setcookie("hcmd", 'kR2RsakY98pHL', time() + 7200, "/", "", 0, true);

            return $dataTable->render('notices.index', $permissions);

        } else
        return Redirect::to('admin');
    }

    //metodo para lista clientes del router seleciconado
    public function postClients(Request $request)
    {

        $clients = ClientService::join('clients', 'clients.id', '=', 'client_services.client_id')
            ->select('clients.id', 'clients.name')
            ->where('client_services.router_id', '=', $request->get('idr'))->where('client_services.status', '=', 'ac')->groupBy('clients.id')->get();

        if (count(json_decode($clients, 1)) == 0)
            return Response::json(array("msg" => "noclients"));

        return Response::json($clients);

    }

    //metodo para enviar avisos a los clientes
    public function postSend(Request $request)
    {
        set_time_limit(0);
        $clients = $request->get('clients');
        $router_id = $request->get('router_id');
        $count = count($clients);

        switch ($request->get('typetem')) {

            case 'email':

            $idn = DB::table('notices')->insertGetId(array('name' => $request->get('name'), 'type' => $request->get('typetem'), 'template_id' => $request->get('template'), 'router_id' => $router_id, 'hits' => $count, 'total' => $count, 'registered' => date("Y-m-d H:i:s")));
            $template = Template::find($request->get('template'));
            $name = explode('.', $template->filename);
            $subject = $request->get('name');
                //buscamos datos del sistema
            $global = GlobalSetting::all()->first();
            $tol = $global->tolerance;
            $company_nam =$global->company;


            for ($i = 0; $i < $count; $i++) {

                $clt = Client::find($clients[$i]);
	
	            
                
                if (!is_null($clt)) {
	
	                $exp = CommonService::getServiceCortadoDate($clt->id);
	                
	                
                    if (!empty($clt->email) && $exp['cortado_date']) {

//                        $plan = Plan::find($clt->plan_id);
                            //buscamos cuando expiro su pago
                            //verificamos que el cliente tenga un email
	                    
                        $timestamp = strtotime($exp['cortado_date']);
                        $cutday = strtotime('+' . $tol . ' day', strtotime($exp['cortado_date']));
                            // variables globases de la plantilla
//                        $Totalcost = $plan->cost + ($plan->iva * ($plan->cost / 100));
                        $Totalcost = round($clt->balance, 2);

                        $data = array(
                            "empresa" => $global->company,
                            "cliente" => $clt->name,
                            "vencimiento" => date("d/m/Y", $timestamp),
                            "corte" => date('d/m/Y', $cutday),
                            "plan" => "",
                            "costo" => $clt->balance,
                            "total" => $Totalcost,
                            "emailCliente" => $clt->email,
                            "direccionCliente" => $clt->address,
                            "telefonoCliente" => $clt->phone,
                            "dniCliente" => $clt->dni,
                            "moneda" => $global->nmoney,
                            "Smoneda" => $global->smoney

                        );

                        $emails = $clt->email;
                        $email_desde = $global->email;
                        if (isset($emails)) {


                            Mail::send('templates.' . $name[0], $data, function ($message) use ($emails, $subject,$company_nam,$email_desde) {
                                $message->subject($subject);
                                $message->to($emails);
                                $message->from($email_desde,$company_nam);
                            });


                        }

                    }
                }
            }



            return Response::json(array("msg" => "success"));

            break;

            default:
            return Response::json(array("msg" => "errorSend"));
            break;

        } //end switch

    }

    //metodo para eliminar los avisos
    public function postDelete(Request $request)
    {

        set_time_limit(0); //unlimited execution time php

        $id_notice = $request->get('id');

        $adv = Notice::find($id_notice);

        //restauramos del router

        //verificamos si esta eliminado un aviso tipo email
        if ($adv->type == 'email') {
            //eliminamos de la base de datos
            $adv->delete();
            return Response::json(array('msg' => 'success'));
        }

    }

}
