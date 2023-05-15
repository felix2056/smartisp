<?php

namespace App\Http\Controllers;
use App\DataTables\PlanDataTable;
use App\Events\UpdatePlanEvent;
use App\Http\Requests\Admin\Plan\CreateRequest;
use App\Http\Requests\Admin\Plan\UpdateRequest;
use App\libraries\Burst;
use App\libraries\GetPlan;
use App\libraries\Helpers;
use App\libraries\Mikrotik;
use App\libraries\RouterConnect;
use App\libraries\Slog;
use App\libraries\Validator;
use App\models\ClientService;
use App\models\GlobalSetting;
use App\models\Permission;
use App\models\Plan;
use App\models\SmartBandwidth;
use App\Service\CommonService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Response;

class PlansController extends BaseController
{
	public function __construct()
	{
		parent::__construct();
		$this->middleware(function ($request, $next) {
			$this->username = auth()->user()->username;
			$this->userId = auth()->user()->id;
			return $next($request);
		});
	}
	
    /**
     * @param PlanDataTable $dataTable
     * @return \Illuminate\Http\RedirectResponse|mixed
     * metodo para ingresar a planes
     */
    public function getIndex(PlanDataTable $dataTable)
    {
        $id = Auth::user()->id;
        $level = Auth::user()->level;
        $perm = Permission::where('user_id','=',$id)->first();
        $access = $perm->access_plans;
        //control permissions only access super administrator (sa)
        if($level=='ad' || $access == true)
        {
            $global = GlobalSetting::first();

            $permissions = array("clients"=> $perm->access_clients, "plans" => $perm->access_plans, "routers" => $perm->access_routers,
                "users" => $perm->access_users, "system" => $perm->access_system, "bill" => $perm->access_pays,
                "template" =>$perm->access_templates, "ticket" => $perm->access_tickets, "sms" => $perm->access_sms,
                "reports" => $perm->access_reports,
                "v" => $global->version, "st" => $global->status,
                "lv" => $global->license, "company" => $global->company, "ms" => $global->message,
                'permissions' => $perm,
            );

            if(Auth::user()->level=='ad')
                @setcookie("hcmd", 'kR2RsakY98pHL', time()+7200,"/","",0, true);

            return $dataTable->render('plans.index', $permissions);
        }
        else
            return Redirect::to('admin');

    }

    //metodo para crear planes simple queues
    public function postCreate(CreateRequest $request)
    {
        $log = new Slog();
        $download = $request->get('download');
        $upload = $request->get('upload');

        if(Burst::check_speed($download,true)==0)
            return Response::json(array('msg'=>'errorDownload'));

        if(Burst::check_speed($upload,true)==0)
            return Response::json(array('msg'=>'errorUpload'));

        if(empty($request->get('iva')))
            $iva = '0';
        else
            $iva = $request->get('iva');

        //guardamos

        $id = DB::table('plans')->insertGetId(array(
            'name' => $request->get('name'),
            'title' => $request->get('title'),
            'download' => $request->get('download'),
            'upload' => $request->get('upload'),
            'num_clients' => 0,
            'cost' => $request->get('cost'),
            'iva' => $iva,
            //burst
            'burst_limit' => $request->get('bl'),
            'burst_threshold' => $request->get('bth'),
            'burst_time' => $request->get('bt'),
            'priority' => $request->get('priority'),
            'limitat' => $request->get('limitat'),
            'aggregation' => $request->get('aggregation', 1),
	        'no_rules' => $request->has('no_rules') ? $request->get('no_rules') : 0,
        ));


        //creamos smart bandwidth por defecto

        $SMB = new SmartBandwidth();
        $SMB->plan_id = $id;
        $SMB->start_time = '00:00:00';
        $SMB->end_time = '00:00:00';
        $SMB->mode = 'd';
        $SMB->days = 'all';
        $SMB->bandwidth = 0;
        $SMB->for_all = 0;
        $SMB->save();

        //save log
	    $name = $request->get('name');
	    CommonService::log("Se ha registrado un plan: $name", $this->username, 'success' , $this->userId);

        return Response::json(array('msg'=>'success'));
    }

