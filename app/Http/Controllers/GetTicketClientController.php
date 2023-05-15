<?php

namespace App\Http\Controllers;
use App\libraries\CheckUser;
use App\models\Answer;
use App\models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class GetTicketClientController extends BaseController
{

    public function postShow(Request $request)
    {
        $user = CheckUser::isLogin();

        if ($user == 1)
            exit();

        $tickets = Answer::where('ticket_id', $request->get('ticket'))->orderBy('created_at', 'desc')->get();

        $ticket = Ticket::find($request->get('ticket'));
        $ticket->read_client = 1;
        $ticket->save();

        return Response::json($tickets);
    }


    public function postStatus(Request $request)
    {

        $user = CheckUser::isLogin();

        if ($user == 1)
            exit();

        $ticket = Ticket::find($request->get('ticket'));

        $data = array('st' => $ticket->status);

        return Response::json($data);
    }


}
