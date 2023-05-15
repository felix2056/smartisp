<?php

namespace App\Http\Controllers;
use App\DataTables\Client\TicketDataTable;
use App\libraries\CheckUser;
use App\libraries\Validator;
use App\models\Answer;
use App\models\Client;
use App\models\GlobalSetting;
use App\models\Ticket;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\View;

class TicketClientController extends BaseController
{
    public function getIndex(TicketDataTable  $dataTable)
    {
        $user = CheckUser::isLogin();

        if ($user == 1)
            return Redirect::to('/');

        $client = Client::where('email', '=', $user)->orWhere('dni', '=', $user)->get();
        $global = GlobalSetting::all()->first();
        
        $types = [
        	'question',
        	'incident',
        	'problem',
        	'feature_request',
        	'lead',
        ];
        $priorities = [
        	'low',
        	'medium',
        	'high',
        	'urgent'
        ];
	
	    $status = [
		    'new',
		    'work_in_progress',
		    'resolved',
		    'waiting_on_customer',
		    'waiting_on_agent'
	    ];
	    
        $data = array(
            "user" => $user,
            "name" => $client[0]->name,
            "company" => $global->company,
            "photo" => $client[0]->photo,
	        "types" => $types,
            "status" => $status,
	        "priorities" => $priorities
        );

        return $dataTable->render('ticketsClient.index', $data);

    }

    public function postList()
    {
        $user = CheckUser::isLogin();

        if ($user == 1)
            return Redirect::to('/');

        $client = Client::where('email', '=', $user)->orWhere('dni', '=', $user)->get();

        $tickets = Ticket::where('client_id', $client[0]->id)->get();

        return Response::json($tickets);
    }

    public function postCreate(Request $request)
    {
        $user = CheckUser::isLogin();

        if ($user == 1)
            return Redirect::to('/');

        $friendly_names = array(

            'subject' => __('app.affair'),
            'message' => __('app.message')
        );

        $rules = array(

            'subject' => 'required',
            'message' => 'required'
        );

        $validation = Validator::make($request->all(), $rules);

        $validation->setAttributeNames($friendly_names);

        if ($validation->fails())
            return Response::json(['msg' => 'error', 'errors' => $validation->getMessageBag()->toArray()]);

        $client = Client::where('email', '=', $user)->orWhere('dni', '=', $user)->get();

        $file = $request->file('file');
	
	    $user = User::where('email', '!=', 'support@smartisp.us')
		    ->where('level' , 'ad')
		    ->orderBy('id', 'asc')
		    ->first();

        $id = DB::table('tickets')->insertGetId(
            array('subject' => $request->get('subject'), 'section' => $request->get('section'), 'status' => 'new','type' => $request->type, 'priority' => $request->priority,
                'read_admin' => 0, 'read_client' => 1, 'client_id' => $client[0]->id, 'user_id' => $user ? $user->id : null, 'created_at' => date("Y-m-d H:i:s"),
                'updated_at' => date("Y-m-d H:i:s"))
        );

        //actualizamos
        $answer = new Answer();
        $answer->ticket_id = $id;
        $answer->message = $request->get('message');
        $answer->user = $client[0]->name;

        $global = GlobalSetting::all()->first();

        if (empty($file)) {
            $answer->file = 'none';
            $answer->save();

            //verificamos si esta el email
            if (!empty($global->email_tickets)) {

                $email = $global->email_tickets;
                $subject = 'Nuevo ticket ' . $client[0]->name . ' - ' . $request->get('subject');
                $data = array(
                    "empresa" => $global->company,
                    "cliente" => $client[0]->name,
                    "mensaje" => $request->get('message'),
                    "ip" => $client[0]->ip,
                    "emailCliente" => $client[0]->email,
                    "telefonoCliente" => $client[0]->phone
                );

                try {
                    //Enviamos un email al administrador
                    Mail::send('emails.ticket', $data, function ($message) use ($email, $subject) {
                        $message->to($email)->subject($subject);
                    });

                } catch (\Exception $e) {

                    return Response::json(array('msg' => 'success'));

                }

                return Response::json(array('msg' => 'success'));

            }

            return Response::json(array('msg' => 'success'));

        } else {
            //esta subiendo un archivo
            $destinationPath = public_path() . '/assets/support_uploads/';
            $url_file = $file->getClientOriginalName();
            $upload_success = $file->move($destinationPath, $file->getClientOriginalName());

            if ($upload_success) {
                $answer->file = $url_file;
                $answer->save();

                //verificamos si esta el email
                if (!empty($global->email_tickets)) {

                    $email = $global->email_tickets;
                    $subject = 'Nuevo ticket ' . $client[0]->name . ' - ' . $request->get('subject');
                    $data = array(
                        "empresa" => $global->company,
                        "cliente" => $client[0]->name,
                        "mensaje" => $request->get('message'),
                        "ip" => $client[0]->ip,
                        "emailCliente" => $client[0]->email,
                        "telefonoCliente" => $client[0]->phone
                    );

                    $pathToFile = $destinationPath . $url_file;

                    try {
                        //Enviamos un email al administrador mas el archivo adjunto
                        Mail::send('emails.ticket', $data, function ($message) use ($email, $subject, $pathToFile) {
                            $message->to($email)->subject($subject);
                            $message->attach($pathToFile);
                        });

                    } catch (\Exception $e) {
                        return Response::json(array('msg' => 'success'));
                    }
                }

                return Response::json(array('msg' => 'success'));
            } else {
                return Response::json(array('msg' => 'error'));
            }
        }
    }

    //metodo para enviar respuestas
    public function postReply(Request $request)
    {
        $user = CheckUser::isLogin();

        if ($user == 1)
            return Redirect::to('/');

        $friendly_names = array(
            'message' => __('app.message')
        );

        $rules = array(
            'message' => 'required'
        );

        $validation = Validator::make($request->all(), $rules);

        $validation->setAttributeNames($friendly_names);

        if ($validation->fails())
            return Response::json(['msg' => 'error', 'errors' => $validation->getMessageBag()->toArray()]);

        $client = Client::where('email', '=', $user)->orWhere('dni', '=', $user)->get();

        $ticket = Ticket::find($request->get('ticket'));

        $file = $request->file('efile');

        $answer = new Answer;
        $answer->ticket_id = $request->get('ticket');
        $answer->message = $request->get('message');
        $answer->user = $client[0]->name;

        if (empty($file)) {
            $answer->file = 'none';
            $answer->save();
            $ticket->read_admin = 0;
            $ticket->read_client = 1;
            $ticket->save();

            return Response::json(array('msg' => 'success'));
        } else {
            //esta subiendo un archivo
            $destinationPath = public_path() . '/assets/support_uploads/';
            $url_file = $file->getClientOriginalName();
            $upload_success = $file->move($destinationPath, $file->getClientOriginalName());

            if ($upload_success) {
                $answer->file = $url_file;
                $answer->save();
                $ticket->read_admin = 0;
                $ticket->read_client = 1;
                $ticket->save();

                return Response::json(array('msg' => 'success'));
            } else {
                return Response::json(array('msg' => 'error'));
            }
            
        }
    }
}
