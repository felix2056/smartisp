<?php

namespace App\Http\Controllers;
use App\Console\Commands\SetMikrotikRulesForBlockedClients;
use App\libraries\RouterConnect;
use App\models\SmartBandwidth;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\libraries\MultiplySpeed;
use App\libraries\MySQLBackup;
use App\libraries\CountClient;
use App\libraries\RocketCore;
use App\libraries\Helpers;
use App\libraries\Burst;
use App\libraries\GetPlan;
use App\libraries\Mikrotik;
use App\libraries\Mkerror;

use App\models\GlobalSetting;
use App\models\Plan;
use App\models\QueuedProcess;
use App\models\Client;
use App\models\ControlRouter;
use App\Console\Commands\GenerateInvoice;
use App\Console\Commands\ChangeStatusOnNegativeBalance;
class AutomaticTasksController extends BaseController
{

    public function startGenerateinvoice()
    {
        // No need of this
//        $GenerateInvoice = new GenerateInvoice;
//        $GenerateInvoice->handle();

        // need to uncomment after test
//        $ChangeStatusOnNegativeBalance = new ChangeStatusOnNegativeBalance;
//        $ChangeStatusOnNegativeBalance->handle();

        // for set mikrotik rules cortado clients
        $ChangeStatusOnNegativeBalance = new SetMikrotikRulesForBlockedClients();
        $ChangeStatusOnNegativeBalance->handle();

        return redirect('finance/invoices');
    }

    public function startbackup()
    {

        //verificamos si esta activo las copias de seguridad

        set_time_limit(0); //unlimited execution time php
        $global = GlobalSetting::all()->first();

        if ($global->backups == '1') {

         if (date('H:i') == date('H:i', strtotime($global->create_copy))) {

                $host = DB::connection()->getConfig('host');
                $user = DB::connection()->getConfig('username');
                $password = DB::connection()->getConfig('password');
                $database = DB::connection()->getConfig('database');
                $Dump = new MySQLBackup($host, $user, $password, $database);
                $Dump->setFilename('assets/backups/backup_smartisp_' . date('d-m-Y'));
                $Dump->setCompress('zip'); // zip | gz | gzip
                $Dump->setDownload(false);
                $Dump->dump();

           }
        }


    }

