<?php
namespace App\Http\Controllers;

use App\libraries\Pencrypt;
use App\models\GlobalSetting;
use App\models\Plan;
use App\models\SuspendClient;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\View;

class ClientLogin extends BaseController
{

    public function user(Request $request)
    {
        // get POST data
        $credentials = array(
            'username' => $request->get('user'),
            'password' => $request->get('password')
        );

        if (empty($credentials['username']) || empty($credentials['password'])) {
            return Redirect::to('/');
        }

        $Auth = DB::table('clients')
        ->select('clients.id', 'clients.name', 'clients.phone', 'clients.address', 'clients.email','clients.balance',
            'clients.dni', 'clients.password')
        ->where('clients.email', '=', $credentials['username'])
        ->orWhere('clients.dni', '=', $credentials['username'])
        ->get();

        if (count($Auth) > 0) {

            $en = new Pencrypt();

            if ($en->decode($Auth[0]->password) == $credentials['password']) {
                //valido
                Session::put('Ruser', $en->encode($credentials['username']));
                //remenver me for ever
                if (!empty($request->get('remember')) && $request->get('remember') == 1) {
                    setcookie("Ruser", $en->encode($credentials['username']), time() + 60 * 60 * 24 * 6004, "/");
                }
                //verificamos si tiene un aviso de corte
//                if (!empty($request->get('adv')) && $request->get('adv') == 1) {

                    //verificamos si esta cortado el servicio
//                    if ($Auth[0]->status == 'de') {
//                        //buscamos datos del plan
//                        $plan = Plan::find($Auth[0]->plan_id);
//                        //buscamos cuando expiro su pago
//                        $exp = SuspendClient::where('client_id', '=', $Auth[0]->id)->get();
//                        //buscamos datos del sistema
//                        $global = GlobalSetting::all()->first();
//                        //comvertimos la fecha
//                        $timestamp = strtotime($exp[0]->expiration);
//                        //variables disponibles en plantilla de aviso de corte
//                        $Totalcost = $plan->cost + ($plan->iva * ($plan->cost / 100));
//                        $Totalcost = round($Totalcost, 2);
//
//                        $data = array(
//                            "empresa" => $global->company,
//                            "cliente" => $Auth[0]->name,
//                            "vencimiento" => date("d/m/Y", $timestamp),
//                            "plan" => $plan->name,
//                            "costo" => $Auth[0]->balance,
//                            "total" => $Totalcost,
//                            "moneda" => $global->nmoney,
//                            "Smoneda" => $global->smoney,
//                            "emailCliente" => $Auth[0]->email,
//                            "direccionCliente" => $Auth[0]->address,
//                            "telefonoCliente" => $Auth[0]->phone,
//                            "dniCliente" => $Auth[0]->dni
//                        );
//                        return View::make('templates.Aviso_de_corte', $data);
//                    }
                    // mostramos aviso estandar
//                } else {

                    return Redirect::to('portal');
//                }

            } else {
                //no valido
                return Redirect::to('/')->with('login_errors', true);
            }
        } else {
            //no valido
            return Redirect::to('/')->with('login_errors', true);
        }

    }
}
