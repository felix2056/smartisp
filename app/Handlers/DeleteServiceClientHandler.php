<?php

namespace App\Handlers;

use App\libraries\Burst;
use App\libraries\Chkerr;
use App\libraries\CountClient;
use App\libraries\Helpers;
use App\libraries\Mikrotik;
use App\libraries\Mkerror;
use App\libraries\Pencrypt;
use App\libraries\PermitidosList;
use App\libraries\RocketCore;
use App\libraries\RouterConnect;
use App\libraries\Slog;
use App\libraries\StatusIp;
use App\models\ClientService;
use App\models\ControlRouter;
use App\models\GlobalSetting;
use App\models\Plan;
use App\models\radius\Radcheck;
use App\models\radius\Radreply;
use App\models\Router;
use App\models\SuspendClient;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Http\Response;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Config;


class DeleteServiceClientHandler implements ShouldQueue
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
    public function handle(\App\Events\DeleteServiceClientEvent $event)
    {
        set_time_limit(0);
        ini_set('memory_limit', '1G');
        try {
            $global = GlobalSetting::all()->first();
            $debug = $global->debug;

            $nameClient = $event->client['name'];

            $process = new Chkerr();
            $rocket = new RocketCore();

            $num_cli = ClientService::where('plan_id', '=', $event->service['plan_id'])->where('router_id', $event->service['router_id'])->count(); //for pcq queue tree
            $error = new Mkerror();

            $authType = ControlRouter::where('router_id', '=', $event->service['router_id'])->first();

            $typeconf = $authType->type_control;
            $address_list = $authType->address_list;

            $router = new RouterConnect();
            $con = $router->get_connect($event->service['router_id']);

            $usedip = new StatusIp();
            $counter = new CountClient();
            $log = new Slog();

            $plan = Plan::find($event->service['plan_id']);

            if ($authType->adv == 1) {
                $drop = 0;
            } else {
                $drop = 1;
            }

            if ($typeconf == 'no') {

                //eliminamos de adv, arp, dhcp o drop
                //GET all data for API
                $conf = Helpers::get_api_options('mikrotik');
                $API = new Mikrotik($con['port'], $conf['a'], $conf['t'], $conf['s']);
                $API->debug = $conf['d'];
                if ($API->connect($con['ip'], $con['login'], $con['password'])) {

                    $error = new Mkerror();

                    $DELETE = $rocket->set_basic_config($API, $error, $event->data, $event->service['ip'], null, 'delete', $debug);

                    if ($debug == 1) {
                        if ($DELETE != false) {
                            return $DELETE;
                        }
                    }

                    //eliminamos en la base de datos

                    //marcamos como libre la ip
                    $usedip->is_used_ip($event->service['ip'], 0, false);
                    //descontamos el numero de clientes del router
                    $counter->step_down_router($event->service['router_id']);
                    //descontamos el numero de clientes del plan
                    $counter->step_down_plan($event->service['plan_id']);
                    //eliminamos de la tabla suspend
                    SuspendClient::where('service_id', '=', $event->service['id'])->delete();

                    //save log
                    $log->saveTo("Se ha eliminado un cliente:","danger",$nameClient,$event->authUser);
                    //Desconectamos de la API
                    $API->disconnect();

                    return $process->show('success');

                } else {
                    return Response::json(array('msg' => 'errorConnect'));
                }

            }

            if ($typeconf == 'sq') {

                //GET all data for API
                $conf = Helpers::get_api_options('mikrotik');
                $API = new Mikrotik($con['port'], $conf['a'], $conf['t'], $conf['s']);
                $API->debug = $conf['d'];

                if ($API->connect($con['ip'], $con['login'], $con['password'])) {

                    $DELETE = $rocket->delete_simple_queues($API, $event->data, $event->service['ip'], $debug);

                    $list = PermitidosList::remove($API,$event->data, $debug, $error);

                    if ($debug == 1) {
                        //control y procesamiento de errores
                        if ($process->check($DELETE)) {
                            $API->disconnect();
                            return $process->check($DELETE);
                        }
                    }

                    //eliminamos en la base de datos

                    //marcamos como libre la ip
                    $usedip->is_used_ip($event->service['ip'],0,false);
                    //descontamos el numero de clientes del router
                    $counter->step_down_router($event->service['router_id']);
                    //descontamos el numero de clientes del plan
                    $counter->step_down_plan($event->service['plan_id']);
                    //eliminamos de la tabla suspend
                    SuspendClient::where('service_id','=',$event->service['id'])->delete();
                    //save log
                    $log->saveTo("Se ha eliminado un cliente:","danger",$nameClient,$event->authUser);
                    //Desconectamos de la API
                    $API->disconnect();

                    return $process->show('success');
                }
                else{
                    return Response::json(array('msg'=>'errorConnect'));
                }
            }

            if($typeconf=='st') { //Simple queue with tree

                //GET all data for API
                $conf = Helpers::get_api_options('mikrotik');
                $API = new Mikrotik($con['port'],$conf['a'],$conf['t'],$conf['s']);
                $API->debug = $conf['d'];

                if ($API->connect($con['ip'], $con['login'], $con['password'])) {

                    $DELETE = $rocket->delete_simple_queue_with_tree($API,$event->data,$event->service['ip'],'delete',$debug);

                    $list = PermitidosList::remove($API,$event->data, $debug, $error);
                    //return $DELETE;

                    if ($debug==1) {
                        //control y procesamiento de errores
                        if($process->check($DELETE)){
                            $API->disconnect();
                            return $process->check($DELETE);
                        }
                    }

                    //eliminamos en la base de datos

                    //marcamos como libre la ip
                    $usedip->is_used_ip($event->service['ip'], 0, false);
                    //descontamos el numero de clientes del router
                    $counter->step_down_router($event->service['router_id']);
                    //descontamos el numero de clientes del plan
                    $counter->step_down_plan($event->service['plan_id']);
                    //eliminamos de la tabla suspend
                    SuspendClient::where('service_id', '=', $event->service['id'])->delete();

                    //save log
                    $log->saveTo("Se ha eliminado un cliente:","danger",$nameClient,$event->authUser);
                    //Desconectamos de la API
                    $API->disconnect();

                    return $process->show('success');
                } else {
                    return Response::json(array('msg' => 'errorConnect'));
                }
            }

            if ($typeconf == 'dl') { //DHCP leases

                //GET all data for API
                $conf = Helpers::get_api_options('mikrotik');
                $API = new Mikrotik($con['port'], $conf['a'], $conf['t'], $conf['s']);
                $API->debug = $conf['d'];

                if ($API->connect($con['ip'], $con['login'], $con['password'])) {

                    $DELETE = $rocket->delete_dhcp_leases($API, $event->data, $event->service['ip'], $debug);

                    $list = PermitidosList::remove($API,$event->data, $debug, $error);

                    if ($debug == 1) {
                        //control y procesamiento de errores
                        if ($process->check($DELETE)) {
                            $API->disconnect();
                            return $process->check($DELETE);
                        }
                    }

                    //eliminamos en la base de datos

                    //marcamos como libre la ip
                    $usedip->is_used_ip($event->service['ip'], 0, false);
                    //descontamos el numero de clientes del router
                    $counter->step_down_router($event->service['router_id']);
                    //descontamos el numero de clientes del plan
                    $counter->step_down_plan($event->service['plan_id']);
                    //eliminamos de la tabla suspend
                    SuspendClient::where('service_id', '=', $event->service['id'])->delete();

                    //save log
                    $log->saveTo("Se ha eliminado un cliente:","danger",$nameClient,$event->authUser);
                    //Desconectamos de la API
                    $API->disconnect();

                    return $process->show('success');

                } else {
                    return Response::json(array('msg' => 'errorConnect'));
                }
            }

            if ($typeconf=='ps') { //PPP secrets Simple Queues

                //GET all data for API
                $conf = Helpers::get_api_options('mikrotik');
                $API = new Mikrotik($con['port'],$conf['a'],$conf['t'],$conf['s']);
                $API->debug = $conf['d'];

                if ($API->connect($con['ip'], $con['login'], $con['password'])) {
                    $DELETE = $rocket->delete_ppp_simple($API,$event->data,$event->service['ip'],$debug);

                    $list = PermitidosList::remove($API,$event->data, $debug, $error);

                    if ($debug==1) {
                        //control y procesamiento de errores
                        if($process->check($DELETE)){
                            $API->disconnect();
                            return $process->check($DELETE);
                        }
                    }


                    //eliminamos en la base de datos

                    //marcamos como libre la ip
                    $usedip->is_used_ip($event->service['ip'],0,false);
                    //descontamos el numero de clientes del router
                    $counter->step_down_router($event->service['router_id']);
                    //descontamos el numero de clientes del plan
                    $counter->step_down_plan($event->service['plan_id']);
                    //eliminamos de la tabla suspend
                    SuspendClient::where('service_id','=',$event->service['id'])->delete();

                    //save log
                    $log->saveTo("Se ha eliminado un cliente:","danger",$nameClient,$event->authUser);
                    //Desconectamos de la API
                    $API->disconnect();

                    return $process->show('success');
                }else{
                    return Response::json(array('msg'=>'errorConnect'));
                }
            }

            if ($typeconf=='pt') { //PPP secrets Simple Queues With Tree

                //GET all data for API
                $conf = Helpers::get_api_options('mikrotik');
                $API = new Mikrotik($con['port'],$conf['a'],$conf['t'],$conf['s']);
                $API->debug = $conf['d'];

                if ($API->connect($con['ip'], $con['login'], $con['password'])) {

                    //eliminamos en la base de datos

                    //marcamos como libre la ip
                    $usedip->is_used_ip($event->service['ip'],0,false);
                    //descontamos el numero de clientes del router
                    $counter->step_down_router($event->service['router_id']);
                    //descontamos el numero de clientes del plan
                    $counter->step_down_plan($event->service['plan_id']);
                    //eliminamos de la tabla suspend
                    SuspendClient::where('service_id','=',$event->service['id'])->delete();

                    $DELETE = $rocket->delete_ppp_simple_queue_with_tree($API,$event->data,$event->service['ip'],'delete',$debug);

                    $list = PermitidosList::remove($API,$event->data, $debug, $error);

                    if ($debug==1) {
                        //control y procesamiento de errores
                        if($process->check($DELETE)){
                            $API->disconnect();
                            return $process->check($DELETE);
                        }
                    }

                    //save log
                    $log->saveTo("Se ha eliminado un cliente:","danger",$nameClient,$event->authUser);
                    //Desconectamos de la API
                    $API->disconnect();

                    return $process->show('success');
                }else{
                    return Response::json(array('msg'=>'errorConnect'));
                }

            }

            if ($typeconf == 'pp') { //PPP secrets

                //GET all data for API
                $conf = Helpers::get_api_options('mikrotik');
                $API = new Mikrotik($con['port'], $conf['a'], $conf['t'], $conf['s']);
                $API->debug = $conf['d'];

                if ($API->connect($con['ip'], $con['login'], $con['password'])) {
                    $DELETE = $rocket->delete_ppp_user($API, $event->data, $event->service['ip'], $debug);

                    $list = PermitidosList::remove($API,$event->data, $debug, $error);

                    if ($debug == 1) {
                        //control y procesamiento de errores
                        if ($process->check($DELETE)) {
                            $API->disconnect();
                            return $process->check($DELETE);
                        }
                    }

                    //eliminamos en la base de datos

                    //marcamos como libre la ip
                    $usedip->is_used_ip($event->service['ip'], 0, false);
                    //descontamos el numero de clientes del router
                    $counter->step_down_router($event->service['router_id']);
                    //descontamos el numero de clientes del plan
                    $counter->step_down_plan($event->service['plan_id']);
                    //eliminamos de la tabla suspend
                    SuspendClient::where('service_id', '=', $event->service['id'])->delete();

                    //save log
                    $log->saveTo("Se ha eliminado un cliente:","danger",$nameClient,$event->authUser);
                    //Desconectamos de la API
                    $API->disconnect();

                    return $process->show('success');
                } else {
                    return Response::json(array('msg' => 'errorConnect'));
                }
            }

            if ($typeconf == 'pa') { //PPP secrets pcq

                //GET all data for API
                $conf = Helpers::get_api_options('mikrotik');
                $API = new Mikrotik($con['port'], $conf['a'], $conf['t'], $conf['s']);
                $API->debug = $conf['d'];

                if ($API->connect($con['ip'], $con['login'], $con['password'])) {

                    //opcion avanzada burst del plan
                    $burst = Burst::get_all_burst($plan->upload, $plan->download, $plan->burst_limit, $plan->burst_threshold, $plan->limitat);

                    $advanced_data = array(
                        'user' => $event->service['user_hot'],
                        'name' => $nameClient.'_'.$event->service['id'],
                        'mac' => $event->service['mac'],
                        'arp' => $event->data['arp'],
                        'adv' => $event->data['adv'],
                        'dhcp' => $event->data['dhcp'],
                        'drop' => $drop,
                        'namePlan' => $plan->name,
                        'num_cl' => $num_cli,
                        'speed_down' => $plan->download,
                        'speed_up' => $plan->upload,
                        //advanced for pcq
                        'burst_rate_down' => $burst['bld'],
                        'burst_rate_up' => $burst['blu'],
                        'burst_threshold_down' => $burst['btd'],
                        'burst_threshold_up' => $burst['btu'],
                        'limit_at_down' => $burst['lim_at_down'],
                        'limit_at_up' => $burst['lim_at_up'],
                        'burst_time' => $plan->burst_time,
                        'priority_a' => $plan->priority,
                    );

                    $DELETE = $rocket->delete_ppp_secrets_pcq($API, $advanced_data, $event->service['ip'], $debug);

                    $list = PermitidosList::remove($API,$event->data, $debug, $error);

                    if ($debug == 1) {
                        //control y procesamiento de errores
                        if ($process->check($DELETE)) {
                            //Desconectamos la API
                            $API->disconnect();
                            return $process->check($DELETE);
                        }
                    }

                    //eliminamos en la base de datos

                    //marcamos como libre la ip
                    $usedip->is_used_ip($event->service['ip'], 0, false);
                    //descontamos el numero de clientes del router
                    $counter->step_down_router($event->service['router_id']);
                    //descontamos el numero de clientes del plan
                    $counter->step_down_plan($event->service['plan_id']);
                    //eliminamos de la tabla suspend
                    SuspendClient::where('service_id', '=', $event->service['id'])->delete();

                    //save log
                    $log->saveTo("Se ha eliminado un cliente:","danger",$nameClient,$event->authUser);
                    //Desconectamos de la API
                    $API->disconnect();

                    return $process->show('success');

                } else {
                    return Response::json(array('msg' => 'errorConnect'));
                }

            }

            if ($typeconf == 'pc') {

                //GET all data for API
                $conf = Helpers::get_api_options('mikrotik');
                $API = new Mikrotik($con['port'], $conf['a'], $conf['t'], $conf['s']);
                $API->debug = $conf['d'];

                if ($API->connect($con['ip'], $con['login'], $con['password'])) {

                    //opcion avanzada burst del plan
                    $burst = Burst::get_all_burst($plan->upload, $plan->download, $plan->burst_limit, $plan->burst_threshold, $plan->limitat);

                    $advanced_data = array(
                        'name' => $nameClient.'_'.$event->service['id'],
                        'mac' => $event->service['mac'],
                        'arp' => $event->data['arp'],
                        'adv' => $event->data['adv'],
                        'dhcp' => $event->data['dhcp'],
                        'drop' => $drop,
                        'namePlan' => $plan->name,
                        'num_cl' => $num_cli,
                        'speed_down' => $plan->download,
                        'speed_up' => $plan->upload,
                        //advanced for pcq
                        'burst_rate_down' => $burst['bld'],
                        'burst_rate_up' => $burst['blu'],
                        'burst_threshold_down' => $burst['btd'],
                        'burst_threshold_up' => $burst['btu'],
                        'limit_at_down' => $burst['lim_at_down'],
                        'limit_at_up' => $burst['lim_at_up'],
                        'burst_time' => $plan->burst_time,
                        'priority_a' => $plan->priority,
                    );

                    $DELETE = $rocket->delete_pcq_list($API, $advanced_data, $event->service['ip'], 'delete', $debug);

                    $list = PermitidosList::remove($API,$event->data, $debug, $error);

                    if ($debug == 1) {
                        //control y procesamiento de errores
                        if ($process->check($DELETE)) {
                            //Desconectamos la API
                            $API->disconnect();
                            return $process->check($DELETE);
                        }
                    }

                    //eliminamos en la base de datos

                    //marcamos como libre la ip
                    $usedip->is_used_ip($event->service['ip'], 0, false);
                    //descontamos el numero de clientes del router
                    $counter->step_down_router($event->service['router_id']);
                    //descontamos el numero de clientes del plan
                    $counter->step_down_plan($event->service['plan_id']);
                    //eliminamos de la tabla suspend
                    SuspendClient::where('service_id', '=', $event->service['id'])->delete();

                    //save log
                    $log->saveTo("Se ha eliminado un cliente:","danger",$nameClient,$event->authUser);
                    //Desconectamos de la API
                    $API->disconnect();

                    return $process->show('success');

                } else {
                    return Response::json(array('msg' => 'errorConnect'));
                }
            }

            if($typeconf == 'ra'){

                $router = Router::find($event->service['router_id']);

//                Radreply::where('username',$event->service['user_hot'])->delete();
                Radcheck::where('username',$event->service['user_hot'])->delete();


//                //marcamos como libre la ip
//                $usedip->is_used_ip($event->service['ip'], 0, false);
//                //descontamos el numero de clientes del router
//                $counter->step_down_router($event->service['router_id']);
//                //descontamos el numero de clientes del plan
//                $counter->step_down_plan($event->service['plan_id']);
//                //eliminamos de la tabla suspend
//                SuspendClient::where('service_id', '=', $event->service['id'])->delete();
//
//                $conf = Helpers::get_api_options('mikrotik');
//                $API = new Mikrotik($con['port'], $conf['a'], $conf['t'], $conf['s']);
//                $API->debug = $conf['d'];
//
//                if ($API->connect($con['ip'], $con['login'], $con['password'])) {
//                    $DELETE = $rocket->delete_ppp_user($API, $event->data, $event->service['ip'], $debug);
//                    $list = PermitidosList::remove($API,$event->data, $debug, $error);
//                }
//
//                //save log
//                $log->saveTo("Se ha eliminado un cliente:","danger",$nameClient,$event->authUser);



                /**ejecutamos mismo caso que con pt**/

                //GET all data for API
                $conf = Helpers::get_api_options('mikrotik');
                $API = new Mikrotik($con['port'],$conf['a'],$conf['t'],$conf['s']);
                $API->debug = $conf['d'];

                if ($API->connect($con['ip'], $con['login'], $con['password'])) {

                    //eliminamos en la base de datos

                    //marcamos como libre la ip
                    $usedip->is_used_ip($event->service['ip'],0,false);
                    //descontamos el numero de clientes del router
                    $counter->step_down_router($event->service['router_id']);
                    //descontamos el numero de clientes del plan
                    $counter->step_down_plan($event->service['plan_id']);
                    //eliminamos de la tabla suspend
                    SuspendClient::where('service_id','=',$event->service['id'])->delete();

                    $DELETE = $rocket->delete_ppp_simple_queue_with_tree($API,$event->data,$event->service['ip'],'delete',$debug);

                    $list = PermitidosList::remove($API,$event->data, $debug, $error);

                    if ($debug==1) {
                        //control y procesamiento de errores
                        if($process->check($DELETE)){
                            $API->disconnect();
                            return $process->check($DELETE);
                        }
                    }

                    //save log
                    $log->saveTo("Se ha eliminado un cliente:","danger",$nameClient,$event->authUser);
                    //Desconectamos de la API
                    $API->disconnect();

                    return $process->show('success');
                }else{
                    return Response::json(array('msg'=>'errorConnect'));
                }

            }

            if($typeconf == 'rp') {
                Radcheck::where('username',$event->service['user_hot'])->delete();
                /**ejecutamos el mismo caso que pa**/

                //GET all data for API
                $conf = Helpers::get_api_options('mikrotik');
                $API = new Mikrotik($con['port'], $conf['a'], $conf['t'], $conf['s']);
                $API->debug = $conf['d'];

                if ($API->connect($con['ip'], $con['login'], $con['password'])) {

                    //opcion avanzada burst del plan
                    $burst = Burst::get_all_burst($plan->upload, $plan->download, $plan->burst_limit, $plan->burst_threshold, $plan->limitat);

                    $advanced_data = array(
                        'user' => $event->service['user_hot'],
                        'name' => $nameClient.'_'.$event->service['id'],
                        'mac' => $event->service['mac'],
                        'arp' => $event->data['arp'],
                        'adv' => $event->data['adv'],
                        'dhcp' => $event->data['dhcp'],
                        'drop' => $drop,
                        'namePlan' => $plan->name,
                        'num_cl' => $num_cli,
                        'speed_down' => $plan->download,
                        'speed_up' => $plan->upload,
                        //advanced for pcq
                        'burst_rate_down' => $burst['bld'],
                        'burst_rate_up' => $burst['blu'],
                        'burst_threshold_down' => $burst['btd'],
                        'burst_threshold_up' => $burst['btu'],
                        'limit_at_down' => $burst['lim_at_down'],
                        'limit_at_up' => $burst['lim_at_up'],
                        'burst_time' => $plan->burst_time,
                        'priority_a' => $plan->priority,
                    );

                    $DELETE = $rocket->delete_ppp_secrets_pcq($API, $advanced_data, $event->service['ip'], $debug);

                    $list = PermitidosList::remove($API,$event->data, $debug, $error);

                    if ($debug == 1) {
                        //control y procesamiento de errores
                        if ($process->check($DELETE)) {
                            //Desconectamos la API
                            $API->disconnect();
                            return $process->check($DELETE);
                        }
                    }

                    //eliminamos en la base de datos

                    //marcamos como libre la ip
                    $usedip->is_used_ip($event->service['ip'], 0, false);
                    //descontamos el numero de clientes del router
                    $counter->step_down_router($event->service['router_id']);
                    //descontamos el numero de clientes del plan
                    $counter->step_down_plan($event->service['plan_id']);
                    //eliminamos de la tabla suspend
                    SuspendClient::where('service_id', '=', $event->service['id'])->delete();

                    //save log
                    $log->saveTo("Se ha eliminado un cliente:","danger",$nameClient,$event->authUser);
                    //Desconectamos de la API
                    $API->disconnect();

                    return $process->show('success');

                } else {
                    return Response::json(array('msg' => 'errorConnect'));
                }

            }

            if($typeconf == 'rr') {

                Radcheck::where('username',$event->service['user_hot'])->delete();
                /**si el plan es con control en mkt*/
                if(!$plan['no_rules'])
                    Radreply::where('username',$event->service['user_hot'])->delete();

                $ip_ro = Router::find($event->service['router_id'])->ip;
                $secret = Router::find($event->service['router_id'])->radius->secret;

                $ejecucion = shell_exec('echo User-Name="'.$event->service['user_hot'].'" | /usr/bin/radclient -c 1 -n 3 -r 3 -t 3 -x '.$ip_ro.':3799 disconnect '.$secret.' 2>&1');


                /**ejecutamos el mismo caso que ps**/
                //GET all data for API
                $conf = Helpers::get_api_options('mikrotik');
                $API = new Mikrotik($con['port'],$conf['a'],$conf['t'],$conf['s']);
                $API->debug = $conf['d'];

                if ($API->connect($con['ip'], $con['login'], $con['password'])) {

                    /**si el plan es con control en mkt*/
                    if(!$plan['no_rules'])
                        $DELETE = $rocket->delete_ppp_simple($API,$event->data,$event->service['ip'],$debug,true);

                    $list = PermitidosList::remove($API,$event->data, $debug, $error);

                    if ($debug==1) {
                        //control y procesamiento de errores
                        if($process->check($DELETE)){
                            $API->disconnect();
                            return $process->check($DELETE);
                        }
                    }


                    //eliminamos en la base de datos

                    //marcamos como libre la ip
                    $usedip->is_used_ip($event->service['ip'],0,false);
                    //descontamos el numero de clientes del router
                    $counter->step_down_router($event->service['router_id']);
                    //descontamos el numero de clientes del plan
                    $counter->step_down_plan($event->service['plan_id']);
                    //eliminamos de la tabla suspend
                    SuspendClient::where('service_id','=',$event->service['id'])->delete();

                    //save log
                    $log->saveTo("Se ha eliminado un cliente:","danger",$nameClient,$event->authUser);
                    //Desconectamos de la API
                    $API->disconnect();

                    return $process->show('success');
                }else{
                    return Response::json(array('msg'=>'errorConnect'));
                }

            }

            } catch(\Exception $exception) {
            Log::error($exception->getMessage());
        }
    }

}
