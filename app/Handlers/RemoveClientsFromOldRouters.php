<?php

namespace App\Handlers;

use App\libraries\Arp;
use App\libraries\Burst;
use App\libraries\Chkerr;
use App\libraries\Dhcp;
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
use App\libraries\RocketCore;
use App\libraries\SimpleQueues;
use App\libraries\SimpleQueuesTree;
use App\models\AddressRouter;
use App\models\ClientService;
use App\models\GlobalSetting;
use App\models\radius\Nas;
use App\models\radius\Radcheck;
use App\models\radius\Radius;
use App\libraries\Radius as RadiusLibrary;
use App\models\radius\Radreply;
use App\models\Router;
use Illuminate\Bus\Queueable;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Config;


class RemoveClientsFromOldRouters implements ShouldQueue
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
    public function handle(\App\Events\RemoveClientsFromOldRouter $event)
    {

        ini_set('memory_limit', '-1');

        $router = $event->router;
        $controlRouter = $event->controlRouter;
        //old data
        $old_arp = $controlRouter['arpmac'];
        $old_dhcp = $controlRouter['dhcp'];
        $old_adv = $controlRouter['adv'];
        $address_list = $controlRouter['address_list'];
        $control = $controlRouter['type_control'];
        $controlRouter = $controlRouter['type_control'];
        $radius_server = $router['radius_server'];

        $con = $router;

        $process = new Chkerr();
        $error = new Mkerror();

        $global = GlobalSetting::all()->first();
        $debug = $global->debug;

        $clients = ClientService::with('client')->where('router_id',$router['id'])->get();

        set_time_limit(count($clients)+3); //unlimited execution time php


        //GET all data for API
        $conf = Helpers::get_api_options('mikrotik');
        $API = new Mikrotik($con['port'],$conf['a'],$conf['t'],$conf['s']);
        $API->debug = $conf['d'];
        try{
            if ($API->connect($con['ip'], $con['login'], $con['password'])) {

                $rocket = new RocketCore();
                $networks = AddressRouter::where('router_id',$router['id'])->get();

                //intentamos quitar las reglas parent si existe
                $DELETE = $rocket->remove_queuetree_parent($API,$debug);

                //en else control
                /////////////////////////////////////END BLOCK QUEUE TREE PARENTS////////////////////////////

                if (count($clients) > 0) {


                    //////////verificamos si esta cambiando el tipo de control////////////
    //                if ($control != $controlRouter) {
                    //cambiamos control migramos los clientes

                    //aditional data
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

                    //iniciamos el migrador
                    $migrate = new MkMigrate();

                    $MK = $this->control_migrate_process($API,$rocket,$router['id'],$options,$control,$controlRouter,$debug,$radius_server);
                    //quitamos las reglas del portal cliente si existen y del web proxy
                    $STATUS = $rocket->remove_advs($API,$debug);

                    ////////////////////////////////////ARP BLOCK////////////////////////////////////////
                    foreach ($clients as $client) {
                        $ARP = Arp::arp_get_id($API,$client->ip);
                        if($ARP != 'notFound'){
                            $ARP = Arp::arp_remove($API,$ARP[0]['.id']);

                        }//end if
                    }//end foreach

                    ////////////////////////////////////END ARP BLOCK////////////////////////////////////////

                    /////////////////////////////////////DHCP BLOCK//////////////////////////////////////////

                    //quitamos los clientes del DCHP Leases
                    if ($control=='sq' || $control=='st' || $control=='pc') {

                        foreach ($clients as $client) {
                            $DHCP = Dhcp::dhcp_get_id($API,$client->ip,$client->mac);
                            if($DHCP != 'notFound'){
                                $DHCP = Dhcp::dhcp_remove($API,$DHCP[0]['.id']);
                            }//end if
                        }//end for each
                    }//end if control

                    /////////////////////////////////////END DHCP BLOCK//////////////////////////////////////


    //                }//end else control chage


                    /////////////////////////////////////BLOCK QUEUE TREE PARENTS////////////////////////////
                    $DELETE = $rocket->remove_queuetree_parent($API,$debug);

                }//end principal if count clients

                else{// no hay clientes aplicamos la configuracion estandar

                    /////////////////////////////////////BLOCK QUEUE TREE PARENTS////////////////////////////

                    //intentamos quitar las reglas parent si existe
                    $DELETE = $rocket->remove_queuetree_parent($API,$debug);

                    /////////////////////////////////////END BLOCK PARENTS/////////////////////////////////////////

                    ////////////////////////////////////BLOCK WEB PROXY //////////////////////////////////////////

                    #quitamos reglas para el portal
                    $STATUS = $rocket->remove_advs($API,$debug);

                }//end else

                $NAT = Firewall::find_block_nat($API,"SmartISP Avisos");

                if ($NAT!='notFound') {
                    $NAT = Firewall::remove_block_nat($API,$NAT[0]['.id']);

                }

                $NAT = Firewall::find_block_nat($API,"Smartisp Avisos");

                if ($NAT!='notFound') {
                    $NAT = Firewall::remove_block_nat($API,$NAT[0]['.id']);

                }

                $list = Firewall::find_block_filter($API, 'Smartisp avisos Smartisp-tcp');
                if ($list != 'notFound') {
                    $ADDLIST = Firewall::remove_filter_block($API, $list[0]['.id']);
                }

                $list = Firewall::find_block_filter($API, 'Smartisp avisos Smartisp-dns');
                if ($list != 'notFound') {
                    $ADDLIST = Firewall::remove_filter_block($API, $list[0]['.id']);
                }

                if ($address_list == 1 || $control == 'no') {

                    $list = Firewall::get_id_client_list_filter_block($API, 'Permitidos');
                    if ($list != 'notFound') {
                        $ADDLIST = Firewall::remove_filter_block($API, $list[0]['.id']);
                    }

                    foreach ($clients as $client) {
                        $ADDLIST = Firewall::get_id_address_list_name($API, $client->ip, 'Permitidos');
                        if ($ADDLIST != 'notFound') {
                            //eliminamos a address list activamos
                            $ADDLIST = Firewall::remove_address_list($API, $ADDLIST[0]['.id']);
                        }
                    }
                }


                if($control == 'ra' || $control == 'rp' || $control == 'rr'){ /**esto es control viejo**/
                    $nc = Router::find($router['id'])->control_router->type_control; /**esto es control nuevo**/

                    /**si el control viejo es con radius no eliminamos nada ya que se va actualizar. si era por radius, eliminamos
                         * esto es asi, ya que por ejemplo si estamos editando la ip nas, siempre se ejecuta este metodo. Entonces si estamos actualizando pero seguimos en radius, no eliminamos nada
                         */

                        if($nc != 'ra' && $nc != 'rp' && $nc != 'rr') {

                            /**esto se hizo, ya que se estaba ejecutando pruimero los new y luego los delete, por lo cual siempre terminaba eliminando la info de radius del router**/
                            /**eliminamos nas sobre bd de radius**/
                            /** aplicamos la configuracion sobre el router **/
                            Artisan::call('cache:clear');
                            Artisan::call('config:clear');

                            $secret = Radius::where('router_id',$router['id'])->first()->secret;
                            $radius = new RadiusLibrary();
                            $radius->radius_delete($API,$secret,$radius_server);

                            /**si el control viejo no era radius, eliminao*/
                            $nas_name= Nas::where('nasname',$router['ip'])->delete();
                            Radius::where('router_id',$router['id'])->delete();

                            $ejecucion=exec("/usr/bin/sudo /etc/init.d/freeradius stop");
                            $ejecucion=exec("sudo killall freeradius");
                            $ejecucion=exec('sudo /etc/init.d/freeradius restart');
                        }

                }

                }

            $API->disconnect();

        }catch(\Exception $e){
            $API->disconnect();
            Log::error($e->getMessage());
        }
    }

    //metodo para migrar al nuevo tipo de control dentro del mismo router
    private function control_migrate_process($API,$rocket,$router_id,$options,$oldcontrol,$control,$debug,$radius_server){

        //verificamos si esta activo el portal adv
        if ($options['adv']==1) {
            //verificamos el tipo de control
            if ($control=='pp' || $control=='pa' || $control=='ps' || $control=='pt') { //control pppoe y pppoe pcq + address list
                //Quitamos el web proxy standar anterior si existe
                $ADV = $rocket->remove_advs($API,$debug);

            }else{

                //quitamos reglas de bloqueo pppoe si existen
                $networks = AddressRouter::where('router_id',$router_id)->get();
                if (count($networks)>0) {
                    foreach ($networks as $net) {
                        //iteramos las ip redes
                        $STATUS = $rocket->remove_advs_ppp($API,$net->network,$debug);

                    }//end foreach
                }//end if count networks

            }//end else tipo control pppoe y pppoe pcq + address list


        }//end if adv
        else{ //quitamos las reglas

            if ($control=='pp' || $control=='pa' || $control=='ps' || $control=='pt' || $control=='ra' || $control=='rp' || $control=='rr') { //control pppoe y pppoe pcq + address list
                //eliminamos solo pppoe
                $STATUS = $rocket->remove_proxy_ppp($API,$debug);
                //activamos reglas de bloequeo avisos solo para pppoe segun las ip redes creadas
                $networks = AddressRouter::where('router_id',$router_id)->get();
                if (count($networks)>0) {
                    foreach ($networks as $net) {
                        $STATUS = $rocket->remove_advs_ppp($API,$net->network,$debug);


                    }//end foreach
                }//end if

            }else{
                //eliminamos reglas para otros tipos de control

                //quitamos las reglas del portal cliente si existen y del web proxy
                $STATUS = $rocket->remove_advs($API,$debug);

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

            switch ($oldcontrol) {
                case 'sq':
                    # delete simple queues
                    $DELETE = $rocket->delete_simple_queues($API,$data,$client->ip,$debug);

                    break;

                case 'st':
                    # delete simple queues with tree

                    $rocket->set_basic_config($API,$error,$data,$client->ip,null,'delete',$debug);

                    $dataNamePlan = Helper::replace_word($data['namePlan']);

                    if($data['tree_priority'] != 0) {
                        $dataNamePlan = $dataNamePlan.'_virtual_'.$data['tree_priority'];
                    }


                    $DELETE = SimpleQueuesTree::simple_parent_get_id($API,$data['name']);
                    //verificamos si se encontro al cliente, si no encontro no eliminamos del router solo de la BD
                    if($DELETE != 'notFound'){
                        SimpleQueues::simple_remove($API,$DELETE[0]['.id']);
                    }

                    //Eliminamos los parents
                    //buscamos regla parent segun el plan
                    $parent = SimpleQueuesTree::simple_parent_get_id($API,$dataNamePlan);

                    if ($parent!='notFound') {

                        $DELETE = SimpleQueuesTree::simple_parent_remove($API,$parent[0]['.id']);
                    }

                    break;

                case 'dl':
                    # delete dhcp leases
                    $DELETE = $rocket->delete_dhcp_leases($API,$data,$client->ip,$debug);


                    break;

                case 'pt':

                    $rocket->set_basic_config($API,$error,$data,$client->ip,null,'delete',$debug);

                    $DELETE = SimpleQueuesTree::simple_parent_get_id($API,$data['name']);
                    //verificamos si se encontro al cliente, si no encontro no eliminamos del router solo de la BD
                    if($DELETE != 'notFound'){
                        SimpleQueues::simple_remove($API,$DELETE[0]['.id']);
                    }

                    //Eliminamos los parents

                    $dataNamePlan = Helper::replace_word($data['namePlan']);

                    if($data['tree_priority'] != 0) {
                        $dataNamePlan = $dataNamePlan.'_virtual_'.$data['tree_priority'];
                    }

                    //buscamos regla parent segun el plan
                    $parent = SimpleQueuesTree::simple_parent_get_id($API,$dataNamePlan);

                    if ($parent!='notFound') {

                        $DELETE = SimpleQueuesTree::simple_parent_remove($API,$parent[0]['.id']);

                    }

                    //eliminamos del active client
                    $active = Ppp::ppp_active_get_id($API,$client->ip);

                    if ($active != 'notFound') {
                        //eliminamos
                        $remove = Ppp::active_ppp_remove($API,$active[0]['.id']);


                    }

                    $PPP = Ppp::ppp_get_id($API,$data['user']);

                    if($PPP != 'notFound'){

                        $PPP = Ppp::ppp_remove($API,$PPP[0]['.id']);


                    }

                    break;

                case 'ps':

                    $DELETE = $rocket->delete_ppp_simple($API,$data,$client->ip,$debug);


                    break;

                case 'pp':
                    # delete pppoe
                    $DELETE = $rocket->delete_ppp_user($API,$data,$client->ip,$debug);



                    break;

                case 'pc':

                    //eliminamos del address list
                    if ($client->status=='ac') {

                        //eliminamos del address list
                        $ADDLIST = Firewall::get_id_address_list_pcq($API,$client->ip,Helper::replace_word($data['name']));

                        if ($ADDLIST!='notFound') {

                            $ADDLIST = Firewall::remove_address_list($API,$ADDLIST[0]['.id']);


                        }

                        //buscamos el plan en QueueTree y eliminamos el plan DOWN
                        $QUEUETREE = QueueTree::get_parent($API,Helper::replace_word($data['namePlan'].'-DOWN'));

                        if ($QUEUETREE!='notFound') {

                            $QUEUETREE = QueueTree::delete_parent($API,$QUEUETREE[0]['.id']);


                        }

                        //buscamos el plan en QueueTree y eliminamos el plan UP
                        $QUEUETREE = QueueTree::get_parent($API,Helper::replace_word($data['namePlan'].'-UP'));

                        if ($QUEUETREE != 'notFound') {

                            $QUEUETREE = QueueTree::delete_parent($API,$QUEUETREE[0]['.id']);



                        }

                        // Buscamos y eliminamos el queue type asociado al plan DOWN

                        $QUEUETYPE = QueueType::find_queuetype_list($API,Helper::replace_word($data['namePlan'].'_DOWN'));

                        if($QUEUETYPE != 'notFound'){

                            $QUEUETYPE = QueueType::delete_queuetype($API,$QUEUETYPE[0]['.id']);

                        }

                        // Buscamos y eliminamos el queue type asociado al plan UP

                        $QUEUETYPE = QueueType::find_queuetype_list($API,Helper::replace_word($data['namePlan'].'_UP'));

                        if($QUEUETYPE != 'notFound'){

                            $QUEUETYPE = QueueType::delete_queuetype($API,$QUEUETYPE[0]['.id']);

                        }


                        // Buscamos y eliminamos las reglas mangle
                        $MANGLE = Firewall::find_mangle($API,Helper::replace_word($data['namePlan'].'_in'));

                        if ($MANGLE!='notFound') {

                            $MANGLE = Firewall::delete_mangle($API,$MANGLE[0]['.id']);

                        }

                        // Buscamos y eliminamos las reglas mangle
                        $MANGLE = Firewall::find_mangle($API,Helper::replace_word($data['namePlan'].'_out'));

                        if ($MANGLE!='notFound') {

                            $MANGLE = Firewall::delete_mangle($API,$MANGLE[0]['.id']);
                        }


                    }//end if status client


                    //eliminamos del web proxy o del filter
                    $DEL = $rocket->set_basic_config($API,$error,$data,$client->ip,null,'delete',$debug);

                    break;

                case 'pa':

                    //eliminamos el resto de secrets
                    $active = Ppp::ppp_active_get_id($API,$client->ip);

                    if ($active != 'notFound') {
                        //eliminamos
                        $remove = Ppp::active_ppp_remove($API,$active[0]['.id']);

                    }

                    //eliminamos ppp secret
                    $PPP = Ppp::ppp_get_id($API,$data['user']);

                    if($PPP != 'notFound'){
                        $PPP = Ppp::ppp_remove($API,$PPP[0]['.id']);

                    }


                    //eliminamos del address list
                    if ($client->status=='ac') {

                        //eliminamos del address list
                        $ADDLIST = Firewall::get_id_address_list_pcq($API,$client->ip,Helper::replace_word($data['name']));

                        if ($ADDLIST!='notFound') {

                            $ADDLIST = Firewall::remove_address_list($API,$ADDLIST[0]['.id']);


                        }

                        //buscamos el plan en QueueTree y eliminamos el plan DOWN
                        $QUEUETREE = QueueTree::get_parent($API,Helper::replace_word($data['namePlan'].'-DOWN'));

                        if ($QUEUETREE!='notFound') {

                            $QUEUETREE = QueueTree::delete_parent($API,$QUEUETREE[0]['.id']);

                        }

                        //buscamos el plan en QueueTree y eliminamos el plan UP
                        $QUEUETREE = QueueTree::get_parent($API,Helper::replace_word($data['namePlan'].'-UP'));

                        if ($QUEUETREE != 'notFound') {

                            $QUEUETREE = QueueTree::delete_parent($API,$QUEUETREE[0]['.id']);

                        }

                        // Buscamos y eliminamos el queue type asociado al plan DOWN

                        $QUEUETYPE = QueueType::find_queuetype_list($API,Helper::replace_word($data['namePlan'].'_DOWN'));

                        if($QUEUETYPE != 'notFound'){

                            $QUEUETYPE = QueueType::delete_queuetype($API,$QUEUETYPE[0]['.id']);

                        }

                        // Buscamos y eliminamos el queue type asociado al plan UP

                        $QUEUETYPE = QueueType::find_queuetype_list($API,Helper::replace_word($data['namePlan'].'_UP'));

                        if($QUEUETYPE != 'notFound'){

                            $QUEUETYPE = QueueType::delete_queuetype($API,$QUEUETYPE[0]['.id']);


                        }


                        // Buscamos y eliminamos las reglas mangle
                        $MANGLE = Firewall::find_mangle($API,Helper::replace_word($data['namePlan'].'_in'));

                        if ($MANGLE!='notFound') {

                            $MANGLE = Firewall::delete_mangle($API,$MANGLE[0]['.id']);


                        }


                        // Buscamos y eliminamos las reglas mangle
                        $MANGLE = Firewall::find_mangle($API,Helper::replace_word($data['namePlan'].'_out'));

                        if ($MANGLE!='notFound') {

                            $MANGLE = Firewall::delete_mangle($API,$MANGLE[0]['.id']);


                        }


                    }//end if status client


                    //eliminamos del web proxy o del filter
                    $DEL = $rocket->set_basic_config($API,$error,$data,$client->ip,null,'delete',$debug);


                    break;
                case 'ra':
                        /** si el control anterior era por radius, eliminamos los registros de la bd de radius**/
                        //Radreply::where('username',$client->user_hot)->delete();
                        Radcheck::where('username',$client->user_hot)->delete();

                        /**aplicamos lo mismo que pt*/
                    $rocket->set_basic_config($API,$error,$data,$client->ip,null,'delete',$debug);

                    $DELETE = SimpleQueuesTree::simple_parent_get_id($API,$data['name']);
                    //verificamos si se encontro al cliente, si no encontro no eliminamos del router solo de la BD
                    if($DELETE != 'notFound'){
                        SimpleQueues::simple_remove($API,$DELETE[0]['.id']);
                    }

                    //Eliminamos los parents

                    $dataNamePlan = Helper::replace_word($data['namePlan']);

                    if($data['tree_priority'] != 0) {
                        $dataNamePlan = $dataNamePlan.'_virtual_'.$data['tree_priority'];
                    }

                    //buscamos regla parent segun el plan
                    $parent = SimpleQueuesTree::simple_parent_get_id($API,$dataNamePlan);

                    if ($parent!='notFound') {

                        $DELETE = SimpleQueuesTree::simple_parent_remove($API,$parent[0]['.id']);

                    }

                    //eliminamos del active client
                    $active = Ppp::ppp_active_get_id($API,$client->ip);

                    if ($active != 'notFound') {
                        //eliminamos
                        $remove = Ppp::active_ppp_remove($API,$active[0]['.id']);


                    }

                    $PPP = Ppp::ppp_get_id($API,$data['user']);

                    if($PPP != 'notFound'){

                        $PPP = Ppp::ppp_remove($API,$PPP[0]['.id']);


                    }

                case 'rp':
                    /** si el control anterior era por radius, eliminamos los registros de la bd de radius**/
                    //Radreply::where('username',$client->user_hot)->delete();
                    Radcheck::where('username',$client->user_hot)->delete();

                    /**aplicamos lo mismo que pa*/
                    //eliminamos el resto de secrets
                    $active = Ppp::ppp_active_get_id($API,$client->ip);

                    if ($active != 'notFound') {
                        //eliminamos
                        $remove = Ppp::active_ppp_remove($API,$active[0]['.id']);

                    }

                    //eliminamos ppp secret
                    $PPP = Ppp::ppp_get_id($API,$data['user']);

                    if($PPP != 'notFound'){
                        $PPP = Ppp::ppp_remove($API,$PPP[0]['.id']);

                    }


                    //eliminamos del address list
                    if ($client->status=='ac') {

                        //eliminamos del address list
                        $ADDLIST = Firewall::get_id_address_list_pcq($API,$client->ip,Helper::replace_word($data['name']));

                        if ($ADDLIST!='notFound') {

                            $ADDLIST = Firewall::remove_address_list($API,$ADDLIST[0]['.id']);


                        }

                        //buscamos el plan en QueueTree y eliminamos el plan DOWN
                        $QUEUETREE = QueueTree::get_parent($API,Helper::replace_word($data['namePlan'].'-DOWN'));

                        if ($QUEUETREE!='notFound') {

                            $QUEUETREE = QueueTree::delete_parent($API,$QUEUETREE[0]['.id']);

                        }

                        //buscamos el plan en QueueTree y eliminamos el plan UP
                        $QUEUETREE = QueueTree::get_parent($API,Helper::replace_word($data['namePlan'].'-UP'));

                        if ($QUEUETREE != 'notFound') {

                            $QUEUETREE = QueueTree::delete_parent($API,$QUEUETREE[0]['.id']);

                        }

                        // Buscamos y eliminamos el queue type asociado al plan DOWN

                        $QUEUETYPE = QueueType::find_queuetype_list($API,Helper::replace_word($data['namePlan'].'_DOWN'));

                        if($QUEUETYPE != 'notFound'){

                            $QUEUETYPE = QueueType::delete_queuetype($API,$QUEUETYPE[0]['.id']);

                        }

                        // Buscamos y eliminamos el queue type asociado al plan UP

                        $QUEUETYPE = QueueType::find_queuetype_list($API,Helper::replace_word($data['namePlan'].'_UP'));

                        if($QUEUETYPE != 'notFound'){

                            $QUEUETYPE = QueueType::delete_queuetype($API,$QUEUETYPE[0]['.id']);


                        }


                        // Buscamos y eliminamos las reglas mangle
                        $MANGLE = Firewall::find_mangle($API,Helper::replace_word($data['namePlan'].'_in'));

                        if ($MANGLE!='notFound') {

                            $MANGLE = Firewall::delete_mangle($API,$MANGLE[0]['.id']);


                        }


                        // Buscamos y eliminamos las reglas mangle
                        $MANGLE = Firewall::find_mangle($API,Helper::replace_word($data['namePlan'].'_out'));

                        if ($MANGLE!='notFound') {

                            $MANGLE = Firewall::delete_mangle($API,$MANGLE[0]['.id']);


                        }


                    }//end if status client


                    //eliminamos del web proxy o del filter
                    $DEL = $rocket->set_basic_config($API,$error,$data,$client->ip,null,'delete',$debug);

                    break;

                    case 'rr';
                        /** si el control anterior era por radius, eliminamos los registros de la bd de radius**/
                        /**si el plan es con control en mkt*/
                        if(!$plan['no_rules'])
                            Radreply::where('username',$client->user_hot)->delete();

                        Radcheck::where('username',$client->user_hot)->delete();

                        $ip_ro = Router::find($router_id)->ip;
                        $router = Router::find($router_id);
                        $secret = $router->radius->secret;
                        $ejecucion = shell_exec('echo User-Name="'.$client->user_hot.'" | /usr/bin/radclient -c 1 -n 3 -r 3 -t 3 -x '.$ip_ro.':3799 disconnect '.$secret.' 2>&1');


                        /**aplicamos lo mismo que ps **/
                        /**si el plan es con control en mkt*/
                        if(!$plan['no_rules'])
                            $DELETE = $rocket->delete_ppp_simple($API,$data,$client->ip,$debug,true);

                        break;

            }//end switch

        }//end foreach

        /**
         * Si el tipo de control es por radius, entonces debemos:
         * eliminar el nas en la bd de radius
         * reiniciar el servicio de freradius para que tome el nas
         * deshabilitar pppoe use radius por api de mkt
         * sacar radius en mkt (con el secret)
         **/


        if($oldcontrol == 'ra' || $oldcontrol == 'rp' || $oldcontrol == 'rr'){
            set_time_limit(0);
            Artisan::call('cache:clear');
            Artisan::call('config:clear');

            $router = Router::find($router_id);
            $secret = $router->radius->secret;

            /** aplicamos la configuracion sobre el router **/
            $radius = new RadiusLibrary();
            $radius->radius_delete($API,$secret,$radius_server);

            /**eliminamos nas sobre bd de radius**/
            $nas_name= Nas::where('nasname',$router->ip)->delete();
            Radius::where('router_id',$router->id)->delete();

            /**reiniciamos el servicio de freeradius del server*/
            $ejecucion=exec("/usr/bin/sudo /etc/init.d/freeradius stop");
            $ejecucion=exec("sudo killall freeradius");
            $ejecucion=exec('sudo /etc/init.d/freeradius restart');

        }
        return true;

    }//end method
}
