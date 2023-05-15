<?php
namespace App\libraries;
use App\models\Plan;

/**
* Get data  for plan
*/
class GetPlan
{
	//metodo para recuperar informacion del plan
	function get($plan_id){

		$plan = Plan::find($plan_id);
		$data = array(
		'cost' => $plan->cost,
		'iva' => $plan->iva,
		'name' => $plan->name,
		'num_clients' => $plan->num_clients,
		'download' => $plan->download,
		'upload' => $plan->upload,
		'maxlimit' => $plan->upload.'k/'.$plan->download.'k',
		'burst_limit' => $plan->burst_limit,
		'burst_threshold' => $plan->burst_threshold,
		'burst_time' => $plan->burst_time,
		'priority' => $plan->priority,
		'limitat' => $plan->limitat,
		'aggregation' => $plan->aggregation,
		'no_rules' => $plan->no_rules,
		);

		return $data;
	}

}
