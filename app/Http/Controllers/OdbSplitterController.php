<?php

namespace App\Http\Controllers;

use App\DataTables\OdbDataTable;
use App\Http\Requests\Admin\Odb\CreateRequest;
use App\Http\Requests\Admin\Odb\UpdateRequest;
use Illuminate\Http\Request;
use App\libraries\Helpers;
use App\libraries\Validator as LibrariesValidator;
use App\models\GlobalSetting;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\View;
use Yajra\DataTables\Facades\DataTables;
use App\models\OdbSplitter;
use App\models\Zone;
use App\models\Client;
use Validator;
class OdbSplitterController extends Controller
{
	public function postData(Request $request)
	{
		$zone_id = $request->get('plan');
		$zone = OdbSplitter::find($zone_id);

		if (is_null($zone))
			return Response::json(array('success' => false));

		$data = array(
			'success' => true,
			'id' => $zone->id,
			'name' => $zone->name,
			'port' => $zone->port,
			'zone_id' => $zone->zone_id,
			'coordinates' => $zone->coordinates,
		);
		return Response::json($data);

	}

	public function getIndex(OdbDataTable $dataTable)
	{
		$id = Auth::user()->id;
		$level = Auth::user()->level;
		$perm = DB::table('permissions')->where('user_id', '=', $id)->get();
		$access = $perm[0]->splitter;
        //control permissions only access super administrator (sa)
		if ($level == 'ad' || $access == true) {

			$global = GlobalSetting::all()->first();

			$GoogleMaps = Helpers::get_api_options('googlemaps');

			if (count($GoogleMaps) > 0 || $global->map_type == 'open_street_map') {
				$key = $GoogleMaps['k'] ?? 1;
			} else {
				$key = 0;
			}
			$zone = Zone::all();

			$permissions = array("clients" => $perm[0]->access_clients, "plans" => $perm[0]->access_plans, "routers" => $perm[0]->access_routers,
				"users" => $perm[0]->access_users, "system" => $perm[0]->access_system, "bill" => $perm[0]->access_pays,
				"template" => $perm[0]->access_templates, "ticket" => $perm[0]->access_tickets, "sms" => $perm[0]->access_sms,
				"reports" => $perm[0]->access_reports,
				"v" => $global->version, "st" => $global->status, "map" => $key,
				"lv" => $global->license, "company" => $global->company,"zone"=>$zone,
                'permissions' => $perm->first(),
                // menu options
                "splitter" => $perm[0]->splitter,
                "onu_cpe" => $perm[0]->onu_cpe,
				"global" => $global
			);

			if (Auth::user()->level == 'ad')
				@setcookie("hcmd", 'kR2RsakY98pHL', time() + 7200, "/", "", 0, true);

			return $dataTable->render('clients.profiles.odb', $permissions);
		} else
		return Redirect::to('admin');

	}

     //metodo para crear planes simple queues
	public function postCreate(CreateRequest $request)
	{
		$plan_name = $request->get('name');

        $OdbSplitter = new OdbSplitter;
        $OdbSplitter->name= $plan_name;
        $OdbSplitter->zone_id= $request->get('zone_id');
        $OdbSplitter->port= $request->get('port');
        $OdbSplitter->coordinates= $request->get('location');
        $OdbSplitter->save();
        return Response::json(array('msg'=>'success'));

	}

       //funcion para elimiar un plan
	public function postDelete(Request $request)
	{

        //buscamos el planen la base de datos segun la id enviada
		$plan_id = $request->get('id');

		$plan = OdbSplitter::find($plan_id);
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
        $plan_name = $request->get('edit_name');

        //obtenemos los datos del plan actual
        $OdbSplitter = OdbSplitter::find($plan_id);
        $OdbSplitter->name=$plan_name;
        $OdbSplitter->zone_id= $request->get('edit_zone_id');
        $OdbSplitter->port= $request->get('edit_port');
        $OdbSplitter->coordinates= $request->get('location_edit');
        $OdbSplitter->save();
        return Response::json(array('msg'=>'success'));

    }

    public function validatePortUsados($id,$port){

    	$Client = Client::select('port')->where('odb_id',$id)->get();
    	$ports="";
    	$conteo=0;
    	$cant_tot = $port;
    	$cant_red = $port;
    	for ($i = 1; $i <= $port; $i++) {
    		$sw=true;
    		foreach ($Client as $key){
    			if($i==$key->port){

    				$cant_red--;
    				$sw=false;
    			}
    		}
    		if($sw){
    			if($conteo==0){
    				$ports = $i;
    			}else{
    				$ports = $ports.",".$i;
    			}
    			$conteo++;
    		}
    	}
    	$status=false;
    	if($cant_tot==$cant_red){
    		$status=true;
    	}
    	return $status;
    }

    public function updateCoordinates(int $id, OdbSplitter $odb_splitter, Request $request)
    {
        $rules = array(
            'coordinates' => 'required|string',
            'id' => 'exists:odb_splitter,id'
        );

        $request->merge(['id' => $id]);
        $validation = LibrariesValidator::make($request->all(), $rules);

        if ($validation->fails()) {
            return Response::json(array(array('msg' => 'error', 'errors' => $validation->getMessageBag()->toArray())));
        }
        $odb_splitter = $odb_splitter->find($id);
        $odb_splitter->coordinates = $request->coordinates;
        $odb_splitter->save();

        Helpers::resetGeoJsonByOdbSplitter($id);
    }

    public function updateMapMarkerIcon(int $id, OdbSplitter $odb_splitter, Request $request)
    {
        $rules = array(
            'map_marker_icon' => 'required|array',
            'id' => 'exists:odb_splitter,id'
        );

        $request->merge(['id' => $id]);
        $validation = LibrariesValidator::make($request->all(), $rules);

        if ($validation->fails()) {
            return Response::json(array(array('msg' => 'error', 'errors' => $validation->getMessageBag()->toArray())));
        }
        $odb_splitter = $odb_splitter->find($id);
        $odb_splitter->map_marker_icon = $request->map_marker_icon['type'] == 'image' ? null : $request->map_marker_icon;
        $odb_splitter->save();
    }

}
