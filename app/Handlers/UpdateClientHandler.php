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
use App\libraries\Pencrypt;
use App\libraries\PermitidosList;
use App\libraries\Ppp;
use App\libraries\RocketCore;
use App\libraries\RouterConnect;
use App\libraries\SimpleQueuesTree;
use App\models\AddressRouter;
use App\models\Client;
use App\models\ClientService;
use App\models\ControlRouter;
use App\models\GlobalSetting;
use App\models\Network;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class UpdateClientHandler implements ShouldQueue
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
    public function handle(\App\Events\UpdateClientEvent $event)
    {
        set_time_limit(0);
        ini_set('memory_limit', '1G');
        try {
            $client = Client::with('service')->find($event->client_id);
            foreach ($client->service as $service) {
                $type = ControlRouter::where('router_id', '=', $service->router_id)->get();
                $address_list = $type[0]->address_list;

                $pl = new GetPlan();
                $plan = $pl->get($service->plan_id);

                $num_cli = ClientService::where('plan_id', '=', $service->plan_id)->where('status', 'ac')->where('router_id', $service->plan_id)->count(); //for pcq queue tree

                //get  burst profiles
                $burst = Burst::get_all_burst($plan['upload'], $plan['download'], $plan['burst_limit'], $plan['burst_threshold'], $plan['limitat']);

                $bt = $plan['burst_time'] . '/' . $plan['burst_time'];
                $bl = $burst['blu'] . '/' . $burst['bld'];
                $bth = $burst['btu'] . '/' . $burst['btd'];
                $limit_at = $burst['lim_at_up'] . '/' . $burst['lim_at_down'];
                $priority = $plan['priority'] . '/' . $plan['priority'];
                $en = new Pencrypt();

                //get connection data for login ruter
                $router = new RouterConnect();
                $con = $router->get_connect($service->router_id);
                $data = array(

                    'service_id' => $service->id,
                    'name' => $client->name.'_'.$service->id,
                    'zona_id' => $client->zona_ident,
                    'port' => $client->edit_port,
                    'odb_id' => $client->edit_odb_id,
                    'mac' => $service->mac,
                    'arp' => $type[0]->arpmac,
                    'adv' => $type[0]->adv,
                    'dhcp' => $type[0]->dhcp,
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
                    //for simple queue with tree
                    'download' => $plan['download'],
                    'upload' => $plan['upload'],
                    'aggregation' => $plan['aggregation'],
                    'limitat' => $plan['limitat'],
                    'burst_limit' => $plan['burst_limit'],
                    'burst_threshold' => $plan['burst_threshold'],
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
                    'comment' => 'SmartISP - '.$plan['name'],
                    'ip' => $service->ip,
                    'plan_id' => $service->plan_id,
                    'router_id' => $service->router_id,
                    'namePlan' => $plan['name'],
                    'oldplan' => $plan['name'],
                    'old_router' => $service->router_id,
                    'user' => $service->user_hot,
                    'pass' => $en->decode($service->pass_hot),
                    'old_user' => $service->user_hot,
                    'typeauth' => $service->typeauth,
                    'newtarget' => $service->ip,
                    'oldtarget' => $service->ip,
                    'client_id' => $client->id,
                    'old_name' => $event->clientOldName.'_'.$service->id,
                    'changePlan' => false,
                    'profile' => $plan['name'],
                    'tree_priority' => $service->tree_priority,
	                'no_rules' => $plan['no_rules'],
                );

                $global = GlobalSetting::first();
                $conf = Helpers::get_api_options('mikrotik');
                $API = new Mikrotik($con['port'], $conf['a'], $conf['t'], $conf['s']);
                $API->debug = $conf['d'];
                $debug = $global->debug;

                $oldtarget = $service->ip;
                $process = new Chkerr();
                $error = new Mkerror();


                //Verificamos el tipo de control
                switch ($type[0]['type_control']) {

                    case 'no': //no shaping control only arp, adv or drop
                        if ($API->connect($con['ip'], $con['login'], $con['password'])) {
                            $rocket = new RocketCore();
                            $error = new Mkerror();

                            $UPDATE = $rocket->set_basic_config($API, $error, $data, $oldtarget, $data['newtarget'], 'update', $debug);

                            if ($debug == 1) {
                                if ($UPDATE != false) {
                                    return $UPDATE;
                                }
                            }


                        } else {
                            return Reply::error('Could not connect to mikrotik api . Please try again later');;
                        }


                        break;

                    case 'sq': //Control simple Queues
                        # verificamos si el cliente sera actualizado en el router

                        if ($API->connect($con['ip'], $con['login'], $con['password'])) {

                            $rocket = new RocketCore();
                            $UPDATE = $rocket->update_simple_queues($API, $data, $oldtarget, $data['newtarget'], $debug);

                            if($address_list == 1) {
                                $list = PermitidosList::add($API,$data, $debug, $error);
                            }

                            if($service->status == 'de') {

                                //añadimos el cliente al address list
                                $ADDLIST = Firewall::get_id_address_list_name($API,$service->ip,'avisos');

                                if($ADDLIST != 'notFound'){
                                    //editamos a address list activamos
                                    $ADDLIST = Firewall::set_address_list($API,$ADDLIST[0]['.id'],$service->ip,'avisos','false',$data['name']);
                                }
                            }

                        } else {
                            return Reply::error('Could not connect to mikrotik api . Please try again later');
                        }


                        break;

                    case 'st': //Simple queues with tree

                        if ($API->connect($con['ip'], $con['login'], $con['password'])) {
                            $rocket = new RocketCore();
                            $error = new Mkerror();
                            if ($address_list == 1) {
                                $list = PermitidosList::add($API, $data, $debug, $error);
                            }

                            if ($service->status == 'ac') {
                                $rocket->set_basic_config($API, $error, $data, $oldtarget, $data['newtarget'], 'update', $debug);

                                $dataNamePlan = Helper::replace_word($data['namePlan']);

                                if($data['tree_priority'] != 0) {
                                    $dataNamePlan = $dataNamePlan.'_virtual_'.$data['tree_priority'];
                                    $data['comment'] = $data['comment'].'_virtual_'.$data['tree_priority'];
                                }

                                /////////////////////////////////////////UPDATE ACTUAL PLAN AND CLIENTS /////////////////////////////////////////////////
                                # add or update clients to parents
                                $P_DATA = $rocket->data_simple_queue_with_tree_parent($data['plan_id'], $data['router_id'], $data['speed_down'], $data['speed_up'], $data['aggregation'], $data['limitat'], $data['burst_limit'], $data['burst_threshold'], $data['tree_priority']);

                                //buscamos regla parent segun el plan actual
                                $parent = SimpleQueuesTree::simple_parent_get_id($API, $dataNamePlan);

                                if ($parent == 'notFound') {
                                    # Creamos parent
                                    SimpleQueuesTree::add_simple_parent($API, $dataNamePlan, $P_DATA['ips'], $P_DATA['maxlimit'], $P_DATA['bl'], $P_DATA['bth'], $data['bt'], $P_DATA['limitat'], $data['priority'], $dataNamePlan);
                                } else {
                                    # Actualizamos parent
                                    SimpleQueuesTree::set_simple_parent($API, $parent[0]['.id'], $dataNamePlan, $P_DATA['maxlimit'], $P_DATA['ips'], $P_DATA['bl'], $P_DATA['bth'], $data['bt'], $P_DATA['limitat'], $data['priority'], $dataNamePlan);
                                }

                                $limitat = $P_DATA['limitat_up_cl'] . 'k/' . $P_DATA['limitat_down_cl'] . 'k';

                                $SQUEUES = SimpleQueuesTree::simple_child_get_id($API, $event->clientOldName . '_' . $service->id);

                                if ($SQUEUES != 'notFound') {

                                    $SQUEUES = SimpleQueuesTree::set_simple_child($API, $SQUEUES[0]['.id'], $data['name'], $data['maxlimit'], $service->ip, $dataNamePlan, $data['bl'], $data['bth'], $data['bt'], $limitat, $data['priority'], $data['comment']);

                                } else {

                                    $SQUEUES = SimpleQueuesTree::add_simple_child($API, $data['name'], $service->ip, $dataNamePlan, $data['maxlimit'], $data['bl'], $data['bth'], $data['bt'], $limitat, $data['priority'], $data['comment']);

                                }
                            }
                            // cortado service name update
                            if ($service->status == 'de') {

                                //añadimos el cliente al address list
                                $ADDLIST = Firewall::get_id_address_list_name($API, $service->ip, 'avisos');

                                if ($ADDLIST != 'notFound') {
                                    //editamos a address list activamos
                                    $ADDLIST = Firewall::set_address_list($API, $ADDLIST[0]['.id'], $service->ip, 'avisos', 'false', $data['name']);
                                }
                            }
                        }
                        //return $UPDATE; //comentar

                        break;

                    case 'dl': //control DHCP leases

                        if ($API->connect($con['ip'], $con['login'], $con['password'])) {

                            $rocket = new RocketCore();
                            $UPDATE = $rocket->update_dhcp_leases_user($API, $data, $oldtarget, $data['newtarget'], $debug);
                            if($address_list == 1) {
                                $list = PermitidosList::add($API,$data, $debug, $error);
                            }
                            if($service->status == 'de') {

                                //añadimos el cliente al address list
                                $ADDLIST = Firewall::get_id_address_list_name($API,$service->ip,'avisos');

                                if($ADDLIST != 'notFound'){
                                    //editamos a address list activamos
                                    $ADDLIST = Firewall::set_address_list($API,$ADDLIST[0]['.id'],$service->ip,'avisos','false',$data['name']);
                                }
                            }
                        }


                        break;

                    case 'pt': //control PPP Simple Queue with tree

                        if ($API->connect($con['ip'], $con['login'], $con['password'])){

                            //get gateway for addres

                            $network = Network::where('ip', $data['newtarget'])->get();

                            if (count($network) == 0) {
                                return Reply::error('The Ipdid not found. Please try again later');;
                            }

                            $gat = AddressRouter::find($network[0]->address_id);

                            $rocket = new RocketCore();




                            $PPP = Ppp::ppp_get_id($API, $data['old_user']);
                            if($address_list == 1) {
                                $list = PermitidosList::add($API,$data, $debug, $error);
                            }
                            if ($PPP != 'notFound') { //actualizamos

                                $PPP = Ppp::ppp_set($API, $PPP[0]['.id'], $data['user'], $data['pass'], $data['newtarget'], $gat->gateway, $data['mac'], "*0", $data['name']);

                                if ($debug == 1) {
                                    $msg = $error->process_error($PPP);
                                    if ($msg)
                                        return $msg;
                                }

                                /////////////Comentar si no se desea quitar del active user
                                //quitamos del user active para que los cambios tengan efecto en mikrotik
                                $active = Ppp::ppp_active_get_id($API, $oldtarget);

                                if ($active != 'notFound') {
                                    //eliminamos
                                    $remove = Ppp::active_ppp_remove($API, $active[0]['.id']);

                                    if ($debug == 1) {
                                        $msg = $error->process_error($remove);
                                        if ($msg)
                                            return $msg;
                                    }

                                }
                                ///////////////////////////////////////////

                            } else { //creamos


                                $PPP = Ppp::ppp_add($API, $data['user'], $data['pass'], $oldtarget, $gat->gateway, $data['mac'], "*0", $data['name']);

                                if ($debug == 1) {
                                    $msg = $error->process_error($PPP);
                                    if ($msg)
                                        return $msg;
                                }

                            }

                            if($service->status == 'ac') {
                                $rocket->set_basic_config($API, $error, $data, $oldtarget, $data['newtarget'], 'update', $debug);

                                $dataNamePlan = Helper::replace_word($data['namePlan']);

                                if($data['tree_priority'] != 0) {
                                    $dataNamePlan = $dataNamePlan.'_virtual_'.$data['tree_priority'];
                                    $data['comment'] = $data['comment'].'_virtual_'.$data['tree_priority'];
                                }

                                /////////////////////////////////////////UPDATE ACTUAL PLAN AND CLIENTS /////////////////////////////////////////////////
                                # add or update clients to parents
                                $P_DATA = $rocket->data_simple_queue_with_tree_parent($data['plan_id'],$data['router_id'], $data['speed_down'], $data['speed_up'], $data['aggregation'], $data['limitat'], $data['burst_limit'], $data['burst_threshold'], $data['tree_priority']);

                                //buscamos regla parent segun el plan actual
                                $parent = SimpleQueuesTree::simple_parent_get_id($API, $dataNamePlan);

                                if ($parent == 'notFound') {
                                    # Creamos parent
                                    SimpleQueuesTree::add_simple_parent($API, $dataNamePlan, $P_DATA['ips'], $P_DATA['maxlimit'], $P_DATA['bl'], $P_DATA['bth'], $data['bt'], $P_DATA['limitat'], $data['priority'], $dataNamePlan);
                                } else {
                                    # Actualizamos parent
                                    SimpleQueuesTree::set_simple_parent($API, $parent[0]['.id'], $dataNamePlan, $P_DATA['maxlimit'], $P_DATA['ips'], $P_DATA['bl'], $P_DATA['bth'], $data['bt'], $P_DATA['limitat'], $data['priority'], $dataNamePlan);
                                }

                                $limitat = $P_DATA['limitat_up_cl'] . 'k/' . $P_DATA['limitat_down_cl'] . 'k';

                                $SQUEUES = SimpleQueuesTree::simple_child_get_id($API, $event->clientOldName . '_' . $service->id);

                                if ($SQUEUES != 'notFound') {

                                    $SQUEUES = SimpleQueuesTree::set_simple_child($API, $SQUEUES[0]['.id'], $data['name'], $data['maxlimit'], $service->ip, $dataNamePlan, $data['bl'], $data['bth'], $data['bt'], $limitat, $data['priority'], $data['comment']);


                                } else {

                                    $SQUEUES = SimpleQueuesTree::add_simple_child($API, $data['name'], $service->ip, $dataNamePlan, $data['maxlimit'], $data['bl'], $data['bth'], $data['bt'], $limitat, $data['priority'], $data['comment']);

                                }
                            }
                            if($service->status == 'de') {

                                //añadimos el cliente al address list
                                $ADDLIST = Firewall::get_id_address_list_name($API,$service->ip,'avisos');

                                if($ADDLIST != 'notFound'){
                                    //editamos a address list activamos
                                    $ADDLIST = Firewall::set_address_list($API,$ADDLIST[0]['.id'],$service->ip,'avisos','false',$data['name']);
                                }
                            }

                        }

                        break;

                    case 'ps': //control PPP Simple Queue

                        if ($API->connect($con['ip'], $con['login'], $con['password'])){

                            //get gateway for addres
                            $network = Network::where('ip',$data['newtarget'])->get();

                            if (count($network)==0) {
                                return $process->show('error_no_address');
                            }

                            $gat = AddressRouter::find($network[0]->address_id);

                            $rocket = new RocketCore();

                            $UPDATE = $rocket->update_ppp_simple($API,$data,$oldtarget,$data['newtarget'],$gat->gateway,$debug);
                            if($address_list == 1) {
                                $list = PermitidosList::add($API,$data, $debug, $error);
                            }
                            if($service->status == 'de') {

                                //añadimos el cliente al address list
                                $ADDLIST = Firewall::get_id_address_list_name($API,$service->ip,'avisos');

                                if($ADDLIST != 'notFound'){
                                    //editamos a address list activamos
                                    $ADDLIST = Firewall::set_address_list($API,$ADDLIST[0]['.id'],$service->ip,'avisos','false',$data['name']);
                                }
                            }

                        }

                        break;


                    case 'pp': //control PPP

                        if ($API->connect($con['ip'], $con['login'], $con['password'])) {

                            //get gateway for addres
                            $network = Network::where('ip', $data['newtarget'])->get();

                            if (count($network) == 0) {
                                return $process->show('error_no_address');
                            }

                            $gat = AddressRouter::find($network[0]->address_id);

                            $rocket = new RocketCore();

                            $UPDATE = $rocket->update_ppp_user($API, $data, $oldtarget, $data['newtarget'], $gat->gateway, $debug);
                            if($address_list == 1) {
                                $list = PermitidosList::add($API,$data, $debug, $error);
                            }
                            if($service->status == 'de') {

                                //añadimos el cliente al address list
                                $ADDLIST = Firewall::get_id_address_list_name($API,$service->ip,'avisos');

                                if($ADDLIST != 'notFound'){
                                    //editamos a address list activamos
                                    $ADDLIST = Firewall::set_address_list($API,$ADDLIST[0]['.id'],$service->ip,'avisos','false',$data['name']);
                                }
                            }

                        }

                        break;

                    case 'pa': //control ppp pcq

                        if ($API->connect($con['ip'], $con['login'], $con['password'])) {

                            //get gateway for addres
                            $network = Network::where('ip', $data['newtarget'])->get();

                            if (count($network) == 0) {
                                return Reply::error('Ip did not found.Please try with other again');;;
                            }

                            $gat = AddressRouter::find($network[0]->address_id);

                            $rocket = new RocketCore();

                            $UPDATE = $rocket->update_ppp_secrets_pcq($API, $data, $oldtarget, $data['newtarget'], $gat->gateway, $debug);
                            if($address_list == 1) {
                                $list = PermitidosList::add($API,$data, $debug, $error);
                            }
                            if($service->status == 'de') {

                                //añadimos el cliente al address list
                                $ADDLIST = Firewall::get_id_address_list_name($API,$service->ip,'avisos');

                                if($ADDLIST != 'notFound'){
                                    //editamos a address list activamos
                                    $ADDLIST = Firewall::set_address_list($API,$ADDLIST[0]['.id'],$service->ip,'avisos','false',$data['name']);
                                }
                            }
                        }

                        break;

                    case 'pc':
                        //Verificamos el tipo de control solo la versión Pro puede utilizar PCQ

                        if ($API->connect($con['ip'], $con['login'], $con['password'])) {

                            $rocket = new RocketCore();

                            $UPDATE = $rocket->update_pcq_list($API, $data, $oldtarget, $data['newtarget'], $debug);
                            if($address_list == 1) {
                                $list = PermitidosList::add($API,$data, $debug, $error);
                            }
                            if($service->status == 'de') {

                                //añadimos el cliente al address list
                                $ADDLIST = Firewall::get_id_address_list_name($API,$service->ip,'avisos');

                                if($ADDLIST != 'notFound'){
                                    //editamos a address list activamos
                                    $ADDLIST = Firewall::set_address_list($API,$ADDLIST[0]['.id'],$service->ip,'avisos','false',$data['name']);
                                }
                            }
                        }

                        break;

                    case 'nc': //Sin control mikrotik

                        break;
                } //end switch

                $API->disconnect();
            }
        } catch(\Exception $exception) {
            Log::error($exception->getMessage());
        }
    }

}
