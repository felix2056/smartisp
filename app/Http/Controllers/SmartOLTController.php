<?php

namespace App\Http\Controllers;

use App\libraries\Helpers;
use App\libraries\SmartOLT;
use App\models\ClientService;
use App\models\GlobalApi;
use App\models\GlobalSetting;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Redirect;
use Session;
use Illuminate\Http\Request;
use View;
use GuzzleHttp\Client;
use Illuminate\Http\JsonResponse;
use Log;
use Redis;



class SmartOLTController extends BaseController
{
    protected $smartolt_libreria;

    public function __construct()
    {
        $this->smartolt_libreria = new SmartOLT();
        $this->global = GlobalSetting::all()->first();

    }

    /**Definimos las variables que necesitamos para el front**/
    public function variables_front(){
        $id = Auth::user()->id;
        $level = Auth::user()->level;
        $perm = DB::table('permissions')->where('user_id','=',$id)->get();
        $access = $perm[0]->access_clients;

        return $data = [
            "clients" => $perm[0]->access_clients, "plans" => $perm[0]->access_plans, "routers" => $perm[0]->access_routers,
            "users" => $perm[0]->access_users, "system" => $perm[0]->access_system, "bill" => $perm[0]->access_pays,
            "template" => $perm[0]->access_templates, "ticket" => $perm[0]->access_tickets, "sms" => $perm[0]->access_sms,
            "reports" => $perm[0]->access_reports,
            "v" => $this->global->version, "st" => $this->global->status,
            "lv" => $this->global->license, "company" => $this->global->company,
            'permissions' => $perm->first(),
        ];
    }


    public function index()
    {

        $smartolt =  Helpers::get_api_options('smartolt');
        if(!isset($smartolt['c']))
            return redirect('/dashboard')->with('status_rol','Debe activar SMART OLT desde la sección de configuración');

        $res = $this->smartolt_libreria->consumir_api_smartolt('GET','api/system/get_olts');
        if(isset($res) && isset($res->status) && $res->status == false)
            return redirect('/dashboard')->with('status_rol','Error SmartOLT: '.$res->error);

        $list_olt = json_decode($res->getBody()->getContents())->response;

        $res = $this->smartolt_libreria->consumir_api_smartolt('GET','api/system/get_zones');
        if(isset($res) && isset($res->status) && $res->status == false)
            return redirect('/dashboard')->with('status_rol','Error SmartOLT: '.$res->error);

        $list_zones = json_decode($res->getBody()->getContents())->response;

        $data = $this->variables_front();
        $data['list_olt'] = $list_olt;
        $data['list_zones'] = $list_zones;

        return view('smartolt/index',$data);
    }