    //metodo para procesar colas
    public function startqueues()
    {

        //pre clasificamos la información segun el tipo
        $queues = QueuedProcess::all(); //procesos para mikrotik
        //otras pre clasificaciones


        if (count($queues) > 0) {
            $counter = new CountClient();
            $rocket = new RocketCore();

            foreach ($queues as $queue) {

                switch ($queue->type) {
                    case 'mikrotik':
                        # mikrotik queues process

                        //recover client information
                    $value = Helpers::get_queued_options($queue->values);
                    $client = Client::find($value['c']);

                        //GET all data for API
                    $conf = Helpers::get_api_options('mikrotik');
                    $router = new RouterConnect();
                    $con = $router->get_connect($client->router_id);
                    $config = ControlRouter::where('router_id', '=', $client->router_id)->get();

                    $typeconf = $config[0]->type_control;
                    $arp = $config[0]->arpmac;
                    $adv = $config[0]->adv;
                    $dhcp = $config[0]->dhcp;

                    if ($adv == 1) {
                        $drop = 0;
                    } else {
                        $drop = 1;
                    }

                    $num_cli = Helpers::getnumcl($client->router_id, $typeconf, $client->plan_id);

                    $pl = new GetPlan();
                    $plan = $pl->get($client->plan_id);

                        //opcion avanzada burst del plan
                    $burst = Burst::get_all_burst($plan['upload'], $plan['download'], $plan['burst_limit'], $plan['burst_threshold'], $plan['limitat']);

                    $data = array(
                            //standar data
                        'name' => $client->name,
                        'user' => $client->user_hot,
                        'status' => $client->status,
                        'arp' => $arp,
                        'adv' => $adv,
                        'dhcp' => $dhcp,
                        'drop' => $drop,
                        'mac' => $client->mac,
                        'lan' => $con['lan'],
                        'namePlan' => $plan['name'],
                        'num_cl' => $num_cli,
                        'speed_down' => $plan['download'],
                        'speed_up' => $plan['upload'],
                            //advanced for pcq
                        'priority_a' => $plan['priority'],
                        'rate_down' => $plan['download'] . 'k',
                        'rate_up' => $plan['upload'] . 'k',
                        'burst_rate_down' => $burst['bld'],
                        'burst_rate_up' => $burst['blu'],
                        'burst_threshold_down' => $burst['btd'],
                        'burst_threshold_up' => $burst['btu'],
                        'limit_at_down' => $burst['lim_at_down'],
                        'limit_at_up' => $burst['lim_at_up'],
                        'burst_time' => $plan['burst_time']
                    );


                    $API = new Mikrotik($con['port'], $conf['a'], $conf['t'], $conf['s']);
                    $API->debug = $conf['d'];

                    switch ($queue->process) {
                        case 'unlock':
                                # unlock client

                        if ($API->connect($con['ip'], $con['login'], $con['password'])) {

                            switch ($typeconf) {
                                case 'no':
                                            # no shaping only arp, adv, drop

                                $error = new Mkerror();
                                            //intentamos desbloquear al cliente
                                $rocket->set_basic_config($API, $error, $data, $client->ip, null, 'unblock');

                                $counter->step_up_plan($client->plan_id);
                                            //activamos el servicio al cliente
                                $client->status = 'ac';
                                $client->save();
                                            //eliminanos la cola
                                $queue->delete();

                                break;

                                case 'sq':
                                            # simple queues unlock

                                            //intentamos desbloquear al cliente
                                $rocket->block_simple_queues($API, $data, $client->ip);

                                $counter->step_up_plan($client->plan_id);
                                            //activamos el servicio al cliente
                                $client->status = 'ac';
                                $client->save();
                                            //eliminanos la cola
                                $queue->delete();

                                break;

                                case 'ho':
                                            # hotspot users profiles
                                            //intentamos desbloquear al cliente
                                $rocket->block_hotspot($API, $data, $client->ip);

                                $counter->step_up_plan($client->plan_id);
                                            //activamos el servicio al cliente
                                $client->status = 'ac';
                                $client->save();
                                            //eliminanos la cola
                                $queue->delete();

                                break;

                                case 'dl':
                                            # dhcp leases
                                            //intentamos desbloquear al cliente
                                $rocket->block_dhcp_lease($API, $data, $client->ip);

                                $counter->step_up_plan($client->plan_id);
                                            //activamos el servicio al cliente
                                $client->status = 'ac';
                                $client->save();
                                            //eliminanos la cola
                                $queue->delete();

                                break;

								case 'pp':
								case 'ps':
											# pppoe secrets
											//intentamos desbloquear al cliente
								$rocket->block_ppp($API,$data,$client->ip);

                                $counter->step_up_plan($client->plan_id);
                                            //activamos el servicio al cliente
                                $client->status = 'ac';
                                $client->save();
                                            //eliminanos la cola
                                $queue->delete();

                                break;

                                case ($typeconf == 'pc' || $typeconf == 'ha' || $typeconf == 'pa'):
                                            # all pcq address list
                                            //intentamos desbloquear al cliente
                                $rocket->block_pcq($API, $data, $client->ip);

                                $counter->step_up_plan($client->plan_id);
                                            //activamos el servicio al cliente
                                $client->status = 'ac';
                                $client->save();
                                            //eliminanos la cola
                                $queue->delete();

                                break;
                                    }//end switch

                                    $API->disconnect();

                                }//end if connect to api


                                break;

                            # other options for mikrotik ......


                        }//end switch

                        break;

                }//end switch

            }//end for each

        }//end if count


    }//end method

