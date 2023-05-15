<?php

namespace App\Http\Controllers;
use App\Classes\Reply;
use App\Http\Requests\LanguageSettings\UpdateRequest;
use App\libraries\Files;
use App\libraries\Getip;
use App\libraries\Helpers;
use App\libraries\Image;
use App\libraries\Mikrotik;
use App\libraries\Pencrypt;
use App\libraries\RocketCore;
use App\libraries\RouterConnect;
use App\libraries\Slog;
use App\libraries\Validator;
use App\libraries\AddEmisor;
use App\libraries\AddDian_settings;
use App\libraries\AddFactel;
use App\libraries\Helper;
use App\models\AdvSetting;
use App\models\ControlRouter;
use App\models\GlobalApi;
use App\models\GlobalSetting;
use App\models\Factel;
use App\models\Emisor;
use App\models\Dian_settings;
use App\models\Language;
use App\models\Departamento;
use App\models\Typedoc;
use App\models\Typetaxpayer;
use App\models\Accountingregime;
use App\models\Typeresponsibility;
use App\models\Economicactivity;
use App\models\InvoiceSettings;
use App\models\Template;
use App\Service\CommonService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\View;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Artisan;

class ConfigController extends BaseController
{
    public function __construct()
    {
    	parent::__construct();
//        $this->beforeFilter('auth');  //bloqueo de acceso
	    $this->middleware(function ($request, $next) {
		    $this->username = auth()->user()->username;
		    $this->userId = auth()->user()->id;
		    return $next($request);
	    });
    }
     // guardar informacion del emisor
    public function postEmisor(Request $request) {
        $status_factel = $request->get('status_factel');
        if($status_factel=='0')
        {

            //guarda los datos del emisor en la facturaciòn electronica de Ecuador
            $ruc = $request->get('ruc');
            $razonSocial = $request->get('razonSocial');
            $regimenMicroempresas = $request->get('regimenMicroempresas');
            $agenteRetencion = $request->get('agenteRetencion');
            $nombreComercial = $request->get('nombreComercial');
            $direccion = $request->get('direccion');
            $status_cont = $request->get('status_cont');

            $data = array();
            $data['ruc']= $ruc;
            $data['razonSocial']=$razonSocial;
            $data['regimenMicroempresas']=$regimenMicroempresas;
            $data['agenteRetencion']=$agenteRetencion;
            $data['nombreComercial']=$nombreComercial;
            $data['direccion']=$direccion;
            $data['status_cont']=$status_cont;

            $emisor = Emisor::all()->first();
            if (empty($emisor)) {
            $emisor = new AddEmisor();
            $id = $emisor->add($data);
            }else{
                $emisor->ruc = $ruc;
                $emisor->razonSocial = $razonSocial;
                $emisor->regimenMicroempresas = $regimenMicroempresas;
                $emisor->agenteRetencion = $agenteRetencion;
                $emisor->nombreComercial = $nombreComercial;
                $emisor->direccion = $direccion;
                $emisor->status_cont = $status_cont;

                $emisor->save();
            }

            if($request->get('activate_ec') == ''){
                $status_factel = 1;
            }
        }
        else if($status_factel=='2')
        {
            //guarda los datos de tola la configuracion en la facturaciòn electronica de Colombia
            $dian_settings_typeoperation_cod =$request->get('dian_settings_typeoperation_cod');
            $dian_settings_testsetid = $request->get('dian_settings_testsetid');
            $dian_settings_tecnicalkey = $request->get('dian_settings_tecnicalkey');
            $dian_settings_softwarename = $request->get('dian_settings_softwarename');
            $dian_settings_softwareid = $request->get('dian_settings_softwareid');
            $dian_settings_softwarepin = $request->get('dian_settings_softwarepin');
            $dian_settings_resolutiondate = $request->get('dian_settings_resolutiondate');
            $dian_settings_resolutiondatestar = $request->get('dian_settings_resolutiondatestar');
            $dian_settings_resolutiondateend = $request->get('dian_settings_resolutiondateend');
            $dian_settings_resolutionnumber = $request->get('dian_settings_resolutionnumber');
            $dian_settings_prefix = $request->get('dian_settings_prefix');
            $dian_settings_numberstart = $request->get('dian_settings_numberstart');
            $dian_settings_numberend = $request->get('dian_settings_numberend');
            $dian_settings_prefixnc = $request->get('dian_settings_prefixnc');
            $dian_settings_numberstartnc = $request->get('dian_settings_numberstartnc');
            $dian_settings_numberendnc = $request->get('dian_settings_numberendnc');
            $dian_settings_prefixnd = $request->get('dian_settings_prefixnd');
            $dian_settings_numberstartnd = $request->get('dian_settings_numberstartnd');
            $dian_settings_numberendnd = $request->get('dian_settings_numberendnd');
            $dian_settings_typedoc_cod = $request->get('dian_settings_typedoc_cod');
            $dian_settings_identificacion = $request->get('dian_settings_identificacion');
            $dian_settings_businessname = $request->get('dian_settings_businessname');
            $dian_settings_tradename = $request->get('dian_settings_tradename');
            $dian_settings_typetaxpayer_cod = $request->get('dian_settings_typetaxpayer_cod');
            $dian_settings_accountingregime_cod = $request->get('dian_settings_accountingregime_cod');
            $dian_settings_typeresponsibility_cod = $request->get('dian_settings_typeresponsibility_cod');
            $dian_settings_economicactivity_cod = $request->get('dian_settings_economicactivity_cod');
            $dian_settings_municipio_cod = $request->get('dian_settings_municipio_cod');
            $dian_settings_direction = $request->get('dian_settings_direction');
            $dian_settings_email = $request->get('dian_settings_email');
            $dian_settings_phone = $request->get('dian_settings_phone');

            $data = array();
            $data['typeoperation_cod']= $dian_settings_typeoperation_cod;
            $data['testsetid']= $dian_settings_testsetid;
            $data['tecnicalkey']= $dian_settings_tecnicalkey;
            $data['softwarename']= $dian_settings_softwarename;
            $data['softwareid']= $dian_settings_softwareid;
            $data['softwarepin']= $dian_settings_softwarepin;
            $data['resolutiondate']= $dian_settings_resolutiondate;
            $data['resolutiondatestar']= $dian_settings_resolutiondatestar;
            $data['resolutiondateend']= $dian_settings_resolutiondateend;
            $data['resolutionnumber']= $dian_settings_resolutionnumber;
            $data['prefix']= $dian_settings_prefix;
            $data['numberstart']= $dian_settings_numberstart;
            $data['numberend']= $dian_settings_numberend;
            $data['prefixnc']= $dian_settings_prefixnc;
            $data['numberstartnc']= $dian_settings_numberstartnc;
            $data['numberendnc']= $dian_settings_numberendnc;
            $data['prefixnd']= $dian_settings_prefixnd;
            $data['numberstartnd']= $dian_settings_numberstartnd;
            $data['numberendnd']= $dian_settings_numberendnd;
            $data['typedoc_cod']= $dian_settings_typedoc_cod;
            $data['identificacion']= $dian_settings_identificacion;
            $data['businessname']= $dian_settings_businessname;
            $data['tradename']= $dian_settings_tradename;
            $data['typetaxpayer_cod']= $dian_settings_typetaxpayer_cod;
            $data['accountingregime_cod']= $dian_settings_accountingregime_cod;
            $data['typeresponsibility_cod']= $dian_settings_typeresponsibility_cod;
            $data['economicactivity_cod']= $dian_settings_economicactivity_cod;
            $data['municipio_cod']= $dian_settings_municipio_cod;
            $data['direction']= $dian_settings_direction;
            $data['email']= $dian_settings_email;
            $data['phone']= $dian_settings_phone;

            $dian_settings = Dian_settings::all()->first();
            if (empty($dian_settings)) {
            $dian_settings = new AddDian_settings();
            $id = $dian_settings->add($data);
            }else{
                $dian_settings->typeoperation_cod = $dian_settings_typeoperation_cod;
                $dian_settings->testsetid = $dian_settings_testsetid;
                $dian_settings->tecnicalkey = $dian_settings_tecnicalkey;
                $dian_settings->softwarename = $dian_settings_softwarename;
                $dian_settings->softwareid = $dian_settings_softwareid;
                $dian_settings->softwarepin = $dian_settings_softwarepin;
                $dian_settings->resolutiondate = $dian_settings_resolutiondate;
                $dian_settings->resolutiondatestar = $dian_settings_resolutiondatestar;
                $dian_settings->resolutiondateend = $dian_settings_resolutiondateend;
                $dian_settings->resolutionnumber = $dian_settings_resolutionnumber;
                $dian_settings->prefix = $dian_settings_prefix;
                $dian_settings->numberstart = $dian_settings_numberstart;
                $dian_settings->numberend = $dian_settings_numberend;
                $dian_settings->prefixnc = $dian_settings_prefixnc;
                $dian_settings->numberstartnc = $dian_settings_numberstartnc;
                $dian_settings->numberendnc = $dian_settings_numberendnc;
                $dian_settings->prefixnd = $dian_settings_prefixnd;
                $dian_settings->numberstartnd = $dian_settings_numberstartnd;
                $dian_settings->numberendnd = $dian_settings_numberendnd;
                $dian_settings->typedoc_cod = $dian_settings_typedoc_cod;
                $dian_settings->identificacion = $dian_settings_identificacion;
                $dian_settings->businessname = $dian_settings_businessname;
                $dian_settings->tradename = $dian_settings_tradename;
                $dian_settings->typetaxpayer_cod = $dian_settings_typetaxpayer_cod;
                $dian_settings->accountingregime_cod = $dian_settings_accountingregime_cod;
                $dian_settings->typeresponsibility_cod = $dian_settings_typeresponsibility_cod;
                $dian_settings->economicactivity_cod = $dian_settings_economicactivity_cod;
                $dian_settings->municipio_cod = $dian_settings_municipio_cod;
                $dian_settings->direction = $dian_settings_direction;
                $dian_settings->email = $dian_settings_email;
                $dian_settings->phone = $dian_settings_phone;

                $dian_settings->save();
            }

            if($request->get('activate_co') == ''){
                $status_factel = 1;
            }
        }
        // Invoice Mexico
        elseif($status_factel === '3'){
            $payload = $request->all();

            $validation = Validator::make($payload, [
                'provider' => 'required',
                'api_key' => 'required',
                'api_testkey' => 'required',
                'rfc' => 'required',
                'sat_prod_serv' => 'required',
                'sat_unit' => 'required'
            ]);
            if ($validation->fails())
                return Response::json(['msg' => 'error', 'errors' => $validation->getMessageBag()->toArray()]);

            $obj = InvoiceSettings::first();

            if($obj === null){
                $obj = InvoiceSettings::create([
                    'is_live' => isset($payload['activate_live']) ? 1 : 0,
                    'is_active' => isset($payload['activate_mx']) ? 1 : 0,
                    'apikey' => $payload['api_key'],
                    'apikey_sandbox' => $payload['api_testkey'],
                    'rfc' => $payload['rfc'],
                    'product_code' => $payload['sat_prod_serv'],
                    'unit_code' => $payload['sat_unit'],
                    'provider_name' => $payload['provider'],
                    'serie' => $payload['serie'],
                    'folio' => $payload['folio']
                ]);
            }else{

                $obj->{'is_live'} = isset($payload['activate_live']) ? 1 : 0;
                $obj->{'is_active'} = isset($payload['activate_mx']) ? 1 : 0;
                $obj->{'apikey'} = $payload['api_key'];
                $obj->{'apikey_sandbox'} = $payload['api_testkey'];
                $obj->{'rfc'} = $payload['rfc'];
                $obj->{'product_code'} = $payload['sat_prod_serv'];
                $obj->{'unit_code'} = $payload['sat_unit'];
                $obj->{'provider_name'} = $payload['provider'];
                $obj->{'serie'} = $payload['serie'];
                $obj->{'folio'} = $payload['folio'];

                $obj->save();
            }

            if(!isset($payload['activate_mx'])){
                $status_factel = 1;
            }

        }

        $data = array();

        $factel = Factel::all()->first();
        if (empty($factel)) {
            return Response::json(array('msg' => 'errorproduct'));
        } else {

            $factel->status = $status_factel;
            $factel->save();
        }
       //
       return Response::json(array('msg' => 'success'));

        //////////////// fin crear archivo /////////////////////////////
   }
   // guardar informacion de dian_settings
   public function postDian_settings(Request $request) {

        $typeoperation_cod = $request->get('typeoperation_cod');
        $softwarename = $request->get('softwarename');
        $softwareid = $request->get('softwareid');
        $softwarepin = $request->get('softwarepin');
        $tecnicalkey = $request->get('tecnicalkey');
        $testsetid = $request->get('testsetid');
        $resolutiondate = $request->get('resolutiondate');
        $resolutiondatestar = $request->get('resolutiondatestar');
        $resolutiondateend = $request->get('resolutiondateend');
        $resolutionnumber = $request->get('resolutionnumber');
        $prefix = $request->get('prefix');
        $numberstart = $request->get('numberstart');
        $numberend = $request->get('numberend');
        $prefixnc = $request->get('prefixnc');
        $numberstartnc = $request->get('numberstartnc');
        $numberendnc = $request->get('numberendnc');
        $prefixnd = $request->get('prefixnd');
        $numberstartnd = $request->get('numberstartnd');
        $numberendnd = $request->get('numberendnd');
        $fes = $request->get('fes');
        $ncs = $request->get('ncs');
        $zips = $request->get('zips');
        $typedoc_cod = $request->get('typedoc_cod');
        $identificacion = $request->get('identificacion');
        $businessname = $request->get('businessname');
        $tradename = $request->get('tradename');
        $typetaxpayer_cod = $request->get('typetaxpayer_cod');
        $accountingregime_cod = $request->get('accountingregime_cod');
        $typeresponsibility_cod = $request->get('typeresponsibility_cod');
        $economicactivity_cod = $request->get('economicactivity_cod');
        $municipio_cod = $request->get('municipio_cod');
        $direction = $request->get('direction');
        /* selects */

        $data = array();
        $data['typeoperation_cod']= $typeoperation_cod;
        $data['softwarename']= $softwarename;
        $data['softwareid']= $softwareid;
        $data['softwarepin']= $softwarepin;
        $data['tecnicalkey']= $tecnicalkey;
        $data['testsetid']= $testsetid;
        $data['resolutiondate']= $resolutiondate;
        $data['resolutiondatestar']= $resolutiondatestar;
        $data['resolutiondateend']= $resolutiondateend;
        $data['resolutionnumber']= $resolutionnumber;
        $data['prefix']= $prefix;
        $data['numberstart']= $numberstart;
        $data['numberend']= $numberend;
        $data['prefixnc']= $prefixnc;
        $data['numberstartnc']= $numberstartnc;
        $data['numberendnc']= $numberendnc;
        $data['prefixnd']= $prefixnd;
        $data['numberstartnd']= $numberstartnd;
        $data['numberendnd']= $numberendnd;
        $data['fes']= $fes;
        $data['ncs']= $ncs;
        $data['zips']= $zips;
        $data['typedoc_cod']= $typedoc_cod;
        $data['identificacion']= $identificacion;
        $data['businessname']= $businessname;
        $data['tradename']= $tradename;
        $data['typetaxpayer_cod']= $typetaxpayer_cod;
        $data['accountingregime_cod']= $accountingregime_cod;
        $data['typeresponsibility_cod']= $typeresponsibility_cod;
        $data['economicactivity_cod']= $economicactivity_cod;
        $data['municipio_cod']= $municipio_cod;
        $data['direction']= $direction;

        $dian_settings = Dian_settings::all()->first();
        if (empty($dian_settings)) {
            $dian_settings = new AddDian_settings();
            $id = $dian_settings->add($data);
        }else{
            $dian_settings->typeoperation_cod = $typeoperation_cod;
            $dian_settings->softwarename = $softwarename;
            $dian_settings->softwareid = $softwareid;
            $dian_settings->softwarepin = $softwarepin;
            $dian_settings->tecnicalkey = $tecnicalkey;
            $dian_settings->testsetid = $testsetid;
            $dian_settings->resolutiondate = $resolutiondate;
            $dian_settings->resolutiondatestar = $resolutiondatestar;
            $dian_settings->resolutiondateend = $resolutiondateend;
            $dian_settings->resolutionnumber = $resolutionnumber;
            $dian_settings->prefix = $prefix;
            $dian_settings->numberstart = $numberstart;
            $dian_settings->numberend = $numberend;
            $dian_settings->prefixnc = $prefixnc;
            $dian_settings->numberstartnc = $numberstartnc;
            $dian_settings->numberendnc = $numberendnc;
            $dian_settings->prefixnd = $prefixnd;
            $dian_settings->numberstartnd = $numberstartnd;
            $dian_settings->numberendnd = $numberendnd;
            $dian_settings->fes = $fes;
            $dian_settings->ncs = $ncs;
            $dian_settings->zips = $zips;
            $dian_settings->typedoc_cod = $typedoc_cod;
            $dian_settings->identificacion = $identificacion;
            $dian_settings->businessname = $businessname;
            $dian_settings->tradename = $tradename;
            $dian_settings->typetaxpayer_cod = $typetaxpayer_cod;
            $dian_settings->accountingregime_cod = $accountingregime_cod;
            $dian_settings->typeresponsibility_cod = $typeresponsibility_cod;
            $dian_settings->economicactivity_cod = $economicactivity_cod;
            $dian_settings->municipio_cod = $municipio_cod;
            $dian_settings->direction = $direction;

            $dian_settings->save();
        }
        return Response::json(array('msg' => 'success'));

        //////////////// fin crear archivo /////////////////////////////
    }
   public function getIndex()
   {

    $id = Auth::user()->id;
    $level = Auth::user()->level;
    $perm = DB::table('permissions')->where('user_id', '=', $id)->get();
    $access = $perm[0]->access_system;
        //control permissions only access super administrator (sa)
    if ($level == 'ad' || $access == true) {

        $global = GlobalSetting::first();
        $GoogleMaps = Helpers::get_api_options('googlemaps');

        if (count($GoogleMaps) > 0 || $global->map_type == 'open_street_map') {
            $key = $GoogleMaps['k'] ?? 1;
        } else {
            $key = 0;
        }
        $factel = Factel::all()->first();
        $status_factel = 1;
        if (!empty($factel)) {
            $status_factel=$factel->status;
        }
        $emisor = Emisor::all()->first();

        $dian_settings = DB::table('dian_settings')
        ->Join('municipio', 'municipio.cod', '=', 'dian_settings.municipio_cod')
        ->where('dian_settings.id', '=', '1')
        ->select('municipio.departamento_cod','dian_settings.id', 'dian_settings.typeoperation_cod', 'dian_settings.softwarename', 'dian_settings.softwareid', 'dian_settings.softwarepin', 'dian_settings.tecnicalkey', 'dian_settings.testsetid', 'dian_settings.resolutiondate', 'dian_settings.resolutiondatestar', 'dian_settings.resolutiondateend', 'dian_settings.resolutionnumber', 'dian_settings.prefix', 'dian_settings.numberstart', 'dian_settings.numberend', 'dian_settings.prefixnc', 'dian_settings.numberstartnc', 'dian_settings.numberendnc', 'dian_settings.prefixnd', 'dian_settings.numberstartnd', 'dian_settings.numberendnd', 'dian_settings.fes', 'dian_settings.ncs', 'dian_settings.nds', 'dian_settings.zips', 'dian_settings.year', 'dian_settings.typedoc_cod', 'dian_settings.identificacion', 'dian_settings.businessname', 'dian_settings.tradename', 'dian_settings.typetaxpayer_cod', 'dian_settings.accountingregime_cod', 'dian_settings.typeresponsibility_cod', 'dian_settings.economicactivity_cod', 'dian_settings.municipio_cod', 'dian_settings.direction','dian_settings.email','dian_settings.phone', 'dian_settings.updated_at')->get()->first();
        /* llenamos los selects */
        $municipio = DB::table('municipio')
        ->Join('departamento', 'departamento.cod', '=', 'municipio.departamento_cod')
        ->select('municipio.Description AS Municipio','departamento.Description AS Departamento','municipio.cod')->get();
        $departamento = Departamento::all();
        $typedoc = Typedoc::all();
        $typetaxpayer = Typetaxpayer::all();
        $accountingregime = Accountingregime::all();
        $typeresponsibility = Typeresponsibility::all();
        $economicactivity = Economicactivity::all();

		$twilioo = GlobalApi::where('name', '=', 'twiliosms')->first();
		$twarr = array();
		if(isset($twilioo->id)){
			$twarr['id'] =$twilioo->id;
			$twarr['name'] =$twilioo->name;
			$toptions=json_decode($twilioo->options, true);
			if(!isset($toptions['n'])){
				$toptions['n']='';
			}
			$twarr['options'] =$toptions;
		}
		else{
			$twarr['id'] ='';
			$twarr['name'] ='';
			$twarr['options'] =array('t'=>'','d'=>'','e'=>'','n'=>'');
		}

		$twiliowsms = GlobalApi::where('name', '=', 'twiliowhatsappsms')->first();
		$twsmsarr = array();
		if(isset($twiliowsms->id)){
			$twsmsarr['id'] =$twiliowsms->id;
			$twsmsarr['name'] =$twiliowsms->name;
			$twoptions=json_decode($twiliowsms->options, true);
			if(!isset($twoptions['n'])){
				$twoptions['n']='';
			}
			$twsmsarr['options'] =$twoptions;
		}
		else{
			$twsmsarr['id'] ='';
			$twsmsarr['name'] ='';
			$twsmsarr['options'] =array('t'=>'','d'=>'','e'=>'','n'=>'');
		}

		$whatsappcloudapi = GlobalApi::where('name', '=', 'whatsappcloudapi')->first();
        $whatsappcloudapi = !empty($whatsappcloudapi->options) ? json_decode($whatsappcloudapi->options) : [];
        
		$weboxapp = GlobalApi::where('name', '=', 'weboxapp')->first();
		$weboxapparr = array();
		if(isset($weboxapp->id)){
			$weboxapparr['id'] =$weboxapp->id;
			$weboxapparr['name'] =$weboxapp->name;
			$toptions=json_decode($weboxapp->options, true);
			if(!isset($toptions['n'])){
										$toptions['n']='';
									}
			$weboxapparr['options'] =$toptions;
		}
		else{
			$weboxapparr['id'] ='';
			$weboxapparr['name'] ='';
			$weboxapparr['options'] =array('t'=>'','d'=>'','e'=>'','n'=>'');
		}

        $languages = Language::where('status', 'enabled')->get();
        $allLanguages = Language::all();
        $invoiceMxSetting = InvoiceSettings::all()->first();
        $facturaTemplates = \App\models\Template::where('type', 'invoice')->get();
        $permissions = array("clients" => $perm[0]->access_clients, "plans" => $perm[0]->access_plans, "routers" => $perm[0]->access_routers,
            "users" => $perm[0]->access_users, "system" => $perm[0]->access_system, "bill" => $perm[0]->access_pays,
            "template" => $perm[0]->access_templates, "ticket" => $perm[0]->access_tickets, "sms" => $perm[0]->access_sms,
            "reports" => $perm[0]->access_reports,
            "v" => $global->version, "st" => $global->status, "map" => $key,
            "lv" => $global->license, "company" => $global->company,
            "stripe_key" => $global->stripe_key, "stripe_secret" => $global->stripe_secret,
            "paypal_client_id" => $global->paypal_client_id, "paypal_secret" => $global->paypal_secret, "paypal_mode" => $global->paypal_mode,
            "payu_merchant_id" => $global->payu_merchant_id, "payu_account_id" => $global->payu_account_id, "payu_api_key" => $global->payu_api_key, "payu_mode" => $global->payu_mode,"email_f" => $global->email_f,
            "emisor_rut" => (!empty($emisor))?$emisor->ruc:'',
            "emisor_razonSocial" => (!empty($emisor))?$emisor->razonSocial:'',
            "regimenMicroempresas" => (!empty($emisor))?$emisor->regimenMicroempresas:'',
            "agenteRetencion" => (!empty($emisor))?$emisor->agenteRetencion:'',
            "emisor_nombreComercial"=>(!empty($emisor))?$emisor->nombreComercial:'',
            "emisor_direccion"=>(!empty($emisor))?$emisor->direccion:'',
            "status_cont"=>(!empty($emisor))?$emisor->status_cont:'',
            "dian_settings_typeoperation_cod"=>(!empty($dian_settings))?$dian_settings->typeoperation_cod:'',
            "dian_settings_softwarename"=>(!empty($dian_settings))?$dian_settings->softwarename:'',
            "dian_settings_softwareid"=>(!empty($dian_settings))?$dian_settings->softwareid:'',
            "dian_settings_softwarepin"=>(!empty($dian_settings))?$dian_settings->softwarepin:'',
            "dian_settings_tecnicalkey"=>(!empty($dian_settings))?$dian_settings->tecnicalkey:'',
            "dian_settings_testsetid"=>(!empty($dian_settings))?$dian_settings->testsetid:'',
            "dian_settings_resolutiondate"=>(!empty($dian_settings))?$dian_settings->resolutiondate:'',
            "dian_settings_resolutiondatestar"=>(!empty($dian_settings))?$dian_settings->resolutiondatestar:'',
            "dian_settings_resolutiondateend"=>(!empty($dian_settings))?$dian_settings->resolutiondateend:'',
            "dian_settings_resolutionnumber"=>(!empty($dian_settings))?$dian_settings->resolutionnumber:'',
            "dian_settings_prefix"=>(!empty($dian_settings))?$dian_settings->prefix:'',
            "dian_settings_numberstart"=>(!empty($dian_settings))?$dian_settings->numberstart:'',
            "dian_settings_numberend"=>(!empty($dian_settings))?$dian_settings->numberend:'',
            "dian_settings_prefixnc"=>(!empty($dian_settings))?$dian_settings->prefixnc:'',
            "dian_settings_numberstartnc"=>(!empty($dian_settings))?$dian_settings->numberstartnc:'',
            "dian_settings_numberendnc"=>(!empty($dian_settings))?$dian_settings->numberendnc:'',
            "dian_settings_prefixnd"=>(!empty($dian_settings))?$dian_settings->prefixnd:'',
            "dian_settings_numberstartnd"=>(!empty($dian_settings))?$dian_settings->numberstartnd:'',
            "dian_settings_numberendnd"=>(!empty($dian_settings))?$dian_settings->numberendnd:'',
            "dian_settings_fes"=>(!empty($dian_settings))?$dian_settings->fes:'',
            "dian_settings_ncs"=>(!empty($dian_settings))?$dian_settings->ncs:'',
            "dian_settings_zips"=>(!empty($dian_settings))?$dian_settings->zips:'',
            "dian_settings_typedoc_cod"=>(!empty($dian_settings))?$dian_settings->typedoc_cod:'',
            "dian_settings_identificacion"=>(!empty($dian_settings))?$dian_settings->identificacion:'',
            "dian_settings_businessname"=>(!empty($dian_settings))?$dian_settings->businessname:'',
            "dian_settings_tradename"=>(!empty($dian_settings))?$dian_settings->tradename:'',
            "dian_settings_typetaxpayer_cod"=>(!empty($dian_settings))?$dian_settings->typetaxpayer_cod:'',
            "dian_settings_accountingregime_cod"=>(!empty($dian_settings))?$dian_settings->accountingregime_cod:'',
            "dian_settings_typeresponsibility_cod"=>(!empty($dian_settings))?$dian_settings->typeresponsibility_cod:'',
            "dian_settings_economicactivity_cod"=>(!empty($dian_settings))?$dian_settings->economicactivity_cod:'',
            "dian_settings_municipio_cod"=>(!empty($dian_settings))?$dian_settings->municipio_cod:'',
            "dian_settings_departamento_cod"=>(!empty($dian_settings))?$dian_settings->departamento_cod:'',
            "dian_settings_direction"=>(!empty($dian_settings))?$dian_settings->direction:'',
            "dian_settings_email"=>(!empty($dian_settings))?$dian_settings->email:'',
            "dian_settings_phone"=>(!empty($dian_settings))?$dian_settings->phone:'',
            "cmbmunicipio"=>(!empty($municipio))?$municipio:'',
            "cmbdepartamento"=>(!empty($departamento))?$departamento:'',
            "cmbtypetaxpayer"=>(!empty($typetaxpayer))?$typetaxpayer:'',
            "cmbtypedoc"=>(!empty($typedoc))?$typedoc:'',
            "cmbaccountingregime"=>(!empty($accountingregime))?$accountingregime:'',
            "cmbtyperesponsibility"=>(!empty($typeresponsibility))?$typeresponsibility:'',
            "cmbeconomicactivity"=>(!empty($economicactivity))?$economicactivity:'',
            "status_factel"=>$status_factel,"languages"=>$languages,"global"=>$global,"allLanguages"=>$allLanguages,"facturaTemplates"=>$facturaTemplates,'twsms'=>$twarr,'twsmsarr'=>$twsmsarr,'weboxapp'=>$weboxapparr,
            "whatsappcloudapi" => $whatsappcloudapi,
            'permissions' => $perm->first(),
            'directo_pago_api_key' => $global->directo_pago_api_key,
            'directo_pago_secret_key' => $global->directo_pago_secret_key,
            'directo_pago_mode' => $global->directo_pago_mode,
            'directo_pago_status' => $global->directo_pago_status,
            'pay_valida_fixed_hash' => $global->pay_valida_fixed_hash,
            'pay_valida_fixed_hash_notification' => $global->pay_valida_fixed_hash_notification,
            'pay_valida_merchant_id' => $global->pay_valida_merchant_id,
            'pay_valida_mode' => $global->pay_valida_mode,
            'pay_valida_status' => $global->pay_valida_status,
            'payu_status' => $global->payu_status,
            'paypal_status' => $global->paypal_status,
            'stripe_status' => $global->stripe_status,
            'street' => $global->street,
            'state' => $global->state,
            'country' => $global->country,
            'status_ven' => $global->activate_ven,

        );

        if (Auth::user()->level == 'ad')
            @setcookie("hcmd", 'kR2RsakY98pHL', time() + 7200, "/", "", 0, true);

        $contents = View::make('config.index', $permissions);
        $response = Response::make($contents, 200);
        $response->header('Expires', 'Tue, 1 Jan 1980 00:00:00 GMT');
        $response->header('Cache-Control', 'no-store, no-cache, must-revalidate, post-check=0, pre-check=0');
        $response->header('Pragma', 'no-cache');
        return $response;
    } else
    return Redirect::to('admin');

}

public function postAdv(Request $request)
{

    $id_adv = $request->get('idadv');

        //verificamos si esta actualizando
    if (empty($id_adv)) {
            //añadimos
        $adv = new AdvSetting;

        $friendly_names = array(
            'ip' => 'URL '.__('app.server'),
            'path' => __('app.directory')
        );

        $rules = array(
            'ip' => 'required',
            'path' => 'required'
        );

        $validation = Validator::make($request->all(), $rules);
        $validation->setAttributeNames($friendly_names);

        if ($validation->fails())
            return Response::json(['msg' => 'error', 'errors' => $validation->getMessageBag()->toArray()]);

        $adv->routers_adv = 1;
        $adv->ip_server = $request->get('ip');
        $adv->server_path = $request->get('path');
        $adv->save();
            //save log
	    CommonService::log("Se ha actualizado la configuración del sistema: (routers)", $this->username, 'change', $this->userId );

        return Response::json(array('msg' => 'success'));
    } else {

            //actualizamos
        $adv = AdvSetting::find($id_adv);

        $friendly_names = array(
            'ip' => 'URL '.__('app.server'),
            'path' => __('app.directory')
        );

        $rules = array(
            'ip' => 'required',
            'path' => 'required'
        );

        $validation = Validator::make($request->all(), $rules);
        $validation->setAttributeNames($friendly_names);

        if ($validation->fails())
            return Response::json(['msg' => 'error', 'errors' => $validation->getMessageBag()->toArray()]);

        $adv->routers_adv = 1;
        $adv->ip_server = $request->get('ip');
        $adv->server_path = $request->get('path');

        $ipserver = $request->get('ip');
        $path = $ipserver . '/' . $request->get('path');
        $adv->save();
            // Actualización masiva en todos los clientes en routers del sistema //

            //obtenemos todos los clientes
        $clients = DB::table('clients')->get();

        if (count($clients) > 0) {


                //recuperamos todos los routers asociados a los clientes
            $dts = array();
            foreach ($clients as $fg) {
                $dts[] = $fg->router_id;
            }

            $mos = array_unique($dts);
            $routers = array_values($mos);

            $rocket = new RocketCore();

            $log = new Slog();
            $counter = array();
            $global = GlobalSetting::all()->first();
            $debug = $global->debug;

                //GET all data for API
            $conf = Helpers::get_api_options('mikrotik');

                //iteramos segun la cantidad de routers
            for ($i = 0; $i < count($routers); $i++) {

                $router = new RouterConnect();
                $con = $router->get_connect($routers[$i]);
                    //obtenemos todos los datos del cliente
                $clients = DB::table('clients')
                ->join('routers', 'routers.id', '=', 'clients.router_id')
                ->join('control_routers', 'control_routers.router_id', '=', 'clients.router_id')
                ->select('clients.name As client_name', 'clients.ip As ipclient',
                    'control_routers.adv')->where('clients.router_id', $routers[$i])->get();

                if ($con['connect'] == 0) {

                        // creamos conexion con el router
                    $API = new Mikrotik($con['port'], $conf['a'], $conf['t'], $conf['s']);
                    $API->debug = $conf['d'];
                        //verificamos si estan activos los advs
                    $type = ControlRouter::where('router_id', $routers[$i])->get();

                        if ($type[0]->adv == 1) { //itereamos los clientes

                            //establecemos la conexion
                            if ($API->connect($con['ip'], $con['login'], $con['password'])) {

                                foreach ($clients as $client) {

                                    $rocket->update_all_webproxy($API, $client->ipclient, $ipserver, $path, $client->client_name, $debug);

                                }//en foreach

                            }//

                            $API->disconnect();

                        }//end if advs

                    }//end if connect

                }//end for iterate routers

            }//end if count clients

            //save log
	    CommonService::log("Se ha actualizado la ruta de los avisos de corte: (routers)", $this->username, 'change', $this->userId );
            return Response::json(array('msg' => 'success'));

        }//end else
    }

