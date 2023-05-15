<?php

namespace App\Http\Controllers;
use App\libraries\CheckUser;
use App\models\Client;
use Illuminate\Support\Facades\DB;

class GetNotificationClientController extends BaseController
{

    public function postData()
    {
        $user = CheckUser::isLogin();

        if ($user != 1) {

            $client = Client::where('email', '=', $user)->orWhere('dni', '=', $user)->get();
            //verificamos si esta registrado en notificaciones

            $tickets = DB::table('tickets')->where('client_id', $client[0]->id)->where('read_client', '0')->count();
            $bills = DB::table('bill_customers')->where('client_id', $client[0]->id)->where('open', '0')->count();

            $data = array(
                'success' => true,
                'tickets' => $tickets,
                'bills' => $bills,
                //'chats' => $chats
            );

            echo json_encode($data);
        }


    }

}
