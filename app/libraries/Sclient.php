<?php
namespace App\libraries;
use App\models\SuspendClient;

/**
* Save client expiration service
*/
class Sclient
{
	//metodo para aumentar el contador de clientes de routers
	function exp($id,$router_id,$pay_date){

		$suspend = new SuspendClient();
		$suspend->client_id = $id;
		$suspend->router_id = $router_id;
		$suspend->expiration = $pay_date;
		$suspend->save();
	}

	function up_exp($client_id,$router_id,$pay_date){

		$suspend = SuspendClient::where('client_id','=',$client_id)->get();
		$suspend[0]->router_id = $router_id;
		$suspend[0]->expiration = $pay_date;
		$suspend[0]->save();
	}

}
