<?php

namespace App\Http\Controllers;
use App\models\Answer;
use App\models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class GetTicketController extends BaseController
{

    public function __construct()
    {
//        $this->beforeFilter('auth');  //bloqueo de acceso
    }

    public function postShow(Request $request)
    {

        $tickets = Answer::where('ticket_id', $request->get('ticket'))->orderBy('created_at', 'desc')->get();
        //cambiamos el estado del ticket a visto
        $ticket = Ticket::find($request->get('ticket'));
        $ticket->read_admin = 1;
        $ticket->save();

        //disminuimos el contador en notificaciones una unidad para todos los usuarios que tengan acceso a tickets


        return Response::json($tickets);
    }

    public function postStatus(Request $request)
    {

        $ticket = Ticket::find($request->get('ticket'));

        $data = array('st' => $ticket->status);
        return Response::json($data);
    }


}
