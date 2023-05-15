<?php

namespace App\Handlers;

use App\libraries\CountClient;
use App\libraries\Helper;
use App\libraries\Helpers;
use App\libraries\Mikrotik;
use App\libraries\Mkerror;
use App\libraries\Pencrypt;
use App\libraries\PermitidosList;
use App\libraries\Radius as RadiusLibrary;
use App\libraries\RocketCore;
use App\libraries\RouterConnect;
use App\libraries\SimpleQueuesTree;
use App\libraries\Slog;
use App\libraries\StatusIp;
use App\models\AddressRouter;
use App\models\ClientService;
use App\models\ControlRouter;
use App\models\GlobalSetting;
use App\models\Network;
use App\models\Plan;
use App\models\radius\Nas;
use App\models\radius\Radcheck;
use App\models\radius\Radreply;
use App\models\Router;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Config;


class AddServiceClientHandler implements ShouldQueue
{
    use InteractsWithQueue, Queueable;
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  object  $event
     * @return void
     */
    public function handle(\App\Events\AddServiceClientEvent $event)
    {
        set_time_limit(0);
        ini_set('memory_limit', '1G');

        try {
            $config = ControlRouter::where('router_id', '=', $event->router_id)->first();
            $typeconf = $config->type_control;
            $address_list = $config->address_list;

            $service = ClientService::with('client')->find($event->client_id);

            $router = new RouterConnect();
            $con = $router->get_connect($event->router_id);

            $conf = Helpers::get_api_options('mikrotik');
            //inicializacion de clases principales
            $API = new Mikrotik($con['port'], $conf['a'], $conf['t'], $conf['s']);
            $API->debug = $conf['d'];

            $rocket = new RocketCore();
            $error = new Mkerror();

            $counter = new CountClient();
            $usedip = new StatusIp();
            $log = new Slog();

            $global = GlobalSetting::all()->first();
            $debug = $global->debug;


            if ($typeconf == 'no') {
                # Sin control de trafico solo dhcp, arp o adv
                if ($API->connect($con['ip'], $con['login'], $con['password'])) {

                    $event->data['name'] =  $service->client->name.'_'.$service->id;
                    $error = new Mkerror();

                    $SB = $rocket->set_basic_config($API, $error, $event->data, $event->requestData['ip'], null, 'add', $debug);

                    //marcamos como ocupada la ip
                    $usedip->is_used_ip($event->requestData['ip'], $service->id, true);
                    // aumentamos el contador de numero de clientes del router
                    $counter->step_up_router($event->router_id);
                    // aumentamos el contador del plan
                    $counter->step_up_plan($event->requestData['plan']);
//                    $expclient->exp($service->id, $event->router_id, $pay_date);
                    //Desconectamos la API MIKROTIK
                    $API->disconnect();
                    //save log
                    $log->saveTo("Se ha registrado un cliente:", "success", $service->client->name, $event->authUser);

                }
            }

            if ($typeconf == 'sq') { //añadimos a simple queues

                if ($API->connect($con['ip'], $con['login'], $con['password'])) {


                    $event->data['name'] =  $service->client->name.'_'.$service->id;
                    $SQUEUES = $rocket->add_simple_queues($API, $event->data, $event->requestData['ip'], $debug);

                    if($address_list == 1) {
                        $list = PermitidosList::add($API,$event->data, $debug, $error);
                    }

                    $API->disconnect();
                    // agregamos a la BD

                    //marcamos como ocupada la ip
                    $usedip->is_used_ip($event->requestData['ip'], $service->id, true);
                    // aumentamos el contador de numero de clientes del router
                    $counter->step_up_router($event->router_id);
                    // aumentamos el contador del plan
                    $counter->step_up_plan($event->requestData['plan']);

                    //save log
                    $log->saveTo("Se ha registrado un cliente:", "success", $service->client->name, $event->authUser);

                }
            } // fin de simple queues

            if ($typeconf == 'st') { //añadimos a simple queues (with Tree)

                // agregamos a la BD
                $event->data['name'] =  $service->client->name.'_'.$service->id;
                $event->data['service_id'] = $service->id;


                if ($API->connect($con['ip'], $con['login'], $con['password'])) {

                    //marcamos como ocupada la ip
                    $usedip->is_used_ip($service->ip, $service->id, true);
                    // aumentamos el contador de numero de clientes del router
                    $counter->step_up_router($service->router_id);
                    // aumentamos el contador del plan
                    $counter->step_up_plan($service->plan_id);
                    //					$expclient->exp($id,$event->router_id,$pay_date);

//                  $SQUEUES = $rocket->add_simple_queue_with_tree($API, $event->data, $service->ip, 'add', $debug);
                    $rocket->set_basic_config($API,$error,$event->data,$service->ip,null,'add',$debug);

                    # add or update clients to parents
                    $P_DATA = $rocket->data_simple_queue_with_tree_parent($event->data['plan_id'],$event->data['router_id'],$event->data['download'],$event->data['upload'],$event->data['aggregation'],$event->data['limitat'],$event->data['burst_limit'],$event->data['burst_threshold'],$event->data['tree_priority']);


                    $dataNamePlan = Helper::replace_word($event->data['namePlan']);

                    if($event->data['tree_priority'] != 0) {
                        $dataNamePlan = $dataNamePlan.'_virtual_'.$event->data['tree_priority'];
                        $event->data['comment'] = $event->data['comment'].'_virtual_'.$event->data['tree_priority'];
                    }

                    //buscamos regla parent segun el plan
                    $parent = SimpleQueuesTree::simple_parent_get_id($API,$dataNamePlan);

                    if ($parent=='notFound') {
                        # Creamos parent
                        SimpleQueuesTree::add_simple_parent($API,$dataNamePlan,$P_DATA['ips'],$P_DATA['maxlimit'],$P_DATA['bl'],$P_DATA['bth'],$event->data['bt'],$P_DATA['limitat'],$event->data['priority'],$dataNamePlan);
                    }else{
                        # Actualizamos parent
                        SimpleQueuesTree::set_simple_parent($API,$parent[0]['.id'],$dataNamePlan,$P_DATA['maxlimit'],$P_DATA['ips'],$P_DATA['bl'],$P_DATA['bth'],$event->data['bt'],$P_DATA['limitat'],$event->data['priority'],$dataNamePlan);
                    }

                    $limitat = $P_DATA['limitat_up_cl'].'k/'.$P_DATA['limitat_down_cl'].'k';


                    $SQUEUES = SimpleQueuesTree::simple_parent_get_id($API,$service->client->name.'_'.$service->id);

                    if($SQUEUES != 'notFound'){

                        $QUEUES = SimpleQueuesTree::set_simple_child($API,$SQUEUES[0]['.id'],$service->client->name.'_'.$service->id,$event->data['maxlimit'],$service->ip,$dataNamePlan,$event->data['bl'],$event->data['bth'],$event->data['bt'],$limitat,$event->data['priority'],$event->data['comment']);

                    }
                    else{

                        $SQUEUES = SimpleQueuesTree::add_simple_child($API,$service->client->name.'_'.$service->id,$service->ip,$dataNamePlan,$event->data['maxlimit'],$event->data['bl'],$event->data['bth'],$event->data['bt'],$limitat,$event->data['priority'],$event->data['comment']);
                    }

                    if($address_list == 1) {
                        $list = PermitidosList::add($API,$event->data, $debug, $error);
                    }
                    //Desconectamos la API MIKROTIK
                    $API->disconnect();
                    //save log
                    $log->saveTo("Se ha registrado un cliente:", "success", $service->client->name, $event->authUser);
                }
            }// fin de simple queues (with Tree)

            if ($typeconf == 'dl') { //DHCP Leases

                if ($API->connect($con['ip'], $con['login'], $con['password'])) {

                    $event->data['name'] =  $service->client->name.'_'.$service->id;

                    $DHCP = $rocket->add_dhcp_leases($API, $event->data, $event->requestData['ip'], $debug);

                    if($address_list == 1) {
                        $list = PermitidosList::add($API,$event->data, $debug, $error);
                    }

                    //marcamos como ocupada la ip
                    $usedip->is_used_ip($event->requestData['ip'], $service->id, true);
                    // aumentamos el contador de numero de clientes del router
                    $counter->step_up_router($event->router_id);
                    $counter->step_up_plan($event->requestData['plan']);
                    // añadimos a la suspención de clientes
//                    $expclient->exp($service->id, $event->router_id, $pay_date);
                    //Desconectamos la API MIKROTIK
                    $API->disconnect();
                    //save log
                    $log->saveTo("Se ha registrado un cliente:", "success", $service->client->name, $event->authUser);
                }
            } //fin de DHCP Leases

            if ($typeconf == 'ps') { //PPP secrets Simple Queues

                if ($API->connect($con['ip'], $con['login'], $con['password'])) {

                    //get gateway for addres
                    $network = Network::where('ip', $event->requestData['ip'])->get();
                    $gat = AddressRouter::find($network[0]->address_id);


                    $event->data['name'] =  $service->client->name.'_'.$service->id;

                    $PPP = $rocket->add_ppp_simple($API, $event->data, $event->requestData['ip'], $gat->gateway, $debug);
                    if($address_list == 1) {
                        $list = PermitidosList::add($API,$event->data, $debug, $error);
                    }
                    //marcamos como ocupada la ip
                    $usedip->is_used_ip($event->requestData['ip'], $service->id, true);
                    // aumentamos el contador de numero de clientes del router
                    $counter->step_up_router($event->router_id);
                    $counter->step_up_plan($event->requestData['plan']);
                    // añadimos a la suspención de clientes
                    //Desconectamos la API MIKROTIK
                    $API->disconnect();
                    //save log
                    $log->saveTo("Se ha registrado un cliente:", "success", $service->client->name, $event->authUser);

                }

            }//fin de PPP secrets SimpleQueues

            if ($typeconf == 'pt') { //PPP secrets Simple Queues with tree

                $event->data['name'] =  $service->client->name.'_'.$service->id;
                $event->data['service_id'] = $service->id;
                event(new \App\Events\PppoeSimpleQueueWithTree($event->data, $API, $con, $event->requestData['ip'], $service, $event->requestData['plan'], $event->router_id, $rocket, $address_list, $log, $error, $debug));

            }//fin de PPP secrets SimpleQueues with tree

            if ($typeconf == 'pp') { //PPP secrets

                if ($API->connect($con['ip'], $con['login'], $con['password'])) {

                    //get gateway for addres
                    $network = Network::where('ip', $event->requestData['ip'])->get();
                    $gat = AddressRouter::find($network[0]->address_id);


                    $event->data['name'] =  $service->client->name.'_'.$service->id;

                    $PPP = $rocket->add_ppp_secrets($API, $event->data, $event->requestData['ip'], $gat->gateway, $debug);
                    if($address_list == 1) {
                        $list = PermitidosList::add($API,$event->data, $debug, $error);
                    }

                    //verificamos si esta activo la opcion de agregar pago

                    //marcamos como ocupada la ip
                    $usedip->is_used_ip($event->requestData['ip'], $service->id, true);
                    // aumentamos el contador de numero de clientes del router
                    $counter->step_up_router($event->router_id);
                    $counter->step_up_plan($event->requestData['plan']);
                    // añadimos a la suspención de clientes
//                    $expclient->exp($service->id, $event->router_id, $pay_date);
                    //Desconectamos la API MIKROTIK
                    $API->disconnect();
                    //save log
                    $log->saveTo("Se ha registrado un cliente:", "success", $service->client->name, $event->authUser);

                }
            } //fin de PPP secrets

            if ($typeconf == 'pa') { //PPP secrets + PCQ-Address List

                if ($API->connect($con['ip'], $con['login'], $con['password'])) {

                    //get gateway for addres
                    $network = Network::where('ip', $event->requestData['ip'])->get();
                    $gat = AddressRouter::find($network[0]->address_id);


                    $event->data['name'] =  $service->client->name.'_'.$service->id;

                    $PPP = $rocket->add_ppp_secrets_pcq($API, $event->data, $event->requestData['ip'], $gat->gateway, $debug);

                    if($address_list == 1) {
                        $list = PermitidosList::add($API,$event->data, $debug, $error);
                    }

                    //marcamos como ocupada la ip
                    $usedip->is_used_ip($event->requestData['ip'], $service->id, true);
                    // aumentamos el contador de numero de clientes del router
                    $counter->step_up_router($event->router_id);
                    $counter->step_up_plan($event->requestData['plan']);
                    // añadimos a la suspención de clientes
//                    $expclient->exp($service->id, $event->router_id, $pay_date);
                    //Desconectamos la API MIKROTIK
                    $API->disconnect();
                    //save log
                    $log->saveTo("Se ha registrado un cliente:", "success", $service->client->name, $event->authUser);

                }
            }

            if ($typeconf == 'pc') { // PCQ-ADDRESS LIST

                if ($API->connect($con['ip'], $con['login'], $con['password'])) {

                    $event->data['name'] =  $service->client->name.'_'.$service->id;
                    ///////////////////////////////////////////////////////////////
                    $PCQ_ADDRESS = $rocket->add_pcq_list($API, $event->data, $event->requestData['ip'], $debug);

                    if($address_list == 1) {
                        $list = PermitidosList::add($API,$event->data, $debug, $error);
                    }
                    //marcamos como ocupada la ip
                    $usedip->is_used_ip($event->requestData['ip'], $service->id, true);
                    // aumentamos el contador de numero de clientes del router
                    $counter->step_up_router($event->router_id);
                    $counter->step_up_plan($event->requestData['plan']);
                    // añadimos a la suspención de clientes
//                    $expclient->exp($service->id, $event->router_id, $pay_date);
                    //Desconectamos la API MIKROTIK
                    $API->disconnect();
                    //save log
                    $log->saveTo("Se ha registrado un cliente:", "success", $service->client->name, $event->authUser);

                }
            } //fin de PCQ-ADDRESS LIST


            if($typeconf == 'ra'){
                $router = Router::find($event->router_id);
                /**sacamos el control, ya que lo vamos hacer manual y no por radius**/
                /*$radreply_ip = Radreply::create([
                    'username' => $event->requestData['user_hot'],
                    'attribute' => 'Framed-IP-Address',
                    'op' => '=',
                    'value' => $event->requestData['ip']
                ]);

                $radreply_bandwidth = Radreply::create([
                    'username' => $event->requestData['user_hot'],
                    'attribute' => 'Mikrotik-Rate-Limit',
                    'op' => '=',
                    'value' => $event->data['upload'].'k/'.$event->data['download'].'k'
                ]);*/

                /**ahora insertamos cual es la password de conexion con radius**/
                $en = new Pencrypt();
                $radcheck_pass = Radcheck::create([
                    //'username' => $service->client->name.'_'.$service->id,
                    'username' => $event->requestData['user_hot'],
                    'attribute' => 'Cleartext-Password',
                    'op' => ':=',
                    'value' => $event->requestData['pass_hot']
                ]);


//                if ($API->connect($con['ip'], $con['login'], $con['password'])) {
//                    /**actualizamos los secret en el mkt**/
//                    $network = Network::where('ip', $event->requestData['ip'])->get();
//                    $gat = AddressRouter::find($network[0]->address_id);
//                    $event->data['name'] =  $service->client->name.'_'.$service->id;
//
//                    $PPP = $rocket->add_ppp_secrets($API, $event->data, $event->requestData['ip'], $gat->gateway, $debug);
//                    if($address_list == 1) {
//                        $list = PermitidosList::add($API,$event->data, $debug, $error);
//                    }
//                    $usedip->is_used_ip($event->requestData['ip'], $service->id, true);
//                    // aumentamos el contador de numero de clientes del router
//                    $counter->step_up_router($event->router_id);
//                    $counter->step_up_plan($event->requestData['plan']);
//
//                    $log->saveTo("Se ha registrado un cliente:", "success", $service->client->name, $event->authUser);
//                }


                /**ejecutamos el mismo caso que pt**/

                $event->data['name'] =  $service->client->name.'_'.$service->id;
                $event->data['service_id'] = $service->id;
                event(new \App\Events\PppoeSimpleQueueWithTree($event->data, $API, $con, $event->requestData['ip'], $service, $event->requestData['plan'], $event->router_id, $rocket, $address_list, $log, $error, $debug));


            }
            if($typeconf == 'rp'){
                $radcheck_pass = Radcheck::create([
                    //'username' => $service->client->name.'_'.$service->id,
                    'username' => $event->requestData['user_hot'],
                    'attribute' => 'Cleartext-Password',
                    'op' => ':=',
                    'value' => $event->requestData['pass_hot']
                ]);

                /*** ejecutamos el mismo caso que pa **/
                if ($API->connect($con['ip'], $con['login'], $con['password'])) {

                    //get gateway for addres
                    $network = Network::where('ip', $event->requestData['ip'])->get();
                    $gat = AddressRouter::find($network[0]->address_id);


                    $event->data['name'] =  $service->client->name.'_'.$service->id;

                    $PPP = $rocket->add_ppp_secrets_pcq($API, $event->data, $event->requestData['ip'], $gat->gateway, $debug);

                    if($address_list == 1) {
                        $list = PermitidosList::add($API,$event->data, $debug, $error);
                    }

                    //marcamos como ocupada la ip
                    $usedip->is_used_ip($event->requestData['ip'], $service->id, true);
                    // aumentamos el contador de numero de clientes del router
                    $counter->step_up_router($event->router_id);
                    $counter->step_up_plan($event->requestData['plan']);
                    // añadimos a la suspención de clientes
//                    $expclient->exp($service->id, $event->router_id, $pay_date);
                    //Desconectamos la API MIKROTIK
                    $API->disconnect();
                    //save log
                    $log->saveTo("Se ha registrado un cliente:", "success", $service->client->name, $event->authUser);

                }

            }
            if($typeconf == 'rr') {

                $radreply_ip = Radreply::create([
                    'username' => $event->requestData['user_hot'],
                    'attribute' => 'Framed-IP-Address',
                    'op' => '=',
                    'value' => $event->requestData['ip']
                ]);
                $plan_buscado = Plan::find($event->data['plan_id']);

                /**si el plan es con control en mkt*/
                if(!$plan_buscado['no_rules']){
                    $radreply_bandwidth = Radreply::create([
                        'username' => $event->requestData['user_hot'],
                        'attribute' => 'Mikrotik-Rate-Limit',
                        'op' => '=',
                        'value' => $event->data['upload'].'k/'.$event->data['download'].'k'
                    ]);
                }

                $radcheck_pass = Radcheck::create([
                    //'username' => $service->client->name.'_'.$service->id,
                    'username' => $event->requestData['user_hot'],
                    'attribute' => 'Cleartext-Password',
                    'op' => ':=',
                    'value' => $event->requestData['pass_hot']
                ]);

                /*** ejecutamos el mismo caso que ps **/
                if ($API->connect($con['ip'], $con['login'], $con['password'])) {

                    //get gateway for addres
                    $network = Network::where('ip', $event->requestData['ip'])->get();
                    $gat = AddressRouter::find($network[0]->address_id);


                    $event->data['name'] =  $service->client->name.'_'.$service->id;
                    /**si el plan es con control en mkt*/
                    if(!$plan_buscado['no_rules'])
                        $PPP = $rocket->add_ppp_simple($API, $event->data, $event->requestData['ip'], $gat->gateway, $debug, true);

                    if($address_list == 1) {
                        $list = PermitidosList::add($API,$event->data, $debug, $error);
                    }
                    //marcamos como ocupada la ip
                    $usedip->is_used_ip($event->requestData['ip'], $service->id, true);
                    // aumentamos el contador de numero de clientes del router
                    $counter->step_up_router($event->router_id);
                    $counter->step_up_plan($event->requestData['plan']);
                    // añadimos a la suspención de clientes
                    //Desconectamos la API MIKROTIK
                    $API->disconnect();
                    //save log
                    $log->saveTo("Se ha registrado un cliente:", "success", $service->client->name, $event->authUser);

                }

            }



//            $jobId = $this->job->getJobId();
//
//            DB::table('jobs')->where('id', $jobId)->delete();

        } catch(\Exception $exception) {
            Log::error($exception->getMessage());
        }

    }
}
