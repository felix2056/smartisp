<?php

namespace App\Http\Controllers;

use App\DataTables\OnuDataTable;
use App\Http\Requests\Admin\Onu\CreateRequest;
use App\Http\Requests\Admin\Onu\UpdateRequest;
use Illuminate\Http\Request;
use App\libraries\Helpers;
use App\models\GlobalSetting;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\View;
use Yajra\DataTables\Facades\DataTables;
use App\models\OnuType;
use Validator;
class OnuTypeController extends Controller
{
    public function postData(Request $request)
    {
        $zone_id = $request->get('plan');
        $zone = OnuType::find($zone_id);

        if (is_null($zone))
            return Response::json(array('success' => false));

        $data = array(
            'success' => true,
            'id' => $zone->id,
            'pontype' => $zone->pontype,
            'onutype' => $zone->onutype,
            'ethernet_ports' => $zone->ethernet_ports,
            'wifi_ssids' => $zone->wifi_ssids,
            'detail' => $zone->detail,
            // 'voip_ports' => $zone->voip_ports,
            // 'catv' => $zone->catv,
            // 'allow_custom_profiles' => $zone->allow_custom_profiles,
            // 'capability' => $zone->capability,
        );
        return Response::json($data);

    }

    public function getIndex(OnuDataTable $dataTable)
    {
        $id = Auth::user()->id;
        $level = Auth::user()->level;
        $perm = DB::table('permissions')->where('user_id', '=', $id)->get();
        $access = $perm[0]->onu_cpe;
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

            return $dataTable->render('clients.profiles.onu', $permissions);

        } else
            return Redirect::to('admin');

    }

     //metodo para crear planes simple queues
     public function postCreate(CreateRequest $request)
     {
        $OnuType = new OnuType;
        $OnuType->pontype = $request->get('pontype');
        $OnuType->onutype = $request->get('onutype');
        $OnuType->ethernet_ports = $request->get('ethernet_ports');
        $OnuType->wifi_ssids = $request->get('wifi_ssids');
        $OnuType->detail = $request->get('detail');
        // $OnuType->voip_ports= $request->get('voip_ports');
        // $OnuType->catv= $catv;
        // $OnuType->allow_custom_profiles= $allow_custom_profiles;
        // $OnuType->capability= $request->get('capability');
        $OnuType->save();
        return Response::json(array('msg'=>'success'));

     }

       //funcion para elimiar un plan
    public function postDelete(Request $request)
    {

        //buscamos el planen la base de datos segun la id enviada
        $plan_id = $request->get('id');

        $plan = OnuType::find($plan_id);
        //coprobamos si no existe debuelve null
        if(is_null($plan)){
            return Response::json(array('msg'=>'error'));
        }
        $plan->delete();

        return Response::json(array('msg'=>'success'));
    }



    public function postUpdate(UpdateRequest $request)
    {
        set_time_limit(0); //unlimited execution time php

        $plan_id = $request->get('plan_id');

        //obtenemos los datos del plan actual
        $OnuType = OnuType::find($plan_id);
        $OnuType->pontype = $request->get('edit_pontype');
        $OnuType->onutype = $request->get('edit_onutype');
        $OnuType->ethernet_ports = $request->get('edit_ethernet_ports');
        $OnuType->wifi_ssids = $request->get('edit_wifi_ssids');
        $OnuType->detail = $request->get('edit_detail');
        // $OnuType->voip_ports= $request->get('edit_voip_ports');
        // $OnuType->catv= $catv;
        // $OnuType->allow_custom_profiles= $allow_custom_profiles;
        // $OnuType->capability= $request->get('edit_capability');
        $OnuType->save();
        return Response::json(array('msg'=>'success'));

    }

}