    public function getDetailOLT($olt_id){

        /*$res = $this->smartolt_libreria->consumir_api_smartolt('GET','api/onu/get_onu_signal/HWTC0113B892');
        $onu_signal = json_decode($res->getBody()->getContents());
        dd($onu_signal);*/

        $todas = Redis::keys($olt_id.'_*');

        $detalles = collect();
        foreach($todas as $onu){
            $onu_informacion = Redis::get($onu);
            $detalles->push(collect(json_decode($onu_informacion)));
        }

        /***Obtenemos los estados agrupados para el dashboard*/

        $onus_online = (clone $detalles)->where('status','Online')->count();
        $onus_offline_pwrfail = (clone $detalles)->where('status','Power fail')->count();
        $onus_offline_los = (clone $detalles)->where('status','LOS')->count();
        $onus_offline = (clone $detalles)->where('status','Offline')->count();
        $onus_offline_total  = $onus_offline_pwrfail + $onus_offline_los + $onus_offline;
        $onus_autorizadas = (clone $detalles)->count();

        /***Obtenemos las señales agrupadas para el dashboard*/
        $onus_very_good = (clone $detalles)->where('signal','Very good')->count();
        $onus_warning = (clone $detalles)->where('signal','Warning')->count();
        $onus_critical = (clone $detalles)->where('signal','Critical')->count();
        $onus_low_signal_total = $onus_warning + $onus_critical;

        $list_onus_asignadas = clone ($detalles);
        $list_onus_asignadas = $list_onus_asignadas->where('unique_external_id','<>',null); // TODO --> revisar si efectivamente estas son las asignadas


        /***Obtenemos las vlans de la OLT*/
        $res = $this->smartolt_libreria->consumir_api_smartolt('GET','api/olt/get_vlans/'.$olt_id);
        if(isset($res))
            $list_vlans = json_decode($res->getBody()->getContents())->response;
        else
            $list_vlans=[];

        /***Obtenemos las onus sin configurar para el listado y para el dashboard*/
        $res = $this->smartolt_libreria->consumir_api_smartolt('GET','api/onu/unconfigured_onus_for_olt/'.$olt_id);
        if(isset($res) && isset($res->status) && $res->status == false)
            return redirect('/dashboard')->with('status_rol','Error SmartOLT: '.$res->error);

        $list_onus_unregistered = json_decode($res->getBody()->getContents())->response;
        $list_onus_unregistered_collect = collect($list_onus_unregistered);
        $onus_waiting = (clone $list_onus_unregistered_collect)->count();
        $onus_warning_epon = (clone $list_onus_unregistered_collect)->where('pon_type','epon')->count();
        $onus_warning_gpon = (clone $list_onus_unregistered_collect)->where('pon_type','gpon')->count();

        $data = $this->variables_front();
        $data['list_vlans'] = $list_vlans;
        $data['list_onus_unregistered'] = $list_onus_unregistered;
        $data['list_onus'] = $list_onus_asignadas;
        $data['olt_id'] = $olt_id;
        $data['onus_waiting'] = $onus_waiting;
        $data['onus_warning_epon'] = $onus_warning_epon;
        $data['onus_warning_gpon'] = $onus_warning_gpon;
        $data['onus_online'] = $onus_online;
        $data['onus_autorizadas'] = $onus_autorizadas;
        $data['onus_offline_pwrfail'] = $onus_offline_pwrfail;
        $data['onus_offline_los'] = $onus_offline_los;
        $data['onus_offline'] = $onus_offline;
        $data['onus_offline_total'] = $onus_offline_total;
        $data['onus_low_signal_total'] = $onus_low_signal_total;
        $data['onus_critical'] = $onus_critical;
        $data['onus_warning'] = $onus_warning;
        $data['link'] = $this->smartolt_libreria->getLink();

        return view('smartolt/detail',$data);
    }


    public function newZona(Request $request){

        $data = [
            'zone' => $request->nombre_zona,
        ];

        $res = $this->smartolt_libreria->consumir_api_smartolt('POST','api/system/add_zone',$data);

        if(isset($res) && isset($res->status) && $res->status == false)
            return redirect('/smartolt')->with('smart_olt_error', $res->error);
        else
            return redirect('/smartolt')->with('smart_olt_success', 'Zona creada con éxito');
    }

    public function newVlan(Request $request){

        $for_iptv = 0;
        $for_mgmt_voip = 0;
        $dhcp_snooping = 0;
        $lan_to_lan = 0;

        if(isset($request->for_iptv))
            $for_iptv = 1;

        if(isset($request->for_mgmt_voip ))
            $for_mgmt_voip = 1;

        if(isset($request->dhcp_snooping))
            $dhcp_snooping = 1;

        if(isset($request->lan_to_lan))
            $lan_to_lan = 1;

        $data = [
            'vlan' => $request->id_vlan,
            'description' => $request->id_vlan,
            'for_iptv' => $for_iptv,
            'for_mgnt_voip' => $for_mgmt_voip,
            'dhcp_snooping' => $dhcp_snooping,
            'lan_to_lan' => $lan_to_lan,
        ];


        $res = $this->smartolt_libreria->consumir_api_smartolt('POST','api/olt/add_vlan/'.$request->olt_id,$data);

        if(isset($res) && isset($res->status) && $res->status == false)
            return redirect('/smartolt')->with('smart_olt_error', $res->error);
        else
            return redirect('/smartolt')->with('smart_olt_success', 'Vlan creada con éxito');


    }