    //metodo para actualizar configuración general
    public function postGeneral(Request $request)
    {

        $global = GlobalSetting::all()->first();

        $friendly_names = array(
            'company' => __('app.companyOrganization'),
            'smoney' => __('app.currencySymbol'),
            'money' => __('app.currency'),
            'nbill' => __('app.InvoiceNumbering'),
            'numdays' => __('app.notifyDaysBefore'),
            'tolerance' => __('app.daysAfterExpiration'),
            'hrsemail' => __('app.timeOfSendingEmails'),
            'invoice_template_id' => __('app.invoiceTemplate')

        );

        $rules = array(
            'company' => 'required',
            'smoney' => 'required',
            'money' => 'required',
            'nbill' => 'required|min:1|numeric',
            'numdays' => 'required|min:1|numeric',
            'tolerance' => 'required|min:0|numeric',
            'hrsemail' => 'required',
            'invoice_template_id' => 'required',
            'router_interval' => 'required',
            'street' => 'required|max:255',
            'state' => 'required|max:255',
            'country' => 'required|max:255',
        );

        $validation = Validator::make($request->all(), $rules);
        $validation->setAttributeNames($friendly_names);

        if ($validation->fails())
            return Response::json(['msg' => 'error', 'errors' => $validation->getMessageBag()->toArray()]);

        $global->company = $request->get('company');
        $global->company_email = $request->get('company_email');
        $global->dni = $request->get('dni');
        $global->phone = $request->get('phone');
        $global->street = $request->get('street');
        $global->state = $request->get('state');
        $global->country = $request->get('country');
        $global->smoney = $request->get('smoney');
        $global->nmoney = $request->get('money');
        $global->num_bill = $request->get('nbill');
        $global->send_prebill = $request->get('preadv');
        $global->send_presms = $request->get('presms');
		$global->send_prewhatsapp = $request->get('prewhatsapp');
		$global->send_prewaboxapp = $request->get('prewaboxapp');
        $global->send_prewhatsappcloudapi = $request->get('prewhatsappcloudapi');
        $global->before_days = $request->get('numdays');
        $global->tolerance = $request->get('tolerance');
        $global->send_hrs = $request->get('hrsemail');
        $global->backups = $request->get('backup');
        $global->create_copy = $request->get('hrsbackup');
        $global->invoice_template_id = $request->get('invoice_template_id');
        $global->router_interval = $request->get('router_interval');
        $global->save();
        //save log
	    CommonService::log("Se ha actualizado la configuración del sistema: (General)", $this->username, 'change' , $this->userId);

        return Response::json(array('msg' => 'success'));
    }
    //metodo para actualizar configuración general
    public function postSearchDisable(Request $request)
    {
        $global = GlobalSetting::first();

        $global->search_show = $request->get('search_show');
        $global->save();

        //save log
	    CommonService::log("La configuración de búsqueda cambió", $this->username, 'change', $this->userId );
        return Reply::success('Successfully saved.');
    }


