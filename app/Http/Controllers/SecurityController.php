<?php

namespace App\Http\Controllers;
use App\libraries\Slog;
use App\models\Client;
use App\models\GlobalSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\View;

class SecurityController extends BaseController
{
    public function __construct()
    {
//        $this->beforeFilter('auth');  //bloqueo de acceso
    }


    public function type_licencia(Request $request)
    {
        set_time_limit(0);
        $licencia = $request->get('licencia');
        $host=$_SERVER["HTTP_HOST"];
        $url="https://licencia.smartisp.us/?edd_action=activate_license&item_id=1646&license=".$licencia."&url=".$host."&ts=".time()."";
        if(!empty($licencia)){
            $id = Auth::user()->id;
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
            $response = curl_exec($ch);
            curl_close($ch);
            if(!$response) {
                return ['status'=>201,'success'=>'error','memssage'=>'Por favor intente más tarde'];
            }else{
                $array = json_decode($response, true);
                if($array['license']=='invalid'){
                    return ['status'=>201,'success'=>'error','memssage'=>'Licencia invalida'];
                }else if($array['license']=='valid'){
                    $now = new \DateTime();
                    $Licencia = GlobalSetting::find(1);
                    $Licencia->license = 11111;
                    $Licencia->license_id = $licencia;
                    $Licencia->status = 'ac';
                    $Licencia->last_update = $now->format('Y-m-d');
                    $Licencia->save();
                    //save log
                    $log = new Slog();
                    $log->save("Agrego la licencia con exito","info");
                    return ['status'=>200,'success'=>'error','memssage'=>'Licencia registrada exitosamente'];

                }else if($array['license']=='expired'){
                    return ['status'=>205,'success'=>'error','memssage'=>'Licencia expirada'];
                }

            }
        }

    }

    public function postActivate(Request $request)
    {
        set_time_limit(0);
        $licencia = $request->get('licencia');
        $host=$_SERVER["HTTP_HOST"];

        $status_res=$this->type_licencia($request);

        if($status_res['status']==205){
            return json_encode(['status'=>404,'success'=>'error','memssage'=>'Licencia expirada']);
        }

        if($status_res['status']==200){
            return json_encode(['status'=>200,'success'=>'error','memssage'=>'Licencia registrada exitosamente']);
        }

        $url="https://licencia.smartisp.us/?edd_action=activate_license&item_id=61&license=".$licencia."&url=".$host."&ts=".time()."";
        if(!empty($licencia)){


            $id = Auth::user()->id;
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
            $response = curl_exec($ch);
            curl_close($ch);
            if(!$response) {
                return json_encode(['status'=>404,'success'=>'error','memssage'=>'Por favor intente más tarde']);
            }else{
                $array = json_decode($response, true);
                if (!isset($array['price_id'])) {
                    $price_ident=17;
                }else{
                    $price_ident=$array['price_id'];
                }
                if($array['license']=='invalid'){
                    return json_encode(['status'=>404,'success'=>'error','memssage'=>'Licencia invalida']);
                }else if($array['license']=='valid'){
                    $now = new \DateTime();
                    $Licencia = GlobalSetting::find(1);
                    $Licencia->license = $price_ident;
                    $Licencia->license_id = $licencia;
                    $Licencia->status = 'ac';
                    $Licencia->last_update = $now->format('Y-m-d');
                    $Licencia->save();
                    //save log
                    $log = new Slog();
                    $log->save("Agrego la licencia con exito","info");
                    return json_encode(['status'=>200,'success'=>'error','memssage'=>'Licencia registrada exitosamente']);

                }else if($array['license']=='expired'){
                    return json_encode(['status'=>404,'success'=>'error','memssage'=>'Licencia expirada']);
                }
                else{
                    return json_encode(['status'=>404,'success'=>'error','memssage'=>'Licencia invalida']);
                }
            }

        }else{
            return json_encode(['status'=>404,'success'=>'error','memssage'=>'Campo de licencia vacio']);
        }


    }
    public static function cant_clientes_api($id_pago)
    {
        set_time_limit(0);
        $url="http://apicantidadcliente.smartisp.us/?token_url=NhjsdbJBjj25484SC5CD5C4DC454SDSD455X8C&id_precio=".$id_pago."&ts=".time()."";
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
        $response = curl_exec($ch);
        curl_close($ch);
        $array = json_decode($response, true);
        return $array;
    }

    public static function cant_clientes_bd()
    {
        return $cantidad_cli=Client::count();
    }

