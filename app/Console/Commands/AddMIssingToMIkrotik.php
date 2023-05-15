<?php

namespace App\Console\Commands;

use App\libraries\Burst;
use App\libraries\GetPlan;
use App\libraries\Helper;
use App\libraries\Helpers;
use App\libraries\Mikrotik;
use App\libraries\Mkerror;
use App\libraries\Pencrypt;
use App\libraries\PermitidosList;
use App\libraries\RocketCore;
use App\libraries\RouterConnect;
use App\libraries\SimpleQueuesTree;
use App\libraries\Slog;
use App\models\AddressRouter;
use App\models\ClientService;
use App\models\ControlRouter;
use App\models\GlobalSetting;
use App\models\Network;
use App\models\Router;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class AddMIssingToMIkrotik extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'Add-missing';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command is for upgrading version 3 to version 4 ( Multiple services for a client )';

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

        Artisan::call('config:clear');
        Artisan::call('cache:clear');

        ini_set('memory_limit', '-1');

        Router::chunkById(5, function($routers) {
	        foreach($routers as $router) {
		        // Add again to mikrotik api
		        $config = ControlRouter::where('router_id', '=', $router->id)->first();
		        $services = ClientService::with('client')->where('router_id', $router->id)->get();
		        echo "Total clients are :- ".$services->count();
		        $this->addMikrotikApi($router, $config, $services);
	        }
        });

        
    }


    public function addMikrotikApi($router, $config, $services) {
        $router_id = $router->id;

        try {

            $typeconf = $config->type_control;

            $router = new RouterConnect();
            $con = $router->get_connect($router_id);

            $conf = Helpers::get_api_options('mikrotik');
            //inicializacion de clases principales
            $API = new Mikrotik($con['port'], $conf['a'], $conf['t'], $conf['s']);
            $API->debug = $conf['d'];

            $rocket = new RocketCore();
            $error = new Mkerror();

            $log = new Slog();
            $user_count = 0;

            //old data
            $old_arp = $config->arpmac;
            $old_dhcp = $config->dhcp;
            $old_adv = $config->adv;
            $address_list = ($config->address_list == 1) ? true : false;

            $options = array(
                //new data
                'adv' =>  $old_adv,
                'arp' => $old_arp,
                'dhcp' => $old_dhcp,
                //old data
                'old_adv' => $old_adv,
                'old_dhcp' => $old_dhcp,
                'old_arp' => $old_arp,
                //other data
                'lan' => $con['lan']

            );

            $global = GlobalSetting::first();
            $debug = $global->debug;
            if ($API->connect($con['ip'], $con['login'], $con['password'])) {
                foreach($services as $service) {
                    $user_count ++;
                    $pl = new GetPlan();
                    $plan = $pl->get($service->plan_id);
                    //Obtenemos opciones avanzada burst
                    $burst = Burst::get_all_burst($plan['upload'],$plan['download'],$plan['burst_limit'],$plan['burst_threshold'],$plan['limitat']);
                    //preparamos la data del cliente
                    $en = new Pencrypt();

                    $data = array(
                        //general data
                        'name' => $service->client->name.'_'.$service->id,
                        'typeauth' => $service->typeauth, //default login
                        'profile' => $plan['name'],
                        'user' => empty($service->user_hot) ? 'User-'.$user_count : $service->user_hot,
                        'pass' => $service->pass_hot == '0' ? '0': $en->decode($service->pass_hot),
                        'mac' => $service->mac,
                        //options set default
                        'arp' => $options['old_arp'],
                        'adv' => $options['old_adv'],
                        'dhcp' => $options['old_dhcp'],
                        'drop' => 0,
                        'lan' => $options['lan'],
                        'namePlan' => $plan['name'],
                        'maxlimit' => $plan['maxlimit'],
                        'comment' => 'SmartISP - '.$plan['name'],
                        //advanced
                        'bl' => $burst['blu'].'/'.$burst['bld'],
                        'bth' => $burst['btu'].'/'.$burst['btd'],
                        'bt' => $plan['burst_time'].'/'.$plan['burst_time'],
                        'priority' => $plan['priority'].'/'.$plan['priority'],
                        'priority_a' => $plan['priority'],
                        'limit_at' => $burst['lim_at_up'].'/'.$burst['lim_at_down'],
                        //advanced for pcq
                        'speed_down' => $plan['download'],
                        'speed_up' => $plan['upload'],
                        //'num_cl' => $plan['num_clients']-1,
                        'rate_down' => $plan['download'].'k',
                        'rate_up' => $plan['upload'].'k',
                        'burst_rate_down' => $burst['bld'],
                        'burst_rate_up' => $burst['blu'],
                        'burst_threshold_down' => $burst['btd'],
                        'burst_threshold_up' => $burst['btu'],
                        'limit_at_down' => $burst['lim_at_down'],
                        'limit_at_up' => $burst['lim_at_up'],
                        'burst_time' => $plan['burst_time'],
                        //for simple queue with tree
                        'plan_id' => $service->plan_id,
                        'router_id' => $service->router_id,
                        'download' => $plan['download'],
                        'upload' => $plan['upload'],
                        'aggregation' => $plan['aggregation'],
                        'limitat' => $plan['limitat'],
                        'burst_limit' => $plan['burst_limit'],
                        'burst_threshold' => $plan['burst_threshold'],
                        'tree_priority' => $service->tree_priority,
                        'oldtarget' => $service->ip,
                        'newtarget' => $service->ip,
                        'ip' => $service->ip,
	                    'no_rules' => $plan['no_rules'],

                    );//end data array

                    //////////start delete previous configuration ///////////

                    //set data for delete items

                    if ($service->status=='de') {
                        if ($options['adv']==1) {
                            $data['drop']=1;
                        }else{
                            $data['drop']=0;
                        }
                    }

                    /////////Start add new configuration //////////////////

                    $data['adv'] = $options['adv'];
                    $data['arp'] = $options['arp'];
                    $data['dhcp'] = $options['dhcp'];

                    if ($service->status=='de') {
                        if ($options['adv']==1) {
                            $data['drop']=0;
                        }else{
                            $data['drop']=1;
                        }
                    }

                    if ($typeconf == 'sq') { //añadimos a simple queues
                        $SQUEUES = $rocket->add_simple_queues($API, $data, $service->ip, $debug);

                        if($address_list == 1) {
                            $list = PermitidosList::add($API,$data, $debug, $error);
                        }
                    }

                    if ($typeconf == 'st') { //añadimos a simple queues (with Tree)
                        $this->info($service->client->name.'_'.$service->ip.'_'.$user_count);
                        // agregamos a la BD
                        $data['service_id'] = $service->id;

                        $rocket->set_basic_config($API,$error,$data,$service->ip,null,'add',$debug);

                        $dataNamePlan = Helper::replace_word($data['namePlan']);

                        if($data['tree_priority'] != 0) {
                            $dataNamePlan = $dataNamePlan.'_virtual_'.$data['tree_priority'];
                            $data['comment'] = $data['comment'].'_virtual_'.$data['tree_priority'];
                        }

                        # add or update clients to parents
                        $P_DATA = $this->data_simple_queue_with_tree_parent($data['plan_id'],$data['router_id'],$data['download'],$data['upload'],$data['aggregation'],$data['limitat'],$data['burst_limit'],$data['burst_threshold'], $data['tree_priority']);

                            //buscamos regla parent segun el plan
                            $parent = SimpleQueuesTree::simple_parent_get_id($API,$dataNamePlan);

                        if ($parent=='notFound') {
//                            dd($P_DATA['ips']);
                            # Creamos parent
                            $addSimpleParent = SimpleQueuesTree::add_simple_parent($API,$dataNamePlan,$P_DATA['ips'],$P_DATA['maxlimit'],$P_DATA['bl'],$P_DATA['bth'],$data['bt'],$P_DATA['limitat'],$data['priority'],$dataNamePlan);

                        }
//                        else{
//                            # Actualizamos parent
//                            $addSimpleParent = SimpleQueuesTree::set_simple_parent($API,$parent[0]['.id'],$dataNamePlan,$P_DATA['maxlimit'],$P_DATA['ips'],$P_DATA['bl'],$P_DATA['bth'],$data['bt'],$P_DATA['limitat'],$data['priority'],$dataNamePlan);
//                        }

                        $limitat = $P_DATA['limitat_up_cl'].'k/'.$P_DATA['limitat_down_cl'].'k';


                        $SQUEUES = SimpleQueuesTree::simple_parent_get_id($API,$service->client->name.'_'.$service->id);

                        if($SQUEUES != 'notFound'){

                            $queue = SimpleQueuesTree::set_simple_child($API,$SQUEUES[0]['.id'],$service->client->name.'_'.$service->id,$data['maxlimit'],$service->ip,$dataNamePlan,$data['bl'],$data['bth'],$data['bt'],$limitat,$data['priority'],$data['comment']);

                        }
                        else{

                            $queue = SimpleQueuesTree::add_simple_child($API,$service->client->name.'_'.$service->id,$service->ip,$dataNamePlan,$data['maxlimit'],$data['bl'],$data['bth'],$data['bt'],$limitat,$data['priority'], $data['comment']);
                        }

                        if($address_list == 1) {
                            $list = PermitidosList::add($API,$data, $debug, $error);
                        }

                    }// fin de simple queues (with Tree)

                    if ($typeconf == 'dl') { //DHCP Leases

                        $data['name'] =  $service->client->name.'_'.$service->id;

                        $DHCP = $rocket->add_dhcp_leases($API, $data, $service->ip, $debug);

                        if($address_list == 1) {
                            $list = PermitidosList::add($API,$data, $debug, $error);
                        }
                    } //fin de DHCP Leases

                    if ($typeconf == 'ps') { //PPP secrets Simple Queues
                        //get gateway for addres
                        $network = Network::where('ip', $service->ip)->get();
                        $gat = AddressRouter::find($network[0]->address_id);


                        $data['name'] =  $service->client->name.'_'.$service->id;

                        $PPP = $rocket->add_ppp_simple($API, $data, $service->ip, $gat->gateway, $debug);
                        if($address_list == 1) {
                            $list = PermitidosList::add($API,$data, $debug, $error);
                        }

                    }//fin de PPP secrets SimpleQueues

                    if ($typeconf == 'pt') { //PPP secrets Simple Queues with tree

                        $data['name'] =  $service->client->name.'_'.$service->id;
                        $data['service_id'] = $service->id;
                        event(new \App\Events\PppoeSimpleQueueWithTree($data, $API, $con, $service->ip, $service, $service->plan_id, $router_id, $rocket, $address_list, $log, $error, $debug));

                    }//fin de PPP secrets SimpleQueues with tree

                    if ($typeconf == 'pp') { //PPP secrets
                        //get gateway for addres
                        $network = Network::where('ip', $service->ip)->get();
                        $gat = AddressRouter::find($network[0]->address_id);


                        $data['name'] =  $service->client->name.'_'.$service->id;

                        $PPP = $rocket->add_ppp_secrets($API, $data, $service->ip, $gat->gateway, $debug);
                        if($address_list == 1) {
                            $list = PermitidosList::add($API,$data, $debug, $error);
                        }

                    } //fin de PPP secrets

                    if ($typeconf == 'pa') { //PPP secrets + PCQ-Address List

                        //get gateway for addres
                        $network = Network::where('ip', $service->ip)->get();
                        $gat = AddressRouter::find($network[0]->address_id);


                        $data['name'] =  $service->client->name.'_'.$service->id;

                        $PPP = $rocket->add_ppp_secrets_pcq($API, $data, $service->ip, $gat->gateway, $debug);

                        if($address_list == 1) {
                            $list = PermitidosList::add($API,$data, $debug, $error);
                        }

                    }

                    if ($typeconf == 'pc') { // PCQ-ADDRESS LIST
                        $data['name'] =  $service->client->name.'_'.$service->id;
                        ///////////////////////////////////////////////////////////////
                        $PCQ_ADDRESS = $rocket->add_pcq_list($API, $data, $service->ip, $debug);

                        if($address_list == 1) {
                            $list = PermitidosList::add($API,$data, $debug, $error);
                        }
                    } //fin de PCQ-ADDRESS LIST

                }

            } // fin de simple queues

            $API->disconnect();


        } catch(\Exception $exception) {
            \Log::error($exception->getMessage());
        }

    }

    public function data_simple_queue_with_tree_parent($plan_id,$router_id,$plan_down,$plan_up,$plan_aggr,$plan_limitat,$plan_bl,$plan_th, $tree_priority){

        ////////////////////////////// CALCULATE PARAMETRES //////////////////////////////////////////

        //Buscamos los clientes asociados al plan
        $clientsData = ClientService::where('plan_id',$plan_id)->where('router_id',$router_id)->where('status','ac')->where('tree_priority', $tree_priority)->get();

        $clients = ClientService::where('plan_id',$plan_id)->where('router_id',$router_id)->where('status','ac')->get();

        $ips = Helpers::get_ips($clients);
        $ipsCount = Helpers::get_ips($clientsData);

        $download = $plan_down / $plan_aggr;
        $upload = $plan_up / $plan_aggr;

        $speed = Burst::get_percent_kb($upload,$download,$plan_limitat);

        if ($ips['ncl'] > $plan_aggr) {

            //Parents
            $maxlimit_down_parent = $download * $ips['ncl'];
            $maxlimit_up_parent = $upload * $ips['ncl'];

            $limit_at_down_parent = round($speed['download'] * $ips['ncl'],0,PHP_ROUND_HALF_DOWN);
            $limit_at_up_parent = round($speed['upload'] * $ips['ncl'],0,PHP_ROUND_HALF_DOWN);
            //sin redondeo
            //$limit_at_down_parent = $speed['download'] * $ips['ncl'];
            //$limit_at_up_parent = $speed['upload'] * $ips['ncl'];

        }else{

            $maxlimit_down_parent = $plan_down;
            $maxlimit_up_parent = $plan_up;

            $limit_at_down_parent = round($speed['download'] * $plan_aggr,0,PHP_ROUND_HALF_DOWN);
            $limit_at_up_parent = round($speed['upload'] * $plan_aggr,0,PHP_ROUND_HALF_DOWN);
            //sin redondeo
            //$limit_at_down_parent = $speed['download'] * $plan_aggr;
            //$limit_at_up_parent = $speed['upload'] * $plan_aggr;

        }


        $burst_parent = Burst::get_all_burst($maxlimit_up_parent,$maxlimit_down_parent,$plan_bl,$plan_th,100);

        //Prepare data
        $maxlimit = $maxlimit_up_parent.'k/'.$maxlimit_down_parent.'k';
        $limitat = $limit_at_up_parent.'k/'.$limit_at_down_parent.'k';
        $bl = $burst_parent['blu'].'/'.$burst_parent['bld'];
        $bth = $burst_parent['btu'].'/'.$burst_parent['btd'];

        $dt = array(
            'ips' => $ipsCount['ips'],
            'ncl' => $ips['ncl'],
            'maxlimit' => $maxlimit,
            'bl' => $bl,
            'bth' => $bth,
            'limitat' => $limitat,
            //for clients
            'limitat_up_cl' => $ips['ncl']==0 ? '0' : round($limit_at_up_parent / $ips['ncl'],0,PHP_ROUND_HALF_DOWN),
            'limitat_down_cl' => $ips['ncl']==0 ? '0' : round($limit_at_down_parent / $ips['ncl'],0,PHP_ROUND_HALF_DOWN)
            //sin redondeo
            //'limitat_up_cl' => $ips['ncl']==0 ? '0' : $limit_at_up_parent / $ips['ncl'],
            //'limitat_down_cl' => $ips['ncl']==0 ? '0' : $limit_at_down_parent / $ips['ncl'],
        );

        return $dt;

    }
}
