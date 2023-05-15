<?php

namespace App\Console\Commands;

use App\libraries\Burst;
use App\libraries\Chkerr;
use App\libraries\CountClient;
use App\libraries\GetPlan;
use App\libraries\Helpers;
use App\libraries\Intersep;
use App\libraries\Mikrotik;
use App\libraries\Mkerror;
use App\libraries\Pencrypt;
use App\libraries\RocketCore;
use App\libraries\RouterConnect;
use App\libraries\Slog;
use App\models\AddressRouter;
use App\models\Client;
use App\models\ClientService;
use App\models\ControlRouter;
use App\models\GlobalSetting;
use App\models\Logg;
use App\models\Network;
use App\models\Router;
use App\Service\CommonService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Response;

class SetActiveBlockedClients extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'set-active';

    /**
     * The console command description.
     *
     * @return void
     * @var string
     *
     * protected $description = 'This cron will set client status';
     *
     * /**
     * Create a new command instance.
     *
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
        ini_set('max_execution_time', 0); //unlimited execution time php
	    $global = GlobalSetting::first();
	    
        Client::with(['billing_settings', 'plan', 'suspend_client', 'service' => function($query) {
            return $query->where('status', 'de')
	            ->where('manually_cortado', '0')
	            ->get();
        }])->chunkById(5, function($clients) use($global)
        {
	        foreach ($clients as $client) {
		        $cortadoDetails = CommonService::getServiceCortadoDate($client->id);
		        foreach ($client->service as $service) {
			        $dia_de_gracia = $client->billing_settings->billing_grace_period;
			        $tot_dias = $dia_de_gracia + $global->tolerance;
			        $expiration = Carbon::parse($cortadoDetails['cortado_date'])->startOfDay()->add('days', $tot_dias);
			        $this->info($expiration . "\n");
			        if ($expiration->greaterThanOrEqualTo(Carbon::now())) {
				        $this->info($client->name . "\n");
				        $this->unblock($client, $service);
			        }
		        }
	        }
        });

       
        
    }

    public function unblock($client, $service)
    {
        $process = new Chkerr();

        $pl = new GetPlan();
        $plan = $pl->get($service->plan_id);
        $namePlan = $plan['name'];

        $config = ControlRouter::where('router_id', '=', $service->router_id)->get();

        $typeconf = $config[0]->type_control;
        $arp = $config[0]->arpmac;
        $advs = $config[0]->adv;
        $dhcp = $config[0]->dhcp;
        $maxlimit = $plan['maxlimit'];
        $burst = Burst::get_all_burst($plan['upload'], $plan['download'], $plan['burst_limit'], $plan['burst_threshold'], $plan['limitat']);
        $comment = 'SmartISP - ' . $namePlan;

        if ($advs == 1) {
            $drop = 0;
        } else {
            $drop = 1;
        }

        $router = new RouterConnect();
        $con = $router->get_connect($service->router_id);
        $log = new Slog();

        $data = array(
            'name' => $client->name . '_' . $service->id,
            'user' => $service->user_hot,
            'status' => 'de',
            'arp' => $arp,
            'adv' => $advs,
            'drop' => $drop,
            'planName' => $namePlan,
            'namePlan' => $namePlan,
            'mac' => $service->mac,
            'lan' => $con['lan'],
            'dhcp' => $dhcp,
            'maxlimit' => $maxlimit,
            'bl' => $burst['blu'] . '/' . $burst['bld'],
            'bth' => $burst['btu'] . '/' . $burst['btd'],
            'bt' => $plan['burst_time'] . '/' . $plan['burst_time'],
            'limit_at' => $burst['lim_at_up'] . '/' . $burst['lim_at_down'],
            'comment' => $comment,
            'priority' => $plan['priority'] . '/' . $plan['priority'],
            'priority_a' => $plan['priority'],
            'pass' => $service->pass_hot,
            //for simple queue with tree
            'plan_id' => $service->plan_id,
            'router_id' => $service->router_id,
            'download' => $plan['download'],
            'upload' => $plan['upload'],
            'aggregation' => $plan['aggregation'],
            'limitat' => $plan['limitat'],
            'burst_limit' => $plan['burst_limit'],
            'burst_threshold' => $plan['burst_threshold'],
            'burst_time' => $plan['burst_time'],
            'tree_priority' => $service->tree_priority,
            'ip' => $service->ip,
        );

        $counter = new CountClient();
        $this->info($typeconf . "\n");
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
                $STATUS = $rocket->set_basic_config($API, $error, $data, $service->ip, null, 'block', $debug);

                if ($debug == 1) {
                    if ($STATUS != false) {
                        return $STATUS;
                    }
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

        }

        if ($typeconf == 'sq') {

            //GET all data for API
            $conf = Helpers::get_api_options('mikrotik');
            $API = new Mikrotik($con['port'], $conf['a'], $conf['t'], $conf['s']);
            $API->debug = $conf['d'];

            //Bloqueamos simple
            if ($API->connect($con['ip'], $con['login'], $con['password'])) {

                $rocket = new RocketCore();

                $STATUS = $rocket->block_simple_queues($API, $data, $service->ip, $debug);

                if ($debug == 1) {
                    if ($process->check($STATUS)) {
                        $API->disconnect();
                        return $process->check($STATUS);
                    }
                }

                $API->disconnect();
                $client = ClientService::find($service->id);
                $client->status = 'ac';
                $client->online = 'on';
                $client->save();
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

                $STATUS = $rocket->block_simple_queue_with_tree($API, $data, $service->ip, $debug);

                if ($debug == 1) {
                    if ($process->check($STATUS)) {
                        $API->disconnect();
                        return $process->check($STATUS);
                    }
                }

                $API->disconnect();
                $client = ClientService::find($service->id);;
                $client->status = 'ac';
                $client->online = 'on';
                $client->save();

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

                $STATUS = $rocket->block_dhcp_lease($API, $data, $service->ip, $debug);

                if ($debug == 1) {
                    if ($process->check($STATUS)) {
                        $API->disconnect();
                        return $process->check($STATUS);
                    }
                }

                $API->disconnect();
                $client = ClientService::find($service->id);
                $client->status = 'ac';
                $client->online = 'on';
                $client->save();
            } else {
                return $process->show('errorConnect');
            }

        }

        if ($typeconf == 'pt') {
            //bloqueamos pppoe simple queue with tree

            //GET all data for API
            $conf = Helpers::get_api_options('mikrotik');
            $API = new Mikrotik($con['port'], $conf['a'], $conf['t'], $conf['s']);
            $API->debug = $conf['d'];

            if ($API->connect($con['ip'], $con['login'], $con['password'])) {

                $rocket = new RocketCore();

                $STATUS = $rocket->block_ppp($API, $data, $service->ip, $debug);

                if ($debug == 1) {
                    if ($process->check($STATUS)) {
                        $API->disconnect();
                        return $process->check($STATUS);
                    }
                }
                $client = ClientService::find($service->id);
                $client->status = 'ac';
                $client->online = 'on';
                $client->save();

                $STATUS = $rocket->block_simple_queue_with_tree($API, $data, $service->ip, $debug);

                if ($debug == 1) {
                    if ($process->check($STATUS)) {
                        $API->disconnect();
                        return $process->check($STATUS);
                    }
                }

                $API->disconnect();

                if ($STATUS == 'true' || $STATUS == 'ac')
                    return $process->show('banned');
                else
                    return $process->show('unbanned');

            } else
                return $process->show('errorConnect');
        }

        if ($typeconf == 'pp' || $typeconf == 'ps') {

            //GET all data for API
            $conf = Helpers::get_api_options('mikrotik');
            $API = new Mikrotik($con['port'], $conf['a'], $conf['t'], $conf['s']);
            $API->debug = $conf['d'];

            if ($API->connect($con['ip'], $con['login'], $con['password'])) {

                $rocket = new RocketCore();

                $STATUS = $rocket->block_ppp($API, $data, $service->ip, $debug);

                if ($debug == 1) {
                    if ($process->check($STATUS)) {
                        $API->disconnect();
                        return $process->check($STATUS);
                    }
                }

                $API->disconnect();
                $client = ClientService::find($service->id);
                $client->status = 'ac';
                $client->online = 'on';
                $client->save();
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

                $num_cli = Helpers::getnumcl($service->router_id, $typeconf, $service->plan_id);
                //opcion avanzada burst del plan
                $burst = Burst::get_all_burst($plan['upload'], $plan['download'], $plan['burst_limit'], $plan['burst_threshold'], $plan['limitat']);

                $advanced_data = array(

                    'name' => $client->name . '_' . $service->id,
                    'user' => $service->user_hot,
                    'status' => 'de',
                    'arp' => $arp,
                    'adv' => $advs,
                    'dhcp' => $dhcp,
                    'drop' => $drop,
                    'mac' => $service->mac,
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

                $STATUS = $rocket->block_ppp_secrets_pcq($API, $advanced_data, $service->ip, $debug);

                if ($debug == 1) {
                    if ($process->check($STATUS)) {
                        $API->disconnect();
                        return $process->check($STATUS);
                    }
                }

                $API->disconnect();
                $client = ClientService::find($service->id);
                $client->status = 'ac';
                $client->online = 'on';
                $client->save();

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

                $num_cli = Helpers::getnumcl($service->router_id, $typeconf, $service->plan_id);
                //opcion avanzada burst del plan
                $burst = Burst::get_all_burst($plan['upload'], $plan['download'], $plan['burst_limit'], $plan['burst_threshold'], $plan['limitat']);

                $advanced_data = array(

                    'name' => $client->name . '_' . $service->id,
                    'status' => 'de',
                    'arp' => $arp,
                    'adv' => $advs,
                    'dhcp' => $dhcp,
                    'drop' => $drop,
                    'mac' => $service->mac,
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

                $STATUS = $rocket->block_pcq($API, $advanced_data, $service->ip, $debug);

                if ($debug == 1) {
                    if ($process->check($STATUS)) {
                        $API->disconnect();
                        return $process->check($STATUS);
                    }
                }

                $API->disconnect();
                $client = ClientService::find($service->id);

                $client->status = 'ac';
                $client->online = 'on';
                $client->save();

            } else {
                return $process->show('errorConnect');
            }

        }
        sleep(10);
    }
}