    public static function status_licencia($licencia)
    {
        set_time_limit(0);
        $host=$_SERVER["HTTP_HOST"];

        $Licencia = GlobalSetting::find(1);
        $price_ident=$Licencia->license;
        $l_item_id=61;
        $demo_gratis=false;
        if(!empty($price_ident)){
            if($price_ident==11111){
                $l_item_id=1646;
                $demo_gratis=true;
            }
        }
        $url="https://licencia.smartisp.us/?edd_action=check_license&item_id=".$l_item_id."&license=".$licencia."&url=".$host."&ts=".time()."";

        if(!empty($licencia)){
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
            $response = curl_exec($ch);
            curl_close($ch);

            if(!$response) {
                return array('status'=>404,'success'=>'error','memssage'=>'Por favor intente más tarde');
            }else{
                $status=404;
                $array = json_decode($response, true);

                if (!isset($array['price_id'])) {
                    $price_ident=17;
                    $status=200;
                }else{

                    $price_ident=$array['price_id'];
                }


                $url2="https://licencia.smartisp.us/?edd_action=check_license&item_id=".$l_item_id."&license=".$licencia."&price_id=".$price_ident."&url=".$host."&ts=".time()."";
                $ch2 = curl_init($url2);
                curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch2, CURLOPT_CUSTOMREQUEST, "GET");
                $response2 = curl_exec($ch2);
                curl_close($ch2);
                if(!$response2) {
                    return array('status'=>404,'success'=>'error','memssage'=>'Por favor intente más tarde');
                }else{
                    $array = json_decode($response2, true);
                    $cantiad_clientes=0;
                    if($array['license']=='invalid'){
                        return array('status'=>404,'success'=>'error','memssage'=>'Licencia invalida');
                    }else if($array['license']=='valid' or $array['license']=='expired'){

                        if($demo_gratis){
                            $price_ident=11111;
                        }
                        $info_client=SecurityController::cant_clientes_api($price_ident);
                        if($info_client['status']==200){
                            $cli_bd=SecurityController::cant_clientes_bd();
                            $status_reg_cli=false;
                            if($info_client['cantidad']=='ilimitados'){
                                $status_reg_cli=true;
                            }else{
                                if($cli_bd<$info_client['cantidad']){
                                    $status_reg_cli=true;
                                }
                            }
                            if($array['license']=='expired'){
                                $now = new \DateTime();
                                $Licencia = GlobalSetting::find(1);
                                $Licencia->status = 'ex';
                                $Licencia->last_update = $now->format('Y-m-d');
                                $Licencia->save();
                            }

                            return array(
                                'license'=>$array['license'],
                                'item_id'=>$array['item_id'],
                                'item_name'=>$array['item_name'],
                                'expires'=>$array['expires'],
                                'license_limit'=>$array['license_limit'],
                                'customer_name'=>$array['customer_name'],
                                'customer_email'=>$array['customer_email'],
                                'price_id'=>$price_ident,
                                'cantiad_clientes_api'=>$info_client['cantidad'],
                                'cantiad_clientes_bd'=>$cli_bd,
                                'status_reg_cli'=>$status_reg_cli,
                                'status'=>200,
                                'success'=>'ok',
                                'memssage'=>'Informacion de licencia',
                            );

                        }else{
                            return array('status'=>404,'success'=>'error','memssage'=>'Error api client');
                        }

                    }else{
                        return array('status'=>404,'success'=>'error','memssage'=>'Licencia invalida');
                    }

                }

            }


        }
    }

    public function getIndex()
    {
        $id = Auth::user()->id;
        $level = Auth::user()->level;
        $perm = DB::table('permissions')->where('user_id','=',$id)->get();
        $access = $perm[0]->access_system;
        //actualizamos la licenci

        //control permissions only access super administrator (sa)
        if($level=='ad' || $access == true)
        {
            $global = GlobalSetting::all()->first();
            //verificamos si la licencia es valida
            $dins = date("d/m/Y", strtotime($global->date_installation));
            $permissions = array("clients" => $perm[0]->access_clients, "plans" => $perm[0]->access_plans, "routers" => $perm[0]->access_routers,
                "users" => $perm[0]->access_users, "system" => $perm[0]->access_system, "bill" => $perm[0]->access_pays,
                "template" =>$perm[0]->access_templates, "ticket" => $perm[0]->access_tickets, "sms" => $perm[0]->access_sms,
                "reports" => $perm[0]->access_reports,
                "st" => $global->status, "lv" => $global->license, "license_id" => $global->license_id, "platform" => $global->platform,
                "updated" => $global->last_update,
                "v" => $global->version, "company" => $global->company,
                'permissions' => $perm->first(),
                // menu options
                
            );

            if(Auth::user()->level=='ad')
                @setcookie("hcmd", 'kR2RsakY98pHL', time()+7200,"/","",0, true);

            $contents = View::make('license.index',$permissions);
            $response = Response::make($contents, 200);
            $response->header('Expires', 'Tue, 1 Jan 1980 00:00:00 GMT');
            $response->header('Cache-Control', 'no-store, no-cache, must-revalidate, post-check=0, pre-check=0');
            $response->header('Pragma', 'no-cache');
            return $response;
        }
        else
            return Redirect::to('admin');

    }



    public function ActualizarUp()
    {
        set_time_limit(0);
        $url="http://apicantidadcliente.smartisp.us/actualizar_up.php?token_url=NhjsdbJBjj25484SC5CD5C4DC454SDSD455X8C&ts=".time()."";
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
        $response = curl_exec($ch);
        curl_close($ch);
        $array = json_decode($response, true);
        $version_api=1;
        $vesion_bd=1;
        if($array['status']=='200'){
            $version_api=$array['version'];
        }
        $Licencia = GlobalSetting::find(1);
        $vesion_bd=$Licencia->actualizar_up;
        if($version_api>$vesion_bd){
            return json_encode(['status'=>200,'newv'=>'true','version_actual'=>$vesion_bd,'version_nueva'=>$version_api,'success'=>'SmartISP '.__('app.itisnotupdated').'.']);
        }else{
            return json_encode(['status'=>200,'newv'=>'false','success'=>'SmartISP '.__('app.itupdated').'.','version_actual'=>$vesion_bd]);
        }

    }

    public function ejecutarUpdate()
    {
        $info_up=$this->ActualizarUp();
        $info_up = json_decode($info_up, TRUE);
        if($info_up['newv']=='true'){
            $cmd = exec('sh /var/www/public/actualizar_update.sh');
            $Licencia = GlobalSetting::find(1);
            $Licencia->actualizar_up = $info_up['version_nueva'];
            $Licencia->save();
        }

        $info=$this->Updatesoft();
        $info = json_decode($info, TRUE);
        if($info['newv']=='true'){
            $cmd = exec('sh /var/www/update.sh');
            $Licencia = GlobalSetting::find(1);
            $Licencia->version = $info['version_nueva'];
            $Licencia->save();
            echo json_encode(['status'=>200,'mensaje'=>'Actualizado con exito']);
        }else{
            echo json_encode(['status'=>404,'mensaje'=>'Ya esta Actualizado.']);
        }

    }


    public function reiniciar_vps()
    {
       $cmd =exec("sudo systemctl restart mysql");
       $cmd = exec('sh /var/www/reiniciar.sh');
       echo json_encode(['status'=>200,'mensaje'=>'Cache borrado con exito']);
   }



   public function Updatesoft()
   {
    set_time_limit(0);
    $url="http://apicantidadcliente.smartisp.us/update2.php?token_url=NhjsdbJBjj25484SC5CD5C4DC454SDSD455X8C&ts=".time()."";
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
    $response = curl_exec($ch);
    curl_close($ch);
    $array = json_decode($response, true);
    $version_api=2.0;
    $vesion_bd=2.0;
    if($array['status']=='200'){
        $version_api=$array['version'];
    }
    $Licencia = GlobalSetting::find(1);
    $vesion_bd=$Licencia->version;
    if($version_api>$vesion_bd){
        return json_encode(['status'=>200,'newv'=>'true','version_actual'=>$vesion_bd,'version_nueva'=>$version_api,'success'=>'SmartISP '.__('app.itisnotupdated').'.']);
    }else{
        return json_encode(['status'=>200,'newv'=>'false','success'=>'SmartISP '.__('app.itupdated').'.','version_actual'=>$vesion_bd]);
    }

}

    //metodo para obtener detalles de la licencia
