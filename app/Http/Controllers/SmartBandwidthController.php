<?php

namespace App\Http\Controllers;
use App\libraries\Slog;
use App\libraries\Validator;
use App\models\Plan;
use App\models\SmartBandwidth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class SmartBandwidthController extends BaseController
{
    public function __construct()
    {
//        $this->beforeFilter('auth');  //bloqueo de acceso
    }


    public function postUpdate(Request $request)
    {
        $friendly_names = array(
            'stcl' => __('app.since'),
            'encl' => __('app.until'),
            'speedx' => __('app.speed'),
            'plan_id_sb' => 'Plan_id'
        );

        //validamos reglas inputs
        $rules = array(
            'stcl' => 'required',
            'encl' => 'required|different:stcl',
            'plan_id_sb' => 'required|integer',
            'speedx' => 'required|numeric|min:0|max:100|integer'
        );

        $validation = Validator::make($request->all(), $rules);
        $validation->setAttributeNames($friendly_names);
        if ($validation->fails())
            return Response::json(['msg' => 'error', 'errors' => $validation->getMessageBag()->toArray()]);

        if ($request->get('act_ser') == 'w') {

            $days = $request->get('days');

            if (count($days) > 0) {

                $Days = array();
                //lunes
                if (in_array("Mon", $days)) $Days[] = "Mon";

                //martes
                if (in_array("Tue", $days)) $Days[] = "Tue";

                //miercoles
                if (in_array("Wed", $days)) $Days[] = "Wed";

                //jueves
                if (in_array("Thu", $days)) $Days[] = "Thu";

                //viernes
                if (in_array("Fri", $days)) $Days[] = "Fri";

                //sabado
                if (in_array("Sat", $days)) $Days[] = "Sat";

                //domingo
                if (in_array("Sun", $days)) $Days[] = "Sun";


                $data = array('days' => $Days);

                $days = json_encode($data, true);


            } else {
                return Response::json(array('msg' => 'emptyDays'));
            }

        }

        if ($request->get('act_ser') == 'd') {
            $days = 'all';
        }

        $plan = Plan::find($request->get('plan_id_sb'));

        $plan_sb = SmartBandwidth::where('plan_id', $request->get('plan_id_sb'))->get();

        $log = new Slog();

        $forall = $request->get('allplans', 0);

        if (count($plan_sb) > 0) {
            //encontro el registro actualizamos
            $plan_sb[0]->start_time = $request->get('stcl');
            $plan_sb[0]->end_time = $request->get('encl');
            $plan_sb[0]->mode = $request->get('act_ser');
            $plan_sb[0]->days = $days;
            $plan_sb[0]->bandwidth = $request->get('speedx');
            $plan_sb[0]->for_all = $forall;
            $plan_sb[0]->save();

            //save log
            $log->save("Se ha actualizado la configiguración de un plan: ", "info", $plan->name);
        } else {
            //creamos
            $new_sb = new SmartBandwidth();
            $new_sb->plan_id = $request->get('plan_id_sb');
            $new_sb->start_time = $request->get('stcl');
            $new_sb->end_time = $request->get('encl');
            $new_sb->mode = $request->get('act_ser');
            $new_sb->days = $days;
            $new_sb->bandwidth = $request->get('speedx');
            $new_sb->for_all = $forall;
            $new_sb->save();

            //save log
            $log->save("Se ha registrado una nueva configuración para el plan: ", "success", $plan->name);
        }

        //actualizar estado todos los planes
        $for_all = SmartBandwidth::where('plan_id', '<>', $request->get('plan_id_sb'))->where('for_all', '=', 1)->get();

        if (count($for_all) > 0) {
            //ponemos a cero la configuración
            $for_all[0]->for_all = 0;
            $for_all[0]->save();
        }

        return Response::json(array('msg' => 'success'));

    }
}
