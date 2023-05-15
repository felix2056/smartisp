<?php

namespace App\Handlers;

use App\libraries\Burst;
use App\libraries\Chkerr;
use App\libraries\Firewall;
use App\libraries\GetPlan;
use App\libraries\Helper;
use App\libraries\Helpers;
use App\libraries\Mikrotik;
use App\libraries\Mkerror;
use App\libraries\MkMigrate;
use App\libraries\PermitidosList;
use App\libraries\QueueTree;
use App\libraries\RecalculateSpeed;
use App\libraries\RocketCore;
use App\libraries\RouterConnect;
use App\libraries\SimpleQueuesTree;
use App\libraries\StatusIp;
use App\models\AddressRouter;
use App\models\ClientService;
use App\models\ControlRouter;
use App\models\GlobalSetting;
use App\models\Network;
use App\models\Plan;
use App\models\radius\Radcheck;
use App\models\radius\Radreply;
use App\models\Router;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Http\Response;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class UpdateServiceClientHandler implements ShouldQueue
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
    public function handle(\App\Events\UpdateServiceClientEvent $event)
    {

        set_time_limit(0);
        ini_set('memory_limit', '1G');
        try {
            $usedip = new StatusIp();
            $global = GlobalSetting::all()->first();
            $debug = $global->debug;

            $client = $event->oldServiceDetails;

            //prepare old data//
            $oldtarget = $client['ip'];
            $oldplan = $client['plan_id'];
            $old_router = $client['router_id'];
            $oldtype = ControlRouter::where('router_id', '=', $old_router)->get();
            $type = ControlRouter::where('router_id', '=', $event->data['router_id'])->get();
            $process = new Chkerr();
            //verificamos si esta cambiando de router
            if ($event->data['changeRouter']) {

                if ($oldtype[0]['adv'] == 1) {
                    $odrop = 0;
                } else {
                    $odrop = 1;
                }

                $pl = new GetPlan();
                $plan = $pl->get($oldplan);
                //get  burst profiles
                $burst = Burst::get_all_burst($plan['upload'], $plan['download'], $plan['burst_limit'], $plan['burst_threshold'], $plan['limitat']);

                $num_cli = ClientService::where('plan_id', '=', $oldplan)->where('status', 'ac')->where('router_id', $old_router)->count();

                $oldData = array(
                    'name' => $event->data['name'],
                    'user' => $client['user_hot'],
                    'mac' => $client['mac'],
                    'arp' => $oldtype[0]['arpmac'],
                    'adv' => $oldtype[0]['adv'],
                    'drop' => $odrop,
                    'dhcp' => $oldtype[0]['dhcp'],
                    'namePlan' => $plan['name'],
                    'plan_id' => $client['plan_id'],
                    'router_id' => $client['router_id'],
                    'typeauth' => $client['typeauth'],

                    //advanced for pcq
                    'num_cl' => $num_cli,
                    'speed_down' => $plan['download'],
                    'speed_up' => $plan['upload'],
                    'rate_down' => $plan['download'] . 'k',
                    'rate_up' => $plan['upload'] . 'k',
                    'burst_rate_down' => $burst['bld'],
                    'burst_rate_up' => $burst['blu'],
                    'burst_threshold_down' => $burst['btd'],
                    'burst_threshold_up' => $burst['btu'],
                    'limit_at_down' => $burst['lim_at_down'],
                    'limit_at_up' => $burst['lim_at_up'],
                    'burst_time' => $plan['burst_time'],
                    'priority_a' => $plan['priority'],
                    //for simple queue with tree
                    'download' => $plan['download'],
                    'upload' => $plan['upload'],
                    'aggregation' => $plan['aggregation'],
                    'limitat' => $plan['limitat'],
                    'burst_limit' => $plan['burst_limit'],
                    'burst_threshold' => $plan['burst_threshold'],
                    'bt' => $plan['burst_time'] . '/' . $plan['burst_time'],
                    'priority' => $plan['priority'] . '/' . $plan['priority'],
                    'maxlimit' => $plan['maxlimit'],
                    'bl' => $burst['blu'] . '/' . $burst['bld'],
                    'bth' => $burst['btu'] . '/' . $burst['btd'],
                    'limit_at' => $event->data['limit_at'],
                    'comment' => 'SmartISP - '.$plan['name'],
                    'tree_priority' => $event->data['tree_priority'],
                );
                //iniciamos el migrador
                $migrate = new MkMigrate();
                $process = new Chkerr();
                $rocket = new RocketCore();

                $router = new RouterConnect();
                $con = $router->get_connect($event->data['router_id']);

                //GET all data for API
                $conf = Helpers::get_api_options('mikrotik');
                $API = new Mikrotik($con['port'], $conf['a'], $conf['t'], $conf['s']);
                $API->debug = $conf['d'];


                if ($API->connect($con['ip'], $con['login'], $con['password'])) {
                    $event->data['rate_down'] = $event->data['rate_down'] . 'k';
                    $event->data['rate_up'] = $event->data['rate_up'] . 'k';

                    $MK = $migrate->migrate_up($API, $rocket, $event->data, $event->data['newtarget'], $type[0]['type_control'], $debug);

                    if ($debug == 1) {
                        if ($process->check($MK)) {
                            return $process->check($MK);
                        }
                    }

                    //desactivamos la ip si esta cambiando
                    if ($event->data['changeIP']) { //esta cambiando de ip
                        //activamos la nueva IP
                        $usedip->is_used_ip($event->data['newtarget'], $event->data['client_id'], true);
                    }
                } else {
                    return Response::json([['msg' => 'errorConnect']]);
                }

                //abrimos otra conexión nueva
                $router = new RouterConnect();
                $con = $router->get_connect($old_router);
                $API = new Mikrotik($con['port'], $conf['a'], $conf['t'], $conf['s']);
                $API->debug = $conf['d'];

                if ($API->connect($con['ip'], $con['login'], $con['password'])) {

                    // Eliminanos de la anterior configuracion
                    $MK = $migrate->remove_previous($API, $rocket, $oldData, $oldtarget, $oldtype[0]['type_control'], $debug);

                    if ($debug == 1) {
                        //control de y procesamiento de errores
                        if ($process->check($MK)) {
                            return $process->check($MK);
                        }
                    }

                    if ($event->data['changeIP']) { //esta cambiando de ip
                        //desactivamos la IP anterior
                        $usedip->is_used_ip($oldtarget, $event->data['client_id'], false);
                    }

                } else {
                    return Response::json([['msg' => 'errorConnect']]);
                }

                return $process->show('success');

            } //fin del if change
            else { // no esta cambiando de router

                $router = new RouterConnect();
                $con = $router->get_connect($event->data['router_id']);

                //GET all data for API
                $conf = Helpers::get_api_options('mikrotik');
                $API = new Mikrotik($con['port'], $conf['a'], $conf['t'], $conf['s']);
                $API->debug = $conf['d'];

                $address_list = $type[0]->address_list;

                //actualizamos en la base de datos

                $error = new Mkerror();
                //Verificamos el tipo de control
                switch ($type[0]['type_control']) {

                    case 'no': //no shaping control only arp, adv or drop
                        if ($API->connect($con['ip'], $con['login'], $con['password'])) {
                            $rocket = new RocketCore();


                            $UPDATE = $rocket->set_basic_config($API, $error, $event->data, $oldtarget, $event->data['newtarget'], 'update', $debug);

                            if ($debug == 1) {
                                if ($UPDATE != false) {
                                    return $UPDATE;
                                }
                            }

                            //actualizamos el estado del ip en IP/Redes
                            if ($event->data['changeIP']) { //esta cambiando de IP
                                //desactivamos la IP anterior
                                $usedip->is_used_ip($oldtarget, $event->data['client_id'], false);
                                //activamos la nueva IP
                                $usedip->is_used_ip($event->data['newtarget'], $event->data['client_id'], true);
                            } else { //no esta cambiando la ip pero actualizamos el estado.
                                $usedip->refresh_ip($oldtarget, $event->data['client_id']);
                            }

                        }

                        break;

                    case 'sq': //Control simple Queues
                        # verificamos si el cliente sera actualizado en el router

                        if ($API->connect($con['ip'], $con['login'], $con['password'])) {

                            $rocket = new RocketCore();
                            $UPDATE = $rocket->update_simple_queues($API, $event->data, $oldtarget, $event->data['newtarget'], $debug);

                            if($address_list == 1) {
                                $list = PermitidosList::add($API,$event->data, $debug, $error);
                            }

                            if ($debug == 1) {
                                if ($process->check($UPDATE)) {
                                    $API->disconnect();
                                    return $process->check($UPDATE);
                                }
                            }

                            //actualizamos el estado del ip en IP/Redes
                            if ($event->data['changeIP']) { //esta cambiando de IP
                                //desactivamos la IP anterior
                                $usedip->is_used_ip($oldtarget, $event->data['client_id'], false);
                                //activamos la nueva IP
                                $usedip->is_used_ip($event->data['newtarget'], $event->data['client_id'], true);
                            } else { //no esta cambiando la ip pero actualizamos el estado.
                                $usedip->refresh_ip($oldtarget, $event->data['client_id']);
                            }

                        }

                        break;

                    case 'st': //Simple queues with tree

//                        event(new SimpleQueueWithTreeUpdate($event->data, $API, $con,$event->data['changeIP'],$usedip,$oldtarget,$address_list,$debug,$error));

                        if($API->connect($con['ip'], $con['login'], $con['password'])){

                            //actualizamos el estado del ip en IP/Redes
                            if ($event->data['changeIP']) { //esta cambiando de IP
                                //desactivamos la IP anterior
                                $usedip->is_used_ip($oldtarget,$event->data['client_id'],false);
                                //activamos la nueva IP
                                $usedip->is_used_ip($event->data['newtarget'],$event->data['client_id'],true);
                            }
                            else{ //no esta cambiando la ip pero actualizamos el estado.
                                $usedip->refresh_ip($oldtarget,$event->data['client_id']);
                            }

                        }
                        $rocket = new RocketCore();

/**Fix to update target old plan*/
                        $plan = Plan::find( $event->oldServiceDetails['plan_id']);
                        $dataNamePlan = $plan->name;
                        $dataNamePlan = Helper::replace_word($dataNamePlan);
                        $bt = $plan->burst_time . '/' . $plan->burst_time;
                        $priotiry = $plan->priority;
                        $P_DATA = $rocket->data_simple_queue_with_tree_parent(
                            $event->oldServiceDetails['plan_id'],
                            $event->oldServiceDetails['router_id'],
                            $plan->download,
                            $plan->upload,
                            $plan->aggregation,
                            $plan->limitat,
                            $plan->burst_limit,
                            $plan->burst_threshold,
                            $event->oldServiceDetails['tree_priority'],
                        );

                        $parent = SimpleQueuesTree::simple_parent_get_id($API, $dataNamePlan);
                        SimpleQueuesTree::set_simple_parent($API,$parent[0]['.id'],$dataNamePlan,$P_DATA['maxlimit'],$P_DATA['ips'],$P_DATA['bl'],$P_DATA['bth'],$bt,$P_DATA['limitat'],$priotiry,$dataNamePlan);

                        //si no tenemos mas clientes en este plan, eliminamos la cola ya que si no no podemos dejar un target en blanco
                        if ($P_DATA['ncl'] == 0) {
                            SimpleQueuesTree::simple_parent_remove($API,$parent[0]['.id']);
                        }
/**end fix */

                        $UPDATE = $rocket->update_simple_queue_with_tree($API,$event->data,$oldtarget,$event->data['newtarget'],$debug);

                        if($event->address_list == 1) {
                            $list = PermitidosList::add($API,$event->data, $debug, $error);
                        }
//                        return $process->show('success');

                        break;

                    case 'dl': //control DHCP leases

                        if ($API->connect($con['ip'], $con['login'], $con['password'])) {

                            $rocket = new RocketCore();
                            $UPDATE = $rocket->update_dhcp_leases_user($API, $event->data, $oldtarget, $event->data['newtarget'], $debug);

                            if($address_list == 1) {
                                $list = PermitidosList::add($API,$event->data, $debug, $error);
                            }

                            if ($debug == 1) {
                                if ($process->check($UPDATE)) {
                                    $API->disconnect();
                                    return $process->check($UPDATE);
                                }
                            }

                            //actualizamos el estado del ip en IP/Redes
                            if ($event->data['changeIP']) { //esta cambiando de IP
                                //desactivamos la IP anterior
                                $usedip->is_used_ip($oldtarget, $event->data['client_id'], false);
                                //activamos la nueva IP
                                $usedip->is_used_ip($event->data['newtarget'], $event->data['client_id'], true);
                            } else { //no esta cambiando la ip pero actualizamos el estado.
                                $usedip->refresh_ip($oldtarget, $event->data['client_id']);
                            }
                        }

                        break;

                    case 'pt': //control PPP Simple Queue with tree

//                        event(new PppoeSimpleQueueWithTreeUpdate($event->data, $API, $con,$event->data['changeIP'],$usedip,$oldtarget,$address_list,$debug,$error));

                        if ($API->connect($con['ip'], $con['login'], $con['password'])){

                            //get gateway for addres
                            $network = Network::where('ip',$event->data['newtarget'])->get();

                            $gat = AddressRouter::find($network[0]->address_id);

                            //actualizamos el estado del ip en IP/Redes
                            if ($event->data['changeIP']) { //esta cambiando de IP
                                //desactivamos la IP anterior
                                $usedip->is_used_ip($oldtarget,$event->data['client_id'],false);
                                //activamos la nueva IP
                                $usedip->is_used_ip($event->data['newtarget'],$event->data['client_id'],true);
                            }
                            else { //no esta cambiando la ip pero actualizamos el estado.
                                $usedip->refresh_ip($oldtarget, $event->data['client_id']);
                            }

                            $rocket = new RocketCore();


/**Fix to update target old plan*/
                            $plan = Plan::find( $event->oldServiceDetails['plan_id']);
                            $dataNamePlan = $plan->name;
                            $dataNamePlan = Helper::replace_word($dataNamePlan);
                            $bt = $plan->burst_time . '/' . $plan->burst_time;
                            $priotiry = $plan->priority;
                            $P_DATA = $rocket->data_simple_queue_with_tree_parent(
                                $event->oldServiceDetails['plan_id'],
                                $event->oldServiceDetails['router_id'],
                                $plan->download,
                                $plan->upload,
                                $plan->aggregation,
                                $plan->limitat,
                                $plan->burst_limit,
                                $plan->burst_threshold,
                                $event->oldServiceDetails['tree_priority'],
                            );

                            $parent = SimpleQueuesTree::simple_parent_get_id($API, $dataNamePlan);
                            SimpleQueuesTree::set_simple_parent($API,$parent[0]['.id'],$dataNamePlan,$P_DATA['maxlimit'],$P_DATA['ips'],$P_DATA['bl'],$P_DATA['bth'],$bt,$P_DATA['limitat'],$priotiry,$dataNamePlan);

                            //si no tenemos mas clientes en este plan, eliminamos la cola ya que si no no podemos dejar un target en blanco
                            if ($P_DATA['ncl'] == 0) {
                                SimpleQueuesTree::simple_parent_remove($API,$parent[0]['.id']);
                            }
/**end fix */

                            $UPDATE = $rocket->update_ppp_simple_queue_with_tree($API,$event->data,$oldtarget,$event->data['newtarget'],$gat->gateway,$debug);

                            if($address_list == 1) {
                                $list = PermitidosList::add($API,$event->data, $debug, $error);
                            }

                        }

                        break;

                    case 'ps': //control PPP Simple Queue

                        if ($API->connect($con['ip'], $con['login'], $con['password'])){

                            //get gateway for addres
                            $network = Network::where('ip',$event->data['newtarget'])->get();

                            if (count($network)==0) {
                                return $process->show('error_no_address');
                            }

                            $gat = AddressRouter::find($network[0]->address_id);

                            $rocket = new RocketCore();

                            $UPDATE = $rocket->update_ppp_simple($API,$event->data,$oldtarget,$event->data['newtarget'],$gat->gateway,$debug);

                            if($address_list == 1) {
                                $list = PermitidosList::add($API,$event->data, $debug, $error);
                            }

                            if ($debug==1) {
                                if($process->check($UPDATE)){
                                    $API->disconnect();
                                    return $process->check($UPDATE);
                                }
                            }

                            //actualizamos el estado del ip en IP/Redes
                            if ($event->data['changeIP']) { //esta cambiando de IP
                                //desactivamos la IP anterior
                                $usedip->is_used_ip($oldtarget,$event->data['client_id'],false);
                                //activamos la nueva IP
                                $usedip->is_used_ip($event->data['newtarget'],$event->data['client_id'],true);
                            }
                            else{ //no esta cambiando la ip pero actualizamos el estado.
                                $usedip->refresh_ip($oldtarget,$event->data['client_id']);
                            }

                        }

                        break;

                    case 'pp': //control PPP

                        if ($API->connect($con['ip'], $con['login'], $con['password'])) {

                            //get gateway for addres
                            $network = Network::where('ip', $event->data['newtarget'])->get();

                            if (count($network) == 0) {
                                return $process->show('error_no_address');
                            }

                            $gat = AddressRouter::find($network[0]->address_id);

                            $rocket = new RocketCore();

                            $UPDATE = $rocket->update_ppp_user($API, $event->data, $oldtarget, $event->data['newtarget'], $gat->gateway, $debug);

                            if($address_list == 1) {
                                $list = PermitidosList::add($API,$event->data, $debug, $error);
                            }

                            if ($debug == 1) {
                                if ($process->check($UPDATE)) {
                                    $API->disconnect();
                                    return $process->check($UPDATE);
                                }
                            }

                            //actualizamos el estado del ip en IP/Redes
                            if ($event->data['changeIP']) { //esta cambiando de IP
                                //desactivamos la IP anterior
                                $usedip->is_used_ip($oldtarget, $event->data['client_id'], false);
                                //activamos la nueva IP
                                $usedip->is_used_ip($event->data['newtarget'], $event->data['client_id'], true);
                            } else { //no esta cambiando la ip pero actualizamos el estado.
                                $usedip->refresh_ip($oldtarget, $event->data['client_id']);
                            }
                        }

                        break;

                    case 'pa': //control ppp pcq

                        if ($API->connect($con['ip'], $con['login'], $con['password'])) {


                            //get gateway for addres
                            $network = Network::where('ip', $event->data['newtarget'])->get();

                            if (count($network) == 0) {
                                return $process->show('error_no_address');
                            }

                            $gat = AddressRouter::find($network[0]->address_id);

                            $rocket = new RocketCore();

                            $UPDATE = $rocket->update_ppp_secrets_pcq($API, $event->data, $oldtarget, $event->data['newtarget'], $gat->gateway, $debug);

                            if($address_list == 1) {
                                $list = PermitidosList::add($API,$event->data, $debug, $error);
                            }

                            if ($debug == 1) {
                                if ($process->check($UPDATE)) {
                                    $API->disconnect();
                                    return $process->check($UPDATE);
                                }
                            }

                            //actualizamos el estado del ip en IP/Redes
                            if ($event->data['changeIP']) { //esta cambiando de IP
                                //desactivamos la IP anterior
                                $usedip->is_used_ip($oldtarget, $event->data['client_id'], false);
                                //activamos la nueva IP
                                $usedip->is_used_ip($event->data['newtarget'], $event->data['client_id'], true);
                            } else { //no esta cambiando la ip pero actualizamos el estado.
                                $usedip->refresh_ip($oldtarget, $event->data['client_id']);
                            }

                        }

                        break;

                    case 'pc':
                        //Verificamos el tipo de control solo la versión Pro puede utilizar PCQ

                        if ($API->connect($con['ip'], $con['login'], $con['password'])) {

                            $rocket = new RocketCore();

                            $UPDATE = $rocket->update_pcq_list($API, $event->data, $oldtarget, $event->data['newtarget'], $debug);

                            if($address_list == 1) {
                                $list = PermitidosList::add($API,$event->data, $debug, $error);
                            }

                            if ($debug == 1) {
                                if ($process->check($UPDATE)) {
                                    $API->disconnect();
                                    return $process->check($UPDATE);
                                }
                            }

                            //actualizamos el estado del ip en IP/Redes
                            if ($event->data['changeIP']) { //esta cambiando de IP
                                //desactivamos la IP anterior
                                $usedip->is_used_ip($oldtarget, $event->data['client_id'], false);
                                //activamos la nueva IP
                                $usedip->is_used_ip($event->data['newtarget'], $event->data['client_id'], true);
                            } else { //no esta cambiando la ip pero actualizamos el estado.
                                $usedip->refresh_ip($oldtarget, $event->data['client_id']);
                            }

                        }

                        break;

                    case 'nc': //Sin control mikrotik
                        //actualizamos el estado del ip en IP/Redes
                        if ($event->data['changeIP']) { //esta cambiando de IP
                            //desactivamos la IP anterior
                            $usedip->is_used_ip($oldtarget, $event->data['client_id'], false);
                            //activamos la nueva IP
                            $usedip->is_used_ip($event->data['newtarget'], $event->data['client_id'], true);
                        } else { //no esta cambiando la ip pero actualizamos el estado.
                            $usedip->refresh_ip($oldtarget, $event->data['client_id']);
                        }

                        break;

                    case 'ra': //Sin control mikrotik
                        /***sacamos el control de radius**/
//                        if ($API->connect($con['ip'], $con['login'], $con['password'])) {
//                            /**primero actualizamos los secret en el mkt**/
//                            $network = Network::where('ip', $event->data['newtarget'])->get();
//                            $gat = AddressRouter::find($network[0]->address_id);
//                            $rocket = new RocketCore();
//                            $UPDATE = $rocket->update_ppp_radius_simple_queue($API, $event->data, $oldtarget, $event->data['newtarget'], $gat->gateway, $debug);
//                            /**ahora se actualiza secret y user en el radius **/
//                        }

                        Radcheck::where('username',$event->data['old_user'])->update(['username' => $event->data['user']]);
                       // Radreply::where('username',$event->data['old_user'])->update(['username' => $event->data['user']]); /***sacamos el control de radius**/

                        Radcheck::where('username',$event->data['user'])->where('attribute','Cleartext-Password')->update(['value' => $event->data['pass']]);

                        /***sacamos el control de radius**/
//                        if ($event->data['changeIP']) { //esta cambiando de IP
//                            /**si cambio de ip, modificamos la base de radius**/
//                            Radreply::where('username',$event->data['user'])->where('attribute','Framed-IP-Address')->update(['value' => $event->data['newtarget']]);
//                        }
//                        if ($event->data['changePlan']) { //esta cambiando de IP
//                            /**si cambio de plan, modificamos la base de radius**/
//                            $download = $event->data['speed_down'] / $event->data['aggregation'];
//                            $upload = $event->data['speed_up'] / $event->data['aggregation'];
//                            Radreply::where('username',$event->data['user'])->where('attribute','Mikrotik-Rate-Limit')->update(['value' => $upload.'k/'.$download.'k']);
//                        }

                        /***por peticion de rodrigo, hacemos el control manual. Por lo cual aplicamos lo mismo que el case "pt"**/

                        if ($API->connect($con['ip'], $con['login'], $con['password'])) {
                            //get gateway for addres
                            $network = Network::where('ip',$event->data['newtarget'])->get();

                            $gat = AddressRouter::find($network[0]->address_id);

                            //actualizamos el estado del ip en IP/Redes
                            if ($event->data['changeIP']) { //esta cambiando de IP
                                //desactivamos la IP anterior
                                $usedip->is_used_ip($oldtarget,$event->data['client_id'],false);
                                //activamos la nueva IP
                                $usedip->is_used_ip($event->data['newtarget'],$event->data['client_id'],true);
                            }
                            else { //no esta cambiando la ip pero actualizamos el estado.
                                $usedip->refresh_ip($oldtarget, $event->data['client_id']);
                            }

                            $rocket = new RocketCore();


                            /**Fix to update target old plan*/
                            $plan = Plan::find( $event->oldServiceDetails['plan_id']);
                            $dataNamePlan = $plan->name;
                            $dataNamePlan = Helper::replace_word($dataNamePlan);
                            $bt = $plan->burst_time . '/' . $plan->burst_time;
                            $priotiry = $plan->priority;
                            $P_DATA = $rocket->data_simple_queue_with_tree_parent(
                                $event->oldServiceDetails['plan_id'],
                                $event->oldServiceDetails['router_id'],
                                $plan->download,
                                $plan->upload,
                                $plan->aggregation,
                                $plan->limitat,
                                $plan->burst_limit,
                                $plan->burst_threshold,
                                $event->oldServiceDetails['tree_priority'],
                            );

                            $parent = SimpleQueuesTree::simple_parent_get_id($API, $dataNamePlan);
                            SimpleQueuesTree::set_simple_parent($API,$parent[0]['.id'],$dataNamePlan,$P_DATA['maxlimit'],$P_DATA['ips'],$P_DATA['bl'],$P_DATA['bth'],$bt,$P_DATA['limitat'],$priotiry,$dataNamePlan);

                            //si no tenemos mas clientes en este plan, eliminamos la cola ya que si no no podemos dejar un target en blanco
                            if ($P_DATA['ncl'] == 0) {
                                SimpleQueuesTree::simple_parent_remove($API,$parent[0]['.id']);
                            }
                            /**end fix */

                            $UPDATE = $rocket->update_ppp_simple_queue_with_tree($API,$event->data,$oldtarget,$event->data['newtarget'],$gat->gateway,$debug);

                            if($address_list == 1) {
                                $list = PermitidosList::add($API,$event->data, $debug, $error);
                            }

                        }

                        break;
                    case 'rp':
                        Radcheck::where('username',$event->data['old_user'])->update(['username' => $event->data['user']]);
                        Radcheck::where('username',$event->data['user'])->where('attribute','Cleartext-Password')->update(['value' => $event->data['pass']]);

                        /***por peticion de rodrigo, hacemos el control manual. Por lo cual aplicamos lo mismo que el case "pa"**/

                        if ($API->connect($con['ip'], $con['login'], $con['password'])) {


                            //get gateway for addres
                            $network = Network::where('ip', $event->data['newtarget'])->get();

                            if (count($network) == 0) {
                                return $process->show('error_no_address');
                            }

                            $gat = AddressRouter::find($network[0]->address_id);

                            $rocket = new RocketCore();

                            $UPDATE = $rocket->update_ppp_secrets_pcq($API, $event->data, $oldtarget, $event->data['newtarget'], $gat->gateway, $debug);

                            if($address_list == 1) {
                                $list = PermitidosList::add($API,$event->data, $debug, $error);
                            }

                            if ($debug == 1) {
                                if ($process->check($UPDATE)) {
                                    $API->disconnect();
                                    return $process->check($UPDATE);
                                }
                            }

                            //actualizamos el estado del ip en IP/Redes
                            if ($event->data['changeIP']) { //esta cambiando de IP
                                //desactivamos la IP anterior
                                $usedip->is_used_ip($oldtarget, $event->data['client_id'], false);
                                //activamos la nueva IP
                                $usedip->is_used_ip($event->data['newtarget'], $event->data['client_id'], true);
                            } else { //no esta cambiando la ip pero actualizamos el estado.
                                $usedip->refresh_ip($oldtarget, $event->data['client_id']);
                            }

                        }


                        break;

                    case 'rr':
                        $pl = new GetPlan();
                        $plan = $pl->get($event->data['plan_id']);
                        $burst = Burst::get_all_burst($plan['upload'], $plan['download'], $plan['burst_limit'], $plan['burst_threshold'], $plan['limitat']);

                        $maxlimit = $plan['upload'].'k/'.$plan['download'].'k';
                        $burst_rate = $burst['blu'].'k/'.$burst['bld'].'k';
                        $burst_threshold = $burst['btu'].'k/'.$burst['btd'].'k';
                        $burst_time = $plan['burst_time'];
                        $priority = $plan['priority'];
                        $limit_at = $burst['lim_at_up'].'/'.$burst['lim_at_down'];

                        $config_final = "'".$maxlimit.' '.$burst_rate.' '.$burst_threshold.' '.$burst_time.' '.$priority.' '.$limit_at."'";

                        Radcheck::where('username',$event->data['old_user'])->update(['username' => $event->data['user']]);
                        Radreply::where('username',$event->data['old_user'])->update(['username' => $event->data['user']]);

                        Radcheck::where('username',$event->data['user'])->where('attribute','Cleartext-Password')->update(['value' => $event->data['pass']]);
                        Radreply::where('username',$event->data['user'])->where('attribute','Framed-IP-Address')->update(['value' => $event->data['newtarget']]);

                        $router_buscado = Router::find($event->data['router_id']);

                        $download = $event->data['speed_down'] / $event->data['aggregation'];
                        $upload = $event->data['speed_up'] / $event->data['aggregation'];
                        $velocidad = $upload.'k/'.$download.'k';

                        /**si el plan es con control en mkt*/
                        if(!$plan['no_rules']){
//                            Radreply::where('username',$event->data['user'])->where('attribute','Mikrotik-Rate-Limit')->update(['value' => $config_final]);
                            Radreply::updateOrCreate(
                                ['username' => $event->data['user'],'attribute' => 'Mikrotik-Rate-Limit'],
                                ['value' => $config_final]
                            );

                            $ejecucion = shell_exec('echo User-Name="'.$event->data['user'].'",Mikrotik-Rate-Limit:="'.$config_final.'" | /usr/bin/radclient -c 1 -n 3 -r 3 -t 3 -x '.$router_buscado->ip.':3799 coa '.$router_buscado->radius->secret.' 2>&1');
                        }
                        else{
                            Radreply::where('username',$event->data['user'])->where('attribute','Mikrotik-Rate-Limit')->delete();
                            /**desconectamos al cliente*/
                            $ejecucion = shell_exec('echo User-Name="'.$event->data['user'].'" | /usr/bin/radclient -c 1 -n 3 -r 3 -t 3 -x '.$router_buscado->ip.':3799 disconnect '.$router_buscado->radius->secret.' 2>&1');
                        }


                        /***por peticion de rodrigo, hacemos el control manual. Por lo cual aplicamos lo mismo que el case "ps"**/
                        if ($API->connect($con['ip'], $con['login'], $con['password'])){

                            //get gateway for addres
                            $network = Network::where('ip',$event->data['newtarget'])->get();

                            if (count($network)==0) {
                                return $process->show('error_no_address');
                            }

                            $gat = AddressRouter::find($network[0]->address_id);

                            $rocket = new RocketCore();

                            $plan = Plan::find( $event->data['plan_id']);
                            /**si el plan es con control en mkt*/
                            if(!$plan['no_rules']){
                                $UPDATE = $rocket->update_ppp_simple($API,$event->data,$oldtarget,$event->data['newtarget'],$gat->gateway,$debug,true);
                            }


                            if($address_list == 1) {
                                $list = PermitidosList::add($API,$event->data, $debug, $error);
                            }

                            if ($debug==1) {
                                if($process->check($UPDATE)){
                                    $API->disconnect();
                                    return $process->check($UPDATE);
                                }
                            }

                            //actualizamos el estado del ip en IP/Redes
                            if ($event->data['changeIP']) { //esta cambiando de IP
                                //desactivamos la IP anterior
                                $usedip->is_used_ip($oldtarget,$event->data['client_id'],false);
                                //activamos la nueva IP
                                $usedip->is_used_ip($event->data['newtarget'],$event->data['client_id'],true);
                            }
                            else{ //no esta cambiando la ip pero actualizamos el estado.
                                $usedip->refresh_ip($oldtarget,$event->data['client_id']);
                            }

                        }



                        break;



                } //end switch
            } //end if change
        } catch(\Exception $exception) {
            Log::error($exception->getMessage());
        }
    }

}
