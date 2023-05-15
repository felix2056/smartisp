<?php

namespace App\Http\Controllers;
use App\Imports\ClientImport;
use App\libraries\CountClient;
use App\libraries\Helpers;
use App\libraries\Hotspot;
use App\libraries\Mikrotik;
use App\libraries\Mkconvert;
use App\libraries\Ppp;
use App\libraries\RouterConnect;
use App\libraries\Slog;
use App\libraries\Validator;
use App\models\ControlRouter;
use App\models\GlobalSetting;
use App\models\Plan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Maatwebsite\Excel\Facades\Excel;

class ImportToolController extends BaseController
{
    public function __construct()
    {
//        $this->beforeFilter('auth'); //bloqueo de acceso

    }


    //metodo para importar
    public function postImport(Request $request)
    {
        $friendly_names = array('router' => 'Router', 'control' => __('app.import'), 'date_pay' => __('app.paymentDate'));
        //validamos reglas inputs
        $rules = array('router' => 'required|numeric',
            'control' => 'required',
            'plan_id' => 'required|exists:plans,id'
        );

        $validation = Validator::make($request->all(), $rules);
        $validation->setAttributeNames($friendly_names);
        if ($validation->fails()) return json_encode(['msg' => 'error', 'errors' => $validation->getMessageBag()->toArray()]);

        $global = GlobalSetting::all()->first();

        if ($global->license_id == '0') {
            return json_encode(array('msg' => 'PleaseActivateALicense'));
        } else {
            $data_licencia = SecurityController::status_licencia($global->license_id);
            if ($data_licencia['status'] == 200) {
                if ($data_licencia['license'] == 'valid') {
                    if (!$data_licencia['status_reg_cli']) {
                        return json_encode(array('msg' => 'allowedByYourLicense'));
                    }

                } else {
                    return json_encode(array('msg' => 'Expiredlicense'));
                }
            } else {
                return json_encode(array('msg' => 'LicenseError'));
            }
        }
         Excel::import(new ClientImport($request->all()), $request->file);

        return json_encode(array('msg' => 'success'));
    }


    //metodo para guardar perfil pppoe o hotspot y convertirlo en plan
    public function postProfile(Request $request)
    {
        $rules = array(
            'routerid' => 'required',
            'profileslistbox' => 'required'
        );

        $validation = Validator::make($request->all(), $rules);

        if ($validation->fails())
            return Response::json(['msg' => 'error', 'errors' => $validation->getMessageBag()->toArray()]);


        $control = ControlRouter::where('router_id', '=', $request->get('routerid'))->get();
        if (count(json_decode($control, 1)) == 0)
            return Response::json(array('control' => 'notfound'));


        $router_id = $request->get('routerid');
        //get data for login ruter
        $router = new RouterConnect();
        $con = $router->get_connect($router_id);
        //GET all data for API
        $conf = Helpers::get_api_options('mikrotik');
        $API = new Mikrotik($con['port'], $conf['a'], $conf['t'], $conf['s']);
        $API->debug = $conf['d'];

        $sp = new Mkconvert();
        $API->debug = false;
        $counter = new CountClient();
        $log = new Slog();

        //verificamos si el router esta en linea
        if (!$API->connect($con['ip'], $con['login'], $con['password'])) {
            $API->disconnect();
            return json_encode(array('msg' => 'errorConnect'));
        }

        //controles de validacion
        switch ($control[0]->type_control) {

            case 'ho':

                $PROFILES = $request->get('profileslistbox');
                /// recuperamos los perfiles e intentamos crear los planes ///
                if ($API->connect($con['ip'], $con['login'], $con['password'])) {

                    for ($i = 0; $i < count($PROFILES); $i++) {
                        $PROFILE = Hotspot::list_profiles($API, $PROFILES[$i]);
                        //intentamos obtener las velocidades
                        $vel = explode('/', isset($PROFILE[0]['rate-limit']) ? $PROFILE[0]['rate-limit'] : '0/0');
                        //buscamos si existe el nombre del plan para evitar duplicidad
                        if (Plan::where('name', $PROFILES[$i])->count() == 0) {
                            $plan = new Plan;
                            $plan->name = $PROFILES[$i];
                            $plan->download = $vel[0];
                            $plan->upload = $vel[1];
                            $plan->num_clients = 0;
                            $plan->cost = 0;
                            $plan->iva = 0;
                            $plan->limitat = 100;
                            $plan->save();
                        }
                    }

                    $API->disconnect();

                }

                return Response::json(array('msg' => 'success'));

                break;

            case 'pp':

                $PROFILES = $request->get('profileslistbox');
                /// recuperamos los perfiles e intentamos crear los planes ///
                if ($API->connect($con['ip'], $con['login'], $con['password'])) {

                    for ($i = 0; $i < count($PROFILES); $i++) {
                        $PROFILE = Ppp::ppp_find_profile($API, $PROFILES[$i]);
                        //intentamos obtener las velocidades
                        $vel = explode('/', isset($PROFILE[0]['rate-limit']) ? $PROFILE[0]['rate-limit'] : '0/0');
                        //buscamos si existe el nombre del plan para evitar duplicidad
                        if (Plan::where('name', $PROFILES[$i])->count() == 0) {
                            $plan = new Plan();
                            $plan->name = $PROFILES[$i];
                            $plan->download = $vel[0];
                            $plan->upload = $vel[1];
                            $plan->num_clients = 0;
                            $plan->cost = 0;
                            $plan->iva = 0;
                            $plan->limitat = 100;
                            $plan->save();
                        }

                    }


                    $API->disconnect();
                }


                return Response::json(array('msg' => 'success'));

                break;

        }

    }

}
