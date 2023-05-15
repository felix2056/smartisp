<?php

namespace App\Console\Commands;

use App\Http\Controllers\AutomaticTasksController;
use App\libraries\Burst;
use App\libraries\Chkerr;
use App\libraries\GetPlan;
use App\libraries\Helpers;
use App\libraries\Intersep;
use App\libraries\Mikrotik;
use App\libraries\Mkerror;
use App\libraries\Pencrypt;
use App\libraries\RocketCore;
use App\libraries\RouterConnect;
use App\models\AddressRouter;
use App\models\Client;
use App\models\ClientService;
use App\models\ControlRouter;
use App\models\GlobalSetting;
use App\models\Network;
use App\models\Plan;
use App\models\radius\Radreply;
use App\models\Router;
use App\models\SmartBandwidth;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;

class UpdateRouterStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'router:status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This cron will update router status every hour';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        Log::debug('start update-router');
        ini_set('max_execution_time', 0); //unlimited execution time php
        Log::debug('start-update-router');

     /*   $taskIncreaseBandwidth = new AutomaticTasksController();
        $taskIncreaseBandwidth->startsmartbandwidth();
        die();*/

        $rout = Router::all();

        if($rout->count() == 0) {
            echo " No router found";
        } else {

            $encrypt = new Pencrypt();
            foreach($rout as $router) {

                if($router->connection == 1) {
                    $stat = 'nc';
                    $model = 'none';
                }
                else{
                    $password  = $router->password;
                    $password = $encrypt->decode($password);
                    $conf = Helpers::get_api_options('mikrotik');
                    $API = new Mikrotik($router->port,2,$conf['t'],$conf['s']);
                    $API->debug = $conf['d'];

                    if ($API->connect($router->ip, $router->login, $password)) {
                        $API->write('/system/resource/print',true);
                        $READ = $API->read(false);
                        $ARRAY = $API->parseResponse($READ);
                        $stat = 'on';
                        $model = $ARRAY[0]['board-name'];
                        $API->disconnect();

                    }else{
                        $stat = 'off';
                        $model = 'none';
                    }

                }

                $router->status = $stat;
                $router->model = $model;
                $router->save();

                $type = ControlRouter::where('router_id', '=', $router->id)->first();
                $routers = new RouterConnect();
                $con = $routers->get_connect($router->id);

                //GET all data for API
                $conf = Helpers::get_api_options('mikrotik');
                $API = new Mikrotik($con['port'], $conf['a'], $conf['t'], $conf['s']);
                $API->debug = $conf['d'];

                $global = GlobalSetting::all()->first();
                $debug = $global->debug;

	            ClientService::with('client')
		            ->where('router_id', $router->id)
		            ->where('status', 'ac')
		            ->chunkById(5, function($clients) use($router, $type, $con, $debug, $API)
		            {
		            	$process = new Chkerr();

		                foreach($clients as $client) {
			                $this->info($client->client->name);
			                $planData = Plan::with('smart_bandwidth')->find($client->plan_id);

			                $hrs = date('H:i') . ':00';
			                $newDate = strtotime('-1 minute', strtotime($hrs));
			                $hrs2 = date('H:i', $newDate) . ':00';
			                $newDate = strtotime('-1 minute', strtotime($hrs2));
			                $hrs3 = date('H:i', $newDate) . ':00';

			                $now = Carbon::now();
			                $start = Carbon::create($planData->smart_bandwidth->start_time); // comienza
			                $start_finish = (clone $start)->addMinutes(15); // hasta pasados los 15 minutos desde que comenzo
			                $end = Carbon::create($planData->smart_bandwidth->end_time);
			                if ($start > $end) // si termina al otro dia, le agregamos un dia
				                $end = $end->addDay();
			                switch ($planData->smart_bandwidth->mode) {
				                case 'w':
					                $search_day = json_decode($planData->smart_bandwidth->days, true);

					                if (in_array(date('D'), $search_day['days']) == true) {
						                # activar los domingos

						                if ($now->between($start, $end)) {
							                # Activamos la velocidad extra...
		//                                break;

						                } else {
							                $this->checkMikrotik($client->client, $client, $router, $type, $con, $debug, $API, $process);
						                }
					                } else {
						                $this->checkMikrotik($client->client, $client, $router, $type, $con, $debug, $API, $process);
					                }
					                break;
				                case 'd':

					                if ($now->between($start, $end)) {
						                # Activamos la velocidad extra...
		//                                break;

					                } else {
						                $this->checkMikrotik($client->client, $client, $router, $type, $con, $debug, $API, $process);
					                }

					                break;
			                }
		                }
	                });


            }

            /**Fix update increaseBandwidth clients
                * En este proceso no estaba realizado, por lo cual lo mantenemos así ya que agregarlo es mucho codigo sin sentido.
                * El proceso esta hecho en AutomaticTasksController con el metodo startsmartbandwidth. Por lo cual, lo invocamos luego de este command
                * Por ahora no lo ejecutamos. Esperamos a ver si desde AutomaticTasksController Funciona ok
             */

          /*  $taskIncreaseBandwidth = new AutomaticTasksController();
            $taskIncreaseBandwidth->startsmartbandwidth();*/

        }
    }

    public function checkMikrotik($client, $service, $router, $type, $con, $debug, $API, $process)
    {

        $pl = new GetPlan();
        $plan = $pl->get($service->plan_id);

        //get  burst profiles
        $burst = Burst::get_all_burst($plan['upload'], $plan['download'], $plan['burst_limit'], $plan['burst_threshold'], $plan['limitat']);

        $bt = $plan['burst_time'] . '/' . $plan['burst_time'];
        $bl = $burst['blu'] . '/' . $burst['bld'];
        $bth = $burst['btu'] . '/' . $burst['btd'];
        $limit_at = $burst['lim_at_up'] . '/' . $burst['lim_at_down'];
        $priority = $plan['priority'] . '/' . $plan['priority'];
        $en = new Pencrypt();

        $num_cli = ClientService::where('plan_id', '=', $service->plan_id)->where('status', 'ac')->where('router_id', $router->id)->count();


        $data = array(
            'name' => $client->name.'_'.$service->id,
            'mac' => $service->mac,
            'arp' => $type->arpmac,
            'adv' => $type->adv,
            'dhcp' => $type->dhcp,
            'speed_down' => $plan['download'],
            'lan' => $con['lan'],
            'maxlimit' => $plan['maxlimit'],
            'bl' => $bl,
            'bth' => $bth,
            'speed_up' => $plan['upload'],
            'bt' => $bt,
            'limit_at' => $limit_at,
            'priority_a' => $plan['priority'],
            'drop' => 0,
            //advanced for pcq
            'num_cl' => $num_cli,
            'rate_down' => $plan['download'], //
            'rate_up' => $plan['upload'], //
            'burst_rate_down' => $burst['bld'],
            'burst_rate_up' => $burst['blu'],
            'burst_threshold_down' => $burst['btd'],
            'burst_threshold_up' => $burst['btu'],
            'limit_at_down' => $burst['lim_at_down'],
            'limit_at_up' => $burst['lim_at_up'],
            'burst_time' => $plan['burst_time'],
            'billing_type' => $service->billing_type,
            //end pcq
            'priority' => $priority,
            'comment' => 'SmartISP - ' . $plan['name'],
            'ip' => $service->ip,
            'plan_id' => $service->plan_id,
            'namePlan' => $plan['name'],
            'aggregation' => $plan['aggregation'],
            'limitat' => $plan['limitat'],
            'burst_limit' => $plan['burst_limit'],
            'burst_threshold' => $plan['burst_threshold'],
            'router_id' => $router->id,
            'email' => $client->email,
            'phone' => $client->phone,
            'user' => Intersep::replace($service->user_hot, ''),
            'pass' => Intersep::replace($en->decode($service->pass_hot), ''),
            'old_user' => $service->user_hot,
            'changePlan' => false,
            'changeRouter' => false,
            'typeauth' => $service->typeauth,
            'client_id' => $client->id,
            'old_name' => $client->name.'_'.$service->id,
            'profile' => $service->plan->name,
            'oldplan' => $service->plan_id,
            'old_router' => $service->router_id,
            'newtarget' => $service->ip,
            'tree_priority' => $service->tree_priority,
	        'no_rules' => $plan['no_rules'],
        );
        //Verificamos el tipo de control
        switch ($type->type_control) {

            case 'no': //no shaping control only arp, adv or drop
                if ($API->connect($con['ip'], $con['login'], $con['password'])) {
                    $rocket = new RocketCore();
                    $error = new Mkerror();

                    $UPDATE = $rocket->set_basic_config($API, $error, $data, $service->ip, $data['newtarget'], 'update', $debug);

                    if ($debug == 1) {
                        if ($UPDATE != false) {
                            return $UPDATE;
                        }
                    }

                } else {
                    echo "Case No fault";
                }

                break;

            case 'sq': //Control simple Queues
                # verificamos si el cliente sera actualizado en el router

                if ($API->connect($con['ip'], $con['login'], $con['password'])) {

                    $rocket = new RocketCore();
                    $UPDATE = $rocket->update_simple_queues($API, $data, $service->ip, $data['newtarget'], $debug);

                    if ($debug == 1) {
                        if ($process->check($UPDATE)) {
                            $API->disconnect();
                            return $process->check($UPDATE);
                        }
                    }

                } else {
                     echo "No connection ";
                }

                break;

            case 'st': //Simple queues with tree

                if($API->connect($con['ip'], $con['login'], $con['password'])){

                    //actualizamos el estado del ip en IP/Redes
                    $rocket = new RocketCore();

                    $UPDATE = $rocket->update_simple_queue_with_tree($API,$data,$service->ip,$data['newtarget'],$debug);

                    //return $UPDATE; //comentar

                    if ($debug==1) {
                        if($process->check($UPDATE)){
                            $API->disconnect();
                            return $process->check($UPDATE);
                        }
                    }

                }
                else{
                    echo "No connection ";
                }

                break;

            case 'dl': //control DHCP leases

                if ($API->connect($con['ip'], $con['login'], $con['password'])) {

                    $rocket = new RocketCore();
                    $UPDATE = $rocket->update_dhcp_leases_user($API, $data, $service->ip, $data['newtarget'], $debug);

                    if ($debug == 1) {
                        if ($process->check($UPDATE)) {
                            $API->disconnect();
                            return $process->check($UPDATE);
                        }
                    }
                } else {
                    echo "No connection";
                }

                break;
            case 'pp': //control PPP

//                            if ($API->connect($con['ip'], $con['login'], $con['password'])) {
//
//                                //get gateway for addres
//                                $network = Network::where('ip', $data['newtarget'])->get();
//
//                                if (count($network) == 0) {
//                                    return $process->show('error_no_address');
//                                }
//
//                                $gat = AddressRouter::find($network[0]->address_id);
//
//                                $rocket = new RocketCore();
//
//                                $UPDATE = $rocket->update_ppp_user($API, $data, $client->ip, $data['newtarget'], $gat->gateway, $debug);
//
//                                if ($debug == 1) {
//                                    if ($process->check($UPDATE)) {
//                                        $API->disconnect();
//                                        return $process->check($UPDATE);
//                                    }
//                                }
//                            } else {
//                                echo "No connection";
//                            }

                break;
            case 'pa': //control ppp pcq

//                            if ($API->connect($con['ip'], $con['login'], $con['password'])) {
//
//                                //get gateway for addres
//                                $network = Network::where('ip', $data['newtarget'])->get();
//
//                                if (count($network) == 0) {
//                                    return $process->show('error_no_address');
//                                }
//
//                                $gat = AddressRouter::find($network[0]->address_id);
//
//                                $rocket = new RocketCore();
//
//                                $UPDATE = $rocket->update_ppp_secrets_pcq($API, $data, $client->ip, $data['newtarget'], $gat->gateway, $debug);
//
//                                if ($debug == 1) {
//                                    if ($process->check($UPDATE)) {
//                                        $API->disconnect();
//                                        return $process->check($UPDATE);
//                                    }
//                                }
//
//                            } else {
//                                echo "No connection";
//                            }

                break;
            case 'pc':
                //Verificamos el tipo de control solo la versión Pro puede utilizar PCQ

                if ($API->connect($con['ip'], $con['login'], $con['password'])) {

                    $rocket = new RocketCore();

                    $UPDATE = $rocket->update_pcq_list($API, $data, $service->ip, $data['newtarget'], $debug);

                    if ($debug == 1) {
                        if ($process->check($UPDATE)) {
                            $API->disconnect();
                            return $process->check($UPDATE);
                        }
                    }

                } else {
                    echo "No connection";
                }

                break;

            case 'nc': //Sin control mikrotik

                break;

            case 'rr':

                /**aca lo que hacemos es volver al estado normal**/

        /*        $multiplicador = Plan::find($data['plan_id'])->smart_bandwidth->bandwidth;
                $upload = ($multiplicador*$data['rate_up']/100)+$data['rate_up'];
                $download = ($multiplicador*$data['rate_down']/100)+$data['rate_down'];
                $velocidad = $upload.'k/'.$download.'k';

                if(!$data['no_rules']){

                    Radreply::where('username',$data['user'])->where('attribute','Mikrotik-Rate-Limit')->update(['value' => $velocidad]);
                    $ejecucion = shell_exec('echo User-Name="'.$data['user'].'",Mikrotik-Rate-Limit:="'.$velocidad.'" | /usr/bin/sudo /usr/bin/radclient -c 1 -n 3 -r 3 -t 3 -x '.$router->ip.':3799 coa '.$router->radius->secret.' 2>&1');
                }*/


                if(!$data['no_rules']){
                    $velocidad = $data['rate_up'].'k/'.$data['rate_down'].'k';

                    /**si hay que aplicar control sobre el mkt, actualizamos la cola**/
                    /** a diferencia del update services, aca si lo hacemos por coa **/
                    Radreply::where('username',$data['user'])->where('attribute','Mikrotik-Rate-Limit')->update(['value' => $velocidad]);
//                    $ejecucion = shell_exec('echo User-Name="'.$data['user'].'",Mikrotik-Rate-Limit:="'.$velocidad.'" | /usr/bin/sudo /usr/bin/radclient -c 1 -n 3 -r 3 -t 3 -x '.$router->ip.':3799 coa '.$router->radius->secret.' 2>&1');
                    $ejecucion = shell_exec('echo User-Name="'.$data['user'].'",Mikrotik-Rate-Limit:="'.$velocidad.'" | /usr/bin/radclient -c 1 -n 3 -r 3 -t 3 -x '.$router->ip.':3799 coa '.$router->radius->secret.' 2>&1');
                }


                break;
        } //end switch
    }
}
