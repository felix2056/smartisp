<?php
namespace App\libraries;
use App\models\Plan;
use App\models\Router;

/**
* Change Conuter clients for routers and plans
*/
class CountClient
{
	//metodo para aumentar el contador de clientes de routers
	function step_up_router($id){
		$router = Router::find($id);
        $numClients = $router->clients;
		$numClients = ($numClients + 1);
		$router->clients = $numClients;
		$router->save();
	}

	//metodo para disminuir el contador de clientes de routers
	function step_down_router($id){
		$router = Router::find($id);
		$numClients = $router->clients;
		$numClients = ($numClients - 1);
		$router->clients = $numClients;
		$router->save();
	}

	//metodo para resetear a 0 clientes router
	function reset_router($id){

		$router = Router::find($id);
		$router->clients = 0;
        $router->save();
	}

	//metodo para aumentar el contador de clientes de planes
	function step_up_plan($id){
//		$plan = Plan::where('id', $id);
//		$plan->update(['num_clients' => $plan->num_clients + 1]);
	}

	//metodo para disminuir el contador del clientes de planes
	function step_down_plan($id){
//        $plan = Plan::find($id);
//        $plan->update(['num_clients' => $plan->num_clients - 1]);
	}

}