    //metodo para guardar la configuracion del sistema facturacion electronica
    public function postFactel(Request $request) {
        $file = $request->file('certificado_digital');
        $pass = $request->get('pass_certificado');

        if (empty($file)) {
            return Response::json(array('msg' => 'nofile'));
        }

        $nombre = $file->getClientOriginalName();
        \Storage::disk('certificados')->put($nombre, \File::get($file));
        $data = array();
        $data['certificado_digital'] = $nombre;
        $data['pass_certificado'] = $pass;
        $data['status'] = 1;
        $factel = Factel::all()->first();
        if (empty($factel)) {
            $factel = new AddFactel();
            $id = $factel->add($data);
        } else {
            $factel->certificado_digital = $nombre;
            $factel->pass_certificado = $pass;
            $factel->status = 1;
            $factel->save();
        }



        return Response::json(array('msg' => 'success'));

        //////////////// fin crear archivo /////////////////////////////
    }

    //metodo para guardar la configuracion del sistema facturacion electronica
    public function postVenezuala(Request $request)
    {
        $global = GlobalSetting::first();
        $global->activate_ven = $request->activate_ven ? $request->activate_ven : 0;
        $global->save();

        return Reply::success("Successfully updated.");

        //////////////// fin crear archivo /////////////////////////////
    }

