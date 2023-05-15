<?php

namespace App\Console\Commands;

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
use App\libraries\Ppp;
use App\libraries\QueueTree;
use App\libraries\QueueType;
use App\libraries\RecalculateSpeed;
use App\libraries\RocketCore;
use App\libraries\RouterConnect;
use App\libraries\SimpleQueues;
use App\libraries\SimpleQueuesTree;
use App\models\AddressRouter;
use App\models\Client;
use App\models\ClientService;
use App\models\ControlRouter;
use App\models\GlobalSetting;
use App\models\Network;
use App\models\Router;
use App\models\Upgrade;
use Illuminate\Console\Command;

class MigrateVersion3to4 extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'upgrade-version';

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
        $routers = Router::all();
        set_time_limit(0); //unlimited execution time php
        $upgrade = Upgrade::first();
        if(!$upgrade) {

            foreach($routers as $router) {
                //remove from mikrotik api
                $this->removeFromMikrotik($router);

                // Add again to mikrotik api
                $this->addMikrotikApi($router);
            }

            $upgrade = new Upgrade();
            $upgrade->upgrade = 1;
            $upgrade->version = 'v4';
            $upgrade->save();
        }

    }

    public function removeFromMikrotik($router) {
        $router_id = $router->id;
        $process = new Chkerr();
        $error = new Mkerror();
        $global = GlobalSetting::all()->first();
        $debug = $global->debug;

        $clients = Client::where('router_id',$router_id)->get();

        $router_con = new RouterConnect();
        $con = $router_con->get_connect($router->id);


        $control = 'no';
        $controlRouter = ControlRouter::where('router_id','=',$router_id)->first();
        //old data
        $old_arp = $controlRouter->arpmac;
        $old_dhcp = $controlRouter->dhcp;
        $old_adv = $controlRouter->adv;
        $controlRouter = $controlRouter->type_control;
        $router_con = new RouterConnect();
        $con = $router_con->get_connect($router_id);


        //GET all data for API
        $conf = Helpers::get_api_options('mikrotik');
        $API = new Mikrotik($con['port'],$conf['a'],$conf['t'],$conf['s']);
        $API->debug = $conf['d'];

        if ($API->connect($con['ip'], $con['login'], $con['password'])) {
            $rocket = new RocketCore();
            $networks = AddressRouter::where('router_id',$router_id)->get();

            $DELETE = $rocket->remove_queuetree_parent($API,$debug);

            if ($debug==1) {
                if($process->check($DELETE)){
                    return $process->check($DELETE);
                }
            }

            if (count($clients) > 0) {
                $options = array(
                    //new data
                    'adv' =>  0,
                    'arp' => 0,
                    'dhcp' => 0,
                    //old data
                    'old_adv' => $old_adv,
                    'old_dhcp' => $old_dhcp,
                    'old_arp' => $old_arp,
                    //other data
                    'lan' => $con['lan']

                );

                $migrate = new MkMigrate();

                $MK = $this->control_migrate_process($API,$rocket,$router_id,$options,$controlRouter,$control,$debug);

            }

        }
    }

    public function addMikrotikApi($router) {
        $router_id = $router->id;
        $process = new Chkerr();
        $error = new Mkerror();
        $global = GlobalSetting::all()->first();
        $debug = $global->debug;

        $clients = ClientService::with('client')->where('router_id',$router_id)->get();

        $router_con = new RouterConnect();
        $con = $router_con->get_connect($router->id);

        $controlRouter = ControlRouter::where('router_id','=',$router_id)->first();
        //old data
        $old_arp = $controlRouter->arpmac;
        $old_dhcp = $controlRouter->dhcp;
        $old_adv = $controlRouter->adv;
        $control = $controlRouter->type_control;
        $controlRouter = $controlRouter->type_control;
        $router_con = new RouterConnect();
        $con = $router_con->get_connect($router_id);

        //GET all data for API
        $conf = Helpers::get_api_options('mikrotik');
        $API = new Mikrotik($con['port'],$conf['a'],$conf['t'],$conf['s']);
        $API->debug = $conf['d'];

        if ($API->connect($con['ip'], $con['login'], $con['password'])) {
            $rocket = new RocketCore();
            $networks = AddressRouter::where('router_id',$router_id)->get();

            if (count($clients) > 0) {
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

                $migrate = new MkMigrate();

                $MK = $migrate->control_migrate_process($API,$rocket,$router_id,$options,$controlRouter,$control,$debug);

                if ($control=='pc' || $control=='pa' || $control=='ha') {
                    //intentamos crear reglas parent
                    $STATUS = $rocket->create_queuetree_parent($API,$debug);

                }else{
                    //intentamos quitar las reglas parent si existe
                    $DELETE = $rocket->remove_queuetree_parent($API,$debug);
                }//en else control

                /////////////////////////////////////BLOCK PARENTS/////////////////////////////////////////

                ///////// Actualizamos ARP la interfaz LAN ///////////
                $INTERFACE = $rocket->update_arp_interface($API,$con['lan'],$old_arp,$debug);

                if ($debug==1) {
                    //control y procesamiento de errores
                    if($process->check($INTERFACE)){
                        return $process->check($INTERFACE);
                    }
                }

                //desconecatamos la API
                $API->disconnect();

            }

        }
    }
    //metodo para migrar al nuevo tipo de control dentro del mismo router
    function control_migrate_process($API,$rocket,$router_id,$options,$oldcontrol,$control,$debug){

        //verificamos si esta activo el portal adv
        if ($options['adv']==1) {
            //verificamos el tipo de control

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


            //activamos web proxy
            $PRO = $rocket->enable_proxy($API,$debug);

            if ($debug==1) {
                if(!empty($PRO)){
                    return $PRO;
                }
            }


        }//end if adv
        else{ //quitamos las reglas

                //quitamos las reglas del portal cliente si existen y del web proxy
                $STATUS = $rocket->remove_advs($API,$debug);

                if ($debug==1) {
                    if(!empty($STATUS)){
                        return $STATUS;
                    }
                }

        }//en else adv

        //////////////MIGRAMOS/////////////////

        //iteramos todos los clientes
        $error = new Mkerror();
        //contador generar usuarios
        $user_count = 0;
        $i=0; //count for plan

        $clients = Client::where('router_id',$router_id)->get();


        foreach ($clients as $client) { //iteramos los clientes

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
                'name' => $client->name,
                'typeauth' => $client->typeauth, //default login
                'profile' => $plan['name'],
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
                'download' => $plan['download'],
                'upload' => $plan['upload'],
                'aggregation' => $plan['aggregation'],
                'limitat' => $plan['limitat'],
                'burst_limit' => $plan['burst_limit'],
                'burst_threshold' => $plan['burst_threshold']

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

            switch ($oldcontrol) {
                case 'sq':
                    # delete simple queues
                    $DELETE = $rocket->delete_simple_queues($API,$data,$client->ip,$debug);

                    if ($debug==1) {
                        //control y procesamiento de errores
                        if(!empty($DELETE)){
                            return $DELETE;
                        }
                    }


                    break;

                case 'st':
                    # delete simple queues with tree

                    $rocket->set_basic_config($API,$error,$data,$client->ip,null,'delete',$debug);

                    $DELETE = SimpleQueuesTree::simple_parent_get_id($API,$data['name']);
                    //verificamos si se encontro al cliente, si no encontro no eliminamos del router solo de la BD
                    if($DELETE != 'notFound'){
                        SimpleQueues::simple_remove($API,$DELETE[0]['.id']);
                    }

                    //Eliminamos los parents
                    //buscamos regla parent segun el plan
                    $parent = SimpleQueuesTree::simple_parent_get_id($API,Helper::replace_word($data['namePlan']));

                    if ($parent!='notFound') {

                        $DELETE = SimpleQueuesTree::simple_parent_remove($API,$parent[0]['.id']);

                        if ($debug==1) {
                            //control y procesamiento de errores
                            if(!empty($DELETE)){
                                return $DELETE;
                            }
                        }
                    }

                    break;

                case 'ho':
                    # delete hotspot
                    $DELETE = $rocket->delete_hotspot_user($API,$data,$client->ip,$debug);

                    if ($debug==1) {
                        //control y procesamiento de errores
                        if(!empty($DELETE)){
                            return $DELETE;
                        }
                    }


                    break;

                case 'dl':
                    # delete dhcp leases
                    $DELETE = $rocket->delete_dhcp_leases($API,$data,$client->ip,$debug);

                    if ($debug==1) {
                        //control y procesamiento de errores
                        if(!empty($DELETE)){
                            return $DELETE;
                        }
                    }

                    break;

                case 'pt':

                    $rocket->set_basic_config($API,$error,$data,$client->ip,null,'delete',$debug);

                    $DELETE = SimpleQueuesTree::simple_parent_get_id($API,$data['name']);
                    //verificamos si se encontro al cliente, si no encontro no eliminamos del router solo de la BD
                    if($DELETE != 'notFound'){
                        SimpleQueues::simple_remove($API,$DELETE[0]['.id']);
                    }

                    //Eliminamos los parents
                    //buscamos regla parent segun el plan
                    $parent = SimpleQueuesTree::simple_parent_get_id($API,Helper::replace_word($data['namePlan']));

                    if ($parent!='notFound') {

                        $DELETE = SimpleQueuesTree::simple_parent_remove($API,$parent[0]['.id']);

                        if ($debug==1) {
                            //control y procesamiento de errores
                            if(!empty($DELETE)){
                                return $DELETE;
                            }
                        }
                    }

                    //eliminamos del active client
                    $active = Ppp::ppp_active_get_id($API,$client->ip);

                    if ($active != 'notFound') {
                        //eliminamos
                        $remove = Ppp::active_ppp_remove($API,$active[0]['.id']);

                        if ($debug==1) {
                            $msg = $error->process_error($remove);
                            if($msg)
                                return $msg;
                        }

                    }

                    $PPP = Ppp::ppp_get_id($API,$data['user']);

                    if($PPP != 'notFound'){

                        $PPP = Ppp::ppp_remove($API,$PPP[0]['.id']);

                        if ($debug==1) {
                            $msg = $error->process_error($PPP);
                            if($msg)
                                return $msg;
                        }

                    }

                    break;

                case 'ps':

                    $DELETE = $rocket->delete_ppp_simple($API,$data,$client->ip,$debug);

                    if ($debug==1) {
                        //control y procesamiento de errores
                        if(!empty($DELETE)){
                            return $DELETE;
                        }
                    }

                    break;

                case 'pp':
                    # delete pppoe
                    $DELETE = $rocket->delete_ppp_user($API,$data,$client->ip,$debug);
                    if ($debug==1) {
                        //control y procesamiento de errores
                        if(!empty($DELETE)){
                            return $DELETE;
                        }
                    }


                    break;

                case 'pc':

                    //eliminamos del address list
                    if ($client->status=='ac') {

                        //eliminamos del address list
                        $ADDLIST = Firewall::get_id_address_list_pcq($API,$client->ip,Helper::replace_word($data['name']));

                        if ($ADDLIST!='notFound') {

                            $ADDLIST = Firewall::remove_address_list($API,$ADDLIST[0]['.id']);

                            if ($debug==1) {
                                $msg = $error->process_error($ADDLIST);
                                if($msg){
                                    return $msg;
                                }
                            }

                        }

                        //buscamos el plan en QueueTree y eliminamos el plan DOWN
                        $QUEUETREE = QueueTree::get_parent($API,Helper::replace_word($data['namePlan'].'-DOWN'));

                        if ($QUEUETREE!='notFound') {

                            $QUEUETREE = QueueTree::delete_parent($API,$QUEUETREE[0]['.id']);

                            if ($debug==1) {
                                $msg = $error->process_error($QUEUETREE);
                                if($msg){
                                    return $msg;
                                }
                            }

                        }

                        //buscamos el plan en QueueTree y eliminamos el plan UP
                        $QUEUETREE = QueueTree::get_parent($API,Helper::replace_word($data['namePlan'].'-UP'));

                        if ($QUEUETREE != 'notFound') {

                            $QUEUETREE = QueueTree::delete_parent($API,$QUEUETREE[0]['.id']);

                            if ($debug==1) {
                                $msg = $error->process_error($QUEUETREE);
                                if($msg){
                                    return $msg;
                                }
                            }


                        }

                        // Buscamos y eliminamos el queue type asociado al plan DOWN

                        $QUEUETYPE = QueueType::find_queuetype_list($API,Helper::replace_word($data['namePlan'].'_DOWN'));

                        if($QUEUETYPE != 'notFound'){

                            $QUEUETYPE = QueueType::delete_queuetype($API,$QUEUETYPE[0]['.id']);

                            if ($debug==1) {
                                $msg = $error->process_error($QUEUETYPE);
                                if($msg){
                                    return $msg;
                                }
                            }


                        }

                        // Buscamos y eliminamos el queue type asociado al plan UP

                        $QUEUETYPE = QueueType::find_queuetype_list($API,Helper::replace_word($data['namePlan'].'_UP'));

                        if($QUEUETYPE != 'notFound'){

                            $QUEUETYPE = QueueType::delete_queuetype($API,$QUEUETYPE[0]['.id']);
                            if ($debug==1) {
                                $msg = $error->process_error($QUEUETYPE);
                                if($msg){
                                    return $msg;
                                }
                            }


                        }


                        // Buscamos y eliminamos las reglas mangle
                        $MANGLE = Firewall::find_mangle($API,Helper::replace_word($data['namePlan'].'_in'));

                        if ($MANGLE!='notFound') {

                            $MANGLE = Firewall::delete_mangle($API,$MANGLE[0]['.id']);

                            if ($debug==1) {
                                $msg = $error->process_error($MANGLE);
                                if($msg){
                                    return $msg;
                                }
                            }

                        }

                        // Buscamos y eliminamos las reglas mangle
                        $MANGLE = Firewall::find_mangle($API,Helper::replace_word($data['namePlan'].'_out'));

                        if ($MANGLE!='notFound') {

                            $MANGLE = Firewall::delete_mangle($API,$MANGLE[0]['.id']);

                            if ($debug==1) {
                                $msg = $error->process_error($MANGLE);
                                if($msg){
                                    return $msg;
                                }
                            }


                        }


                    }//end if status client


                    //eliminamos del web proxy o del filter
                    $DEL = $rocket->set_basic_config($API,$error,$data,$client->ip,null,'delete',$debug);

                    if ($debug==1) {
                        if ($DEL!=false) {
                            return $DEL;
                        }
                    }



                    break;

                case 'pa':

                    //eliminamos el resto de secrets
                    $active = Ppp::ppp_active_get_id($API,$client->ip);

                    if ($active != 'notFound') {
                        //eliminamos
                        $remove = Ppp::active_ppp_remove($API,$active[0]['.id']);

                        if ($debug==1) {
                            $msg = $error->process_error($remove);
                            if($msg)
                                return $msg;
                        }

                    }

                    //eliminamos ppp secret
                    $PPP = Ppp::ppp_get_id($API,$data['user']);

                    if($PPP != 'notFound'){
                        $PPP = Ppp::ppp_remove($API,$PPP[0]['.id']);

                        if ($debug==1) {
                            $msg = $error->process_error($PPP);
                            if($msg)
                                return $msg;
                        }

                    }


                    //eliminamos del address list
                    if ($client->status=='ac') {

                        //eliminamos del address list
                        $ADDLIST = Firewall::get_id_address_list_pcq($API,$client->ip,Helper::replace_word($data['name']));

                        if ($ADDLIST!='notFound') {

                            $ADDLIST = Firewall::remove_address_list($API,$ADDLIST[0]['.id']);

                            if ($debug==1) {
                                $msg = $error->process_error($ADDLIST);
                                if($msg)
                                    return $msg;
                            }

                        }

                        //buscamos el plan en QueueTree y eliminamos el plan DOWN
                        $QUEUETREE = QueueTree::get_parent($API,Helper::replace_word($data['namePlan'].'-DOWN'));

                        if ($QUEUETREE!='notFound') {

                            $QUEUETREE = QueueTree::delete_parent($API,$QUEUETREE[0]['.id']);

                            if ($debug==1) {
                                $msg = $error->process_error($QUEUETREE);
                                if($msg)
                                    return $msg;
                            }

                        }

                        //buscamos el plan en QueueTree y eliminamos el plan UP
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


                    }//end if status client


                    //eliminamos del web proxy o del filter
                    $DEL = $rocket->set_basic_config($API,$error,$data,$client->ip,null,'delete',$debug);

                    if ($debug==1) {
                        if ($DEL!=false) {
                            return $DEL;
                        }
                    }


                    break;

                case 'ha':

                    //itentamos eliminamos el resto de usuarios de ip binding
                    if ($data['typeauth']=='binding') {

                        //buscamos la ipbinding
                        $BINDING = Hotspot::get_id_ipbinding($API,$client->ip);

                        if ($BINDING != 'notFound') {
                            //seteamos
                            $BINDING = Hotspot::remove_ipbinding($API,$BINDING[0]['.id']);

                            if ($debug==1) {
                                $msg = $error->process_error($BINDING);
                                if($msg)
                                    return $msg;
                            }

                        }

                    }

                    //itentamos eliminamos el resto de usuarios del hotspot
                    $HOTSPOT = Hotspot::hotspot_get_id($API,$client->ip);
                    if($HOTSPOT != 'notFound'){
                        $HOTSPOT = Hotspot::hotspot_remove($API,$HOTSPOT[0]['.id']);

                        if ($debug==1) {
                            $msg = $error->process_error($HOTSPOT);
                            if($msg)
                                return $msg;
                        }

                    }

                    $HOTSPOT = Hotspot::hotspot_useractive_get_id($API,$client->ip);

                    if($HOTSPOT!='notFound'){
                        $HOTSPOT = Hotspot::hotspot_remove_active($API,$HOTSPOT[0]['.id']);

                        if ($debug==1) {
                            $msg = $error->process_error($HOTSPOT);
                            if($msg)
                                return $msg;
                        }

                    }


                    //eliminamos del address list
                    if ($client->status=='ac') {

                        //eliminamos del address list
                        $ADDLIST = Firewall::get_id_address_list_pcq($API,$client->ip,Helper::replace_word($data['name']));

                        if ($ADDLIST!='notFound') {

                            $ADDLIST = Firewall::remove_address_list($API,$ADDLIST[0]['.id']);

                            if ($debug==1) {
                                $msg = $error->process_error($ADDLIST);
                                if($msg)
                                    return $msg;
                            }

                        }

                        //buscamos el plan en QueueTree y eliminamos el plan DOWN
                        $QUEUETREE = QueueTree::get_parent($API,Helper::replace_word($data['namePlan'].'-DOWN'));

                        if ($QUEUETREE!='notFound') {

                            $QUEUETREE = QueueTree::delete_parent($API,$QUEUETREE[0]['.id']);

                            if ($debug==1) {
                                $msg = $error->process_error($QUEUETREE);
                                if($msg)
                                    return $msg;
                            }

                        }

                        //buscamos el plan en QueueTree y eliminamos el plan UP
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


                    }//end if status client


                    //eliminamos del web proxy o del filter
                    $DEL = $rocket->set_basic_config($API,$error,$data,$client->ip,null,'delete',$debug);

                    if ($debug==1) {
                        if ($DEL!=false) {
                            return $DEL;
                        }
                    }


                    break;

            }//end switch

            //////////End delete previous configuration ///////////
        }//end foreach

        /////////////END MIGRATION ///////////

        return true;

    }//end method

}