public function postDetails(){

    $global = GlobalSetting::all()->first();


    if ($global->license == 'in' || $global->license_id == '0') {
            //Version developer
        $data = array('success' => true,
            'product' => 'SmartISP',
            'version' => 'Demo inicial',
            'expires' => 'Sin registrar',
            'registered' => 'Sin registrar',
            'email_register' => 'Sin registar',
            'numpc' => 1,
            'status' => 'No encontrado'
        );
        return Response::json($data);
    }elseif($global->license_id!='0'){
        $detalle_info=SecurityController::status_licencia($global->license_id);
        $stado="noactivo";
        if($detalle_info['license']=="valid"){
            $stado="ac";
        }else if($detalle_info['license']=="expired"){
            $stado="ex";
        }
        $data = array(
            'success' => true,
            'product' => $detalle_info['item_name'],
            'version' =>  'SmartISP',
            'expires' => $detalle_info['expires'],
            'registered' => $detalle_info['customer_name'],
            'email_register' => $detalle_info['customer_email'],
            'clientes_cant' => $detalle_info['cantiad_clientes_api'],
            'numpc' => 1,
            'status' => $stado
        );
        $Licencia = GlobalSetting::find(1);
        $Licencia->status = $stado;
        $Licencia->save();
        return Response::json($data);


    }else{
        $data = array('success' => false,
            'license' => 'No encontrado',
            'product' => 'No encontrado',
            'version' => 'No encontrado',
            'expires' => 'No encontrado',
            'registered' => 'No encontrado',
            'email_register' => 'No encontrado',
            'numpc' => 'No encontrado',
            'status' => 'No encontrado'
        );

        return Response::json($data);

        }//end else

    }//end method

}
