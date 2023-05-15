<?php

namespace App\Handlers;

use App\libraries\Burst;
use App\libraries\Dhcp;
use App\libraries\Helper;
use App\libraries\Helpers;
use App\libraries\Hotspot;
use App\libraries\Mikrotik;
use App\libraries\Mkerror;
use App\libraries\MkMigrate;
use App\libraries\Ppp;
use App\libraries\RocketCore;
use App\libraries\RouterConnect;
use App\libraries\SimpleQueues;
use App\libraries\SimpleQueuesTree;
use App\models\ClientService;
use App\models\ControlRouter;
use App\models\GlobalSetting;
use App\models\radius\Radcheck;
use App\models\radius\Radreply;
use App\models\Router;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class UpdatePlanHandler implements ShouldQueue
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
    public function handle(\App\Events\UpdatePlanEvent $event)
    {
        set_time_limit(0);
        ini_set('memory_limit', '1G');
        $global = GlobalSetting::first();
        $debug = $global->debug;
        $conf = Helpers::get_api_options('mikrotik');
        $error = new Mkerror();
        $rocket = new RocketCore();
        $plan = $event->plan;

        $editAggregation = isset($event->requestData['edit_aggregation']) ? $event->requestData['edit_aggregation'] : 1;

        //iteramos la cantidad de routers

        for ($i=0; $i < count($event->routers); $i++) {
            $clients = ClientService::with('client')->where('plan_id','=',$event->plan_id)->where('router_id', $event->routers[$i])->where('status', 'ac')->get();

            $router = new RouterConnect();
            $con = $router->get_connect($event->routers[$i]);
            // creamos conexion con el router
            $API = new Mikrotik($con['port'],$conf['a'],$conf['t'],$conf['s']);
            $API->debug = $conf['d'];

            if(!isset($event->requestData['no_rules'])) {
	            $event->requestData['no_rules'] = 0;
            }

            if ($con['connect']==0) {
                if ($API->connect($con['ip'], $con['login'], $con['password'])) {

                    foreach($clients as $client) {
                        //verificamos si solo esta cambiando el costo o el iva
                        if ($plan['name']!=$event->requestData['edit_name'] ||
	                        $plan['download']!=$event->requestData['edit_download'] ||
	                        $plan['upload']!=$event->requestData['edit_upload'] ||
	                        $plan['limitat']!=$event->requestData['edit_limitat'] ||
	                        $plan['priority']!=$event->requestData['edit_priority'] ||
	                        $plan['burst_limit']!=$event->requestData['edit_bl'] ||
	                        $plan['burst_threshold']!=$event->requestData['edit_bth'] ||
	                        $plan['burst_time']!=$event->requestData['edit_bt'] ||
	                        $plan['aggregation']!=$event->requestData['edit_aggregation']  ||
	                        $plan['no_rules'] != (int)$event->requestData['no_rules']
                        ){

                            //preparamos las datos del nuevo plan
                            $comment = 'Smartisp - '.$event->requestData['edit_name'];
                            $maxlimit = $event->requestData['edit_upload'].'k/'.$event->requestData['edit_download'].'k';
                            //obtenemos la configuracion burst del nuevo plan
                            $burst = Burst::get_all_burst($event->requestData['edit_upload'],$event->requestData['edit_download'],$event->requestData['edit_bl'],$event->requestData['edit_bth'],$event->requestData['edit_limitat']);
                            $bt = $event->requestData['edit_bt'];
                            //burst limit
                            $bl = $burst['blu'].'/'.$burst['bld'];
                            //burst threshold
                            $bth = $burst['btu'].'/'.$burst['btd'];
                            //burst time
                            $bt = $bt.'/'.$bt;
                            //priority
                            $priority = $event->requestData['edit_priority'].'/'.$event->requestData['edit_priority'];
                            //limit At
                            $limit_at = $burst['lim_at_up'].'/'.$burst['lim_at_down'];

                            //verificamos tipo de control
                            $control = ControlRouter::where('router_id',$event->routers[$i])->get();
                            $control = $control[0]->type_control;

                            $num_cli = Helpers::getnumcl($event->routers[$i],$control,$event->plan_id);
                            //switch de control

                            switch ($control) {

                                case 'sq':
                                case 'ps':
                                    # simple queues
//                                foreach ($clients as $client) {

                                    //SIMPLE Q
                                    $SQUEUES = SimpleQueues::simple_get_id($API,$client->ip,$client->client->name.'_'.$client->id);
                                    if($SQUEUES != 'notFound'){
                                        $SQUEUES = SimpleQueues::simple_set($API,$SQUEUES[0]['.id'],$client->client->name.'_'.$client->id,$maxlimit,$client->ip,$bl,$bth,$bt,$limit_at,$priority,$comment);

                                        if ($debug==1) {
                                            $msg = $error->process_error($SQUEUES);
                                            if($msg)
                                                return $msg;
                                        }

                                    }
                                    else{

                                        $SQUEUES = SimpleQueues::simple_add($API,$client->client->name.'_'.$client->id,$client->ip,$maxlimit,$bl,$bth,$bt,$limit_at,$priority,$comment);

                                        if ($debug==1) {
                                            $msg = $error->process_error($SQUEUES);
                                            if($msg)
                                                return $msg;
                                        }

                                    }

//                                    }//end foreach

                                    break;

                                case 'st':
                                case 'pt':
                                    # simple queues (with tree)

                                    # add or update clients to parents
                                    $P_DATA = $rocket->data_simple_queue_with_tree_parent($event->plan_id,$client->router_id,$event->requestData['edit_download'],$event->requestData['edit_upload'],$editAggregation,$event->requestData['edit_limitat'],$event->requestData['edit_bl'],$event->requestData['edit_bth'], $client->tree_priority);

//                                    dd($P_DATA);
                                    $dataNamePlan = Helper::replace_word($event->requestData['edit_name']);
                                    $oldPlanName = Helper::replace_word($plan['name']);
                                    $requestComment = 'SmartISP - '.$event->requestData['edit_name'];

                                    if($client->tree_priority != 0) {
                                        $dataNamePlan = $dataNamePlan.'_virtual_'.$client->tree_priority;
                                        $oldPlanName = $oldPlanName.'_virtual_'.$client->tree_priority;
                                        $requestComment = $requestComment.'_virtual_'.$client->tree_priority;
                                    }

                                    //buscamos regla parent segun el plan
                                    $parent = SimpleQueuesTree::simple_parent_get_id($API,$oldPlanName);

                                    if ($P_DATA['ncl']>0) { // aplicamos los cambios si existen clientes activos en el plan

                                        if ($parent=='notFound') {
                                            # Creamos parent
                                            $PARENT = SimpleQueuesTree::add_simple_parent($API,$dataNamePlan,$P_DATA['ips'],$P_DATA['maxlimit'],$P_DATA['bl'],$P_DATA['bth'],$bt,$P_DATA['limitat'],$priority,$requestComment);

                                        }else{
                                            # Actualizamos parent
                                            $PARENT = SimpleQueuesTree::set_simple_parent($API,$parent[0]['.id'],$dataNamePlan,$P_DATA['maxlimit'],$P_DATA['ips'],$P_DATA['bl'],$P_DATA['bth'],$bt,$P_DATA['limitat'],$priority,$requestComment);
                                        }

                                        //$limitat = Burst::get_percent_kb($event->requestData['edit_upload'],$event->requestData['edit_download'],$event->requestData['edit_limitat']);

//                                        $clients = ClientService::with('client')->where('plan_id',$event->plan_id)->where('router_id',$event->routers[$i])->where('status','ac')->get();

                                        $limitat = $P_DATA['limitat_up_cl'].'k/'.$P_DATA['limitat_down_cl'].'k';

//                                        foreach ($clients as $client) {

                                        //SIMPLE SQWT
                                        $SQUEUES = SimpleQueuesTree::simple_child_get_id($API,$client->client->name.'_'.$client->id);

                                        if($SQUEUES != 'notFound'){

                                            SimpleQueuesTree::set_simple_child($API,$SQUEUES[0]['.id'],$client->client->name.'_'.$client->id,$maxlimit,$client->ip,$dataNamePlan,$bl,$bth,$bt,$limitat,$priority,$requestComment);

                                        }
                                        else{

                                            SimpleQueuesTree::add_simple_child($API,$client->client->name.'_'.$client->id,$client->ip,$dataNamePlan,$maxlimit,$bl,$bth,$bt,$limitat,$priority,$requestComment);

                                        }//end else


//                                        }//end foreach

                                    }//end if
                                    else{ //no hay clientes en el plan intentamos eliminar el plan si existe en el router

                                        if ($parent!='notFound') {

                                            $SQUEUES = SimpleQueuesTree::simple_parent_remove($API,$parent[0]['.id']);

                                        }

                                    }

                                    break;

                                case 'dl':
                                    # dhcp leases


//                                    foreach ($clients as $client) {

                                    //DHCP
                                    $DHCP = Dhcp::dhcp_get_id($API,$client->ip,$client->mac);
                                    if($DHCP != 'notFound'){
                                        $DHCP = Dhcp::dhcp_rate_set($API,$DHCP[0]['.id'],$client->mac,$client->ip,$maxlimit,$bl,$bth,$bt,$client->client->name.'_'.$client->id.' - '.$comment);

                                        if ($debug==1) {
                                            $msg = $error->process_error($DHCP);
                                            if($msg)
                                                return $msg;
                                        }

                                    }
                                    else{

                                        $DHCP = Dhcp::dhcp_add_rate($API,$client->ip,$client->mac,$maxlimit,$bl,$bth,$bt,$client->client->name.'_'.$client->id.' - '.$comment);

                                        if ($debug==1) {
                                            $msg = $error->process_error($DHCP);
                                            if($msg)
                                                return $msg;
                                        }

                                    }

//                                    }//end foreach

                                    break;

                                case 'pp':
                                    # ppp secret

                                    //buscamos un profile segun nombre del plan
                                    $PROFILE = Ppp::ppp_find_profile($API,$plan['name']);

                                    if ($debug==1) {
                                        $msg = $error->process_error($PROFILE);
                                        if($msg)
                                            return $msg;
                                    }


                                    if(count($PROFILE)>0){ // verificamos si el profile existe si es asi actualizamos los registros

                                        $PPP = Ppp::ppp_set_profile($API,$PROFILE[0]['.id'],$event->requestData['edit_name'],$maxlimit,$bl,$bth,$bt,$event->requestData['edit_priority'],$limit_at);

                                        if ($debug==1) {
                                            $msg = $error->process_error($PPP);
                                            if($msg)
                                                return $msg;
                                        }

                                        //quitamos del user active a todos los clientes asociados al perfil para que este tenga efecto

//                                        foreach ($clients as $client) {

                                        //quitamos del user active para que los cambios tengan efecto en mikrotik
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
                                        //end user active

//                                        }//end foreach
                                    }
                                    else { //creamos el perfil

                                        $PPP = Ppp::ppp_add_profile($API,$event->requestData['edit_name'],$maxlimit,$bl,$bth,$bt,$event->requestData['edit_priority'],$limit_at);

                                        if ($debug==1) {
                                            $msg = $error->process_error($PPP);
                                            if($msg)
                                                return $msg;
                                        }

                                    }

                                    break;

                                case ($control=='pc' || $control=='pa'):

                                    # pcq address list

                                    //Aditional Data for PCQ
                                    $data = array(
                                        //general data
                                        'namePlan' => $plan['name'], //old plan
                                        'newPlan' => $event->requestData['edit_name'],
                                        //advanced for pcq for new plan
                                        'speed_down' => $event->requestData['edit_download'],
                                        'speed_up' => $event->requestData['edit_upload'],
                                        'num_cl' => $num_cli,
                                        'rate_down' => $event->requestData['edit_download'].'k',
                                        'rate_up' => $event->requestData['edit_upload'].'k',
                                        'burst_rate_down' => $burst['bld'],
                                        'burst_rate_up' => $burst['blu'],
                                        'burst_threshold_down' => $burst['btd'],
                                        'burst_threshold_up' => $burst['btu'],
                                        'limit_at_down' => $burst['lim_at_down'],
                                        'limit_at_up' => $burst['lim_at_up'],
                                        'burst_time' => $event->requestData['edit_bt'],
                                        'priority_a' => $event->requestData['edit_priority'],
	                                    'no_rules' => $event->requestData['no_rules'],
                                    );//end data array

                                    $Migrate = new MkMigrate();
                                    $UPDATE = $Migrate->plan_migrate_pcq($API,$rocket,$data,$clients,$debug);

                                    if ($debug==1) {
                                        if (!empty($UPDATE)) {
                                            return $UPDATE;
                                        }
                                    }


                                    break;

                                case 'rr':

                                    $maxlimit = $event->requestData['edit_upload'].'k/'.$event->requestData['edit_download'].'k';
                                    $burst_rate = $burst['blu'].'k/'.$burst['bld'].'k';
                                    $burst_threshold = $burst['btu'].'k/'.$burst['btd'].'k';
                                    $burst_time = $event->requestData['edit_bt'];
                                    $priority = $event->requestData['edit_priority'];
                                    $limit_at = $burst['lim_at_up'].'/'.$burst['lim_at_down'];

                                    $config_final = "'".$maxlimit.' '.$burst_rate.' '.$burst_threshold.' '.$burst_time.' '.$priority.' '.$limit_at."'";

                                    $router_buscado = Router::find($event->routers[$i]);
                                    /**si el plan es con control en mkt*/
                                    if(!$event->requestData['no_rules']){
                                        //Radreply::where('username',$client->user_hot)->where('attribute','Mikrotik-Rate-Limit')->update(['value' => $config_final]);
                                        Radreply::updateOrCreate(
                                            ['username' => $client->user_hot,'attribute' => 'Mikrotik-Rate-Limit'],
                                            ['value' => $config_final]
                                        );
//                                        $ejecucion = shell_exec('echo User-Name="'.$client->user_hot.'",Mikrotik-Rate-Limit:="'.$config_final.'" | /usr/bin/sudo /usr/bin/radclient -c 1 -n 3 -r 3 -t 3 -x '.$router_buscado->ip.':3799 coa '.$router_buscado->radius->secret.' 2>&1');
                                        $ejecucion = shell_exec('echo User-Name="'.$client->user_hot.'",Mikrotik-Rate-Limit:="'.$config_final.'" | /usr/bin/radclient -c 1 -n 3 -r 3 -t 3 -x '.$router_buscado->ip.':3799 coa '.$router_buscado->radius->secret.' 2>&1');
                                    }
                                    else{
                                        Radreply::where('username',$client->user_hot)->where('attribute','Mikrotik-Rate-Limit')->delete();
                                        /**desconectamos al cliente*/
                                        $ejecucion = shell_exec('echo User-Name="'.$client->user_hot.'" | /usr/bin/radclient -c 1 -n 3 -r 3 -t 3 -x '.$router_buscado->ip.':3799 disconnect '.$router_buscado->radius->secret.' 2>&1');

                                    }

                                    break;

                            }//end switch



                        }//end if

                    }//end foreach
                    $API->disconnect();
                } else
                    $API->disconnect();
            }//end if connect
        }

    }
}