    //metodo para guardar la configuracion del estado del sistema facturacion electronica
    public function postFactelStatus(Request $request) {

        $status = $request->get('status');


        $data = array();

        $factel = Factel::all()->first();
        if (empty($factel)) {
            return Response::json(array('msg' => 'errorproduct'));
        } else {

            $factel->status = $status;
            $factel->save();
        }



        return Response::json(array('msg' => 'success'));

        //////////////// fin crear archivo /////////////////////////////
    }


    //metodo para subir y guardar el logo
    public function postLogo_factura(Request $request)
    {
        $file = $request->file('file');
        if (empty($file))
            return Response::json(array('msg' => 'nofile'));

        if ($request->hasFile('file')) {

            $valid_exts = array('jpg','jpeg'); // valid extensions
            $max_size = 2000 * 1024; // max file size (200kb)

            $path = public_path() . '/js/lib_firma_sri/src/services/uploads/';     // upload directory

            $file = $request->file('file');
            // get uploaded file extension
            //$ext = $file['extension'];
            $ext = $file->guessClientExtension();

            // get size
            $size = $file->getSize();
            // looking for format and size validity
            $name = $file->getClientOriginalName();
            if (in_array($ext, $valid_exts) AND $size < $max_size) {

                if ($file->move($path, 'Logo.jpg')) {
                    //redimencionamos la imagen
                    $resize = new Image();
                    $img = $path . 'Logo.jpg';
                    $resize->smart_resize_image($img, 320, 120); //use in windows chage option for linux

                    return Response::json(array('msg' => 'success'));
                } else {
                    return Response::json(array('msg' => 'errorupload'));
                }
            } else {
                return Response::json(array('msg' => 'noformat'));
            }
        } else {
            return Response::json(array('msg' => 'nofile'));
        }

    }


