<?php
namespace App\libraries;
use App\models\BillCustomer;
use App\models\Client;
use App\models\ClientService;
use App\models\SuspendClient;
use App\Service\CommonService;
use Carbon\Carbon;

/**
* UPDATE CLIENT FUNCTIONS
*/
class UpdateClient
{

	function update($data){
		if($data['odb_id']=="" and $data['onu_id']==""){

			$data['zona_id']=0;

		}
		$client = Client::find($data['client_id']);
		$client->name = $data['name'];
		$client->ip = $data['newtarget'];
		$client->mac = $data['mac'];
		$client->date_in = $data['date_in'];
		$client->plan_id = $data['plan_id'];
		$client->router_id = $data['router_id'];
		$client->email = $data['email'];
		$client->phone = $data['phone'];
		$client->address = $data['dir'];
		$client->coordinates = $data['loc'];
		$client->dni = $data['dni'];
		$client->user_hot = $data['user'];
		$client->typeauth = $data['typeauth'];
		$client->billing_type = $data['billing_type'];
		$client->odb_id = $data['odb_id'];
		$client->onu_id = $data['onu_id'];
		$client->port = $data['port'];
		$client->zona_id = $data['zona_id'];
		$client->onusn = $data['onusn'];
		$client->onmikrotik = 1;

		if(!empty($data['pass'])){
			$en = new Pencrypt();
			$client->pass_hot = $en->encode($data['pass']);
		}
		if(!empty($data['pass2'])){
			$en = new Pencrypt();
			$client->password = $en->encode($data['pass2']);
		}

		$client->save();
		$log = new Slog();
		$counter = new CountClient();

		//actualizamos la fecha de expiracion en la tabla supendClients
		$expiring = SuspendClient::where('client_id','=',$data['client_id'])->get();
		$expiring[0]->expiration = $data['pay_date'];
		$expiring[0]->save();


		if($data['changePlan']){
			$counter->step_down_plan($data['oldplan']);
			$counter->step_up_plan($data['plan_id']);
		}

		if($data['changeRouter']){
			// aumentamos el contador de numero de clientes del router actualmente seleccionado
			$counter->step_up_router($data['router_id']);
			// descontamos el contador de numero de clientes del anterior router
			$counter->step_down_router($data['old_router']);
			// actualizamos el id en suspend client
			$suspcli = SuspendClient::where('client_id','=',$data['client_id'])->get();
			$suspcli[0]->router_id = $data['router_id'];
			$suspcli[0]->save();
		}

		//save log
		$log->save("Se ha actualizado a un cliente:","change",$data['name']);
	}

	function updateService($data) {
		$client = Client::find($data['client_id']);
		$service = ClientService::find($data['service_id']);

        $service->ip = $data['newtarget'];
        $service->mac = $data['mac'];
        $service->plan_id = $data['plan_id'];
        $service->date_in = $data['date_in'];
        $service->router_id = $data['router_id'];
        $service->user_hot = $data['user'];
        $service->typeauth = $data['typeauth'];
		$service->send_invoice = $data['send_invoice'];


		if(!empty($data['pass'])){
			$en = new Pencrypt();
			$service->pass_hot = $en->encode($data['pass']);
		}

        $service->save();

		$log = new Slog();
		$counter = new CountClient();

		//actualizamos la fecha de expiracion en la tabla supendClients
		/*$expiring = SuspendClient::where('client_id','=',$data['client_id'])->where('service_id','=',$data['service_id'])->first();

		$expiring->expiration = $data['pay_date'];
		$expiring->save();*/
		
		$cortadoDetails = CommonService::getServiceCortadoDate($service->client_id);
		
		if($cortadoDetails['invoiceId']) {
			$invoice = BillCustomer::find($cortadoDetails['invoiceId']);
			$invoice->cortado_date = Carbon::parse($data['pay_date'])->format('Y-m-d');
			$invoice->save();
		}


		if($data['changePlan']) {
			$counter->step_down_plan($data['oldplan']);
			$counter->step_up_plan($data['plan_id']);
		}

		if($data['changeRouter']){
			// aumentamos el contador de numero de clientes del router actualmente seleccionado
			$counter->step_up_router($data['router_id']);
			// descontamos el contador de numero de clientes del anterior router
			$counter->step_down_router($data['old_router']);
			// actualizamos el id en suspend client
			$suspcli = SuspendClient::where('client_id','=',$data['client_id'])->get();
			$suspcli[0]->router_id = $data['router_id'];
			$suspcli[0]->save();
		}

		//save log
		$log->save("Se ha actualizado a un cliente:","change",$data['name']);
	}
}