    public function check_information($id)
    {
        /**if has SN into DATABASE, return this information, if not, return onus unregistered for authorize*/
        $services = ClientService::with('client')->find($id);
        if(is_null($services->smartolt_sn)){
            $res = $this->smartolt_libreria->consumir_api_smartolt('GET','api/onu/unconfigured_onus/');
            if(isset($res) && isset($res->status) && $res->status == false)
                return view('smartolt/error_modal',['error' => 'Error SmartOLT: '.$res->error]);

            $list_onus_unregistered = json_decode($res->getBody()->getContents())->response;

            $res = $this->smartolt_libreria->consumir_api_smartolt('GET','api/system/get_olts');
            if(isset($res) && isset($res->status) && $res->status == false)
                return view('smartolt/error_modal',['error' => 'Error SmartOLT: '.$res->error]);

            $olt_list= json_decode($res->getBody()->getContents())->response;
            $olt_list_collect = collect ($olt_list);
            foreach ($list_onus_unregistered as $onu) {
                $onu->olt_name =  $olt_list_collect->where('id',$onu->olt_id)->first()->name;
            }

            /**also, search onus enables to assignmet client*/
            $all_in_redis = Redis::keys('*');
            $all_in_database = ClientService::where('smartolt_sn','<>',null)->get();

            $sn_enables = array();
            foreach($all_in_redis as $onu_redis){
                $onu_informacion = Redis::get($onu_redis);
                array_push($sn_enables,json_decode($onu_informacion)->sn);
            }

            $sn_in_database = array();
            foreach($all_in_database as $onu_db){
                array_push($sn_in_database,$onu_db->smartolt_sn);
            }
            // sacamos las asignadas de las que estan en redis, y el restante lo mostramos

            $onus_enables_to_assignment = (array_diff($sn_enables,$sn_in_database));
//        $services = ClientService::with('client')->find($id);
            return view('smartolt/modal_onus_unregistered',[
                'list_onus_unregistered' => $list_onus_unregistered,
                'onus_enables_to_assignment' => $onus_enables_to_assignment,
                'id_service' => $id]);
        }
        /**return information**/
        else{
            $res = $this->smartolt_libreria->consumir_api_smartolt('GET','api/onu/get_onus_details_by_sn/'.$services->smartolt_sn);
            if(isset($res) && isset($res->status) && $res->status == false)
                return view('smartolt/error_modal',['error' => 'Error SmartOLT: '.$res->error]);

            $onu = json_decode($res->getBody()->getContents())->onus[0];
            return view('smartolt/modal_onu_detail',['onu' => $onu,'id_service' => $id]);
        }
    }

    public function authorize_data($id,$sn,$board,$port,$olt_id,$type_id)
    {

        $res = $this->smartolt_libreria->consumir_api_smartolt('GET','api/system/get_zones');
        if(isset($res) && isset($res->status) && $res->status == false)
            return view('smartolt/error_modal',['error' => 'Error SmartOLT: '.$res->error]);

        $list_zones = json_decode($res->getBody()->getContents())->response;

        $res = $this->smartolt_libreria->consumir_api_smartolt('GET','api/olt/get_vlans/'.$olt_id);
        if(isset($res) && isset($res->status) && $res->status == false)
            return view('smartolt/error_modal',['error' => 'Error SmartOLT: '.$res->error]);

        $list_vlans = json_decode($res->getBody()->getContents())->response;

        $res = $this->smartolt_libreria->consumir_api_smartolt('GET','api/system/get_onu_types');
        if(isset($res) && isset($res->status) && $res->status == false)
            return view('smartolt/error_modal',['error' => 'Error SmartOLT: '.$res->error]);

        $onus_type = json_decode($res->getBody()->getContents())->response;

        $onu_mode = collect($onus_type)->where('id',$type_id)->first()->capability;

        $services = ClientService::with('client')->find($id);
        return view('smartolt/modal_authorizate_onu',[
            'cliente' => $services->client->name,
            'id_service' => $id,
            'list_zones' => $list_zones,
            'list_vlans' => $list_vlans,
            'sn' => $sn,
            'board' => $board,
            'port' => $port,
            'onus_type' => $onus_type,
            'type_id' => $type_id,
            'olt_id' => $olt_id,
            'onu_mode' => $onu_mode,
        ]);
    }