    //metodo para subir y guardar el logo
    public function postLogo(Request $request)
    {

        $file = $request->file('file');
        if (empty($file))
            return Response::json(array('msg' => 'nofile'));

        if ($request->hasFile('file')) {

            $valid_exts = array('png'); // valid extensions
            $max_size = 2000 * 1024; // max file size (200kb)

            $path = public_path() . '/assets/img/';     // upload directory

            $file = $request->file('file');
            // get uploaded file extension
            //$ext = $file['extension'];
            $ext = $file->guessClientExtension();
            // get size
            $size = $file->getSize();
            // looking for format and size validity
            $name = $file->getClientOriginalName();
            if (in_array($ext, $valid_exts) AND $size < $max_size) {

                if ($file->move($path, 'logo.png')) {
                    //redimencionamos la imagen
                    $resize = new Image();
                    $img = $path . 'logo.png';
                    $resize->smart_resize_image($img, 320, 120); //use in windows chage option for linux

                    return Response::json(array('msg' => 'success'));
                } else {
                    return Response::json(array('msg' => 'errorupload'));
                }
            } else {
                return Response::json(array('msg' => 'noformat'));
            }
        } else {
            return Response::json(array('msg' => 'nofile'));
        }

    }

    //metodo para guardar la configuracion del sistema genera un archivo .php
    public function postSmtp(Request $request)
    {

        $friendly_names = array(
            'server' => __('app.server'),
            'email' => 'Email',
            'pass' => __('app.password'),
            'port' => __('app.port'),
            'protocol' => __('app.protocol')
        );

        $rules = array(
            'server' => 'required',
            'email' => 'required|email',
            'pass' => 'required|min:3',
            'port' => 'required|min:1|numeric',
            'protocol' => 'required'

        );

        $validation = Validator::make($request->all(), $rules);
        $validation->setAttributeNames($friendly_names);

        if ($validation->fails())
            return Response::json(['msg' => 'error', 'errors' => $validation->getMessageBag()->toArray()]);

        ///////////////// creamos archivo ///////////////////////
        $en = new Pencrypt();

        $server = $request->get('server');
        $email = $request->get('email');
        $pass = $request->get('pass');
        $port = $request->get('port');
        $protocol = $request->get('protocol');
        $global = GlobalSetting::all()->first();
        $company = $global->company;
        $zone = $global->zone;
        $debug = $global->debug;

        $file = config_path() . '/mail.php';
        // $code = "<?php
        // \$ServidorSMTP = '$server';
        // \$userSMTP = '$email';
        // \$passwordSMTP = '$pass';
        // \$portSMTP = $port;
        // \$protocol = '$protocol';
        // \$nameMail = '$company';
        // \$zone = '$zone';
        // \$debug = $debug;
        // ";
        $code = "
        <?php
        return [
        'driver' => 'smtp',
        'host' => '$server',
        'port' =>$port,
        'from' => [
        'address' => '$email',
        'name' => '$company',
        ],
        'encryption' => '$protocol',
        'username' => '$email',
        'password' => '$pass',
        'sendmail' => '/usr/sbin/sendmail -bs',
        'markdown' => [
        'theme' => 'default',

        'paths' => [
        resource_path('views/vendor/mail'),
        ],
        ],
        'log_channel' => env('MAIL_LOG_CHANNEL'),
        ];
        ";

        $fp = fopen($file, "w") or die("Unable to open file!");
        fwrite($fp, $code);
        fclose($fp);
        chmod($file, 0644);  //changed to add the zero

        // Actualizamos en la base de datos
        $global->email = $email;
        $global->password = $pass;
        // $global->password = $en->encode(Input::get('pass'));
        $global->server = $server;
        $global->port = $port;
        $global->protocol = $protocol;
        $global->zone = $zone;
        $global->save();


        Artisan::call('config:clear');
        Artisan::call('cache:clear');

        return Response::json(array('msg' => 'success'));

        //////////////// fin crear archivo /////////////////////////////
    }

      //metodo para guardar o actualizar email tickets
    public function postEmail_f(Request $request)
    {

        $friendly_names = array(
            'email_f' => 'email_f'
        );

        $rules = array(
            'email_f' => 'required|email'
        );

        $validation = Validator::make($request->all(), $rules);
        $validation->setAttributeNames($friendly_names);

        if ($validation->fails())
            return Response::json(['msg' => 'error', 'errors' => $validation->getMessageBag()->toArray()]);

        //primero verificamos si ya configuro el email SMTP principal

        $global = GlobalSetting::all()->first();




        $global->email_f = $request->get('email_f');
        $global->save();

        return Response::json(array('msg' => 'success'));

    }

    //metodo para guardar o actualizar email tickets
    public function postEmailticket(Request $request)
    {

        $friendly_names = array(
            'email' => 'Email'
        );

        $rules = array(
            'email' => 'required|email'
        );

        $validation = Validator::make($request->all(), $rules);
        $validation->setAttributeNames($friendly_names);

        if ($validation->fails())
            return Response::json(['msg' => 'error', 'errors' => $validation->getMessageBag()->toArray()]);

        //primero verificamos si ya configuro el email SMTP principal

        $global = GlobalSetting::all()->first();


        if ($global->email == 'ejemplo@ejemplo.com' && empty($global->password)) {
            return Response::json(array('msg' => 'nosmtp'));
        }

        $global->email_tickets = $request->get('email');
        $global->save();

        return Response::json(array('msg' => 'success'));

    }

