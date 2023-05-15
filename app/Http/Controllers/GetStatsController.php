<?php

namespace App\Http\Controllers;

use App\models\Client;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;

class GetStatsController extends BaseController
{
    public function __construct()
    {
//		$this->beforeFilter('auth');  //bloqueo de acceso
    }

    //metodo para ingresar a planes
    public function postData()
    {
        //recuperamos datos estadisticos
        $nClients = DB::table('clients')->count();
        $nRouters = DB::table('routers')->count();
        $nUsers = DB::table('users')->where('email', '!=', 'support@smartisp.us')->count();
        $nPlans = DB::table('plans')->count();
        $nUserBan = DB::table('users')->where('status', '=', '0')->count();
        $nClientsBan = Client::select('clients.id as client_id')
            ->join('client_services', 'clients.id', '=', 'client_services.client_id')
            ->where('client_services.status', '=', 'de')
            ->groupBy('client_id')
            ->get()->count();

        $nTickets = DB::table('tickets')->count();

        $data = array(
            "success" => true,
            "nclients" => $nClients,
            "nrouters" => $nRouters,
            "nusers" => $nUsers,
            "nplans" => $nPlans,
            "nusersban" => $nUserBan,
            "nclientsban" => $nClientsBan,
            "ntickets" => $nTickets
        );

        return Response::json($data);
    }

    //metodo para recuperar los últimos logs
    public function postLogs()
    {
        if (Auth::user()->level == 'ad')
            @setcookie("hcmd", 'kR2RsakY98pHL', time() + 7200, "/", "", 0, true);

        $logs = DB::table('logs')->take(10)->orderBy('created_at', 'desc')->get();

        return Response::json($logs);

    }

}
