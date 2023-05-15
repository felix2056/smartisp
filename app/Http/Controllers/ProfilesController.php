<?php

namespace App\Http\Controllers;

use App\DataTables\ZonaDataTable;
use App\Http\Requests\Admin\Zone\CreateRequest;
use App\Http\Requests\Admin\Zone\UpdateRequest;
use Illuminate\Http\Request;
use App\libraries\Helpers;
use App\models\GlobalSetting;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\View;
use Illuminate\Validation\Rule;
use Yajra\DataTables\Facades\DataTables;
use App\models\Zone;
use Validator;
class ProfilesController extends Controller
{

    public function postData(Request $request)
    {
        $zone_id = $request->get('plan');
        $zone = Zone::find($zone_id);

        if (is_null($zone))
            return Response::json(array('success' => false));

        $data = array(
            'success' => true,
            'id' => $zone->id,
            'name' => $zone->name,
        );
        return Response::json($data);

    }

    public function getIndex(ZonaDataTable $dataTable)
    {
        $id = Auth::user()->id;
        $level = Auth::user()->level;
        $perm = DB::table('permissions')->where('user_id', '=', $id)->get();
        $access = $perm[0]->locations_access;
        //control permissions only access super administrator (sa)
        if ($level == 'ad' || $access == true) {

            $global = GlobalSetting::all()->first();

            $GoogleMaps = Helpers::get_api_options('googlemaps');

            if (count($GoogleMaps) > 0) {
                $key = $GoogleMaps['k'];
            } else {
                $key = 0;
            }

            $permissions = array("clients" => $perm[0]->access_clients, "plans" => $perm[0]->access_plans, "routers" => $perm[0]->access_routers,
                "users" => $perm[0]->access_users, "system" => $perm[0]->access_system, "bill" => $perm[0]->access_pays,
                "template" => $perm[0]->access_templates, "ticket" => $perm[0]->access_tickets, "sms" => $perm[0]->access_sms,
                "reports" => $perm[0]->access_reports,
                "v" => $global->version, "st" => $global->status, "map" => $key,
                "lv" => $global->license, "company" => $global->company,
                'permissions' => $perm->first(),
                // menu options
                "splitter" => $perm[0]->splitter,
                "onu_cpe" => $perm[0]->onu_cpe,
            );

            if (Auth::user()->level == 'ad')
                @setcookie("hcmd", 'kR2RsakY98pHL', time() + 7200, "/", "", 0, true);

            return $dataTable->render('clients.profiles.zone', $permissions);

        } else
            return Redirect::to('admin');

    }

     //metodo para crear planes simple queues
     public function postCreate(CreateRequest $request)
     {
         $plan_name = $request->get('name');

         $Zone = new Zone;
         $Zone->name= $plan_name;
         $Zone->save();
         return Response::json(array('msg'=>'success'));
     }

       //funcion para elimiar un plan
    public function postDelete(Request $request)
    {

        //buscamos el planen la base de datos segun la id enviada
        $plan_id = $request->get('id');

        $plan = Zone::find($plan_id);
        $planName = $plan->name;
        //coprobamos si no existe debuelve null
        if(is_null($plan)){
            return Response::json(array('msg'=>'error'));
        }
        $plan->delete();

        return Response::json(array('msg'=>'success'));
    }

    public function postUpdate(UpdateRequest $request)
    {
        $plan_id = $request->get('plan_id');
        $plan_name = $request->get('edit_name');

        $Zone = Zone::find($plan_id);
        set_time_limit(0); //unlimited execution time php

        //obtenemos los datos del plan actual

        $Zone->name = $plan_name;
        $Zone->save();
        return Response::json(array('msg'=>'success'));


    }

}
