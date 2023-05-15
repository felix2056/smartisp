<?php

namespace App\Http\Controllers;
use App\models\Plan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class GetPlanController extends BaseController
{

    public function __construct()
    {
//        $this->beforeFilter('auth');  //bloqueo de acceso
    }

    public function postData(Request $request)
    {
        $plan_id = $request->get('plan');
        $plan = Plan::find($plan_id);

        if (is_null($plan))
            return Response::json(array('success' => false));

        $data = array(
            'success' => true,
            'id' => $plan->id,
            'title' => $plan->title,
            'name' => $plan->name,
            'download' => $plan->download,
            'upload' => $plan->upload,
            'price' => $plan->cost,
            'iva' => $plan->iva,
            'no_rules' => $plan->no_rules,
            //advanced
            'limitat' => $plan->limitat,
            'priority' => $plan->priority,
            'bl' => $plan->burst_limit,
            'bth' => $plan->burst_threshold,
				'bt' => $plan->burst_time,
				'aggregation' => $plan->aggregation
        );


        return Response::json($data);

    }


}
