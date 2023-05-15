<?php

namespace App\Http\Controllers;
use App\libraries\CheckUser;
use App\models\Client;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;

class StatsClientController extends BaseController
{
    public function postData()
    {
        $user = CheckUser::isLogin();

        if ($user == 1)
            exit();

        $client = Client::where('email', '=', $user)->orWhere('dni', '=', $user)->get();
        //recuperamos datos estadisticos del cliente
        $nNopayeds = DB::table('bill_customers')->where('client_id', $client[0]->id)->where('status', 0)->count();
        $nPayeds = DB::table('bill_customers')->where('client_id', $client[0]->id)->where('status', 1)->count();
        $nBills = DB::table('bill_customers')->where('client_id', $client[0]->id)->count();
        $nTickets = DB::table('tickets')->where('client_id', $client[0]->id)->count();

        $data = array(
            "success" => true,
            "nnopayed" => $nNopayeds,
            "npayed" => $nPayeds,
            "nbills" => $nBills,
            "ntickets" => $nTickets
        );

        return Response::json($data);
    }

    public function postLasttickets()
    {
        $user = CheckUser::isLogin();

        if ($user == 1)
            exit();

        $client = Client::where('email', '=', $user)->orWhere('dni', '=', $user)->get();

        $tickets = DB::table('tickets')->where('client_id', $client[0]->id)->take(10)->orderBy('created_at', 'desc')->get();

        return Response::json($tickets);
    }
}
