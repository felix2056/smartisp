<?php

namespace App\Handlers;

use App\libraries\Burst;
use App\libraries\Chkerr;
use App\libraries\Firewall;
use App\libraries\GetPlan;
use App\libraries\Helper;
use App\libraries\Helpers;
use App\libraries\Hotspot;
use App\libraries\Mikrotik;
use App\libraries\Mkerror;
use App\libraries\MkMigrate;
use App\libraries\Pencrypt;
use App\libraries\PermitidosList;
use App\libraries\Ppp;
use App\libraries\RocketCore;
use App\libraries\RouterConnect;
use App\libraries\SimpleQueuesTree;
use App\models\AddressRouter;
use App\models\ClientService;
use App\models\ControlRouter;
use App\models\GlobalSetting;
use App\models\Network;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

class ImportClients implements ShouldQueue
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
    public function handle(\App\Events\ImportClients $event)
    {
        set_time_limit(0);

        Artisan::call('config:clear');
        Artisan::call('cache:clear');

        ini_set('memory_limit', '1G');
        try {
            $router = $event->router;
            $router_id = $router['id'];
            $process = new Chkerr();
            $error = new Mkerror();
            $global = GlobalSetting::all()->first();
            $debug = $global->debug;

            $clients = ClientService::with('client')->where('router_id',$router_id)->get();

            $controlRouter = ControlRouter::where('router_id','=',$router_id)->first();

            //old data
            $old_arp = $controlRouter->arpmac;
            $old_dhcp = $controlRouter->dhcp;
            $old_adv = $controlRouter->adv;
            $control = $controlRouter->type_control;
            $address_list = ($controlRouter->address_list == 1) ? true : false;
            $controlRouter = $controlRouter->type_control;

            $router_con = new RouterConnect();
            $con = $router_con->get_connect($router_id);

            //GET all data for API
            $conf = Helpers::get_api_options('mikrotik');
            $API = new Mikrotik($con['port'],$conf['a'],$conf['t'],$conf['s']);
            $API->debug = $conf['d'];

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

            $rocket = new RocketCore();
            $networks = AddressRouter::where('router_id',$router_id)->get();

            if (count($clients) > 0) {
                if ($API->connect($con['ip'], $con['login'], $con['password'])) {

                    $migrate = new MkMigrate();

                    $MK = $this->control_migrate_process($API, $rocket, $router_id, $options, $controlRouter, $control, $debug);

                    if ($control == 'pc' || $control == 'pa') {
                        //intentamos crear reglas parent
                        $STATUS = $rocket->create_queuetree_parent($API, $debug);

                    } else {
                        //intentamos quitar las reglas parent si existe
                        $DELETE = $rocket->remove_queuetree_parent($API, $debug);
                    }//en else control

                    if ($address_list) {
                        $list = PermitidosList::checkRuleForClientList($API, 'Permitidos', $debug, $error);

                        if ($debug == 1) {
                            $msg = $error->process_error($list);
                            if ($msg) {
                                return $msg;
                            }
                        }

                        foreach ($clients as $client) {
                            $ADDLIST = Firewall::get_id_address_list_name($API, $client->ip, 'Permitidos');

                            if ($ADDLIST == 'notFound') {
                                $ADDLIST = Firewall::add_address_list($API, $client->ip, 'Permitidos', 'false', $client->client->name . '_' . $client->id);
                                if ($debug == 1) {
                                    $msg = $error->process_error($ADDLIST);
                                    if ($msg) {
                                        return $msg;
                                    }
                                }
                            }

                        }
                    }

                    ///////// Actualizamos ARP la interfaz LAN ///////////
                    $INTERFACE = $rocket->update_arp_interface($API, $con['lan'], $old_arp, $debug);

                    if ($debug == 1) {
                        //control y procesamiento de errores
                        if ($process->check($INTERFACE)) {
                            return $process->check($INTERFACE);
                        }
                    }

                    //desconecatamos la API
                    $API->disconnect();
                }

            } else {
                if ($options['adv']==1) {
                    if ($API->connect($con['ip'], $con['login'], $con['password'])) {
                        $STATUS = $this->enabled_advs($API, $options['lan'], $control, $debug);
                        $API->disconnect();
                    }
                }
            }
        }catch(\Exception $e){
            Log::error($e->getMessage());
        }
    }

    //metodo para migrar al nuevo tipo de control dentro del mismo router
    public function control_migrate_process($API,$rocket,$router_id,$options,$oldcontrol,$control,$debug){

        //verificamos si esta activo el portal adv
        if ($options['adv']==1) {
            //verificamos el tipo de control
            if ($control=='pp' || $control=='pa' || $control=='ps' || $control=='pt') { //control pppoe y pppoe pcq + address list
                //Quitamos el web proxy standar anterior si existe
                $ADV = $rocket->remove_advs($API,$debug);

                if ($debug==1) {
                    if(!empty($ADV)){
                        return $ADV;
                    }
                }


                //creamos reglas de bloqueo avisos solo para pppoe segun las ip redes creadas
                $networks = AddressRouter::where('router_id',$router_id)->get();
                if (count($networks)>0) {
                    foreach ($networks as $net) {
                        //iteramos las ip redes
                        $STATUS = $rocket->enabled_pppoe_advs($API,$net->network,$debug);

                        if ($debug==1) {
                            if(!empty($STATUS)){
                                return $STATUS;
                            }
                        }


                    }//end foreach
                }//end if count networks


            }else{

                //quitamos reglas de bloqueo pppoe si existen
                $networks = AddressRouter::where('router_id',$router_id)->get();
                if (count($networks)>0) {
                    foreach ($networks as $net) {
                        //iteramos las ip redes
                        $STATUS = $rocket->remove_advs_ppp($API,$net->network,$debug);
                        if ($debug==1) {
                            if(!empty($STATUS)){
                                return $STATUS;
                            }
                        }

                    }//end foreach
                }//end if count networks

                //activamos las reglas de bloqueo standar
                $STATUS = $rocket->enabled_advs($API,$options['lan'],$control,$debug);

                if ($debug==1) {
                    if(!empty($STATUS)){
                        return $STATUS;
                    }
                }


            }//end else tipo control pppoe y pppoe pcq + address list



            //activamos web proxy
            $PRO = $rocket->enable_proxy($API,$debug);

            if ($debug==1) {
                if(!empty($PRO)){
                    return $PRO;
                }
            }


        }//end if adv
        else{ //quitamos las reglas

            if ($control=='pp' || $control=='pa' || $control=='ps' || $control=='pt') { //control pppoe y pppoe pcq + address list
                //eliminamos solo pppoe
                $STATUS = $rocket->remove_proxy_ppp($API,$debug);
                //activamos reglas de bloequeo avisos solo para pppoe segun las ip redes creadas
                $networks = AddressRouter::where('router_id',$router_id)->get();
                if (count($networks)>0) {
                    foreach ($networks as $net) {
                        $STATUS = $rocket->remove_advs_ppp($API,$net->network,$debug);
                        if ($debug==1) {
                            if(!empty($STATUS)){
                                return $STATUS;
                            }
                        }

                    }//end foreach
                }//end if

            }else{
                //eliminamos reglas para otros tipos de control

                //quitamos las reglas del portal cliente si existen y del web proxy
                $STATUS = $rocket->remove_advs($API,$debug);

                if ($debug==1) {
                    if(!empty($STATUS)){
                        return $STATUS;
                    }
                }


            }//end else control pppoe y pppoe pcq + address list

        }//en else adv

        //////////////MIGRAMOS/////////////////

        //iteramos todos los clientes
        $error = new Mkerror();
        //contador generar usuarios
        $user_count = 0;
        $i=0; //count for plan

        $clients = ClientService::with('client')->where('router_id',$router_id)->get();


        foreach ($clients as $client) { //iteramos los clientes
            Log::debug($client->client->name);
            $user_count ++;
            //$adv2 = $adv ? 0 : 1; //negamos adv para no eliminar del web proxy durante la migración
            //obtenemos el plan del cliente
            $pl = new GetPlan();
            $plan = $pl->get($client->plan_id);
            //Obtenemos opciones avanzada burst
            $burst = Burst::get_all_burst($plan['upload'],$plan['download'],$plan['burst_limit'],$plan['burst_threshold'],$plan['limitat']);
            //preparamos la data del cliente
            $en = new Pencrypt();

            $data = array(
                //general data
                'name' => $client->client->name.'_'.$client->id,
                'typeauth' => $client->typeauth, //default login
                'profile' => $plan['name'],
                'ip' => $client->ip,
                'user' => empty($client->user_hot) ? 'User-'.$user_count : $client->user_hot,
                'pass' => $client->pass_hot == '0' ? '0': $en->decode($client->pass_hot),
                'mac' => $client->mac,
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
                'plan_id' => $client->plan_id,
                'router_id' => $client->router_id,
                'download' => $plan['download'],
                'upload' => $plan['upload'],
                'aggregation' => $plan['aggregation'],
                'limitat' => $plan['limitat'],
                'burst_limit' => $plan['burst_limit'],
                'burst_threshold' => $plan['burst_threshold'],
                'tree_priority' => $client->tree_priority

            );//end data array

            //////////start delete previous configuration ///////////

            //set data for delete items

            if ($client->status=='de') {
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

            if ($client->status=='de') {
                if ($options['adv']==1) {
                    $data['drop']=0;
                }else{
                    $data['drop']=1;
                }
            }

            switch ($control) {
                case 'sq':
                    # add simplequeues
                    $ADD = $rocket->add_simple_queues($API,$data,$client->ip,$debug);

                    if ($debug==1) {
                        //control y procesamiento de errores
                        if(!empty($ADD)){
                            return $ADD;
                        }
                    }


                    break;

                case 'st':
                    # add simplequeues with tree
//                    $ADD = $rocket->add_simple_queue_with_tree($API,$data,$client->ip,'add',$debug);

                    $this->addSimpleQueueWithTree($API,$error,$data,$client,$rocket,$debug);
//                    $rocket->set_basic_config($API,$error,$data,$client->ip,null,'add',$debug);
//
//                    # add or update clients to parents
//                    $P_DATA = $rocket->data_simple_queue_with_tree_parent($data['plan_id'],$data['router_id'],$data['download'],$data['upload'],$data['aggregation'],$data['limitat'],$data['burst_limit'],$data['burst_threshold']);
//
//                    //buscamos regla parent segun el plan
//                    $parent = SimpleQueuesTree::simple_parent_get_id($API,Helper::replace_word($data['namePlan']));
//
//                    if ($parent=='notFound') {
//                        # Creamos parent
//                        SimpleQueuesTree::add_simple_parent($API,Helper::replace_word($data['namePlan']),$P_DATA['ips'],$P_DATA['maxlimit'],$P_DATA['bl'],$P_DATA['bth'],$data['bt'],$P_DATA['limitat'],$data['priority'],$data['namePlan']);
//                    }else{
//                        # Actualizamos parent
//                        SimpleQueuesTree::set_simple_parent($API,$parent[0]['.id'],Helper::replace_word($data['namePlan']),$P_DATA['maxlimit'],$P_DATA['ips'],$P_DATA['bl'],$P_DATA['bth'],$data['bt'],$P_DATA['limitat'],$data['priority'],$data['namePlan']);
//                    }
//
//                    $limitat = $P_DATA['limitat_up_cl'].'k/'.$P_DATA['limitat_down_cl'].'k';
//
//
//                    //SIMPLE Q
//                    $SQUEUES = SimpleQueuesTree::simple_parent_get_id($API,$client->client->name.'_'.$client->id);
//
//                    if($SQUEUES != 'notFound'){
//
//                        $QUEUES = SimpleQueuesTree::set_simple_child($API,$SQUEUES[0]['.id'],$client->client->name.'_'.$client->id,$data['maxlimit'],$client->ip,Helper::replace_word($data['namePlan']),$data['bl'],$data['bth'],$data['bt'],$limitat,$data['priority'],$data['comment']);
//
//                        if ($debug==1) {
//                            $msg = $error->process_error($SQUEUES);
//                            if($msg)
//                                return $msg;
//                        }
//
//                    }
//                    else{
//
//                        $SQUEUES = SimpleQueuesTree::add_simple_child($API,$client->client->name.'_'.$client->id,$client->ip,Helper::replace_word($data['namePlan']),$data['maxlimit'],$data['bl'],$data['bth'],$data['bt'],$limitat,$data['priority'],$data['comment']);
//
//                        if ($debug==1) {
//                            $msg = $error->process_error($SQUEUES);
//                            if($msg)
//                                return $msg;
//                        }
//
//                    }
//
//
//                    if ($debug==1) {
//                        //control y procesamiento de errores
//                        if(!empty($ADD)){
//                            return $ADD;
//                        }
//                    }

                    break;

                case 'dl':
                    # add dhcp leases
                    $ADD = $rocket->add_dhcp_leases($API,$data,$client->ip,$debug);
                    if ($debug==1) {
                        //control y procesamiento de errores
                        if(!empty($ADD)){
                            return $ADD;
                        }
                    }


                    break;

                case 'ps':

                    $drop = $data['drop'];
                    $data['drop']=0;

                    $network = Network::where('ip',$client->ip)->get();
                    $gat = AddressRouter::find($network[0]->address_id);

                    $ADD = $rocket->add_ppp_simple($API,$data,$client->ip,$gat->gateway,$debug);

                    if ($debug==1) {
                        //control y procesamiento de errores
                        if(!empty($ADD)){
                            return $ADD;
                        }
                    }

                    if ($drop==1) {
                        //bloqueamos definitivo si esta activo el drop
                        $PPP = Ppp::ppp_get_id($API,$data['user']);

                        if($PPP != 'notFound'){
                            //desactivamos el secret del usuario
                            $PPP = Ppp::enable_disable_secret($API,$PPP[0]['.id'],'true','Servicio cortado - '.$data['name']);

                            if ($debug==1) {
                                $msg = $error->process_error($PPP);
                                if($msg)
                                    return $msg;
                            }
                        }
                    }

                    //verificamos si tiene un nombre de usuario para secret caso contrario asignamos
                    if (empty($client->user_hot)){
                        $client->user_hot = $data['user'];
                        $client->save();
                    }

                    break;

                case 'pt':
                    Log::debug($client->client->name. ' inside pt control');
                    # add pppoe simple queue with tree
                    $drop = $data['drop'];
                    $data['drop']=0;

                    $network = Network::where('ip',$client->ip)->get();
                    $gat = AddressRouter::find($network[0]->address_id);

                    $ADD = $this->add_ppp_simple_queue_with_tree($API,$data,$client->ip,$gat->gateway,'add',$debug, $client, $rocket);

                    if ($debug==1) {
                        //control y procesamiento de errores
                        if(!empty($ADD)){
                            return $ADD;
                        }
                    }

                    if ($drop==1) {
                        //bloqueamos definitivo si esta activo el drop
                        $PPP = Ppp::ppp_get_id($API,$data['user']);

                        if($PPP != 'notFound'){
                            //desactivamos el secret del usuario
                            $PPP = Ppp::enable_disable_secret($API,$PPP[0]['.id'],'true','Servicio cortado - '.$data['name']);

                            if ($debug==1) {
                                $msg = $error->process_error($PPP);
                                if($msg)
                                    return $msg;
                            }
                        }
                    }

                    if (empty($client->user_hot)){
                        $client->user_hot = $data['user'];
                        $client->save();
                    }


                    break;

                case 'pp':
                    # add pppoe
                    //get gateway for addres
                    $network = Network::where('ip',$client->ip)->get();
                    $gat = AddressRouter::find($network[0]->address_id);
                    $drop = $data['drop'];
                    $data['drop']=0;


                    $ADD = $rocket->add_ppp_secrets($API,$data,$client->ip,$gat->gateway,$debug);

                    if ($debug==1) {
                        //control y procesamiento de errores
                        if(!empty($ADD)){
                            return $ADD;
                        }
                    }


                    if ($drop==1) {
                        //bloqueamos definitivo si esta activo el drop
                        $PPP = Ppp::ppp_get_id($API,$data['user']);

                        if($PPP != 'notFound'){
                            //desactivamos el secret del usuario
                            $PPP = Ppp::enable_disable_secret($API,$PPP[0]['.id'],'true','Servicio cortado - '.$data['name']);

                            if ($debug==1) {
                                $msg = $error->process_error($PPP);
                                if($msg)
                                    return $msg;
                            }
                        }
                    }


                    //verificamos si tiene un nombre de usuario para secret caso contrario asignamos
                    if (empty($client->user_hot)){
                        $client->user_hot = $data['user'];
                        $client->save();
                    }

                    break;

                case 'pc':


                    //guardamos todos los planes asociados al router
                    $PLANS[$i]=$client->plan_id;

                    $ADD = $rocket->set_basic_config($API,$error,$data,$client->ip,null,'add',$debug);

                    if ($debug==1) {
                        if ($ADD!=false){
                            return $ADD;
                        }
                    }



                    $i++;

                    break;

                case 'pa':
                    //añadimos clientes al ppp secrets

                    //get gateway for addres
                    $network = Network::where('ip',$client->ip)->get();
                    $gat = AddressRouter::find($network[0]->address_id);


                    $PPP = Ppp::ppp_get_id($API,$data['user']);

                    if($PPP!='notFound'){
                        $PPP = Ppp::ppp_set($API,$PPP[0]['.id'],$data['user'],$data['pass'],$client->ip,$gat->gateway,$data['mac'],'default',$data['name']);

                        if ($debug==1) {
                            $msg = $error->process_error($PPP);
                            if($msg){
                                return $msg;
                            }
                        }

                    }else{ // no existe el usuario creamos el secret

                        $PPP = Ppp::ppp_add($API,$data['user'],$data['pass'],$client->ip,$gat->gateway,$data['mac'],'default',$data['name']);
                        if ($debug==1) {
                            $msg = $error->process_error($PPP);
                            if($msg){
                                return $msg;
                            }
                        }

                    }

                    $drop = $data['drop'];
                    $data['drop']=0;

                    if ($drop==1) {
                        //bloqueamos definitivo si esta activo el drop
                        $PPP = Ppp::ppp_get_id($API,$data['user']);

                        if($PPP != 'notFound'){
                            //desactivamos el secret del usuario
                            $PPP = Ppp::enable_disable_secret($API,$PPP[0]['.id'],'true','Servicio cortado - '.$data['name']);
                            if ($debug==1) {
                                $msg = $error->process_error($PPP);
                                if($msg){
                                    return $msg;
                                }
                            }

                        }
                    }


                    # agregamos al filter para que sean bloqueados
                    $ADD = $rocket->set_basic_config($API,$error,$data,$client->ip,null,'add',$debug);

                    if ($debug==1) {
                        if ($ADD!=false){
                            return $ADD;
                        }
                    }


                    $PLANS[$i]=$client->plan_id;

                    //verificamos si tiene un nombre de usuario para pppoe caso contrario asignamos por defecto uno
                    if (empty($client->user_hot)){
                        $client->user_hot = $data['user'];
                        $client->save();
                    }

                    $i++;

                    break;

            }//end switch

            //agregamos los clientes suspendidos
            if ($options['adv']!=0) {
                //los avisos estan activos agregamos a los clientes estado bloqueado del address list
                if ($client->status=='de') {
                    //añadimos el cliente al address list
                    $ADDLIST = Firewall::get_id_address_list_name($API,$client->ip,'avisos');
                    if($ADDLIST != 'notFound'){
                        //editamos a address list activamos
                        $ADDLIST = Firewall::set_address_list($API,$ADDLIST[0]['.id'],$client->ip,'avisos','false',$client->client->name.'_'.$client->id);

                        if ($debug==1) {
                            $msg = $error->process_error($ADDLIST);
                            if($msg){
                                return $msg;
                            }
                        }

                    }
                    else{
                        //no encontro la ip del usuario address list creamos con los nuevos datos enviados
                        $ADDLIST = Firewall::add_address_list($API,$client->ip,'avisos','false',$client->client->name.'_'.$client->id);
                        if ($debug==1) {
                            $msg = $error->process_error($ADDLIST);
                            if($msg){
                                return $msg;
                            }
                        }

                    }//end if
                }//end if
            }else{
                //intentamos eliminar del address list
                if ($client->status=='de') {
                    //intentamos eliminar los clientes suspendidos a de addres list
                    $ADDLIST = Firewall::get_id_address_list_name($API,$client->ip,'avisos');
                    if($ADDLIST != 'notFound'){
                        //eliminamos a address list activamos
                        $ADDLIST = Firewall::remove_address_list($API,$ADDLIST[0]['.id']);
                        if ($debug==1) {
                            $msg = $error->process_error($ADDLIST);
                            if($msg){
                                return $msg;
                            }
                        }

                    }//end if
                }//endif
            }//end else adv

        }//end foreach

        ///////////// BLOCK PCQ FOR CREATE PLANS ///////////////////

        if ($control=='pc' || $control=='pa') {

            $plans = array_unique($PLANS);
            $plans = array_values($plans);

            $pl = new GetPlan();



            for ($i=0; $i < count($plans); $i++) {

                $num_cli = Helpers::getnumcl($router_id,$oldcontrol,$plans[$i]);

                $clients = ClientService::where('plan_id',$plans[$i])->where('router_id',$router_id)->get();



                //creamos los planes QUEUE TREE
                $plan = $pl->get($plans[$i]);
                //Obtenemos opciones avanzada burst
                $burst = Burst::get_all_burst($plan['upload'],$plan['download'],$plan['burst_limit'],$plan['burst_threshold'],$plan['limitat']);
                //preparamos la data del cliente

                //Aditional Data for PCQ
                $data = array(
                    //general data
                    'namePlan' => $plan['name'],
                    'newPlan' => $plan['name'],
                    //advanced for pcq plan
                    'speed_down' => $plan['download'],
                    'speed_up' => $plan['upload'],
                    'num_cl' => $num_cli,
                    'rate_down' => $plan['download'].'k',
                    'rate_up' => $plan['upload'].'k',
                    'burst_rate_down' => $burst['bld'],
                    'burst_rate_up' => $burst['blu'],
                    'burst_threshold_down' => $burst['btd'],
                    'burst_threshold_up' => $burst['btu'],
                    'limit_at_down' => $burst['lim_at_down'],
                    'limit_at_up' => $burst['lim_at_up'],
                    'burst_time' => $plan['burst_time'],
                    'priority_a' => $plan['priority'],
	                'no_rules' => $plan['no_rules'],
                );//end data array
                //////////////////////////////////////////////////////////////////

                $Migrate = new MkMigrate();
                $UPDATE = $Migrate->plan_migrate_pcq($API,$rocket,$data,$clients,$debug);


                if ($debug==1) {
                    if (!empty($MIGRATE)) {
                        return $MIGRATE;
                    }
                }


                ///////////////////////////////////////////////////////////////////////////
            }//end for

        }//end if

        /////////////END BLOCK PCQ CREATE PLANS //////////////////

        /////////////END MIGRATION ///////////

        return true;

    }//end method

    public function enabled_advs($API,$lan,$op,$debug){

        $error = new Mkerror();

        // Creamos Reglas en firewall filter

        //buscamos si ya existe la regla filter para udp
        $FILTER = Firewall::find_block_filter($API,'Smartisp avisos Smartisp-dns');

        if($FILTER == 'notFound'){
            //agregamos
            $FILTER = Firewall::filter_block_udp($API,$lan);

        }
        else{
            //seteamos
            $FILTER = Firewall::filter_set_udp($API,$FILTER[0][".id"],$lan);

        }

        $FILTER = Firewall::find_block_filter($API,'Smartisp avisos Smartisp-tcp');

        if($FILTER == 'notFound'){
            $FILTER = Firewall::filter_block_tcp($API,$lan);
        }
        else{
            //seteamos
            $FILTER = Firewall::filter_set_tcp($API,$FILTER[0]['.id'],$lan);

        }

        $NAT = Firewall::find_block_nat($API,'Smartisp Avisos');

        if($NAT == 'notFound'){
            // creamos regla nat para redirigir
            $NAT = Firewall::add_nat_adv($API,$lan,$op);

            $NAT = Firewall::add_nat_adv_2($API,$lan,$op);

        }
        else{
            //seteamos
            $NAT = Firewall::set_block_nat($API,$NAT[0]['.id'],$lan,$op);

        }

    }

    function add_ppp_simple_queue_with_tree($API,$data,$Address,$gateway,$operation,$debug, $client, $rocket){

        $error = new Mkerror();

        $PPP = Ppp::ppp_get_id($API,$data['user']);
        //Add to ppp secret
        if($PPP!='notFound'){

            $PPP = Ppp::ppp_set($API,$PPP[0]['.id'],$data['user'],$data['pass'],$Address,$gateway,$data['mac'],"*0",$data['name']);

            if ($debug==1) {
                $msg = $error->process_error($PPP);
                if($msg)
                    return $msg;
            }


        }else{ // no existe el usuario creamos
            $PPP = Ppp::ppp_add($API,$data['user'],$data['pass'],$Address,$gateway,$data['mac'],'default',$data['name']);

            if ($debug==1) {
                $msg = $error->process_error($PPP);
                if($msg)
                    return $msg;
            }
        }

        //Add to simplequeue with tree
        $this->addSimpleQueueWithTree($API,$error,$data,$client,$rocket,$debug);

    }

    public function addSimpleQueueWithTree($API,$error,$data,$client,$rocket,$debug)
    {
        $error = new Mkerror();

        $rocket->set_basic_config($API,$error,$data,$client->ip,null,'add',$debug);


        $dataNamePlan = Helper::replace_word($data['namePlan']);

        if($data['tree_priority'] != 0) {
            $dataNamePlan = $dataNamePlan.'_virtual_'.$data['tree_priority'];
            $data['comment'] = $data['comment'].'_virtual_'.$data['tree_priority'];
        }

        # add or update clients to parents
        $P_DATA = $rocket->data_simple_queue_with_tree_parent($data['plan_id'],$data['router_id'],$data['download'],$data['upload'],$data['aggregation'],$data['limitat'],$data['burst_limit'],$data['burst_threshold'], $data['tree_priority']);

        //buscamos regla parent segun el plan
        $parent = SimpleQueuesTree::simple_parent_get_id($API,$dataNamePlan);

        if ($parent=='notFound') {
            # Creamos parent
            SimpleQueuesTree::add_simple_parent($API,$dataNamePlan,$P_DATA['ips'],$P_DATA['maxlimit'],$P_DATA['bl'],$P_DATA['bth'],$data['bt'],$P_DATA['limitat'],$data['priority'],$dataNamePlan);
        }else{
            # Actualizamos parent
            SimpleQueuesTree::set_simple_parent($API,$parent[0]['.id'],$dataNamePlan,$P_DATA['maxlimit'],$P_DATA['ips'],$P_DATA['bl'],$P_DATA['bth'],$data['bt'],$P_DATA['limitat'],$data['priority'],$dataNamePlan);
        }

        $limitat = $P_DATA['limitat_up_cl'].'k/'.$P_DATA['limitat_down_cl'].'k';


        //Simple Queue
        $SQUEUES = SimpleQueuesTree::simple_parent_get_id($API,$client->client->name.'_'.$client->id);

        if($SQUEUES != 'notFound'){

            $QUEUES = SimpleQueuesTree::set_simple_child($API,$SQUEUES[0]['.id'],$client->client->name.'_'.$client->id,$data['maxlimit'],$client->ip,$dataNamePlan,$data['bl'],$data['bth'],$data['bt'],$limitat,$data['priority'],$data['comment']);

            if ($debug==1) {
                $msg = $error->process_error($SQUEUES);
                if($msg)
                    return $msg;
            }

        }
        else{

            $SQUEUES = SimpleQueuesTree::add_simple_child($API,$client->client->name.'_'.$client->id,$client->ip,$dataNamePlan,$data['maxlimit'],$data['bl'],$data['bth'],$data['bt'],$limitat,$data['priority'],$data['comment']);

            if ($debug==1) {
                $msg = $error->process_error($SQUEUES);
                if($msg)
                    return $msg;
            }

        }

    }
}
