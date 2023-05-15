<?php

namespace App\Http\Controllers;
use App\DataTables\BoxDataTable;
use App\libraries\Chkerr;
use App\libraries\RegReport;
use App\libraries\Slog;
use App\libraries\Validator;
use App\models\Box;
use App\models\Client;
use App\models\GlobalSetting;
use App\models\PaymentRecord;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\View;
use App\models\PaymentNew;

class BoxController extends BaseController
{

    public function __construct()
    {
//        $this->beforeFilter('auth');  //bloqueo de acceso
    }

    public function getIndex(BoxDataTable $dataTable)
    {
        $id = Auth::user()->id;
        $level = Auth::user()->level;
        $perm = DB::table('permissions')->where('user_id', '=', $id)->get();
        $access = $perm[0]->access_pays;
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

            return $dataTable->render('box.index', $permissions);

        } else
            return Redirect::to('admin');
    }

    //metodo para listar ingresos
    public function postListin()
    {

        $inputs = DB::table('boxes')
            ->join('users', 'boxes.user_id', '=', 'users.id')
            ->select('users.username As usname', 'boxes.id',
                'boxes.date_reg', 'boxes.amount', 'boxes.num_receipt',
                'boxes.detail', 'boxes.type', 'boxes.name As client')
            ->where('boxes.type', '=', 'in')
            ->get();

        return Response::json($inputs);
    }

    //metodo para listar egresos
    public function postListout()
    {
        $outputs = DB::table('boxes')
            ->join('users', 'boxes.user_id', '=', 'users.id')
            ->select('users.username As usname', 'boxes.id',
                'boxes.date_reg', 'boxes.amount', 'boxes.num_receipt',
                'boxes.detail', 'boxes.type', 'boxes.name As social')
            ->where('boxes.type', '=', 'ou')
            ->get();

        return Response::json($outputs);
    }

    //metodo para obtener la cantitad total de ingresos
    public function getTotalcounters()
    {

        $Tinputs = PaymentNew::select('id', 'amount', 'deleted_at')->whereNull('deleted_at')->get()->sum('amount');
        $Toutputs = DB::table('boxes')->where('type', '=', 'ou')->sum('amount');
        $global = GlobalSetting::all()->first();

        $total = round($Tinputs - $Toutputs, 2);

        $data = array(
            'success' => true,
            'total_in' => round($Tinputs, 2),
            'total_out' => round($Toutputs, 2),
            'total' => $total,
            'simbol' => $global->smoney
        );

        return Response::json($data);
    }


    //metodo registrar ingresos o egresos
    public function postCreate(Request $request)
    {

        if ($request->get('type') == 'in') {
            # ingreso
            $friendly_names = array(
                'type' => __('app.typeofOperation'),
                'numr' => __('app.voucherNumber'),
                'detail' => __('app.operationDetail'),
                'amount' => __('app.totalAmount'),
                'client' => __('app.client'),
                'date_reg' => __('app.date')
            );

            $rules = array(
                'type' => 'required',
                'numr' => 'required|min:1|numeric',
                'detail' => 'required',
                'amount' => 'required|numeric',
                'client' => 'required',
                'date_reg' => 'required:date'
            );
        } else {

            $friendly_names = array(
                'type' => __('app.typeofOperation'),
                'numr' => 'Nº de comprobante',
                'detail' => __('app.operationDetail'),
                'amount' => __('app.totalAmount'),
                'social' => __('app.businessName'),
                'router' => 'Router',
                'date_reg' => __('app.date')
            );

            $rules = array(
                'type' => 'required',
                'numr' => 'required|min:1|numeric',
                'detail' => 'required',
                'amount' => 'required|numeric',
                'social' => 'required',
                'router' => 'required',
                'date_reg' => 'required:date'
            );

        }


        $validation = Validator::make($request->all(), $rules);
        $validation->setAttributeNames($friendly_names);

        if ($validation->fails())
            return Response::json(['msg' => 'error', 'errors' => $validation->getMessageBag()->toArray()]);

        $log = new Slog();
        $process = new Chkerr();


        if ($request->get('type') == 'out') {
            $nClient = $request->get('social');
            $nRouter = $request->get('router');
        } else {
            $client = Client::find($request->get('client'));
            $nRouter = $client->router_id;
            $nClient = $client->name;
        }


        $id = DB::table('boxes')->insertGetId(
            array(
                'user_id' => Auth::user()->id,
                'name' => $nClient,
                'date_reg' => date("Y-m-d", strtotime($request->get('date_reg'))),
                'amount' => $request->get('amount'),
                'num_receipt' => $request->get('numr', 0),
                'detail' => $request->get('detail'),
                'type' => $request->get('type'),
                'router_id' => $nRouter
            )
        );

        $data = array(
            'name' => $nClient,
            'router_id' => $nRouter,
            'box_id' => $id,
            'detail' => $request->get('detail'),
            'type' => $request->get('type'),
            'date' => date('Y-m-d'),
            'amount' => $request->get('amount')
        );

        $regRep = new RegReport();

        $regRep->add($data);

        if ($request->get('type') == 'in')
            $mv = 'Ingreso';
        else
            $mv = 'Egreso';

        //save log
        $log->save("Se ha registrado un movimiento en caja:", "info", $mv);

        return Response::json(array('msg' => 'success'));
    }

    //metodo para eliminar
    public function postDelete(Request $request)
    {

        $box_id = $request->get('id');
        $box = Box::find($box_id);
        //coprobamos si no existe debuelve null
        if (is_null($box))
            return Response::json(array('msg' => 'notfound'));

        if ($box->type == 'in')
            $mv = 'Ingreso';
        else
            $mv = 'Egreso';
        //eliminamos y redirigimos
        PaymentRecord::where('box_id', '=', $box_id)->delete();

        $box->delete();

        $log = new Slog();
        //save log
        $log->save("Se ha eliminado un registro en caja:", "danger", $mv);
        return Response::json(array('msg' => 'success'));
    }


}
