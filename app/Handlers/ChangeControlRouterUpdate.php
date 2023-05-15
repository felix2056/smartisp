<?php

namespace App\Handlers;

use App\libraries\AddClient;
use App\libraries\Arp;
use App\libraries\Burst;
use App\libraries\Chkerr;
use App\libraries\CountClient;
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
use App\libraries\PermitidosList;
use App\libraries\Ppp;
use App\libraries\Proxy;
use App\libraries\QueueTree;
use App\libraries\QueueType;
use App\libraries\RocketCore;
use App\libraries\RouterConnect;
use App\libraries\SimpleQueues;
use App\libraries\SimpleQueuesTree;
use App\libraries\StatusIp;
use App\models\AddressRouter;
use App\models\AdvSetting;
use App\models\ClientService;
use App\models\ControlRouter;
use App\models\GlobalSetting;
use App\models\Network;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;

class ChangeControlRouterUpdate implements ShouldQueue
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
    public function handle(\App\Events\ChangeControlRouterUpdate $event)
    {
        set_time_limit(0);
        $router = $event->router;
        $request = $event->request;
        $controlRouter = $event->controlRouter;

            $control = $request['control'];
            Log::debug($control);
            Log::debug($controlRouter);
            //old data
            $old_arp = $controlRouter['arpmac'];
            $old_dhcp = $controlRouter['dhcp'];
            $old_adv = $controlRouter['adv'];
            $controlRouter = $controlRouter['type_control'];
            $router_con = new RouterConnect();
            $con = $router_con->get_connect($router['id']);
            $process = new Chkerr();
            $error = new Mkerror();

            $global = GlobalSetting::all()->first();
            $debug = $global->debug;

            $clients = ClientService::with('client')->where('router_id',$router['id'])->get();

            set_time_limit(count($clients)+3); //unlimited execution time php


            $adv =  $request['adv'];
            $arp = $request['arp'];
            $dhcp = $request['dhcp'];
            $address_list = $request['address_list'];
            //GET all data for API
            $conf = Helpers::get_api_options('mikrotik');
            $API = new Mikrotik($con['port'],$conf['a'],$conf['t'],$conf['s']);
            $API->debug = $conf['d'];

            if ($API->connect($con['ip'], $con['login'], $con['password'])) {

                $rocket = new RocketCore();
                $networks = AddressRouter::where('router_id',$router['id'])->get();

                /////////////////////////////////////BLOCK QUEUE TREE PARENTS////////////////////////////

                if ($control=='pc' || $control=='pa') {
                    //intentamos crear reglas parent
                    $STATUS = $rocket->create_queuetree_parent($API,$debug);

                    if ($debug==1) {
                        if($process->check($STATUS)){
                            return $process->check($STATUS);
                        }
                    }


                }else{
                    //intentamos quitar las reglas parent si existe
                    $DELETE = $rocket->remove_queuetree_parent($API,$debug);

                    if ($debug==1) {
                        if($process->check($DELETE)){
                            return $process->check($DELETE);
                        }
                    }
                }
                //en else control
                /////////////////////////////////////END BLOCK QUEUE TREE PARENTS////////////////////////////

                if (count($clients) > 0) {


                    //////////verificamos si esta cambiando el tipo de control////////////
                    if ($control != $controlRouter) {
                        //cambiamos control migramos los clientes

                        //aditional data
                        $options = array(
                            //new data
                            'adv' =>  $adv,
                            'arp' => $arp,
                            'dhcp' => $dhcp,
                            //old data
                            'old_adv' => $old_adv,
                            'old_dhcp' => $old_dhcp,
                            'old_arp' => $old_arp,
                            //other data
                            'lan' => $con['lan']

                        );

                        if ($adv!=0) {//verificamos que el portal este configurado
                            $advs = AdvSetting::all();
                            if(count(json_decode($advs,1))==0)
                                return Response::json(array('msg'=>'noadv'));

                            if($advs[0]->routers_adv==0 || $advs[0]->ip_server == '')
                                return Response::json(array('msg'=>'noconf'));

                        }//end if

                        //iniciamos el migrador
                        $migrate = new MkMigrate();

                        $MK = $this->control_migrate_process($API,$rocket,$router['id'],$options,$controlRouter,$control,$debug);

                        if ($debug==1) {
                            if($process->check($MK))
                                return $process->check($MK);
                        }



                    }else{
                        //No esta cambiando el control solo actualizamos
                        ////////////////////////////////////ADV BLOCK////////////////////////////////////////

                        if($adv!=0){ //activamos el portal cliente web proxy
                            //verificamos si ya agrego el phath url/ip / aviso
                            $advs = AdvSetting::all();
                            if(count(json_decode($advs,1)) == 0)
                                return Response::json(array('msg'=>'noadv'));

                            if($advs[0]->routers_adv==1 && $advs[0]->ip_server != ''){

                                //recuperamos los datos del aviso de config
                                $adv = AdvSetting::all()->first();
                                $url = $adv->ip_server.'/'.$adv->server_path;
                                //verificamos el tipo de control
                                if ($control=='pp' || $control=='pa' || $control=='ps') { //control pppoe y pppoe pcq + address list
                                    //Quitamos el web proxy standar anterior si existe
                                    $ADV = $rocket->remove_advs($API,$debug);

                                    if ($debug==1) {
                                        if($process->check($ADV)){
                                            $API->disconnect();
                                            return $process->check($ADV);
                                        }
                                    }


                                    //creamos reglas de bloqueo avisos solo para pppoe segun las ip redes creadas
                                    $networks = AddressRouter::where('router_id',$router['id'])->get();
                                    if (count($networks) > 0) {
                                        foreach ($networks as $net) {
                                            //iteramos las ip redes
                                            $STATUS = $rocket->enabled_pppoe_advs($API,$net->network,$debug);

                                            if ($debug==1) {
                                                if($process->check($STATUS)){
                                                    $API->disconnect();
                                                    return $process->check($STATUS);
                                                }//end if
                                            }

                                        }//end foreach
                                    }//end if count networks

                                }else{

                                    //quitamos reglas de bloqueo pppoe si existen
                                    $networks = AddressRouter::where('router_id',$router['id'])->get();
                                    if (count($networks)>0) {
                                        foreach ($networks as $net) {
                                            //iteramos las ip redes
                                            $STATUS = $rocket->remove_advs_ppp($API,$net->network,$debug);

                                            if ($debug==1) {
                                                if($process->check($STATUS)){
                                                    $API->disconnect();
                                                    return $process->check($STATUS);
                                                }//end if
                                            }

                                        }//end foreach
                                    }//end if count networks

                                    //activamos las reglas de bloqueo
                                    $STATUS = $rocket->enabled_advs($API,$con['lan'],$control,$debug);

                                    if ($debug==1) {
                                        if($process->check($STATUS)){
                                            $API->disconnect();
                                            return $process->check($STATUS);
                                        }
                                    }


                                }//end else tipo control pppoe y pppoe pcq + address list


                                //habilitamos web proxy
                                $PRO = $rocket->enable_proxy($API,$debug);

                                if ($debug==1) {
                                    if($process->check($PRO)){
                                        $API->disconnect();
                                        return $process->check($PRO);
                                    }
                                }


                            }else{
                                return Response::json(array('msg'=>'noconf'));
                            }



                            foreach ($clients as $client) { //iteramos los clientes

                                //añadimos al web proxy todos clientes
                                $PROXY = Proxy::proxy_get_id($API,$client->ip);

                                if($PROXY != 'notFound'){
                                    //encontro la ip del usuario en webproxy seteamos
                                    $PROXY = Proxy::proxy_set($API,$PROXY[0]['.id'],$client->ip,$adv->ip_server,$url,$client->client->name.'_'.$client->id);

                                    if ($debug==1) {
                                        if($process->check($PROXY)){
                                            return $process->check($PROXY);
                                        }
                                    }

                                }
                                else{
                                    //no encontro la ip del usuario en webproxy creamos nuevo
                                    $PROXY = Proxy::proxy_add($API,$client->ip,$adv->ip_server,$url,$client->client->name.'_'.$client->id);

                                    if ($debug==1) {
                                        if($process->check($PROXY)){
                                            return $process->check($PROXY);
                                        }
                                    }

                                }

                                //añadimos los clientes bloqueados a address list
                                if ($client->status=='de') {
                                    //añadimos el cliente al address list
                                    $ADDLIST = Firewall::get_id_address_list_name($API,$client->ip,'avisos');

                                    if($ADDLIST != 'notFound'){
                                        //editamos a address list activamos
                                        $ADDLIST = Firewall::set_address_list($API,$ADDLIST[0]['.id'],$client->ip,'avisos','false',$client->client->name.'_'.$client->id);

                                        if ($debug==1) {
                                            if($process->check($ADDLIST)){
                                                return $process->check($ADDLIST);
                                            }
                                        }

                                    }
                                    else{
                                        //no encontro la ip del usuario address list creamos con los nuevos datos enviados
                                        $ADDLIST = Firewall::add_address_list($API,$client->ip,'avisos','false',$client->client->name.'_'.$client->id);
                                        if ($debug==1) {
                                            if($process->check($ADDLIST)){
                                                return $process->check($ADDLIST);
                                            }
                                        }

                                    }
                                }//end if

                                //Intentamos eliminamos las reglas drop
                                $DROP = Firewall::get_id_filter_block($API,$client->ip);

                                if ($DROP!='notFound') {
                                    //encontro la regla eliminamos
                                    $DROP = Firewall::remove_filter_block($API,$DROP[0]['.id']);

                                    if ($debug==1) {
                                        if($process->check($DROP)){
                                            return $process->check($DROP);
                                        }
                                    }

                                }//end if

                                //activamos secret en caso de pppoe

                                if ($control=='pp' || $control=='pa' || $control=='ps' || $control=='pt') {

                                    //buscamos la id del usuario en secrets
                                    $PPP = Ppp::ppp_get_id($API,$client->user_hot);

                                    if($PPP != 'notFound'){
                                        //desactivamos el secret del usuario
                                        $PPP = Ppp::enable_disable_secret($API,$PPP[0]['.id'],'false',$client->client->name.'_'.$client->id);

                                        if ($debug==1) {
                                            $msg = $error->process_error($PPP);
                                            if($msg){
                                                return $msg;
                                            }
                                        }

                                    }
                                }



                            }//end foreach

                        }else{ //desactivamos el portal cliente adv


                            if ($control=='pp' || $control=='pa' || $control=='ps' || $control=='pt') { //control pppoe y pppoe pcq + address list
                                //eliminamos solo pppoe
                                $STATUS = $rocket->remove_proxy_ppp($API,$debug);

                                if ($debug==1) {
                                    if($process->check($STATUS)){
                                        return $process->check($STATUS);
                                    }
                                }

                                //activamos reglas de bloequeo avisos solo para pppoe segun las ip redes creadas
                                $networks = AddressRouter::where('router_id',$router['id'])->get();
                                if (count($networks)>0) {
                                    foreach ($networks as $net) {
                                        $STATUS = $rocket->remove_advs_ppp($API,$net->network, $debug);

                                        if ($debug==1) {
                                            if($process->check($STATUS)){
                                                return $process->check($STATUS);
                                            }
                                        }

                                    }//end foreach
                                }//end if

                            }else{
                                //eliminamos reglas para otros tipos de control

                                //quitamos las reglas del portal cliente si existen y del web proxy
                                $STATUS = $rocket->remove_advs($API,$debug);

                                if ($debug==1) {
                                    if($process->check($STATUS)){
                                        return $process->check($STATUS);
                                    }
                                }



                            }//end else control pppoe y pppoe pcq + address list

                            foreach ($clients as $client) { //iteramos los clientes

                                if ($client->status=='de') {

                                    //desactivamos secret
                                    if ($control=='pp' || $control=='pa' || $control=='ps' || $control=='pt') {

                                        $PPP = Ppp::ppp_get_id($API,$client->user_hot);

                                        if($PPP != 'notFound'){
                                            //desactivamos el secret del usuario
                                            $PPP = Ppp::enable_disable_secret($API,$PPP[0]['.id'],'true','Servicio cortado - '.$client->client->name.'_'.$client->id);

                                            if ($debug==1) {
                                                $msg = $error->process_error($PPP);
                                                if($msg){
                                                    return $msg;
                                                }
                                            }

                                        }

                                    }else{

                                        //agregamos reglas drop a los clientes suspendidos
                                        $DROP = Firewall::get_id_filter_block($API,$client->ip);

                                        if ($DROP=='notFound') {
                                            //establecemos el orden de insercion de la regla

                                            $ORDER = Firewall::count_filter_all($API);

                                            $DROP = Firewall::filter_add_block($API,$client->ip,$client->mac,'Servicio cortado - '.$client->client->name.'_'.$client->id,$ORDER);

                                            if ($debug==1) {
                                                if($process->check($DROP)){
                                                    return $process->check($DROP);
                                                }
                                            }

                                        }

                                    }//end else



                                    //intentamos eliminar todos los clientes de addres list
                                    $ADDLIST = Firewall::get_id_address_list_name($API,$client->ip,'avisos');
                                    if($ADDLIST != 'notFound'){
                                        //eliminamos a address list activamos
                                        $ADDLIST = Firewall::remove_address_list($API,$ADDLIST[0]['.id']);

                                        if ($debug==1) {
                                            if($process->check($ADDLIST)){
                                                return $process->check($ADDLIST);
                                            }
                                        }


                                    }



                                }//end if status

                                //eliminamos todos los clientes del web proxy
                                $PROXY = Proxy::proxy_get_id($API,$client->ip);
                                if($PROXY != 'notFound'){
                                    $PROXY = Proxy::proxy_remove($API,$PROXY[0]['.id']);
                                    if ($debug==1) {
                                        if($process->check($PROXY)){
                                            return $process->check($PROXY);
                                        }
                                    }


                                }

                            }//end foreach

                        }//end else adv

                        ////////////////////////////////////END ADV BLOCK////////////////////////////////////////


                        ////////////////////////////////////ARP BLOCK////////////////////////////////////////
                        //Intentamos agregar o quitar las macs si en caso esta activando el Amarre IP/MAC
                        if ($arp!=0) {

                            foreach ($clients as $client) {//iteramos los clientes

                                if ($client->mac!='00:00:00:00:00:00') { //agregamos al ARP LIST solo los clientes que tienen mac

                                    $ARP = Arp::arp_get_id($API,$client->ip);

                                    if($ARP!='notFound'){

                                        if ($ARP[0]['dynamic'] == 'true') {

                                            $ARP = Arp::arp_add($API,$client->ip,$client->mac,$con['lan'],$client->client->name.'_'.$client->id);

                                            if ($debug==1) {
                                                $msg = $error->process_error($ARP);
                                                if($msg){
                                                    return $msg;
                                                }
                                            }

                                        }else{

                                            $ARP = Arp::arp_set($API,$ARP[0]['.id'],$client->mac,$client->ip,$client->client->name.'_'.$client->id);

                                            if ($debug==1) {
                                                $msg = $error->process_error($ARP);
                                                if($msg){
                                                    return $msg;
                                                }
                                            }
                                        }

                                    }else{

                                        //añadimos los registros
                                        $ARP = Arp::arp_add($API,$client->ip,$client->mac,$con['lan'],$client->client->name.'_'.$client->id);

                                        if ($debug==1) {
                                            $msg = $error->process_error($ARP);
                                            if($msg){
                                                return $msg;
                                            }
                                        }

                                    }//end else
                                }//end if mac
                            }//end foreach

                        }else{
                            //arp esta desactiva eliminamos usuarios del ARP LIST
                            foreach ($clients as $client) {
                                $ARP = Arp::arp_get_id($API,$client->ip);
                                if($ARP != 'notFound'){
                                    $ARP = Arp::arp_remove($API,$ARP[0]['.id']);

                                    if ($debug==1) {
                                        $msg = $error->process_error($ARP);
                                        if($msg){
                                            return $msg;
                                        }
                                    }

                                }//end if
                            }//end foreach

                        }//end else ARP

                        ////////////////////////////////////END ARP BLOCK////////////////////////////////////////

                        /////////////////////////////////////DHCP BLOCK//////////////////////////////////////////

                        if ($dhcp!=0) {
                            //verificamos el tipo de control permitidos
                            if ($control=='sq' || $control=='st' || $control=='pc') {

                                foreach ($clients as $client) {//iteramos los clientes

                                    if($client->mac !='00:00:00:00:00:00'){//agregamos a DHC Leases solo los clientes con mac


                                        $DHCP = Dhcp::dhcp_get_id($API,$client->ip, $client->mac);

                                        if($DHCP!='notFound'){

                                            if($DHCP!='notFound'){
                                                $DHCP = Dhcp::dhcp_set($API,$DHCP[0]['.id'],$client->mac,$client->ip,$client->client->name.'_'.$client->id);

                                                if ($debug==1) {
                                                    $msg = $error->process_error($DHCP);
                                                    if($msg){
                                                        return $msg;
                                                    }
                                                }

                                            }

                                        }else{//significa que no hay duplicidad añadimos los registros
                                            $DHCP = Dhcp::dhcp_add($API,$client->ip,$client->mac,$client->client->name.'_'.$client->id);

                                            if ($debug==1) {
                                                $msg = $error->process_error($DHCP);
                                                if($msg){
                                                    return $msg;
                                                }
                                            }

                                        }
                                    } // end if
                                }//end foreach

                            }//end if control

                        }else{

                            //quitamos los clientes del DCHP Leases
                            if ($control=='sq' || $control=='st' || $control=='pc') {

                                foreach ($clients as $client) {
                                    $DHCP = Dhcp::dhcp_get_id($API,$client->ip,$client->mac);
                                    if($DHCP != 'notFound'){
                                        $DHCP = Dhcp::dhcp_remove($API,$DHCP[0]['.id']);

                                        if ($debug==1) {
                                            $msg = $error->process_error($DHCP);
                                            if($msg){
                                                return $msg;
                                            }
                                        }

                                    }//end if
                                }//end for each
                            }//end if control

                        }//end else DHCP

                        /////////////////////////////////////END DHCP BLOCK//////////////////////////////////////


                    }//end else control chage


                    /////////////////////////////////////BLOCK QUEUE TREE PARENTS////////////////////////////

                    if ($control=='pc' || $control=='pa') {
                        //intentamos crear reglas parent
                        $STATUS = $rocket->create_queuetree_parent($API,$debug);

                        if ($debug==1) {
                            if($process->check($STATUS)){
                                return $process->check($STATUS);
                            }
                        }


                    }else{
                        //intentamos quitar las reglas parent si existe
                        $DELETE = $rocket->remove_queuetree_parent($API,$debug);

                        if ($debug==1) {
                            if($process->check($DELETE)){
                                return $process->check($DELETE);
                            }
                        }

                    }//en else control

                    /////////////////////////////////////BLOCK PARENTS/////////////////////////////////////////

                    ///////// Actualizamos ARP la interfaz LAN ///////////
                    $INTERFACE = $rocket->update_arp_interface($API,$con['lan'],$arp,$debug);

                    if ($debug==1) {
                        //control y procesamiento de errores
                        if($process->check($INTERFACE)){
                            return $process->check($INTERFACE);
                        }
                    }

                    //desconecatamos la API
                    $API->disconnect();

                }//end principal if count clients

                else{// no hay clientes aplicamos la configuracion estandar

                    /////////////////////////////////////BLOCK QUEUE TREE PARENTS////////////////////////////

                    if ($control=='pc' || $control=='pa') {
                        //intentamos crear reglas parent
                        $STATUS = $rocket->create_queuetree_parent($API,$debug);

                        if ($debug==1) {
                            if($process->check($STATUS)) {
                                return $process->check($STATUS);
                            }
                        }


                    }else{
                        //intentamos quitar las reglas parent si existe
                        $DELETE = $rocket->remove_queuetree_parent($API,$debug);

                        if ($debug==1) {
                            if($process->check($DELETE)){
                                return $process->check($DELETE);
                            }
                        }

                    }//en else control

                    /////////////////////////////////////END BLOCK PARENTS/////////////////////////////////////////

                    ////////////////////////////////////BLOCK WEB PROXY //////////////////////////////////////////

                    if ($adv==1) {

                        //verificamos el tipo de control
                        if ($control=='pp' || $control=='pa' || $control=='ps' || $control=='pt') { //control pppoe y pppoe pcq + address list
                            //Quitamos el web proxy standar anterior si existe
                            $ADV = $rocket->remove_advs($API,$debug);

                            if ($debug==1) {
                                if($process->check($ADV)){
                                    return $process->check($ADV);
                                }
                            }


                            //creamos reglas de bloqueo avisos solo para pppoe segun las ip redes creadas
                            $networks = AddressRouter::where('router_id',$router['id'])->get();
                            if (count($networks) > 0) {
                                foreach ($networks as $net) {
                                    //iteramos las ip redes
                                    $STATUS = $rocket->enabled_pppoe_advs($API,$net->network,$debug);

                                    if ($debug == 1) {
                                        if($process->check($STATUS)){
                                            return $process->check($STATUS);
                                        }
                                    }

                                }//end foreach
                            }//end if count networks

                            //activamos web proxy
                            $PRO = $rocket->enable_proxy($API,$debug);

                            if ($debug == 1) {
                                if(!empty($PRO)) {
                                    return $PRO;
                                }
                            }

                        }else{
                            //quitamos reglas de bloqueo pppoe si existen
                            $networks = AddressRouter::where('router_id',$router['id'])->get();
                            if (count($networks) > 0) {
                                foreach ($networks as $net) {
                                    //iteramos las ip redes
                                    $STATUS = $rocket->remove_advs_ppp($API,$net->network,$debug);

                                    if ($debug==1) {
                                        if($process->check($STATUS)){
                                            return $process->check($STATUS);
                                        }
                                    }

                                }//end foreach
                            }//end if count networks

                            //activamos las reglas de bloqueo
                            $STATUS = $rocket->enabled_advs($API,$con['lan'],$control,$debug);

                            if ($debug==1) {
                                if($process->check($STATUS)){
                                    $API->disconnect();
                                    return $process->check($STATUS);
                                }
                            }


                            //habilitamos web proxy
                            $PRO = $rocket->enable_proxy($API,$debug);

                            if ($debug==1) {
                                if($process->check($PRO)){
                                    return $process->check($PRO);
                                }
                            }


                        }//end else tipo control pppoe y pppoe pcq + address list

                    }//end adv
                    else{

                        //verificamos el tipo de control
                        if ($control=='pp' || $control=='pa' || $control=='ps' || $control=='pt') { //control pppoe y pppoe pcq + address list

                            //quitamos reglas de bloqueo pppoe si existen
                            $networks = AddressRouter::where('router_id',$router['id'])->get();
                            if (count($networks) > 0) {
                                foreach ($networks as $net) {
                                    //iteramos las ip redes
                                    $STATUS = $rocket->remove_advs_ppp($API,$net->network,$debug);

                                    if ($debug==1) {
                                        if($process->check($STATUS)){
                                            return $process->check($STATUS);
                                        }
                                    }

                                }//end foreach
                            }//end if count networks

                            $PRO = $rocket->remove_proxy_ppp($API,$debug);

                            if ($debug == 1) {
                                if($process->check($PRO)) {
                                    return $process->check($PRO);
                                }
                            }


                        }else{

                            #quitamos reglas para el portal
                            $STATUS = $rocket->remove_advs($API,$debug);

                            if ($debug==1) {
                                if($process->check($STATUS)){
                                    return $process->check($STATUS);
                                }
                            }

                        }

                    }//end else

                    ////////////////////////////////////END BLOCK WEB PROXY //////////////////////////////////////////

                    ///////// Actualizamos ARP la interfaz LAN ///////////
                    $INTERFACE = $rocket->update_arp_interface($API,$con['lan'],$arp,$debug);

                    if ($debug==1) {
                        //control y procesamiento de errores
                        if($process->check($INTERFACE)){
                            return $process->check($INTERFACE);
                        }
                    }


                    //desconecatamos la API
                    $API->disconnect();

                }//end else

            }else{

                $API->disconnect();
            }//end else connection

            if ($API->connect($con['ip'], $con['login'], $con['password'])) {

                if($control == 'no') {

                    $NAT = Firewall::find_block_nat($API,"SmartISP Avisos");

                    if ($NAT!='notFound') {
                        $NAT = Firewall::remove_block_nat($API,$NAT[0]['.id']);

                        if ($debug==1) {
                            $msg = $error->process_error($NAT);
                            if($msg)
                                return $msg;
                        }

                    }

                    $NAT = Firewall::find_block_nat($API,"Smartisp Avisos");

                    if ($NAT!='notFound') {
                        $NAT = Firewall::remove_block_nat($API,$NAT[0]['.id']);

                        if ($debug==1) {
                            $msg = $error->process_error($NAT);
                            if($msg)
                                return $msg;
                        }

                    }

                    $list = Firewall::find_block_filter($API, 'Smartisp avisos Smartisp-tcp');
                    if ($list != 'notFound') {
                        $ADDLIST = Firewall::remove_filter_block($API, $list[0]['.id']);
                        if ($debug == 1) {
                            $msg = $error->process_error($ADDLIST);
                            if ($msg) {
                                return $msg;
                            }
                        }
                    }

                    $list = Firewall::find_block_filter($API, 'Smartisp avisos Smartisp-dns');
                    if ($list != 'notFound') {
                        $ADDLIST = Firewall::remove_filter_block($API, $list[0]['.id']);
                        if ($debug == 1) {
                            $msg = $error->process_error($ADDLIST);
                            if ($msg) {
                                return $msg;
                            }
                        }
                    }
                }

                if ($address_list == 0 || $control == 'no') {

                    $list = Firewall::get_id_client_list_filter_block($API, 'Permitidos');
                    if ($list != 'notFound') {
                        $ADDLIST = Firewall::remove_filter_block($API, $list[0]['.id']);
                        if ($debug == 1) {
                            $msg = $error->process_error($ADDLIST);
                            if ($msg) {
                                return $msg;
                            }
                        }
                    }

                    foreach ($clients as $client) {
                        $ADDLIST = Firewall::get_id_address_list_name($API, $client->ip, 'Permitidos');
                        if ($ADDLIST != 'notFound') {
                            //eliminamos a address list activamos
                            $ADDLIST = Firewall::remove_address_list($API, $ADDLIST[0]['.id']);
                            if ($debug == 1) {
                                $msg = $error->process_error($ADDLIST);
                                if ($msg) {
                                    return $msg;
                                }
                            }
                        }
                    }



                } else {
                    $list = PermitidosList::checkRuleForClientList($API, 'Permitidos', $debug, $error);

                    if ($debug == 1) {
                        $msg = $error->process_error($list);
                        if ($msg) {
                            return $msg;
                        }
                    }

                    foreach ($clients as $client) {
                        $ADDLIST = Firewall::get_id_address_list_name($API,$client->ip,'Permitidos');

                        if($ADDLIST == 'notFound') {
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
                $API->disconnect();

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
                'tree_priority' => $client->tree_priority,
                'ip' => $client->ip,

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

            Log::debug('hii from old control');

            switch ($oldcontrol) {
                case 'sq':
                    # delete simple queues
                    $DELETE = $rocket->delete_simple_queues($API,$data,$client->ip,$debug);

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
                    //buscamos regla parent segun el plan
                    $parent = SimpleQueuesTree::simple_parent_get_id($API,Helper::replace_word($data['namePlan']));

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

            }//end switch

            //////////End delete previous configuration ///////////

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

            Log::debug('Hello from new control');
            Log::debug($control);
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
                    $ADD = $rocket->add_simple_queue_with_tree($API,$data,$client->ip,'add',$debug);

                    if ($debug==1) {
                        //control y procesamiento de errores
                        if(!empty($ADD)){
                            return $ADD;
                        }
                    }

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
                    # add pppoe simple queue with tree
                    $drop = $data['drop'];
                    $data['drop']=0;

                    $network = Network::where('ip',$client->ip)->get();
                    $gat = AddressRouter::find($network[0]->address_id);

                    $ADD = $rocket->add_ppp_simple_queue_with_tree($API,$data,$client->ip,$gat->gateway,'add',$debug);

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

                $migrate = new MkMigrate();
                $MIGRATE = $migrate->plan_migrate_pcq($API,$rocket,$data,$clients,$debug);

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

}
