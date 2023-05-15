<?php

namespace App\Console\Commands;

use App\libraries\Burst;
use App\libraries\Chkerr;
use App\libraries\CountClient;
use App\libraries\GetPlan;
use App\libraries\Helpers;
use App\libraries\Mikrotik;
use App\libraries\Mkerror;
use App\libraries\RocketCore;
use App\libraries\RouterConnect;
use App\libraries\Slog;
use App\models\AddressRouter;
use App\models\BillCustomer;
use App\models\Client;
use App\models\ControlRouter;
use App\models\GlobalSetting;
use App\models\Logg;
use App\models\Network;
use App\models\radius\Radgroupcheck;
use App\models\radius\Radusergroup;
use App\models\Router;
use App\models\SuspendClient;
use App\Service\CommonService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\ClientsController;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Log;

class ChangeStatusOnNegativeBalance extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'set-status';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Set client status Cortado if client balance is negative';

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
        Log::debug('start Change Status on Negative Balance');
	    $global = GlobalSetting::first();
        Client::with(['billing_settings', 'service'])
	        ->chunkById(5, function($clients) use($global)
	        {
		        foreach ($clients as $client) {
			        foreach($client->service as $service) {
				        try{
					
					        $cortadoDetails = CommonService::getServiceCortadoDate($client->id);
					
					        if ($cortadoDetails['cortado_date'] && !$cortadoDetails['paid'] && $client->billing_settings) {

						        $dia_de_gracia = $client->billing_settings->billing_grace_period;
						        $tot_dias=$dia_de_gracia+$global->tolerance;
						        $billingDueDate = Carbon::parse($cortadoDetails['cortado_date'])->startOfDay()->add('days', $tot_dias);

						        if (now()->startOfDay()->greaterThanOrEqualTo($billingDueDate->startOfDay()) &&
							        $service->status == 'ac'
						        ) {
							        $this->info($billingDueDate->startOfDay());
							        $client->billing_settings->billing_grace_period = 0;
							        $client->billing_settings->save();
							        $process = new Chkerr();
							
							        //obtenemos la ip del cliente
							        $nameClient = $client->name;
							        $target = $service->ip;
							        $mac = is_null($service->mac) ? '00:00:00:00:00:00' : $service->mac;
							        $statusClient = $service->status;
							        $router_id = $service->router_id;
							        $userClient = $service->user_hot;
							
							        $pl = new GetPlan();
							        $plan = $pl->get($service->plan_id);
							        $namePlan = $plan['name'];
							        $burst = Burst::get_all_burst($plan['upload'],$plan['download'],$plan['burst_limit'],$plan['burst_threshold'],$plan['limitat']);
							        $comment = 'SmartISP - '.$namePlan;
							        $config = ControlRouter::where('router_id', '=', $router_id)->get();
							
							        $typeconf = $config[0]->type_control;
							        $arp = $config[0]->arpmac;
							        $advs = $config[0]->adv;
							        $dhcp = $config[0]->dhcp;
							
							        if ($advs == 1) {
								        $drop = 0;
							        } else {
								        $drop = 1;
							        }
							
							        $router = new RouterConnect();
							        $con = $router->get_connect($router_id);
							        $log = new Slog();
							
							        $data = array(
								        'name' => $nameClient.'_'.$service->id,
								        'user' => $userClient,
								        'status' => $statusClient,
								        'arp' => $arp,
								        'adv' => $advs,
								        'drop' => $drop,
								        'planName' => $namePlan,
								        'namePlan' => $namePlan,
								        'mac' => $mac,
								        'lan' => $con['lan'],
								        'dhcp' => $dhcp,
								        'bl' => $burst['blu'].'/'.$burst['bld'],
								        'bth' => $burst['btu'].'/'.$burst['btd'],
								        'bt' => $plan['burst_time'].'/'.$plan['burst_time'],
								        'limit_at' => $burst['lim_at_up'].'/'.$burst['lim_at_down'],
								        'comment' => $comment,
								        'priority' => $plan['priority'].'/'.$plan['priority'],
								        'priority_a' => $plan['priority'],
								        'pass' => $service->pass_hot,
								        'router_id' => $router_id,
								        //for simple queue with tree
								        'plan_id' => $service->plan_id,
								        'download' => $plan['download'],
								        'upload' => $plan['upload'],
								        'maxlimit' => $plan['maxlimit'],
								        'aggregation' => $plan['aggregation'],
								        'limitat' => $plan['limitat'],
								        'burst_limit' => $plan['burst_limit'],
								        'burst_threshold' => $plan['burst_threshold'],
								        'burst_time' => $plan['burst_time'],
								        'tree_priority' => $service->tree_priority,
								        'ip' => $service->ip,
							        );
							
							        $counter = new CountClient();
							
							        if ($typeconf == 'nc') {
								        $st = 'de';
								        $online = 'off';
								        $m = "Se ha cortado el servicio al cliente: ";
								        //descontamos el numero de clientes del plan
								        $counter->step_down_plan($service->plan_id);
								
								        $service->status = $st;
								        $service->online = $online;
								        $service->save();
								        $log = new Logg();
								
								        $log->detail = $m . ' ' . $nameClient;
								        $log->user = "Automatic";
								        $log->type = 'change';
								        $log->save();
								
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
								        $m = "Se ha cortado el servicio al cliente: ";
								        //descontamos el numero de clientes del plan
								        $counter->step_down_plan($service->plan_id);
								
								        //guardamos en la base de datos
								        $service->status = $st;
								        $service->online = $online;
								        $service->save();
								
								        $log = new Logg();
								
								        $log->detail = $m . ' ' . $nameClient;
								        $log->user = "Automatic";
								        $log->type = 'change';
								        $log->save();
								
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
									
									
									        $st = 'de';
									        $online = 'off';
									        $m = "Se ha cortado el servicio al cliente: ";
									        //descontamos el numero de clientes del plan
									        $counter->step_down_plan($service->plan_id);
									
									        //guardamos en la base de datos
									        $service->status = $st;
									        $service->online = $online;
									        $service->save();
									        $log = new Logg();
									
									        $log->detail = $m . ' ' . $nameClient;
									        $log->user = "Automatic";
									        $log->type = 'change';
									        $log->save();
									
								        }
								
								        $API->disconnect();
								
							        }
							
							        if($typeconf=='st'){
								
								        //GET all data for API
								        $conf = Helpers::get_api_options('mikrotik');
								        $API = new Mikrotik($con['port'],$conf['a'],$conf['t'],$conf['s']);
								        $API->debug = $conf['d'];
								
								        //Bloqueamos simple
								        if ($API->connect($con['ip'], $con['login'], $con['password'])) {
									
									        $st='de';
									        $online = 'off';
									        $m = "Se ha cortado el servicio al cliente: ";
									        //descontamos el numero de clientes del plan
									        $counter->step_down_plan($service->plan_id);
									
									        //guardamos en la base de datos
									        $service->status = $st;
									        $service->online = $online;
									        $service->save();
									
									        $log = new Logg();
									
									        $log->detail = $m . ' ' . $nameClient;
									        $log->user = "Automatic";
									        $log->type = 'change';
									        $log->save();
									
									        $rocket = new RocketCore();
									
									        $STATUS = $rocket->block_simple_queue_with_tree($API,$data,$target,$debug);
									
									        if ($debug==1) {
										        if($process->check($STATUS)){
											        $API->disconnect();
											        return $process->check($STATUS);
										        }
									        }
									
									        $API->disconnect();
									
									
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
									        $log = new Logg();
									
									        $log->detail = $m . ' ' . $nameClient;
									        $log->user = "Automatic";
									        $log->type = 'change';
									        $log->save();
									
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
									
									        $st='de';
									        $online='off';
									        $m = "Se ha cortado el servicio al cliente: ";
									        //descontamos el numero de clientes del plan
									        $counter->step_down_plan($service->plan_id);
									
									        //guardamos en la base de datos
									        $service->status = $st;
									        $service->online=$online;
									        $service->save();
									
									        $log = new Logg();
									
									        $log->detail = $m . ' ' . $nameClient;
									        $log->user = "Automatic";
									        $log->type = 'change';
									        $log->save();
									
									        $STATUS = $rocket->block_simple_queue_with_tree($API,$data,$target,$debug);
									
									        if ($debug==1) {
										        if($process->check($STATUS)){
											        $API->disconnect();
											        return $process->check($STATUS);
										        }
									        }
									
									        $API->disconnect();
									
								        }
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
									        $log = new Logg();
									
									        $log->detail = $m . ' ' . $nameClient;
									        $log->user = "Automatic";
									        $log->type = 'change';
									        $log->save();
									
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
										
										        'name' => $nameClient.'_'.$service->id,
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
									        $log = new Logg();
									
									        $log->detail = $m . ' ' . $nameClient;
									        $log->user = "Automatic";
									        $log->type = 'change';
									        $log->save();
									
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
										
										        'name' => $nameClient.'_'.$service->id,
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
									
									        $STATUS = $rocket->block_pcq($API, $advanced_data, $target, $debug);
									
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
									        $log = new Logg();
									
									        $log->detail = $m . ' ' . $nameClient;
									        $log->user = "Automatic";
									        $log->type = 'change';
									        $log->save();
									
								        }
								
							        }

                                    if($typeconf=='ra' || $typeconf == 'rp' || $typeconf == 'rr'){

                                        /**TODO: cuando este testeado y funcionando correctamente, lo que vamos hacer es refactorizar y poner en typeconf un || para los dos casos, asi no replicamos todo esto**/
                                        /**bloqueamos en Radius y ademas bloqueamos segun el tipo que es, replicando el pa,pt,ps**/
                                        $existe = Radgroupcheck::where('groupname','locked')->where('attribute','Auth-Type')->where('value','Reject')->first();
                                        if(!$existe){
                                            Radgroupcheck::create([
                                                'groupname' => 'locked',
                                                'attribute' => 'Auth-Type',
                                                'op' => ':=',
                                                'value' => 'Reject',

                                            ]);
                                        }

                                        if($typeconf=='ra'){ /**aplicamos el mismo caso que pt**/
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
                                                $ip = $service->ip;

                                                if($STATUS=='true' || $STATUS=='ac'){
                                                    $st='de';
                                                    $online='off';
                                                    $m = "Se ha cortado el servicio de atención al cliente para ip $ip";

                                                    Radusergroup::create([
                                                        'username' => $service->user_hot,
                                                        'groupname' => 'locked',
                                                        'priority' => 1
                                                    ]);

                                                }
                                                else{
                                                    $st='ac';
                                                    $online='on';
                                                    $m = "Se ha activado el servicio de atención al cliente para ip $ip";

                                                    Radusergroup::where('username',$service->user_hot)->delete();

                                                }

                                                //guardamos en la base de datos
                                                $service->status = $st;
                                                $service->online = $online;
                                                $service->save();

                                                $log = new Logg();

                                                $log->detail = $m . ' ' . $nameClient;
                                                $log->user = "Automatic";
                                                $log->type = 'change';
                                                $log->save();
//                                                CommonService::log($m, $this->username, 'change' , $this->userId, $service->client_id);


                                                $STATUS = $rocket->block_simple_queue_with_tree($API,$data,$target,$debug);

                                                if ($debug==1) {
                                                    if($process->check($STATUS)){
                                                        $API->disconnect();
                                                        return $process->check($STATUS);
                                                    }
                                                }

                                                $API->disconnect();

                                            }
                                        }
                                        if($typeconf=='rp'){ /**aplicamos el mismo caso que pa**/
                                            //bloqueo PPP-PCQ
                                            //GET all data for API
                                            $conf = Helpers::get_api_options('mikrotik');
                                            $API = new Mikrotik($con['port'],$conf['a'],$conf['t'],$conf['s']);
                                            $API->debug = $conf['d'];

                                            if ($API->connect($con['ip'], $con['login'], $con['password'])) {

                                                $rocket = new RocketCore();

                                                //$num_cli = Helpers::getnumcl($router_id,$typeconf,$client->plan_id);
                                                $num_cli = Helpers::getnumcl($router_id,$typeconf,$service->plan_id); /**fix 19/06**/
                                                //opcion avanzada burst del plan
                                                $burst = Burst::get_all_burst($plan['upload'],$plan['download'],$plan['burst_limit'],$plan['burst_threshold'],$plan['limitat']);

                                                $advanced_data = array(
                                                    'name' => $nameClient.'_'.$service->id,
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
                                                    'rate_down' => $plan['download'].'k',
                                                    'rate_up' => $plan['upload'].'k',
                                                    'burst_rate_down' => $burst['bld'],
                                                    'burst_rate_up' => $burst['blu'],
                                                    'burst_threshold_down' => $burst['btd'],
                                                    'burst_threshold_up' => $burst['btu'],
                                                    'limit_at_down' => $burst['lim_at_down'],
                                                    'limit_at_up' => $burst['lim_at_up'],
                                                    'burst_time' => $plan['burst_time'],
                                                    'no_rules' => $plan['no_rules'],
                                                );


                                                $STATUS = $rocket->block_ppp_secrets_pcq($API,$advanced_data,$target,$debug);

                                                if ($debug==1) {
                                                    if($process->check($STATUS)){
                                                        $API->disconnect();
                                                        return $process->check($STATUS);
                                                    }
                                                }


                                                $API->disconnect();
                                                $ip = $service->ip;

                                                if($STATUS=='true' || $STATUS=='ac'){
                                                    $st='de';
                                                    $online='off';
                                                    $m = "Se ha cortado el servicio de atención al cliente para ip $ip";
                                                    //descontamos el numero de clientes del plan
                                                    $counter->step_down_plan($client->plan_id);
                                                    Radusergroup::create([
                                                        'username' => $service->user_hot,
                                                        'groupname' => 'locked',
                                                        'priority' => 1
                                                    ]);

                                                }
                                                else{
                                                    $st='ac';
                                                    $online ='on';
                                                    $m = "Se ha activado el servicio de atención al cliente para ip $ip";
                                                    //incrementamos el numero de clientes en el plan
                                                    $counter->step_up_plan($client->plan_id);

                                                    Radusergroup::where('username',$service->user_hot)->delete();
                                                }


                                                //guardamos en la base de datos
                                                $service->status = $st;
                                                $service->online = $online;
                                                $service->save();
//                                                CommonService::log($m, $this->username, 'change' , $this->userId, $service->client_id);
                                                $log = new Logg();

                                                $log->detail = $m . ' ' . $nameClient;
                                                $log->user = "Automatic";
                                                $log->type = 'change';
                                                $log->save();
                                            }

                                        }
                                        if($typeconf=='rr'){ /**aplicamos el mismo caso que ps**/

                                            //bloqueamos pppoe

                                            //GET all data for API
                                            $conf = Helpers::get_api_options('mikrotik');
                                            $API = new Mikrotik($con['port'],$conf['a'],$conf['t'],$conf['s']);
                                            $API->debug = $conf['d'];

                                            if ($API->connect($con['ip'], $con['login'], $con['password'])) {

                                                $rocket = new RocketCore();

                                                /**si es radius no tiene secret en el mkt**/
                                                // $STATUS = $rocket->block_ppp($API,$data,$target,$debug);

                                                if ($debug==1) {
                                                    if($process->check($data['status'])){
                                                        $API->disconnect();
                                                        return $process->check($data['status']);
                                                    }
                                                }

                                                $API->disconnect();
                                                $ip = $service->ip;
                                                if($data['status']=='true' || $data['status']=='ac'){

                                                    // con el comando coa mandamos a desconectar al cliente
                                                    $secret = Router::find($router_id)->radius->secret;
                                                    $ip_ro = Router::find($router_id)->ip;

                                                    Radusergroup::create([
                                                        'username' => $service->user_hot,
                                                        'groupname' => 'locked',
                                                        'priority' => 1
                                                    ]);

                                                    $st='de';
                                                    $online='off';
                                                    $m = "Se ha cortado el servicio de atención al cliente para ip $ip";
                                                    //descontamos el numero de clientes del plan
                                                    $counter->step_down_plan($client->plan_id);

                                                    $ejecucion = shell_exec('echo User-Name="'.$service->user_hot.'" | /usr/bin/radclient -c 1 -n 3 -r 3 -t 3 -x '.$ip_ro.':3799 disconnect '.$secret.' 2>&1');

                                                }
                                                else{
                                                    $st='ac';
                                                    $online='on';
                                                    $m = "Se ha activado el servicio de atención al cliente para ip $ip";
                                                    //incrementamos el numero de clientes en el plan
                                                    $counter->step_up_plan($client->plan_id);

                                                    Radusergroup::where('username',$service->user_hot)->delete();
                                                }

                                                //guardamos en la base de datos
                                                $service->status = $st;
                                                $service->online = $online;
                                                $service->save();
//                                                CommonService::log($m, $this->username, 'change' , $this->userId, $service->client_id);

                                                $log = new Logg();

                                                $log->detail = $m . ' ' . $nameClient;
                                                $log->user = "Automatic";
                                                $log->type = 'change';
                                                $log->save();
                                            }
                                        }


                                    }
							
							        sleep(7);
						        }
	//		                }
					        }
				        } catch(\Exception $exception) {
					        Log::debug("$service->id : ". $exception->getMessage());
					        continue;
				        }
			        }
		        }
		
		        foreach($clients as $client)  {
			        foreach($client->recurring_invoices as $recurring_invoice) {
				        if ($recurring_invoice->service_status == 'active') {
					        if(!is_null($recurring_invoice->expiration_date) && $recurring_invoice->expiration_date->format('Y-m-d') == Carbon::now()-> format('Y-m-d')) {
						        $recurring_invoice->service_status = 'block';
						        $recurring_invoice->save();
					        }
				        }
			        }
			
		        }
	        });

    }
}
