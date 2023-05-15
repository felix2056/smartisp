<?php

namespace App\Console\Commands;

use App\libraries\Burst;
use App\libraries\Chkerr;
use App\libraries\CountClient;
use App\libraries\Firewall;
use App\libraries\GetPlan;
use App\libraries\Helper;
use App\libraries\Helpers;
use App\libraries\Mikrotik;
use App\libraries\Mkerror;
use App\libraries\QueueTree;
use App\libraries\QueueType;
use App\libraries\RecalculateSpeed;
use App\libraries\RocketCore;
use App\libraries\RouterConnect;
use App\libraries\Slog;
use App\models\BillCustomer;
use App\models\Client;
use App\models\ControlRouter;
use App\models\GlobalSetting;
use App\models\SuspendClient;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\ClientsController;
use Illuminate\Support\Facades\Log;

class SetMikrotikRulesForBlockedClients extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'set-mikrotik-rules';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Set mikrotik rules for blocked clients if removed from mikrotik by mistake';

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
	    set_time_limit(0);
	    //Unlimited momory limit
	    ini_set('memory_limit', '-1');
        Log::debug('start Mikrotik Rules');
        // get cortado users and check mikrotik rules
        Client::with(['service' => function($query) {
            return $query->where('status', 'de')->get();
        }])->chunkById(5, function($clients)
        {
	        foreach ($clients as $client) {
		        foreach ($client->service as $service) {
			        try {
				        $this->block($client, $service);
			        } catch(\Exception $exception) {
				        Log::debug($exception->getMessage());
				        continue;
			        }
			
		        }
	        }
        });


        // get active users and check mikrotik rules
        Client::with(['service' => function($query) {
            return $query->where('status', 'ac')
	            ->where('manually_cortado', '0')
	            ->get();
        }])->chunkById(5, function($clients)
        {
	        foreach ($clients as $client) {
		
		        foreach ($client->service as $service) {
			        $this->info($client->name . '_' . $service->id);
			        try {
				        $this->unblock($client, $service);
			        } catch(\Exception $exception) {
				        Log::debug($exception->getMessage());
				        continue;
			        }
		        }
	        }
        });

        
    }

    public function block($client, $service)
    {
        $process = new Chkerr();

        //obtenemos la ip del cliente
        $nameClient = $client->name;
        $target = $service->ip;
        $mac = $service->mac;
        $statusClient = $service->status;
        $router_id = $service->router_id;
        $userClient = $service->user_hot;

        $pl = new GetPlan();
        $plan = $pl->get($service->plan_id);
        $namePlan = $plan['name'];
        $maxlimit = $plan['maxlimit'];

        $config = ControlRouter::where('router_id', '=', $router_id)->get();

        $typeconf = $config[0]->type_control;
        $arp = $config[0]->arpmac;
        $advs = $config[0]->adv;
        $dhcp = $config[0]->dhcp;
        $maxlimit = $plan['maxlimit'];
        $burst = Burst::get_all_burst($plan['upload'],$plan['download'],$plan['burst_limit'],$plan['burst_threshold'],$plan['limitat']);
        $comment = 'SmartISP - '.$namePlan;

        if ($advs == 1) {
            $drop = 0;
        } else {
            $drop = 1;
        }

        $router = new RouterConnect();
        $con = $router->get_connect($router_id);
        $log = new Slog();

        $data = array(
            'name' => $client->name . '_' . $service->id,
            'user' => $userClient,
            'status' => 'ac',
            'arp' => $arp,
            'adv' => $advs,
            'drop' => $drop,
            'planName' => $namePlan,
            'namePlan' => $namePlan,
            'mac' => $mac,
            'lan' => $con['lan'],
            'dhcp' => $dhcp,
            'maxlimit' => $maxlimit,
            'bl' => $burst['blu'].'/'.$burst['bld'],
            'bth' => $burst['btu'].'/'.$burst['btd'],
            'bt' => $plan['burst_time'].'/'.$plan['burst_time'],
            'limit_at' => $burst['lim_at_up'].'/'.$burst['lim_at_down'],
            'comment' => $comment,
            'priority' => $plan['priority'].'/'.$plan['priority'],
            'priority_a' => $plan['priority'],
            'pass' => $service->pass_hot,
            //for simple queue with tree
            'plan_id' => $service->plan_id,
            'download' => $plan['download'],
            'upload' => $plan['upload'],
            'aggregation' => $plan['aggregation'],
            'limitat' => $plan['limitat'],
            'burst_limit' => $plan['burst_limit'],
            'burst_threshold' => $plan['burst_threshold'],
            'burst_time' => $plan['burst_time'],
            'tree_priority' => $service->tree_priority,
            'ip' => $service->ip,
            'router_id' => $service->router_id,
        );

        $counter = new CountClient();

        if ($typeconf == 'nc') {
            $st = 'de';
            $online = 'off';
            $m = "Se ha cortado el servicio al cliente: ";
            //descontamos el numero de clientes del plan
            $counter->step_down_plan($service->plan_id);

            $service->status = $st;
            $client->online = $online;
            $client->save();


        }

        $global = GlobalSetting::all()->first();
        $debug = $global->debug;

        if ($typeconf == 'no') {

            //GET all data for API
            $conf = Helpers::get_api_options('mikrotik');
            $API = new Mikrotik($con['port'], $conf['a'], $conf['t'], $conf['s']);
            $API->debug = $conf['d'];

            //Bloqueamos simple
            if ($API->connect($con['ip'], $con['login'], $con['password'])) {

                $rocket = new RocketCore();
                $error = new Mkerror();
                $STATUS = $rocket->set_basic_config($API, $error, $data, $target, null, 'block', $debug);

                if ($debug == 1) {
                    if ($STATUS != false) {
                        return $STATUS;
                    }
                }
            }

            $API->disconnect();
            $st = 'de';
            $online = 'off';
            //descontamos el numero de clientes del plan
            $counter->step_down_plan($service->plan_id);

            //guardamos en la base de datos
            $service->status = $st;
            $service->online = $online;
            $service->save();



        }

        if ($typeconf == 'sq') {

            //GET all data for API
            $conf = Helpers::get_api_options('mikrotik');
            $API = new Mikrotik($con['port'], $conf['a'], $conf['t'], $conf['s']);
            $API->debug = $conf['d'];

            //Bloqueamos simple
            if ($API->connect($con['ip'], $con['login'], $con['password'])) {

                $rocket = new RocketCore();

                $STATUS = $rocket->block_simple_queues($API, $data, $target, $debug);

                if ($debug == 1) {
                    if ($process->check($STATUS)) {
                        $API->disconnect();
                        return $process->check($STATUS);
                    }
                }

                $API->disconnect();

                $st = 'de';
                $online = 'off';
                //descontamos el numero de clientes del plan
                $counter->step_down_plan($service->plan_id);

                //guardamos en la base de datos
                $service->status = $st;
                $service->online = $online;
                $service->save();


            } else {
                return $process->show('errorConnect');
            }

        }

        if ($typeconf == 'st') {

            //GET all data for API
            $conf = Helpers::get_api_options('mikrotik');
            $API = new Mikrotik($con['port'], $conf['a'], $conf['t'], $conf['s']);
            $API->debug = $conf['d'];

            //Bloqueamos simple
            if ($API->connect($con['ip'], $con['login'], $con['password'])) {

                $rocket = new RocketCore();

                $STATUS = $rocket->block_simple_queue_with_tree($API,$data,$target,$debug);

                if ($debug == 1) {
                    if ($process->check($STATUS)) {
                        $API->disconnect();
                        return $process->check($STATUS);
                    }
                }

                $API->disconnect();

            } else {
                return $process->show('errorConnect');
            }

        }

        if ($typeconf == 'dl') {
            //bloqueamos hotspot

            //GET all data for API
            $conf = Helpers::get_api_options('mikrotik');
            $API = new Mikrotik($con['port'], $conf['a'], $conf['t'], $conf['s']);
            $API->debug = $conf['d'];

            if ($API->connect($con['ip'], $con['login'], $con['password'])) {

                $rocket = new RocketCore();

                $STATUS = $rocket->block_dhcp_lease($API, $data, $target, $debug);

                if ($debug == 1) {
                    if ($process->check($STATUS)) {
                        $API->disconnect();
                        return $process->check($STATUS);
                    }
                }

                $API->disconnect();
                $st = 'de';
                $online = 'off';
                $m = "Se ha cortado el servicio al cliente: ";
                //descontamos el numero de clientes del plan
                $counter->step_down_plan($service->plan_id);

                //guardamos en la base de datos
                $service->status = $st;
                $service->online = $online;
                $service->save();


            } else {
                return $process->show('errorConnect');
            }

        }

        if ($typeconf=='pt') {
            //bloqueamos pppoe simple queue with tree

            //GET all data for API
            $conf = Helpers::get_api_options('mikrotik');
            $API = new Mikrotik($con['port'],$conf['a'],$conf['t'],$conf['s']);
            $API->debug = $conf['d'];

            if ($API->connect($con['ip'], $con['login'], $con['password'])) {

                $rocket = new RocketCore();

                $STATUS = $rocket->block_ppp($API,$data,$target,$debug);

                if ($debug==1) {
                    if($process->check($STATUS)){
                        $API->disconnect();
                        return $process->check($STATUS);
                    }
                }

                $STATUS = $rocket->block_simple_queue_with_tree($API,$data,$target,$debug);

                if ($debug==1) {
                    if($process->check($STATUS)){
                        $API->disconnect();
                        return $process->check($STATUS);
                    }
                }

                $API->disconnect();

            }
            else
                return $process->show('errorConnect');
        }

        if ($typeconf == 'pp' || $typeconf == 'ps') {
            //bloqueamos pppoe

            //GET all data for API
            $conf = Helpers::get_api_options('mikrotik');
            $API = new Mikrotik($con['port'], $conf['a'], $conf['t'], $conf['s']);
            $API->debug = $conf['d'];

            if ($API->connect($con['ip'], $con['login'], $con['password'])) {

                $rocket = new RocketCore();

                $STATUS = $rocket->block_ppp($API, $data, $target, $debug);

                if ($debug == 1) {
                    if ($process->check($STATUS)) {
                        $API->disconnect();
                        return $process->check($STATUS);
                    }
                }

                $API->disconnect();
                $st = 'de';
                $online = 'off';
                $m = "Se ha cortado el servicio al cliente: ";
                //descontamos el numero de clientes del plan
                $counter->step_down_plan($service->plan_id);

                //guardamos en la base de datos
                $service->status = $st;
                $service->online = $online;
                $service->save();


            } else {
                return $process->show('errorConnect');
            }

        }

        if ($typeconf == 'pa') {
            //bloqueo PPP-PCQ
            //GET all data for API
            $conf = Helpers::get_api_options('mikrotik');
            $API = new Mikrotik($con['port'], $conf['a'], $conf['t'], $conf['s']);
            $API->debug = $conf['d'];

            if ($API->connect($con['ip'], $con['login'], $con['password'])) {

                $rocket = new RocketCore();

                $num_cli = Helpers::getnumcl($router_id, $typeconf, $service->plan_id);
                //opcion avanzada burst del plan
                $burst = Burst::get_all_burst($plan['upload'], $plan['download'], $plan['burst_limit'], $plan['burst_threshold'], $plan['limitat']);

                $advanced_data = array(

                    'name' => $client->name . '_' . $service->id,
                    'user' => $userClient,
                    'status' => $statusClient,
                    'arp' => $arp,
                    'adv' => $advs,
                    'dhcp' => $dhcp,
                    'drop' => $drop,
                    'mac' => $mac,
                    'lan' => $con['lan'],
                    'namePlan' => $namePlan,
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
                    'burst_time' => $plan['burst_time'],
                );

                $STATUS = $rocket->block_ppp_secrets_pcq($API, $advanced_data, $target, $debug);

                if ($debug == 1) {
                    if ($process->check($STATUS)) {
                        $API->disconnect();
                        return $process->check($STATUS);
                    }
                }

                $API->disconnect();


                $st = 'de';
                $online = 'off';
                $m = "Se ha cortado el servicio al cliente: ";
                //descontamos el numero de clientes del plan
                $counter->step_down_plan($service->plan_id);

                //guardamos en la base de datos
                $service->status = $st;
                $service->online = $online;
                $service->save();


            } else {
                return $process->show('errorConnect');
            }

        }

        if ($typeconf == 'pc') {
            //bloqueamos PCQ

            //GET all data for API
            $conf = Helpers::get_api_options('mikrotik');
            $API = new Mikrotik($con['port'], $conf['a'], $conf['t'], $conf['s']);
            $API->debug = $conf['d'];

            if ($API->connect($con['ip'], $con['login'], $con['password'])) {

                $rocket = new RocketCore();

                $num_cli = Helpers::getnumcl($router_id, $typeconf, $service->plan_id);
                //opcion avanzada burst del plan
                $burst = Burst::get_all_burst($plan['upload'], $plan['download'], $plan['burst_limit'], $plan['burst_threshold'], $plan['limitat']);

                $advanced_data = array(

                    'name' => $client->name . '_' . $service->id,
                    'status' => 'ac',
                    'arp' => $arp,
                    'adv' => $advs,
                    'dhcp' => $dhcp,
                    'drop' => $drop,
                    'mac' => $mac,
                    'lan' => $con['lan'],
                    'namePlan' => $namePlan,
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
                    'burst_time' => $plan['burst_time'],
	                'no_rules' => $plan['no_rules'],
                );



                $STATUS = $this->block_pcq($API, $advanced_data, $target, $debug);

                $this->info($advanced_data['status']);

                if ($debug == 1) {
                    if ($process->check($STATUS)) {
                        $API->disconnect();
                        return $process->check($STATUS);
                    }
                }

                $API->disconnect();

                $st = 'de';
                $online = 'off';
                $m = "Se ha cortado el servicio al cliente: ";
                //descontamos el numero de clientes del plan
                $counter->step_down_plan($service->plan_id);

                //guardamos en la base de datos
                $service->status = $st;
                $service->online = $online;
                $service->save();


            } else {
                return $process->show('errorConnect');
            }

        }
        sleep(10);
    }

    public function unblock($client, $service) {
        $process = new Chkerr();

        //obtenemos la ip del cliente
        $nameClient = $client->name;
        $target = $service->ip;
        $mac = $service->mac;
        $router_id = $service->router_id;
        $userClient = $service->user_hot;

        $servicePlan = $service->plan;
        $smartBandwidth = $servicePlan->smart_bandwidth;

        $now = Carbon::now();
        $start = Carbon::create($smartBandwidth->start_time); // comienza
        $start_finish = (clone $start)->addMinutes(15); // hasta pasados los 15 minutos desde que comenzo
        $end = Carbon::create($smartBandwidth->end_time);
        if($start > $end) // si termina al otro dia, le agregamos un dia
            $end = $end->addDay();

        // si estamos en proceso de duplicidad de ancho de banda, no hacemos nada
        if($now->between($start, $end)) {
            // not do any
        } else {
            $pl = new GetPlan();
            $plan = $pl->get($service->plan_id);
            $namePlan = $plan['name'];

            $config = ControlRouter::where('router_id', '=', $router_id)->get();

            $typeconf = $config[0]->type_control;
            $arp = $config[0]->arpmac;
            $advs = $config[0]->adv;
            $dhcp = $config[0]->dhcp;
            $maxlimit = $plan['maxlimit'];
            $burst = Burst::get_all_burst($plan['upload'],$plan['download'],$plan['burst_limit'],$plan['burst_threshold'],$plan['limitat']);
            $comment = 'SmartISP - '.$namePlan;

            if ($advs == 1) {
                $drop = 0;
            } else {
                $drop = 1;
            }

            $router = new RouterConnect();
            $con = $router->get_connect($router_id);
            $log = new Slog();

            $data = array(
                'name' => $client->name . '_' . $service->id,
                'user' => $userClient,
                'status' => 'de',
                'arp' => $arp,
                'adv' => $advs,
                'drop' => $drop,
                'planName' => $namePlan,
                'namePlan' => $namePlan,
                'mac' => $mac,
                'lan' => $con['lan'],
                'dhcp' => $dhcp,
                'maxlimit' => $maxlimit,
                'bl' => $burst['blu'].'/'.$burst['bld'],
                'bth' => $burst['btu'].'/'.$burst['btd'],
                'bt' => $plan['burst_time'].'/'.$plan['burst_time'],
                'limit_at' => $burst['lim_at_up'].'/'.$burst['lim_at_down'],
                'comment' => $comment,
                'priority' => $plan['priority'].'/'.$plan['priority'],
                'priority_a' => $plan['priority'],
                'pass' => $service->pass_hot,
                //for simple queue with tree
                'plan_id' => $service->plan_id,
                'download' => $plan['download'],
                'upload' => $plan['upload'],
                'aggregation' => $plan['aggregation'],
                'limitat' => $plan['limitat'],
                'burst_limit' => $plan['burst_limit'],
                'burst_threshold' => $plan['burst_threshold'],
                'burst_time' => $plan['burst_time'],
                'tree_priority' => $service->tree_priority,
                'ip' => $service->ip,
                'router_id' => $service->router_id
            );

            $counter = new CountClient();

            if ($typeconf == 'nc') {
                $st = 'de';
                $online = 'off';
                //descontamos el numero de clientes del plan
                $counter->step_down_plan($service->plan_id);

                $service->status = $st;
                $service->online = $online;
                $service->save();


            }

            $global = GlobalSetting::all()->first();
            $debug = $global->debug;

            if ($typeconf == 'no') {

                //GET all data for API
                $conf = Helpers::get_api_options('mikrotik');
                $API = new Mikrotik($con['port'], $conf['a'], $conf['t'], $conf['s']);
                $API->debug = $conf['d'];

                //Bloqueamos simple
                if ($API->connect($con['ip'], $con['login'], $con['password'])) {

                    $rocket = new RocketCore();
                    $error = new Mkerror();
                    $STATUS = $rocket->set_basic_config($API, $error, $data, $target, null, 'block', $debug);

                    if ($debug == 1) {
                        if ($STATUS != false) {
                            return $STATUS;
                        }
                    }
                }

                $API->disconnect();
                $st = 'de';
                $online = 'off';
//            $m = "Se ha cortado el servicio al cliente: ";
                //descontamos el numero de clientes del plan
                $counter->step_down_plan($service->plan_id);

                //guardamos en la base de datos
                $service->status = $st;
                $service->online = $online;
                $service->save();



            }

            if ($typeconf == 'sq') {

                //GET all data for API
                $conf = Helpers::get_api_options('mikrotik');
                $API = new Mikrotik($con['port'], $conf['a'], $conf['t'], $conf['s']);
                $API->debug = $conf['d'];

                //Bloqueamos simple
                if ($API->connect($con['ip'], $con['login'], $con['password'])) {

                    $rocket = new RocketCore();

                    $STATUS = $rocket->block_simple_queues($API, $data, $target, $debug);

                    if ($debug == 1) {
                        if ($process->check($STATUS)) {
                            $API->disconnect();
                            return $process->check($STATUS);
                        }
                    }

                    $API->disconnect();
                    $st = 'ac';
                    $online = 'on';
//            $m = "Se ha cortado el servicio al cliente: ";
                    //descontamos el numero de clientes del plan
                    $counter->step_down_plan($service->plan_id);

                    //guardamos en la base de datos
                    $service->status = $st;
                    $service->online = $online;
                    $service->save();

                } else {
                    return $process->show('errorConnect');
                }

            }

            if ($typeconf == 'st') {

                //GET all data for API
                $conf = Helpers::get_api_options('mikrotik');
                $API = new Mikrotik($con['port'], $conf['a'], $conf['t'], $conf['s']);
                $API->debug = $conf['d'];

                //Bloqueamos simple
                if ($API->connect($con['ip'], $con['login'], $con['password'])) {

                    $rocket = new RocketCore();

                    $STATUS = $rocket->block_simple_queue_with_tree($API,$data,$target,$debug);

                    if ($debug == 1) {
                        if ($process->check($STATUS)) {
                            $API->disconnect();
                            return $process->check($STATUS);
                        }
                    }

                    $API->disconnect();
                    $st = 'ac';
                    $online = 'on';
//            $m = "Se ha cortado el servicio al cliente: ";
                    //descontamos el numero de clientes del plan
                    $counter->step_down_plan($service->plan_id);

                    //guardamos en la base de datos
                    $service->status = $st;
                    $service->online = $online;
                    $service->save();

                } else {
                    return $process->show('errorConnect');
                }

            }

            if ($typeconf == 'dl') {
                //bloqueamos hotspot

                //GET all data for API
                $conf = Helpers::get_api_options('mikrotik');
                $API = new Mikrotik($con['port'], $conf['a'], $conf['t'], $conf['s']);
                $API->debug = $conf['d'];

                if ($API->connect($con['ip'], $con['login'], $con['password'])) {

                    $rocket = new RocketCore();

                    $STATUS = $rocket->block_dhcp_lease($API, $data, $target, $debug);

                    if ($debug == 1) {
                        if ($process->check($STATUS)) {
                            $API->disconnect();
                            return $process->check($STATUS);
                        }
                    }

                    $API->disconnect();
                    $st = 'ac';
                    $online = 'on';
//            $m = "Se ha cortado el servicio al cliente: ";
                    //descontamos el numero de clientes del plan
                    $counter->step_down_plan($service->plan_id);

                    //guardamos en la base de datos
                    $service->status = $st;
                    $service->online = $online;
                    $service->save();
                } else {
                    return $process->show('errorConnect');
                }

            }

            if ($typeconf=='pt') {
                //bloqueamos pppoe simple queue with tree

                //GET all data for API
                $conf = Helpers::get_api_options('mikrotik');
                $API = new Mikrotik($con['port'],$conf['a'],$conf['t'],$conf['s']);
                $API->debug = $conf['d'];

                if ($API->connect($con['ip'], $con['login'], $con['password'])) {

                    $rocket = new RocketCore();

                    $STATUS = $rocket->block_ppp($API,$data,$target,$debug);

                    if ($debug==1) {
                        if($process->check($STATUS)){
                            $API->disconnect();
                            return $process->check($STATUS);
                        }
                    }

                    $STATUS = $rocket->block_simple_queue_with_tree($API,$data,$target,$debug);

                    if ($debug==1) {
                        if($process->check($STATUS)){
                            $API->disconnect();
                            return $process->check($STATUS);
                        }
                    }

                    $API->disconnect();
                    $st = 'ac';
                    $online = 'on';
//            $m = "Se ha cortado el servicio al cliente: ";
                    //descontamos el numero de clientes del plan
                    $counter->step_down_plan($service->plan_id);

                    //guardamos en la base de datos
                    $service->status = $st;
                    $service->online = $online;
                    $service->save();
                }
                else
                    return $process->show('errorConnect');
            }

            if ($typeconf == 'pp' || $typeconf == 'ps') {

                //GET all data for API
                $conf = Helpers::get_api_options('mikrotik');
                $API = new Mikrotik($con['port'], $conf['a'], $conf['t'], $conf['s']);
                $API->debug = $conf['d'];

                if ($API->connect($con['ip'], $con['login'], $con['password'])) {

                    $rocket = new RocketCore();

                    $STATUS = $rocket->block_ppp($API, $data, $target, $debug);

                    if ($debug == 1) {
                        if ($process->check($STATUS)) {
                            $API->disconnect();
                            return $process->check($STATUS);
                        }
                    }

                    $API->disconnect();
                    $st = 'ac';
                    $online = 'on';
//            $m = "Se ha cortado el servicio al cliente: ";
                    //descontamos el numero de clientes del plan
                    $counter->step_down_plan($service->plan_id);

                    //guardamos en la base de datos
                    $service->status = $st;
                    $service->online = $online;
                    $service->save();
                } else {
                    return $process->show('errorConnect');
                }

            }

            if ($typeconf == 'pa') {
                //bloqueo PPP-PCQ
                //GET all data for API
                $conf = Helpers::get_api_options('mikrotik');
                $API = new Mikrotik($con['port'], $conf['a'], $conf['t'], $conf['s']);
                $API->debug = $conf['d'];

                if ($API->connect($con['ip'], $con['login'], $con['password'])) {

                    $rocket = new RocketCore();

                    $num_cli = Helpers::getnumcl($router_id, $typeconf, $service->plan_id);
                    //opcion avanzada burst del plan
                    $burst = Burst::get_all_burst($plan['upload'], $plan['download'], $plan['burst_limit'], $plan['burst_threshold'], $plan['limitat']);

                    $advanced_data = array(

                        'name' => $client->name . '_' . $service->id,
                        'user' => $userClient,
                        'status' => 'de',
                        'arp' => $arp,
                        'adv' => $advs,
                        'dhcp' => $dhcp,
                        'drop' => $drop,
                        'mac' => $mac,
                        'lan' => $con['lan'],
                        'namePlan' => $namePlan,
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
                        'burst_time' => $plan['burst_time'],
                    );

                    $STATUS = $rocket->block_ppp_secrets_pcq($API, $advanced_data, $target, $debug);

                    if ($debug == 1) {
                        if ($process->check($STATUS)) {
                            $API->disconnect();
                            return $process->check($STATUS);
                        }
                    }

                    $API->disconnect();
                    $st = 'ac';
                    $online = 'on';
//            $m = "Se ha cortado el servicio al cliente: ";
                    //descontamos el numero de clientes del plan
                    $counter->step_down_plan($service->plan_id);

                    //guardamos en la base de datos
                    $service->status = $st;
                    $service->online = $online;
                    $service->save();

                } else {
                    return $process->show('errorConnect');
                }

            }

            if ($typeconf == 'pc') {
                //bloqueamos PCQ

                //GET all data for API
                $conf = Helpers::get_api_options('mikrotik');
                $API = new Mikrotik($con['port'], $conf['a'], $conf['t'], $conf['s']);
                $API->debug = $conf['d'];

                if ($API->connect($con['ip'], $con['login'], $con['password'])) {

                    $rocket = new RocketCore();

                    $num_cli = Helpers::getnumcl($router_id, $typeconf, $service->plan_id);
                    //opcion avanzada burst del plan
                    $burst = Burst::get_all_burst($plan['upload'], $plan['download'], $plan['burst_limit'], $plan['burst_threshold'], $plan['limitat']);

                    $advanced_data = array(

                        'name' => $client->name . '_' . $service->id,
                        'status' => 'de',
                        'arp' => $arp,
                        'adv' => $advs,
                        'dhcp' => $dhcp,
                        'drop' => $drop,
                        'mac' => $mac,
                        'lan' => $con['lan'],
                        'namePlan' => $namePlan,
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
                        'burst_time' => $plan['burst_time'],
	                    'no_rules' => $plan['no_rules'],

                    );

                    $STATUS = $rocket->block_pcq($API, $advanced_data, $target, $debug);

                    if ($debug == 1) {
                        if ($process->check($STATUS)) {
                            $API->disconnect();
                            return $process->check($STATUS);
                        }
                    }

                    $API->disconnect();
                    $st = 'ac';
                    $online = 'on';
//            $m = "Se ha cortado el servicio al cliente: ";
                    //descontamos el numero de clientes del plan
                    $counter->step_down_plan($service->plan_id);

                    //guardamos en la base de datos
                    $service->status = $st;
                    $service->online = $online;
                    $service->save();

                } else {
                    return $process->show('errorConnect');
                }

            }
        }


        sleep(10);
    }

    //////////////////// BLOQUEAR PCQ-ADDRESS-LIST ///////////////////////////////
    public function block_pcq($API,$data,$Address,$debug){

        $error = new Mkerror();
        $rocket = new RocketCore();
        //creamos reglas parent si no existen
        $PARENTS = $rocket->create_queuetree_parent($API,$debug);

        if ($debug==1) {
            $msg = $error->process_error($PARENTS);
            if($msg)
                return $msg;
        }


        if($data['status'] == 'ac'){ //significa que esta activo en ese caso bloqueamos

            $SBC = $rocket->set_basic_config($API,$error,$data,$Address,null,'block',$debug);

            if ($debug==1) {
                if ($SBC!=false)
                    return $SBC;
            }

            $BLOCK = $this->delete_pcq_list($API,$data,$Address,'none',$debug);

            if (empty($BLOCK)) {
                return 'true';
            }else{
                if ($debug==1) {
                    return $BLOCK;
                }
            }


        }

        if($data['status'] == 'de'){ //significa que esta bloqueado en ese caso desbloqueamos

            $SBC = $rocket->set_basic_config($API,$error,$data,$Address,null,'unblock',$debug);

            if ($debug==1) {
                if ($SBC!=false)
                    return $SBC;
            }


            //negamos el drop para que no vuelva a bloquear al cliente
            $data['drop']=0;

            $BLOCK = $rocket->add_pcq_list($API,$data,$Address,$debug);

            if (empty($BLOCK)) {
                return 'false';
            }else{
                if ($debug==1) {
                    return $BLOCK;
                }
            }


        }

    }

    function delete_pcq_list($API,$data,$Address,$option,$debug){

        $error = new Mkerror();

        //recuperamos la cantidad de clientes
        $ncl = ($data['num_cl']-1);


        if ($ncl>0) { //significa que encontro el plan, descontamos un cliente

            //eliminamos del address list


            //buscamos el plan en QueueTree y descontamos si no hay usuarios eliminamos el plan
            $QUEUETREE = QueueTree::get_parent($API,Helper::replace_word($data['namePlan'].'-DOWN'));


            if ($QUEUETREE!='notFound') { //no se encontro el parent creamos nuevo parent

                //se encontro el parent seteamos los nuevos datos descontando
                $QUEUETREE = QueueTree::set_child($API,
                    $QUEUETREE[0]['.id'],
                    null,
                    null,
                    null,
                    RecalculateSpeed::speed($data['limit_at_down'],$ncl,true),
                    RecalculateSpeed::speed($data['speed_down'],$ncl,true),
                    RecalculateSpeed::speed($data['burst_rate_down'],$ncl,true),
                    RecalculateSpeed::speed($data['burst_threshold_down'],$ncl,true),
                    $data['burst_time'],
                    $data['priority_a']);
                if ($debug==1) {
                    $msg = $error->process_error($QUEUETREE);
                    if($msg)
                        return $msg;
                }

            }

            //buscamos el plan en QueueTree y descontamos si no hay usuarios eliminamos el plan
            $QUEUETREE = QueueTree::get_parent($API,Helper::replace_word($data['namePlan'].'-UP'));

            if ($QUEUETREE!='notFound') { //no se encontro el parent creamos nuevo parent

                //se encontro el parent seteamos los nuevos datos descontando
                $QUEUETREE = QueueTree::set_child($API,
                    $QUEUETREE[0]['.id'],
                    null,
                    null,
                    null,
                    RecalculateSpeed::speed($data['limit_at_up'],$ncl,true),
                    RecalculateSpeed::speed($data['speed_up'],$ncl,true),
                    RecalculateSpeed::speed($data['burst_rate_up'],$ncl,true),
                    RecalculateSpeed::speed($data['burst_threshold_up'],$ncl,true),
                    $data['burst_time'],
                    $data['priority_a']);
                if ($debug==1) {
                    $msg = $error->process_error($QUEUETREE);
                    if($msg)
                        return $msg;
                }

            }

        }else{ //significa que es el ultimo cliente dentro del plan eliminamos los planes del router


            //buscamos el plan en QueueTree y eliminamos el plan
            $QUEUETREE = QueueTree::get_parent($API,Helper::replace_word($data['namePlan'].'-DOWN'));

            if ($QUEUETREE != 'notFound') {

                $QUEUETREE = QueueTree::delete_parent($API,$QUEUETREE[0]['.id']);

                if ($debug==1) {
                    $msg = $error->process_error($QUEUETREE);
                    if($msg)
                        return $msg;
                }


            }

            //buscamos el plan en QueueTree y eliminamos el plan
            $QUEUETREE = QueueTree::get_parent($API,Helper::replace_word($data['namePlan'].'-UP'));

            if ($QUEUETREE != 'notFound') {

                $QUEUETREE = QueueTree::delete_parent($API,$QUEUETREE[0]['.id']);

                if ($debug==1) {
                    $msg = $error->process_error($QUEUETREE);
                    if($msg)
                        return $msg;
                }


            }

            // Buscamos y eliminamos el queue type asociado al plan DOWN

            $QUEUETYPE = QueueType::find_queuetype_list($API,Helper::replace_word($data['namePlan'].'_DOWN'));

            if($QUEUETYPE != 'notFound'){

                $QUEUETYPE = QueueType::delete_queuetype($API,$QUEUETYPE[0]['.id']);

                if ($debug==1) {
                    $msg = $error->process_error($QUEUETYPE);
                    if($msg)
                        return $msg;
                }


            }

            // Buscamos y eliminamos el queue type asociado al plan UP

            $QUEUETYPE = QueueType::find_queuetype_list($API,Helper::replace_word($data['namePlan'].'_UP'));

            if($QUEUETYPE != 'notFound'){

                $QUEUETYPE = QueueType::delete_queuetype($API,$QUEUETYPE[0]['.id']);

                if ($debug==1) {
                    $msg = $error->process_error($QUEUETYPE);
                    if($msg)
                        return $msg;
                }


            }

            // Buscamos y eliminamos las reglas mangle
            $MANGLE = Firewall::find_mangle($API,Helper::replace_word($data['namePlan'].'_in'));

            if ($MANGLE!='notFound') {

                $MANGLE = Firewall::delete_mangle($API,$MANGLE[0]['.id']);

                if ($debug==1) {
                    $msg = $error->process_error($MANGLE);
                    if($msg)
                        return $msg;
                }


            }


            // Buscamos y eliminamos las reglas mangle
            $MANGLE = Firewall::find_mangle($API,Helper::replace_word($data['namePlan'].'_out'));

            if ($MANGLE!='notFound') {

                $MANGLE = Firewall::delete_mangle($API,$MANGLE[0]['.id']);

                if ($debug==1) {
                    $msg = $error->process_error($MANGLE);
                    if($msg)
                        return $msg;
                }


            }

        }


    }
}