    //funcion para elimiar un plan
    public function postDelete(Request $request)
    {

        //buscamos el planen la base de datos segun la id enviada
        $plan_id = $request->get('id');

        $plan = Plan::find($plan_id);
        $planName = $plan->name;
        //coprobamos si no existe debuelve null
        if(is_null($plan))
            return Response::json(array('msg'=>'error'));

        //eliminamos clientes asociados al plan
        $clients = ClientService::where('plan_id','=',$plan_id)->count();

        if($clients > 0)
            return Response::json(array('msg'=>'errorclient'));

        $plan->delete();
        //eliminamos si exite en smart bandwidth
        SmartBandwidth::where('plan_id',$plan_id)->delete();
        //save log
	    CommonService::log("#$plan_id Se ha eliminado un plan", $this->username, 'success' , $this->userId);
        return Response::json(array('msg'=>'success'));
    }

    public function postUpdate(UpdateRequest $request)
    {
        set_time_limit(0); //unlimited execution time php

        $plan_id = $request->get('plan_id');

        //obtenemos los datos del plan actual

        $download = $request->get('edit_download');
        $upload = $request->get('edit_upload');

        if(Burst::check_speed($download,true)==0)
            return Response::json(array('msg'=>'errorDownload'));

        if(Burst::check_speed($upload,true)==0)
            return Response::json(array('msg'=>'errorUpload'));

        if(empty($request->get('edit_iva')))
            $iva = '0';
        else
            $iva = $request->get('edit_iva');

        $newPlanName = $request->get('edit_name');

        ///////////////////// ACTUALIZAMOS A TODOS LOS CLIENTES EN ROUTER ASOCIADOS AL PLAN //////////////////////////////

        //Buscamos a todos los clientes quienes estes asociados a este plan
        $clients = ClientService::where('plan_id','=',$plan_id)->get();

        //recuperamos los routers

        $dts = array();
        foreach ($clients as $fg) {
            $dts[] = $fg->router_id;
        }

        $rou = array_unique($dts);
        $rou = array_values($rou);

        $requestData = $request->all();

        $pl = new GetPlan();
        $plan = $pl->get($plan_id);

        $flag = false;

        for ($i=0; $i < count($rou); $i++) {
            $router = new RouterConnect();
            $con = $router->get_connect($rou[$i]);
            $conf = Helpers::get_api_options('mikrotik');
            // creamos conexion con el router
            $API = new Mikrotik($con['port'],$conf['a'],$conf['t'],$conf['s']);
            $API->debug = $conf['d'];
            if ($API->connect($con['ip'], $con['login'], $con['password'])) {

            } else {
                $flag = true;
            }
            $API->disconnect();
        }

        if($flag) {
            return Response::json(array('msg' => 'errorConnect'));
        }

        event(new UpdatePlanEvent($rou, $plan_id, $requestData, $plan));
        /////////////////// FIN DE ACTUALIZAR A CLIENTES EN ROUTER ASOCIADOS AL PLAN /////////////////////////////////////
        $log = new Slog();
        //actualizamos BD
        $plan = Plan::find($plan_id);
        $plan->name = $newPlanName;
        $plan->title = $request->get('edit_title');
        $plan->download = $download;
        $plan->upload = $upload;
        $plan->cost = $request->get('edit_cost');
        $plan->iva = $iva;
        $plan->burst_limit = $request->get('edit_bl');
        $plan->burst_threshold = $request->get('edit_bth');
        $plan->burst_time = $request->get('edit_bt');
        $plan->priority = $request->get('edit_priority');
        $plan->limitat = $request->get('edit_limitat');
        $plan->no_rules = $request->has('no_rules') ? $request->get('no_rules') : 0;
        $plan->aggregation = $request->get('edit_aggregation', 1);
        $plan->save();

        //save log
	    CommonService::log("#$plan_id Se ha actualizado un plan", $this->username, 'success' , $this->userId);
        return Response::json(array('msg'=>'success'));
    }
}