    //metodo para ejecutar smart bandwidth
    public function startsmartbandwidth()
    {

        set_time_limit(0); //unlimited execution time php

        //procesamos tiempos
        //recuperamos todas las configuraciones para todos activas sb
        $SB = SmartBandwidth::where('bandwidth', '>', 0)->where('for_all', 1)->get();
        $now = Carbon::now();

        if (count($SB) > 0) {


            $start = Carbon::create($SB['start_time']); // comienza
            $start_finish = (clone $start)->addMinutes(15); // hasta pasados los 15 minutos desde que comenzo
            $end = Carbon::create($SB['end_time']);
            if($start > $end) // si termina al otro dia, le agregamos un dia
                $end = $end->addDay();

            $end_finish = (clone $end)->addMinutes(15); // hasta pasados los 15 minutos desde que termino

            //aplicamos a todos los planes
         $plan = Plan::all();
         $SMB = new MultiplySpeed();

         switch ($SB[0]->mode) {

            case 'w':
                    # every weekend
            $search_day = json_decode($SB[0]['days'], true);

                    //verificamos primero los días de activación
            if (in_array("Mon", $search_day['days']) == true && date('D') == 'Mon') {
                        # activar los lunes
                if(($now->between($start, $start_finish))){
                                # Activamos la velocidad extra para todos los planes...
                        foreach ($plan as $pl) {
                            $SMB->multiply_process('multiply', $pl->id, $SB[0]->bandwidth);
                        }//end foreach
                    }
                    if (($now->between($end, $end_finish))) {
                            # Restauramos la velocidad original a todos los planes
                            foreach ($plan as $pl) {
                                $SMB->multiply_process('restore', $pl->id, $SB[0]->bandwidth);
                            }//end foreach
                        }
                    }

                    if (in_array("Tue", $search_day['days']) == true && date('D') == 'Tue') {
                        # activar los martes
                        if(($now->between($start, $start_finish))){
                            # Activamos la velocidad extra...
                            foreach ($plan as $pl) {
                                $SMB->multiply_process('multiply', $pl->id, $SB[0]->bandwidth);
                            }//end foreach
                        }

                        //verificamos la hora de finalizacion
                        if (($now->between($end, $end_finish))) {
                            # Restauramos la velocidad original
                            foreach ($plan as $pl) {
                                $SMB->multiply_process('restore', $pl->id, $SB[0]->bandwidth);
                            }//end foreach
                        }
                    }

                    if (in_array("Wed", $search_day['days']) == true && date('D') == 'Wed') {
                        # activar los miercoles
                        if(($now->between($start, $start_finish))){
                            # Activamos la velocidad extra...
                            foreach ($plan as $pl) {
                                $SMB->multiply_process('multiply', $pl->id, $SB[0]->bandwidth);
                            }//end foreach
                        }

                        //verificamos la hora de finalizacion
                        if (($now->between($end, $end_finish))) {
                            # Restauramos la velocidad original
                            foreach ($plan as $pl) {
                                $SMB->multiply_process('restore', $pl->id, $SB[0]->bandwidth);
                            }//end foreach
                        }
                    }

                    if (in_array("Thu", $search_day['days']) == true && date('D') == 'Thu') {
                        # activar los jueves
                        if(($now->between($start, $start_finish))){
                            # Activamos la velocidad extra...
                            foreach ($plan as $pl) {
                                $SMB->multiply_process('multiply', $pl->id, $SB[0]->bandwidth);
                            }//end foreach
                        }

                        //verificamos la hora de finalizacion
                        if (($now->between($end, $end_finish))) {
                            # Restauramos la velocidad original
                            foreach ($plan as $pl) {
                                $SMB->multiply_process('restore', $pl->id, $SB[0]->bandwidth);
                            }//end foreach
                        }
                    }

                    if (in_array("Fri", $search_day['days']) == true && date('D') == 'Fri') {
                        # activar los viernes
                        if(($now->between($start, $start_finish))){
                            # Activamos la velocidad extra...
                            foreach ($plan as $pl) {
                                $SMB->multiply_process('multiply', $pl->id, $SB[0]->bandwidth);
                            }//end foreach
                        }

                        //verificamos la hora de finalizacion
                        if (($now->between($end, $end_finish))) {
                            # Restauramos la velocidad original
                            foreach ($plan as $pl) {
                                $SMB->multiply_process('restore', $pl->id, $SB[0]->bandwidth);
                            }//end foreach
                        }
                    }

                    if (in_array("Sat", $search_day['days']) == true && date('D') == 'Sat') {
                        # activar los sabados
                        if(($now->between($start, $start_finish))){
                            # Activamos la velocidad extra...
                            foreach ($plan as $pl) {
                                $SMB->multiply_process('multiply', $pl->id, $SB[0]->bandwidth);
                            }//end foreach
                        }

                        //verificamos la hora de finalizacion
                        if (($now->between($end, $end_finish))) {
                            # Restauramos la velocidad original
                            foreach ($plan as $pl) {
                                $SMB->multiply_process('restore', $pl->id, $SB[0]->bandwidth);
                            }//end foreach
                        }
                    }

                    if (in_array("Sun", $search_day['days']) == true && date('D') == 'Sun') {
                        # activar los domingos
                        if(($now->between($start, $start_finish))){
                            # Activamos la velocidad extra...
                            foreach ($plan as $pl) {
                                $SMB->multiply_process('multiply', $pl->id, $SB[0]->bandwidth);
                            }//end foreach
                        }

                        //verificamos la hora de finalizacion
                        if (($now->between($end, $end_finish))) {
                            # Restauramos la velocidad original
                            foreach ($plan as $pl) {
                                $SMB->multiply_process('restore', $pl->id, $SB[0]->bandwidth);
                            }//end foreach
                        }
                    }


                    break;

                    case 'd':
                    # every day

                        if(($now->between($start, $start_finish))){
                        # Activamos la velocidad extra a todos los planes...
                        foreach ($plan as $pl) {
                            $SMB->multiply_process('multiply', $pl->id, $SB[0]->bandwidth);
                        }//end foreach
                    }

                    //verificamos la hora de finalizacion
                        if (($now->between($end, $end_finish))) {
                        # Restauramos la velocidad original
                        foreach ($plan as $pl) {
                            $SMB->multiply_process('restore', $pl->id, $SB[0]->bandwidth);
                        }//end foreach
                    }

                    break;
            }//end switch


        } else {

            $sb = SmartBandwidth::where('bandwidth', '>', 0)->where('for_all', 0)->get();
            if (count($sb) > 0) {

               $SMB = new MultiplySpeed();

                //iteramos todas las configuraciones
                foreach ($sb as $SB) {

                    //tolerance 3 minutes
                $hrs = date('H:i') . ':00';
                $newDate = strtotime('-1 minute', strtotime($hrs));
                $hrs2 = date('H:i', $newDate) . ':00';
                $newDate = strtotime('-1 minute', strtotime($hrs2));
                $hrs3 = date('H:i', $newDate) . ':00';


               $start = Carbon::create($SB['start_time']); // comienza
               $start_finish = (clone $start)->addMinutes(15); // hasta pasados los 15 minutos desde que comenzo
               $end = Carbon::create($SB['end_time']);
               if($start > $end) // si termina al otro dia, le agregamos un dia
                   $end = $end->addDay();

               $end_finish = (clone $end)->addMinutes(15); // hasta pasados los 15 minutos desde que termino

                    switch ($SB->mode) {


                    case 'w':
                            # ejecucion por dias de la semana
                    $search_day = json_decode($SB['days'], true);
                    $pname = Plan::find($SB->plan_id);

                            //verificamos primero los días de activación
                    if (in_array("Mon", $search_day['days']) == true && date('D') == 'Mon') {
                                # activar los lunes
                        if(($now->between($start, $start_finish))){
                                    # Activamos la velocidad extra...
                            $SMB->multiply_process('multiply', $SB->plan_id, $SB->bandwidth);
                        }
                                //verificamos la hora de finalizacion de la velocidad extra
                        if (($now->between($end, $end_finish))) {
                                    # Restauramos la velocidad original
                            $SMB->multiply_process('restore', $SB->plan_id, $SB->bandwidth);
                        }
                    }

                    if (in_array("Tue", $search_day['days']) == true && date('D') == 'Tue') {
                                # activar los martes
                        if(($now->between($start, $start_finish))){
                                    # Activamos la velocidad extra...
                            $SMB->multiply_process('multiply', $SB->plan_id, $SB->bandwidth);
                        }
                                //verificamos la hora de finalizacion
                        if (($now->between($end, $end_finish))) {
                                    # Restauramos la velocidad original
                            $SMB->multiply_process('restore', $SB->plan_id, $SB->bandwidth);

                        }
                    }

                    if (in_array("Wed", $search_day['days']) == true && date('D') == 'Wed') {
                                # activar los miercoles
                        if(($now->between($start, $start_finish))){
                                    # Activamos la velocidad extra...
                            $SMB->multiply_process('multiply', $SB->plan_id, $SB->bandwidth);

                        }
                                //verificamos la hora de finalizacion
                        if (($now->between($end, $end_finish))) {
                                    # Restauramos la velocidad original
                            $SMB->multiply_process('restore', $SB->plan_id, $SB->bandwidth);
                        }
                    }

                    if (in_array("Thu", $search_day['days']) == true && date('D') == 'Thu') {
                                # activar los jueves
                        if(($now->between($start, $start_finish))){
                                    # Activamos la velocidad extra...
                            $SMB->multiply_process('multiply', $SB->plan_id, $SB->bandwidth);

                        }
                                //verificamos la hora de finalizacion
                        if (($now->between($end, $end_finish))) {
                                    # Restauramos la velocidad original
                            $SMB->multiply_process('restore', $SB->plan_id, $SB->bandwidth);
                        }
                    }

                    if (in_array("Fri", $search_day['days']) == true && date('D') == 'Fri') {
                                # activar los viernes
                        if(($now->between($start, $start_finish))){
                                    # Activamos la velocidad extra...
                            $SMB->multiply_process('multiply', $SB->plan_id, $SB->bandwidth);

                        }

                                //verificamos la hora de finalizacion
                        if (($now->between($end, $end_finish))) {
                                    # Restauramos la velocidad original
                            $SMB->multiply_process('restore', $SB->plan_id, $SB->bandwidth);

                        }
                    }

                        if (in_array("Sat", $search_day['days']) == true && date('D') == 'Sat') {
                                # activar los sabados
                            if(($now->between($start, $start_finish))){
                                    # Activamos la velocidad extra...
                                $SMB->multiply_process('multiply', $SB->plan_id, $SB->bandwidth);
                            }
                                //verificamos la hora de finalizacion
                            if (($now->between($end, $end_finish))) {
                                    # Restauramos la velocidad original
                            $SMB->multiply_process('restore', $SB->plan_id, $SB->bandwidth);
                        }
                    }

                    if (in_array("Sun", $search_day['days']) == true && date('D') == 'Sun') {
                                # activar los domingos
                        if(($now->between($start, $start_finish))){
                                    # Activamos la velocidad extra...
                            $SMB->multiply_process('multiply', $SB->plan_id, $SB->bandwidth);
                        }
                                //verificamos la hora de finalizacion
                        if (($now->between($end, $end_finish))) {
                                    # Restauramos la velocidad original
                            $SMB->multiply_process('restore', $SB->plan_id, $SB->bandwidth);
                        }
                    }


                    break;

                    case 'd':

                        // si estamos dentro de los primeros 15 minutos de conf del start time, ejecutamos el multiply. Sino, no lo ejecutamos nada
                        if(($now->between($start, $start_finish))){
                            # Activamos la velocidad extra...d
                            $SMB->multiply_process('multiply', $SB->plan_id, $SB->bandwidth);
                        }

                        if (($now->between($end, $end_finish))) {
                            # Restauramos la velocidad original
                            $SMB->multiply_process('restore', $SB->plan_id, $SB->bandwidth);
                        }

                    break;

                    }//end switch


                }//end foreach

            }//end if count

        }//end else


    }//end method

}