    //metodo para guardar o actualizar ubicacion por defecto google maps
    public function postDefaultmap(Request $request)
    {

        $friendly_names = array(
            'map' => __('app.coordinates')
        );

        $rules = array(
            'map' => 'required'
        );

        $validation = Validator::make($request->all(), $rules);
        $validation->setAttributeNames($friendly_names);

        if ($validation->fails())
            return Response::json(['msg' => 'error', 'errors' => $validation->getMessageBag()->toArray()]);


        $global = GlobalSetting::all()->first();


        $global->default_location = $request->get('map');
        $global->save();

        return Response::json(array('msg' => 'success'));


    }

    //metodo para actualizar zona horaria
    public function postZone(Request $request)
    {

        $friendly_names = array(
            'zone' => __('app.Timezone')

        );
        $rules = array(
            'zone' => 'required|timezone'
        );

        $validation = Validator::make($request->all(), $rules);
        $validation->setAttributeNames($friendly_names);

        if ($validation->fails())
            return Response::json(['msg' => 'error', 'errors' => $validation->getMessageBag()->toArray()]);

        $en = new Pencrypt();

        $zone = $request->get('zone');
        $global = GlobalSetting::all()->first();
        $server = $global->server;
        $email = $global->email;
        $pass = $en->decode($global->password);
        $port = $global->port;
        $protocol = $global->protocol;
        $company = $global->company;
        $debug = $global->debug;

        $file = config_path() . '/user_conf.php';
        $code = "<?php
        \$ServidorSMTP = '$server';
        \$userSMTP = '$email';
        \$passwordSMTP = '$pass';
        \$portSMTP = $port;
        \$protocol = '$protocol';
        \$nameMail = '$company';
        \$zone = '$zone';
        \$debug = $debug;
        ";
        //enabled permissions
        $fp = fopen($file, "w") or die("Unable to open file!");
        fwrite($fp, $code);
        fclose($fp);
        chmod($file, 0644);  //changed to add the zero

        $global->zone = $zone;
        $global->save();
        return Response::json(array('msg' => 'success'));

    }

    //metodo para activar el modo repuración
    public function postDebug(Request $request)
    {
        $rules = array(
            'idb' => 'required'
        );

        $validation = Validator::make($request->all(), $rules);
        if ($validation->fails())
            return Response::json(array('msg' => 'error'));

        $en = new Pencrypt();

        $debug = $request->get('idb');

        $global = GlobalSetting::all()->first();
        $server = $global->server;
        $email = $global->email;
        $pass = $en->decode($global->password);
        $port = $global->port;
        $protocol = $global->protocol;
        $company = $global->company;
        $zone = $global->zone;

        $file = config_path() . '/user_conf.php';
        $code = "<?php
        \$ServidorSMTP = '$server';
        \$userSMTP = '$email';
        \$passwordSMTP = '$pass';
        \$portSMTP = $port;
        \$protocol = '$protocol';
        \$nameMail = '$company';
        \$zone = '$zone';
        \$debug = $debug;
        ";

        $fp = fopen($file, "w") or die("Unable to open file!");
        fwrite($fp, $code);
        fclose($fp);
        chmod($file, 0644);  //changed to add the zero

        $global->debug = $debug;
        $global->save();
        return Response::json(array('msg' => 'success'));

    }

    //metodo para borrar la cache del sistema
    public function postCache()
    {

        $Cache = new Files();
        $Cache->Delete(storage_path() . '/views');
        return Response::json(array('msg' => 'success'));
    }


    //metodo para reestablecer el sistema
    public function postRessys(Request $request)
    {

        $resop = $request->get('idp');
        $rules = array(
            'idp' => 'required'

        );

        $validation = Validator::make($request->all(), $rules);
        if ($validation->fails())
            return Response::json(array('msg' => 'error'));

        $log = new Slog();

        switch ($resop) {
            case 'reset-System421':
                //reseteamos sistema
            $my_id = Auth::user()->id;
            DB::table('address_routers')->truncate();
            DB::table('adv_settings')->truncate();
                //reinsertamos a la tabla
            $ip = new Getip();
            DB::table('adv_settings')->insert(
                array('routers_adv' => '1', 'ip_server' => $ip->getServer(), 'server_path' => 'aviso')
            );
            DB::table('clients')->truncate();
            DB::table('control_routers')->truncate();
                //restauramos global config
            $global = GlobalSetting::all()->first();
            $global->company = 'SmartISP';
            $global->smoney = '$';
            $global->nmoney = 'Pesos';
            $global->num_bill = '0001';
            $global->send_prebill = 0;
            $global->email = 'ejemplo@ejemplo.com';
            $global->email_tickets = 'ejemplo2@ejemplo.com';
            $global->before_days = 1;
            $global->send_hrs = '00:00:00';
            $global->backups = 1;
            $global->create_copy = '00:00:00';
            $global->send_prebill = 0;
            $global->send_presms = 0;
            $global->tolerance = 0;
            $global->password = '';
            $global->server = 'smtp.gmail.com';
            $global->port = 587;
            $global->protocol = 'tls';
            $global->zone = 'UTC';
            $global->default_location = '0';
            $global->logo = 'none';
            $global->debug = 1;
            $global->message = 'none';
            $global->phone_code = 54;
            $global->delay_sms = 2;
            $global->save();

            DB::table('global_apis')->truncate();
            DB::table('global_apis')->insert(
                array('name' => 'mikrotik', 'status' => 1, 'options' => '{"a":"5","t":"3","d":false,"s":false}')
            );

            DB::table('logs')->truncate();
            DB::table('payments')->truncate();
            DB::table('notices')->truncate();
            DB::table('temp_advs')->truncate();
            DB::table('payment_records')->truncate();
            DB::table('tickets')->truncate();
            DB::table('answers')->truncate();
            DB::table('address_routers')->truncate();
            DB::table('networks')->truncate();
            DB::table('bill_customers')->truncate();
            DB::table('boxes')->truncate();
            $ilv = User::where('level', '=', 'ad')->get();
            DB::table('permissions')->where('id', '<>', $ilv[0]->id)->delete();
            DB::table('plans')->truncate();
            DB::table('sms')->truncate();
            DB::table('sms_inbox')->truncate();
            DB::table('temp_sms')->truncate();
            DB::table('routers')->truncate();
            DB::table('queued_processes')->truncate();
            DB::table('suspend_clients')->truncate();
            DB::table('users')->where('level', '<>', 'ad')->where('id', '<>', $my_id)->delete();
                //actualizamos el usuairo
            $ilv[0]->username = 'admin';
            $ilv[0]->name = 'default';
            $ilv[0]->email = 'default@example.com';
            $ilv[0]->phone = 0;
            $ilv[0]->photo = 'none';
            $ilv[0]->password = Hash::make('123');
            $ilv[0]->save();
                //reseteamos el archivo user_conf

            $file = config_path() . '/user_conf.php';
            $code = "<?php
            \$ServidorSMTP = 'smtp.gmail.com';
            \$userSMTP = null;
            \$passwordSMTP = null;
            \$portSMTP = 587;
            \$protocol = 'tls';
            \$nameMail = 'SmartISP';
            \$zone = 'UTC';
            \$debug = 0;
            ";

            $fp = fopen($file, "w") or die("Unable to open file!");
            fwrite($fp, $code);
            fclose($fp);
                chmod($file, 0644);  //changed to add the zero

	            CommonService::log("Reseteo general del sistema", $this->username, 'danger', $this->userId );
                return Response::json(array('msg' => 'success'));

                break;
                case 'reset-System325':
                //reseteamos pagos
                DB::table('payments')->delete();
	            CommonService::log("Reseteo de pagos", $this->username, 'danger', $this->userId );
                return Response::json(array('msg' => 'success'));

                break;
                case 'reset-System121':
                //reseteamos logs
                DB::table('logs')->delete();
	            CommonService::log("Reseteo de logs", $this->username, 'danger', $this->userId );
                return Response::json(array('msg' => 'success'));

                break;
            }

        }

    //metodo para guardar configuracion de la api de mikrotik
        public function postApimikrotik(Request $request)
        {

            $friendly_names = array(
                'attempts' => 'Attempts',
                'timeout' => 'Timeout'
            );

            $rules = array(
                'attempts' => 'required|numeric|integer',
                'timeout' => 'required|numeric|integer'
            );

            $validation = Validator::make($request->all(), $rules);
            $validation->setAttributeNames($friendly_names);

            if ($validation->fails())
                return Response::json(['msg' => 'error', 'errors' => $validation->getMessageBag()->toArray()]);

            $options = array(
                'a' => $request->get('attempts', 5),
                't' => $request->get('timeout', 3),
                'd' => $request->get('mkdebug', false),
                's' => $request->get('mkssl', false)
            );

            $mikrotik = GlobalApi::where('name', 'mikrotik')->get();
            $mikrotik[0]->options = json_encode($options, true);
            $mikrotik[0]->save();

            return Response::json(array('msg' => 'success'));

        }

