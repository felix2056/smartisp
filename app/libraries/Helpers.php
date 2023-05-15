<?php
namespace App\libraries;
use App\models\Client;
use App\models\GlobalApi;
use App\models\ClientService;
use App\models\GlobalSetting;
use App\models\SuspendClient;
use App\models\Template;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

/**
* General helper for de system
*/
class Helpers {
	//metodo para obtener datos de la apis generales del sistema
	public static function get_api_options($api){

		$data = GlobalApi::where('name',$api)->select('options')->get();

		if (count($data)>0) {
			return json_decode($data[0]->options,true);
		}
		else{
			return array();
		}

	}

	public static function get_whatsappcloud_api_options() {

		$data = GlobalApi::where('name', 'whatsappcloudapi')->select('options')->first();

		if (!empty($data)) {
			return json_decode($data->options, true);
		} else {
			return array();
		}
	}

	public static function get_ips($clients){

		$ips='';

		for ($i=0; $i < count($clients); $i++) {

			if($i==0){
				$ips = 	$clients[$i]->ip;
			}else{
				$ips.=','.$clients[$i]->ip;
			}
		}

		return ['ips'=>$ips,'ncl'=>count($clients)];
	}

	public static function getnumcl($router,$control,$plan){

		$numcl = DB::table('client_services')
            ->join('clients', 'clients.id', '=', 'client_services.client_id')
            ->join('plans', 'plans.id', '=', 'client_services.plan_id')
            ->join('routers', 'routers.id', '=', 'client_services.router_id')
            ->join('control_routers','control_routers.router_id','=', 'client_services.router_id')
            ->where('client_services.status','ac')->where('client_services.router_id',$router)
            ->where('control_routers.type_control',$control)
            ->where('client_services.plan_id',$plan)
            ->count();

        return $numcl;

	}

	public static function get_queued_options($word){

		return json_decode($word,true);

	}

	public static function delete_db_tool($router_id){

		$counter = new CountClient();
		$clients = Client::where('router_id',$router_id)->get();

		foreach ($clients as $client) {
			$counter->step_down_plan($client->plan_id);
		}

		//eliminamos todos los clientes asociados al router
        Client::where('router_id', '=', $router_id)->delete();

        SuspendClient::where('router_id', '=', $router_id)->delete();
        //volvemos a 0 el contador de clientes del router
        $counter->reset_router($router_id);

	}

	public static function get_global_settings(){
		return GlobalSetting::first();
	}

	public static function resetGeoJsonByClient(int $client_id) {
		$clientService = ClientService::where('client_id', $client_id)->first();
        $clientService->geo_json = null;
        $clientService->save();

		return true;
	}

	public static function resetGeoJsonByRouter(int $router_id) {
		$clientService = ClientService::where('router_id', $router_id)->first();
        $clientService->geo_json = null;
        $clientService->save();

		return true;
	}

	public static function resetGeoJsonByOdbSplitter(int $odb_splitter_id) {
		$client = Client::where('odb_id', $odb_splitter_id)->first();
        $client->odb_geo_json = null;
        $client->save();

		return true;
	}

	public static function createWhatsappTemplates(string $business_account_id, string $access_token, string $language_code, Template $template) {

		if (empty($template->provider_template_id)) {
			$template_name =  $template->name . '_' . time();

			try {
				$response = Http::withHeaders([
					'Authorization' => 'Bearer ' . $access_token
				])->withBody(
					json_encode([
						"name" => $template_name,
						"language" => $language_code, 
						"category" => "TRANSACTIONAL", 
						"components" => [
							[
								"type" => "BODY", 
								"text" => $template->content, 
								"example" => [
									"body_text" => [
										[
											'fname lname',
											'01/12/1991',
											'25'
										]
									] 
								] 
							]  
						] 
					 ]), 'application/json'
				)->post("https://graph.facebook.com/v15.0/{$business_account_id}/message_templates");
	
				if ($response->status() != 200) {
					$error = config('app.general_error_message');
	
					if (!empty($response->object()->error->message)) {
						$error = $response->object()->error->message;
					}
					if (!empty($response->object()->error->error_user_msg)) {
						$error = $response->object()->error->error_user_msg;
					}
	
					throw new \Exception($error);	
				}
	
				return [
					'id' => $response->object()->id,
					'name' => $template_name
				];
			} catch (\Exception $e) {
				throw new \Exception($e->getMessage());
			}
		}

		return self::updateWhatsappTemplates($template, $access_token);
	}

	public static function updateWhatsappTemplates(Template $template, $access_token) {
		
		try {
			$response = Http::withHeaders([
				'Authorization' => 'Bearer ' . $access_token
			])->withBody(
				json_encode([
					"components" => [
						[
							"type" => "BODY", 
							"text" => $template->content, 
							"example" => [
								"body_text" => [
									[
										'fname lname',
										'01/12/1991',
										'25'
									]
								] 
							] 
						]  
					]
				 ]), 'application/json'
			)->post("https://graph.facebook.com/v15.0/{$template->provider_template_id}");

			if ($response->status() != 200) {
				if (!empty($response->object()->error->message)) {
					$error = $response->object()->error->message;
				}
				if (!empty($response->object()->error->error_user_msg)) {
					$error = $response->object()->error->error_user_msg;
				}

				throw new \Exception($error);
			}

			return [
				'id' => $template->provider_template_id,
				'name' => $template->provider_template_name
			];
		} catch (\Exception $e) {
			throw new \Exception($e->getMessage());
		}
	}

}