    public function authorize_nuevo(Request $request){

        $data = [
            'olt_id' => strval($request->olt_id),
            'pon_type' => strval($request->pon_type),
            'board' => strval($request->board),
            'port' => strval($request->port),
            'sn' => strval($request->sn),
            'vlan' => strval($request->vlan_id),
            'onu_type' => strval($request->onu_type),
            'zone' => strval($request->zone),
//            'odb' => 'Splitter325',
            'name' => strval($request->cliente),
//            'address_or_comment' => 'Avenue 9',
//            'onu_mode' => 'Bridging',
            'onu_mode' => strval($request->onu_mode),
            'onu_external_id' => strval($request->sn)
        ];


        if(isset($request->check_custom_profile))
            $data['custom_profile'] = $request->custom_profile;

        $res = $this->smartolt_libreria->consumir_api_smartolt('POST','api/onu/authorize_onu',$data);

        if(isset($res) && isset($res->status) && $res->status == false)
            return redirect('/smartolt')->with('smart_olt_error', $res->error);
        else{

            $client = ClientService::with('client')->find($request->id_service);
            $client->smartolt_sn = $request->sn;
            $client->save();

            // wait 2 second for establish values into smartolt
            sleep(2);
            $res = $this->smartolt_libreria->consumir_api_smartolt('GET','api/onu/get_onus_details_by_sn/'.$request->sn);
            $onu = json_decode($res->getBody()->getContents())->onus[0];

            $res = $this->smartolt_libreria->consumir_api_smartolt('GET','api/onu/get_onu_signal/'.$request->sn);
            $onu_signal = json_decode($res->getBody()->getContents());

            $onu->signal = $onu_signal->onu_signal;
            $onu->signal_1310 = $onu_signal->onu_signal_1310;
            $onu->signal_1490 = $onu_signal->onu_signal_1490;
            $onu->onu_signal_value = $onu_signal->onu_signal_value;

            Redis::set($request->olt_id.'_'.$onu->sn,json_encode((array)($onu)));

            return redirect('/smartolt/'.$request->olt_id)->with('smart_olt_success', 'Onu autorizada con éxito');

        }

    }

    public function delete(Request $request){

         $client = ClientService::with('client')->find($request->id);
         $onu_external_id = $client->smartolt_sn;

         if(!$onu_external_id)
                return Response::json(array('msg'=>'error','text' => __('app.no hay ONU asociada a este servicio')));

         $res = $this->smartolt_libreria->consumir_api_smartolt('POST','api/onu/delete/'.$onu_external_id);
         if(isset($res) && isset($res->status) && $res->status == false){
             return Response::json(array('msg'=>'error','text' => $res->error));
         }
         else{
             $olt_id = $request->olt_id.'_';
             Redis::del($olt_id.$client->smartolt_sn);
             $client->smartolt_sn = null;
             $client->save();
             return Response::json(array('msg'=>'success','text' =>  __('app.onu eliminada con éxito')));
         }
    }

    public function asociar(Request $request){
        $client = ClientService::with('client')->find($request->serviceId);
        $client->smartolt_sn = $request->sn;
        $client->save();

        return Response::json(array('msg'=>'success','text' =>  __('app.onu asociada con éxito')));

    }

}