    public function postSmartoltApi(Request $request)
    {

        if(isset($request->check_smartolt)){
            $rules = array(
                'apikey_smartolt' => 'required',
                'url_smartolt' => 'required'
            );

            $validation = Validator::make($request->all(), $rules);

            if ($validation->fails())
                return Response::json(['msg' => 'error', 'errors' => $validation->getMessageBag()->toArray()]);
        }


        $options = array(
            'l' => $request->get('url_smartolt'),
            'a' => $request->get('apikey_smartolt'),
            'c' => $request->get('check_smartolt'),
        );

        $smartolt = GlobalApi::where('name', 'smartolt')->first();

        if($smartolt){
            $smartolt->options = json_encode($options, true);
            $smartolt->save();
        }
        else{

            $smartolt_new = new GlobalApi();
            $smartolt_new->name = 'smartolt';
            $smartolt_new->options = json_encode($options, true);
            $smartolt_new->status = 1;
            $smartolt_new->save();
        }


        return Response::json(array('msg' => 'success'));

    }

    //metodo para guardar la configuracion generl sms
        public function postWhatsappsms(Request $request)
        {

            $friendly_names = array(
                'wappsid' => 'Account Sid',
                'wapptoken' => 'Auth Token',
				'wappnumber' => 'From Number'

            );

            $rules = array(
                'wappsid' => 'required',
                'wapptoken' => 'required',
				'wappnumber' => 'required'

            );

            $validation = Validator::make($request->all(), $rules);
            $validation->setAttributeNames($friendly_names);

            if ($validation->fails())
                return Response::json(['msg' => 'error', 'errors' => $validation->getMessageBag()->toArray()]);


        //recuperamos las opciones del sms gateway para actualizar

            $gateway = GlobalApi::where('name', 'twiliowhatsappsms')->get();

			$chkval = $request->get('enabledsmsg');

			if($chkval=='on'){
				$status = 1;
			}
			else{
				$status = 0;
			}


			 $sms_gateway = array(
			't' => $request->get('wappsid'),
			'd' => $request->get('wapptoken'),
			'e' => $status,
			'n' => $request->get('wappnumber'),
			);


            if (count($gateway) > 0) {
            # Se encontro el registro actualizamos
                $gateway[0]->options = json_encode($sms_gateway, true);
                $gateway[0]->save();
            } else {
            # No se encontro agregamos nuevo


                $gateway = new GlobalApi();
                $gateway->name = 'twiliowhatsappsms';
                $gateway->options = json_encode($sms_gateway, true);
                $gateway->status = 1;
                $gateway->save();
            }

            return Response::json(array('msg' => 'success'));


        }

        public function postWhatsappCloudApi(Request $request)
        {

            $friendly_names = [
                'phonenumberid' => 'Phone Number ID',
                'access_token' => 'Access Token',
                'business_account_id' => 'Business Account ID'
            ];

            $rules = [
                'phonenumberid' => 'required',
                'access_token' => 'required',
                'business_account_id' => 'required'
            ];

            $validation = Validator::make($request->all(), $rules);
            $validation->setAttributeNames($friendly_names);

            if ($validation->fails()) {
                return Response::json(['msg' => 'error', 'errors' => $validation->getMessageBag()->toArray()]);
            }

			$chkval = $request->get('enabledsmsg');

			if ($chkval == 'on') {
				$status = 1;
			} else {
				$status = 0;
			}

			$sms_gateway = [
                'phonenumberid' => $request->get('phonenumberid'),
                'access_token' => $request->get('access_token'),
                'business_account_id' => $request->get('business_account_id'),
                'status' => $status,
			];

            $gateway = GlobalApi::firstOrNew(['name' => 'whatsappcloudapi']);

            $gateway->options = json_encode($sms_gateway, true);
            $gateway->status = 1;
            $gateway->save();

            $global = GlobalSetting::first();
            $templates = Template::where(['type' => 'whatsapp', 'status' => 1])->get();

            foreach ($templates as $template) {
                $whatsapp_template = Helpers::createWhatsappTemplates($request->get('business_account_id'), $request->get('access_token'), $global->locale, $template);
                $template->provider_template_id = $whatsapp_template['id'];
                $template->provider_template_name = $whatsapp_template['name'];
                $template->save();
            }

            return Response::json(array('msg' => 'success'));
        }

		public function postGeneralsms(Request $request)
        {

            $friendly_names = array(
                'phonecode' => __('app.countryCode'),
                'delaysend' => __('app.Pausebetweenmessages')

            );

            $rules = array(
                'phonecode' => 'required',
                'delaysend' => 'required|integer'

            );

            $validation = Validator::make($request->all(), $rules);
            $validation->setAttributeNames($friendly_names);

            if ($validation->fails())
                return Response::json(['msg' => 'error', 'errors' => $validation->getMessageBag()->toArray()]);


        //guardamos
            $global = GlobalSetting::first();
            $global->phone_code = $request->get('phonecode');
            $global->delay_sms = $request->get('delaysend');
            $global->save();

            return Response::json(array('msg' => 'success'));


        }

    //metodo para guardar la configuracion de sms modem
        public function postModem(Request $request)
        {

            $friendly_names = array(
                'routersms' => 'Router',
                'portusb' => __('app.port').' USB',
                'channel' => __('app.channel').' USB'
            );

            $rules = array(
                'routersms' => 'required',
                'portusb' => 'required',
                'channel' => 'required'
            );

            $validation = Validator::make($request->all(), $rules);
            $validation->setAttributeNames($friendly_names);

            if ($validation->fails())
                return Response::json(['msg' => 'error', 'errors' => $validation->getMessageBag()->toArray()]);


        //recuperamos las opciones del modem para actualizar
            $smsgateway = GlobalApi::where('name', 'smsgateway')->get();

            $en = new Pencrypt();

            if (count($smsgateway) > 0) {
            # Se encontro el registro recuperamos y actualizamos
                $data_sms = Helpers::get_api_options('smsgateway');
                $sms_gateway = array(
                    't' => $data_sms['t'],
                    'd' => $data_sms['d'],
                    'e' => $request->get('eng', 0)
                );
                $smsgateway[0]->options = json_encode($sms_gateway, true);
                $smsgateway[0]->save();

            } else {
            # No se encontro agregamos nuevo
                $smsgateway = new GlobalApi();
                $smsgateway->name = 'smsgateway';
                $smsgateway->options = json_encode(['t' => 'none', 'd' => '0', 'e' => '0'], true);
                $smsgateway->status = 1;
                $smsgateway->save();
            }


            $options = array(
                'r' => $request->get('routersms'),
                'p' => $request->get('portusb'),
                'c' => $request->get('channel'),
                'e' => $request->get('enabledmodem', 0)
            );

        //buscamos el registro

            $modem = GlobalApi::where('name', 'modem')->get();
            if (count($modem) > 0) {
            # Se encontro el registro actualizamos
                $modem[0]->options = json_encode($options, true);
                $modem[0]->save();

            } else {
            # No se encontro agregamos nuevo
                $modem = new GlobalApi();
                $modem->name = 'modem';
                $modem->options = json_encode($options, true);
                $modem->status = 1;
                $modem->save();
            }

        //seteamos los parametros en el router
        //get connection data for login ruter
            $router = new RouterConnect();
            $con = $router->get_connect($request->get('routersms'));
            $conf = Helpers::get_api_options('mikrotik');
            $API = new Mikrotik($con['port'], $conf['a'], $conf['t'], $conf['s']);
            $API->debug = $conf['d'];

            if ($API->connect($con['ip'], $con['login'], $con['password'])) {

                Psms::set_channel($API, $request->get('portusb'), $request->get('channel'), $request->get('enabledmodem', 0));
            } else
            return Response::json(array('msg' => 'errorConnect'));


            return Response::json(array('msg' => 'success'));
        }

    //metodo para guardar la configuracion de sms gateway
        public function postSmsgateway(Request $request)
        {

            $friendly_names = array(
                'token' => 'Account Sid',
                'deviceid' => 'Auth Token',
				'twinumber' => 'From Number'
            );

            $rules = array(
                'token' => 'required',
                'deviceid' => 'required',
				'twinumber' => 'required'
            );

            $validation = Validator::make($request->all(), $rules);
            $validation->setAttributeNames($friendly_names);

            if ($validation->fails())
                return Response::json(['msg' => 'error', 'errors' => $validation->getMessageBag()->toArray()]);

        //recuperamos las opciones del sms gateway para actualizar
            $gateway = GlobalApi::where('name', 'twiliosms')->get();

			$chkval = $request->get('enabledsmsg');

			if($chkval=='on'){
				$status = 1;
			}
			else{
				$status = 0;
			}


            $sms_gateway = array(
                't' => $request->get('token'),
                'd' => $request->get('deviceid'),
                'e' => $status,
				'n' => $request->get('twinumber')
            );

            if (count($gateway) > 0) {
            # Se encontro el registro actualizamos
                $gateway[0]->options = json_encode($sms_gateway, true);
                $gateway[0]->save();
            } else {
            # No se encontro agregamos nuevo
                $gateway = new GlobalApi();
                $gateway->name = 'twiliosms';
                $gateway->options = json_encode($sms_gateway, true);
                $gateway->status = 1;
                $gateway->save();
            }

            return Response::json(array('msg' => 'success'));

        }

