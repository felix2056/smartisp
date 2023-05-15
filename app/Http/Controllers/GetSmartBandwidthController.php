<?php

namespace App\Http\Controllers;
use App\models\SmartBandwidth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class GetSmartBandwidthController extends BaseController
{

    public function __construct()
    {
//        $this->beforeFilter('auth');  //bloqueo de acceso
    }

    public function postData(Request $request)
    {

        $sb_data = SmartBandwidth::where('plan_id', $request->get('idp', 0))->get();

        if (count($sb_data) > 0) {

            $days = array();

            //mostramos los datos
            if ($sb_data[0]->days == 'all') {

                $days = array('days' => ['Mon', 'Tue', 'Wed', 'Thur', 'Fri', 'Sat', 'Sun']);

            } else {

                $days = json_decode($sb_data[0]->days, true);
            }


            $df = $days['days'];


            $data = array(
                'success' => true,
                'mode' => $sb_data[0]->mode,
                'star_time' => $sb_data[0]->start_time,
                'end_time' => $sb_data[0]->end_time,
                'bandwidth' => $sb_data[0]->bandwidth,
                'for_all' => $sb_data[0]->for_all,
                //days
                'days' => $df
            );


        } else {
            //no se encontro informacion de configuracion reseteamos a cero el form
            $data = array(
                'success' => true,
                'mode' => 'd',
                'star_time' => '20:00:00',
                'end_time' => '6:00:00',
                'bandwidth' => 0,
                'for_all' => 0,
                //days
                'days' => ''

            );

        }


        return Response::json($data);

    }

    public function getDays()
    {


        return Response::json(array("results" => [["id" => "mon", "text" => "Lunes", "selected" => true], ["id" => "tue", "text" => "Martes", "selected" => true], ["id" => "wed", "text" => "Miercoles", "selected" => true]]));


    }

}