    //metodo para guardar la configuracion de google maps api
        public function postApimaps(Request $request)
        {

            $friendly_names = array(

                'googlemapsapi' => 'Clave API'
            );

            $rules = array(
                'map_type' => 'required|in:google_map,open_street_map',
                'googlemapsapi' => 'required_if:map_type,google_map',
            );

            $validation = Validator::make($request->all(), $rules);
            $validation->setAttributeNames($friendly_names);

            if ($validation->fails())
                return Response::json(['msg' => 'error', 'errors' => $validation->getMessageBag()->toArray()]);

            // save map type
            $global = GlobalSetting::first();
            $global->map_type = $request->map_type;

            $global->save();

            //recuperamos las opciones del modem para actualizar
            $maps = GlobalApi::where('name', 'googlemaps')->get();

            if (count($maps) > 0) {

                $maps_api = array(
                    'k' => $request->get('googlemapsapi', 0)
                );
                $maps[0]->options = json_encode($maps_api, true);
                $maps[0]->save();

            } else {
            # No se encontro agregamos nuevo
                $maps = new GlobalApi();
                $maps->name = 'googlemaps';
                $maps->options = json_encode(['k' => $request->get('googlemapsapi', 0)], true);
                $maps->status = 1;
                $maps->save();
            }

            return Response::json(array('msg' => 'success'));


        }

        public function postApistreet(Request $request)
        {

            $friendly_names = array(

                'googlstreetviewapi' => 'Clave API'
            );

            $rules = array(

                'googlstreetviewapi' => 'required'
            );

            $validation = Validator::make($request->all(), $rules);
            $validation->setAttributeNames($friendly_names);

            if ($validation->fails())
                return Response::json(['msg' => 'error', 'errors' => $validation->getMessageBag()->toArray()]);


        //recuperamos las opciones de googlestreetview para actualizar
            $street = GlobalApi::where('name', 'googlestreetview')->get();

            if (count($street) > 0) {

                $street_api = array(
                    'k' => $request->get('googlstreetviewapi', 0)
                );
                $street[0]->options = json_encode($street_api, true);
                $street[0]->save();

            } else {
            # No se encontro agregamos nuevo
                $street = new GlobalApi();
                $street->name = 'googlestreetview';
                $street->options = json_encode(['k' => $request->get('googlstreetviewapi', 0)], true);
                $street->status = 1;
                $street->save();
            }

            return Response::json(array('msg' => 'success'));


        }

        public function postStripeGateway(Request $request)
        {
        // validate data
            $validator = Validator::make($request->all(), [
                'stripe_key' => 'required',
                'stripe_secret' => 'required'
            ]);

            if ($validator->fails())
                return Response::json(['msg' => 'error', 'errors' => $validator->getMessageBag()->toArray()]);

            // save stripe details
            $global = GlobalSetting::all()->first();
            $global->stripe_key = $request->stripe_key;
            $global->stripe_secret = $request->stripe_secret;

            if($request->has('stripe_status')) {
                $global->stripe_status = $request->stripe_status;
            } else {
                $global->stripe_status = 0;
            }

            $global->save();

            return Response::json(array('msg' => 'success'));
        }

        public function postPaypalGateway(Request $request)
        {
        // validate data
            $validator = Validator::make($request->all(), [
                'paypal_client_id' => 'required',
                'paypal_secret' => 'required',
                'paypal_mode' => [
                    'required',
                    Rule::in(['sandbox', 'live'])
                ]
            ]);

            if ($validator->fails())
                return Response::json(['msg' => 'error', 'errors' => $validator->getMessageBag()->toArray()]);

            // save stripe details
            $global = GlobalSetting::all()->first();
            $global->paypal_client_id = $request->paypal_client_id;
            $global->paypal_secret = $request->paypal_secret;
            $global->paypal_mode = $request->paypal_mode;

            if($request->has('paypal_status')) {
                $global->paypal_status = $request->paypal_status;
            } else {
                $global->paypal_status = 0;
            }

            $global->save();

            return Response::json(array('msg' => 'success'));
        }

        public function postPayuGateway(Request $request)
        {
        // validate data
            $validator = Validator::make($request->all(), [
                'payu_merchant_id' => 'required',
                'payu_account_id' => 'required',
                'payu_api_key' => 'required',
                'payu_mode' => [
                    'required',
                    Rule::in(['sandbox', 'live'])
                ]
            ]);

            if ($validator->fails())
                return Response::json(['msg' => 'error', 'errors' => $validator->getMessageBag()->toArray()]);

            // save stripe details
            $global = GlobalSetting::all()->first();
            $global->payu_merchant_id = $request->payu_merchant_id;
            $global->payu_account_id = $request->payu_account_id;
            $global->payu_api_key = $request->payu_api_key;
            $global->payu_mode = $request->payu_mode;

            if($request->has('payu_status')) {
                $global->payu_status = $request->payu_status;
            } else {
                $global->payu_status = 0;
            }

            $global->save();

            return Response::json(array('msg' => 'success'));
        }

        public function postDirectoPago(Request $request)
        {
        // validate data
            $validator = Validator::make($request->all(), [
                'directo_pago_api_key' => 'required',
                'directo_pago_secret_key' => 'required',
                'directo_pago_mode' => [
                    'required',
                    Rule::in(['sandbox', 'live'])
                ]
            ]);

            if ($validator->fails())
                return Response::json(['msg' => 'error', 'errors' => $validator->getMessageBag()->toArray()]);

        // save stripe details
            $global = GlobalSetting::all()->first();
            $global->directo_pago_api_key = $request->directo_pago_api_key;
            $global->directo_pago_secret_key = $request->directo_pago_secret_key;
            $global->payu_api_key = $request->payu_api_key;
            $global->directo_pago_mode = $request->directo_pago_mode;

            if($request->has('directo_pago_status')) {
                $global->directo_pago_status = $request->directo_pago_status;
            } else {
                $global->directo_pago_status = 0;
            }

            $global->save();

            return Response::json(array('msg' => 'success'));
        }

        public function postPayValida(Request $request)
        {
        // validate data
            $validator = Validator::make($request->all(), [
                'pay_valida_fixed_hash' => 'required',
                'pay_valida_merchant_id' => 'required',
                'pay_valida_mode' => [
                    'required',
                    Rule::in(['sandbox', 'live'])
                ]
            ]);

            if ($validator->fails())
                return Response::json(['msg' => 'error', 'errors' => $validator->getMessageBag()->toArray()]);

        // save stripe details
            $global = GlobalSetting::all()->first();
            $global->pay_valida_fixed_hash = $request->pay_valida_fixed_hash;
            $global->pay_valida_fixed_hash_notification = $request->pay_valida_fixed_hash_notification;
            $global->pay_valida_merchant_id = $request->pay_valida_merchant_id;
            $global->pay_valida_mode = $request->pay_valida_mode;

            if($request->has('pay_valida_status')) {
                $global->pay_valida_status = $request->pay_valida_status;
            } else {
                $global->pay_valida_status = 0;
            }

            $global->save();

            return Response::json(array('msg' => 'success'));
        }

        public function postSaveLocale(Request $request)
        {
            // validate data
            $validator = Validator::make($request->all(), [
                'language' => 'required',
            ]);

            if ($validator->fails())
                return Response::json(['msg' => 'error', 'errors' => $validator->getMessageBag()->toArray()]);

            // save locale
            $global = GlobalSetting::first();
            $global->locale = $request->language;

            $global->save();

            return Response::json(array('msg' => 'success'));
        }

        public function postMapType(Request $request)
        {
            // validate data
            $validator = Validator::make($request->all(), [
                'map_type' => 'required|in:google_map,open_street_map',
            ]);

            if ($validator->fails())
                return Response::json(['msg' => 'error', 'errors' => $validator->getMessageBag()->toArray()]);

            // save locale
            $global = GlobalSetting::first();
            $global->map_type = $request->map_type;

            $global->save();

            return Response::json(array('msg' => 'success'));
        }
        
        public function postLanguageSetting(UpdateRequest $request)
        {
            Language::query()->update([
                'status' => 'disabled'
            ]);

            if($request->get('language_code'))
            {
                foreach ($request->get('language_code') as $key => $item) {
                    $language = Language::where('language_code', $key)->first();
                    $language->status = 'enabled';
                    $language->save();
                }
            }

            return Response::json(array('msg' => 'success'));
        }

		public function postWebox(Request $request){

			$friendly_names = array(
									'smsweboxtokenid' => 'Token',
									'weboxuid' => 'uid',

							);
			$rules = array(
							'smsweboxtokenid' => 'required',
							'weboxuid' => 'required',

					);
			$validation = Validator::make($request->all(), $rules);
			$validation->setAttributeNames($friendly_names);
			if ($validation->fails())
				return Response::json(['msg' => 'error', 'errors' => $validation->getMessageBag()->toArray()]);

			$gateway = GlobalApi::where('name', 'weboxapp')->get();
			$chkval = $request->get('enabledsmsg');
			if($chkval=='on'){
				$status = 1;
			}
			else{
				$status = 0;
			}
			$webox_gateway = array(
									't' => $request->get('smsweboxtokenid'),
									'd' => $request->get('weboxuid'),
									'e' => $status,
									);
			if (count($gateway) > 0) {
				$gateway[0]->options = json_encode($webox_gateway, true);
				$gateway[0]->save();
			}
			else {
				$gateway = new GlobalApi();
				$gateway->name = 'weboxapp';
				$gateway->options = json_encode($webox_gateway, true);
				$gateway->status = 1;
				$gateway->save();
			}
			return Response::json(array('msg' => 'success'));
		}

    }
