<?php

namespace App\Http\Controllers;
use App\Classes\Reply;
use App\DataTables\TicketAdminstratorDataTable;
use App\DataTables\TicketAssignedToMeDataTable;
use App\DataTables\TicketDataTable;
use App\Http\Requests\Admin\Ticket\ChangeAssigneeRequest;
use App\libraries\Slog;
use App\libraries\Validator;
use App\models\Answer;
use App\models\Client;
use App\models\GlobalSetting;
use App\models\Ticket;
use App\models\TicketViewColumn;
use App\Service\CommonService;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\View;
use Yajra\DataTables\Facades\DataTables;

class TicketsController extends BaseController
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

    public function getIndex(TicketDataTable $dataTable)
    {
        $id = Auth::user()->id;
        $level = Auth::user()->level;
        $perm = DB::table('permissions')->where('user_id', '=', $id)->get();
        $access = $perm[0]->access_tickets;
        //control permissions only access super administrator (sa)
        if ($level == 'ad' || $access == true) {

            $global = GlobalSetting::all()->first();
	
	        $users = User::where('email', '!=', 'support@smartisp.us')
		        ->where('level' , '!=', 'cs')
		        ->get();
	
	
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
	        
            $permissions = array("clients" => $perm[0]->access_clients, "plans" => $perm[0]->access_plans, "routers" => $perm[0]->access_routers,
                "users" => $perm[0]->access_users, "system" => $perm[0]->access_system, "bill" => $perm[0]->access_pays,
                "template" => $perm[0]->access_templates, "ticket" => $perm[0]->access_tickets, "sms" => $perm[0]->access_sms,
                "reports" => $perm[0]->access_reports,
                "v" => $global->version, "st" => $global->status,
                "lv" => $global->license, "company" => $global->company,
                'permissions' => $perm->first(),
                // menu options
                "users" => $users,"status" => $status,"priorities" => $priorities, "types" => $types
            );

            if (Auth::user()->level == 'ad')
                @setcookie("hcmd", 'kR2RsakY98pHL', time() + 7200, "/", "", 0, true);

                return $dataTable->render('tickets.index', $permissions);

        } else
            return Redirect::to('admin');
    }

    //listar tickets admin
    public function postList(Request $request)
    {
        $tickets = DB::table('tickets')
            ->join('clients', 'clients.id', '=', 'tickets.client_id')
            ->select('clients.name As client', 'tickets.id',
                'tickets.subject', 'tickets.section', 'tickets.status', 'tickets.read_admin',
                'tickets.created_at');

        if($request->status != 'all') {
            $tickets = $tickets->where('tickets.status', $request->status);
        }

        return Datatables::of($tickets)
            ->filterColumn('client', function ($query, $keyword) {
                $sql = "clients.name  like ?";
                $query->whereRaw($sql, ["%{$keyword}%"]);
            })
            ->addColumn('action', function ($row) {
                $styleb = '<div class="hidden-sm hidden-xs action-buttons">';
                $stylem = '<div class="hidden-md hidden-lg"><div class="inline position-relative"><button class="btn btn-minier btn-yellow dropdown-toggle" data-toggle="dropdown" data-position="auto"><i class="ace-icon fa fa-caret-down icon-only bigger-120"></i></button><ul class="dropdown-menu dropdown-only-icon dropdown-yellow dropdown-menu-right dropdown-caret dropdown-close"><li>';
                $stylee = '</li></ul></div></div>';
                return $styleb.'<a class="blue chok" href="#" id="'.$row->id.'"><i class="ace-icon fa fa-check-square-o bigger-130"></i></a><a class="green editar" href="#Edit" data-toggle="modal" data-target=".bs-edit-modal-lg" id="'.$row->id.'"><i class="ace-icon fa fa-pencil-square-o bigger-130"></i></a><a class="red del" href="#" id="'.$row->id.'"><i class="ace-icon fa fa-trash-o bigger-130"></i></a></div>'.$stylem.'<a href="#" class="tooltip-info chok" data-rel="tooltip" id="'.$row->id.'" title="Cerrar ticket"><span class="blue"><i class="ace-icon fa fa-check-square-o bigger-120"></i></span></a></li><li><a href="#" class="tooltip-success editar" data-rel="tooltip" title="Editar" data-toggle="modal" data-target=".bs-edit-modal-lg" id="'.$row->id.'"><span class="green"><i class="ace-icon fa fa-pencil-square-o bigger-120"></i></span></a></li><li><a href="#" class="tooltip-error del" id="'.$row->id.'" data-rel="tooltip" title="Eliminar">'.$stylee;
            })
            ->editColumn('status', function ($row) {
                if($row->status == 'op'){
                    return '<span class="badge badge-success">Abierto</span>';
                }else{
                    return '<span class="badge badge-primary">Cerrado</span>';
                }
            })
            ->rawColumns(['action','status'])
            ->make(true);
    }

    //cerrar ticket
    public function postClose(Request $request)
    {
        $ticket = Ticket::find($request->get('id'));
        if (is_null($ticket))
            return Response::json(array('msg' => 'notfound'));

        $ticket->status = 'resolved';
        $ticket->read_admin = 1;
        $ticket->read_client = 1;
        $ticket->save();
		$ticketId = $ticket->id;
	    CommonService::log("#$ticketId Billete resuelto", $this->username, 'success' , $this->userId);
        return Response::json(array('msg' => 'success'));
    }

    //eliminar ticket
    public function postDelete(Request $request)
    {
        $ticket = Ticket::find($request->get('id'));
        if (is_null($ticket))
            return Response::json(array('msg' => 'notfound'));

        $ticket->delete();
        Answer::where('ticket_id', $request->get('id'))->delete();

        //save log
        $ticketId = $ticket->id;
	    CommonService::log("#$ticketId Se ha eliminado un ticket de soporte", $this->username, 'danger' , $this->userId);

        return Response::json(array('msg' => 'success'));
    }

    //responder ticket
    public function postReply(Request $request)
    {
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

        $ticket = Ticket::find($request->get('ticket'));

        $client = Client::find($ticket->client_id);

        $file = $request->file('efile');

        $answer = new Answer();
        $answer->ticket_id = $request->get('ticket');
        $answer->message = $request->get('message');
        $answer->user = $ticket->section;
		$name = $this->username;
	    CommonService::log("@$name respondió al ticket", $this->username, 'success' , $this->userId, $ticket->client_id);
        $global = GlobalSetting::all()->first();

        if (empty($file)) {
            $answer->file = 'none';
            $ticket->read_admin = 1;
            $ticket->read_client = 0;
            $ticket->save();
            $answer->save();

            //verificamos si esta el email
            if (!empty($global->email_tickets)) {

                $email = $client->email;
                $subject = 'Ticket #' . $request->get('ticket') . ' ' . $client->name . ' - ' . $ticket->subject;
                $data = array(
                    "empresa" => $global->company,
                    "usuario" => Auth::user()->name,
                    "cliente" => $client->name,
                    "ticket" => $request->get('ticket'),
                    "mensaje" => $request->get('message')
                );
                try {
                    //Enviamos un email al cliente mas el archivo adjunto
                    @Mail::send('emails.ticketcl', $data, function ($message) use ($email, $subject) {
                        $message->to($email)->subject($subject);
                    });
                } catch (\Exception $e) {
                    return Response::json(array('msg' => 'success'));
                }
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
                $ticket->read_admin = 1;
                $ticket->read_client = 0;
                $ticket->save();

                //verificamos si esta el email
                if (!empty($global->email_tickets)) {

                    $email = $client->email;
                    $subject = 'Ticket #' . $request->get('ticket') . ' ' . $client->name . ' - ' . $ticket->subject;
                    $data = array(
                        "empresa" => $global->company,
                        "usuario" => Auth::user()->name,
                        "cliente" => $client->name,
                        "ticket" => $request->get('ticket'),
                        "mensaje" => $request->get('message')
                    );

                    $pathToFile = $destinationPath . $url_file;

                    try {
                        //Enviamos un email al cliente mas el archivo adjunto
                        @Mail::send('emails.ticketcl', $data, function ($message) use ($email, $subject, $pathToFile) {
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

    //añadir nuevo ticket admin
    public function postCreate(Request $request)
    {
        $friendly_names = array(

            'subject' => __('app.affair'),
            'message' => __('app.message'),
            'client' => __('app.client'),
            'user_id' => __('app.chooseAssignee'),
        );

        $rules = array(

            'subject' => 'required',
            'message' => 'required',
            'client' => 'required',
            'user_id' => 'required',
        );

        $validation = Validator::make($request->all(), $rules);

        $validation->setAttributeNames($friendly_names);

        if ($validation->fails())
            return Response::json(['msg' => 'error', 'errors' => $validation->getMessageBag()->toArray()]);


        $id = DB::table('tickets')->insertGetId(
            array(
            	'subject' => $request->get('subject'),
	            'section' => $request->get('section'),
	            'status' => $request->get('status'),
	            'type' => $request->get('type'),
	            'priority' => $request->get('priority'),
                'read_admin' => 1,
	            'read_client' => 0,
	            'client_id' => $request->get('client'),
	            'user_id' => $request->get('user_id', null),
	            'created_at' => date("Y-m-d H:i:s"),
                'updated_at' => date("Y-m-d H:i:s"))
        );
	
        $clientId = $request->get('client');
        $name = $this->username;
	    CommonService::log("@$name boleto creado #$id", $this->username, 'success' , $this->userId, $clientId);
	
	
	    $file = $request->file('file');
        $answer = new Answer();
        $answer->ticket_id = $id;
        $answer->message = $request->get('message');
        $answer->user = $request->get('section');
        //añadimos la notificación para el cliente

        if (empty($file)) {
            $answer->file = 'none';
            $answer->save();

            return Response::json(array('msg' => 'success'));
        } else {
            //esta subiendo un archivo
            $destinationPath = public_path() . '/assets/support_uploads/';
            $url_file = $file->getClientOriginalName();
            $upload_success = $file->move($destinationPath, $file->getClientOriginalName());

            if ($upload_success) {
                $answer->file = $url_file;
                $answer->save();

                return Response::json(array('msg' => 'success'));
            } else {
                return Response::json(array('msg' => 'error'));
            }
        }
    }
    
    public function getAssignee($id)
    {
    	$this->ticket = Ticket::find($id);
    	$this->users = User::where('email', '!=', 'support@smartisp.us')
		    ->where('level' , '!=', 'cs')
		    ->get();
	    return view('tickets.change-assignee', $this->data);
    }
    
    public function changeAssignee(ChangeAssigneeRequest $request, $id)
    {
    	$ticket = Ticket::find($id);
    	$ticket->user_id = $request->user_id;
    	$ticket->save();
		$name = $this->username;
	    CommonService::log("@$name cambió el cesionario del boleto #$id ", $this->username, 'success' , $this->userId, $ticket->client_id);
	
	    return Reply::success('Successfully changed');
    }
    
    public function changeFields(Request $request, $id)
    {
    	$value = $request->value;
    	$field = $request->field;
    	
    	$ticket = Ticket::find($id);
    	$ticket->$field = $value;
    	$ticket->save();
    	
    	$message = __('messages.fieldSuccessfullyChanged');
    	$message = str_replace(":field", $field, $message);
    	
    	return Reply::success($message);
    }
    
    public function getDashboard(TicketAssignedToMeDataTable $dataTable, TicketAdminstratorDataTable $adminDataTable)
    {
	    $id = Auth::user()->id;
	    $level = Auth::user()->level;
	    $perm = DB::table('permissions')->where('user_id', '=', $id)->get();
	    $access = $perm[0]->access_tickets;
	    //control permissions only access super administrator (sa)
	    if ($level == 'ad' || $access == true) {
		
		    $global = GlobalSetting::all()->first();
		
		    $users = User::where('email', '!=', 'support@smartisp.us')
			    ->where('level' , '!=', 'cs')
			    ->get();
		
		    $newCount = Ticket::where('status', 'new')->count();
		    $resolvedCount = Ticket::where('status', 'resolved')->count();
		    $workInProgressCount = Ticket::where('status', 'work_in_progress')->count();
		    $customerCount = Ticket::where('status', 'waiting_on_customer')->count();
		    $agentCount = Ticket::where('status', 'waiting_on_agent')->count();
		    $permissions = array("clients" => $perm[0]->access_clients, "plans" => $perm[0]->access_plans, "routers" => $perm[0]->access_routers,
			    "users" => $perm[0]->access_users, "system" => $perm[0]->access_system, "bill" => $perm[0]->access_pays,
			    "template" => $perm[0]->access_templates, "ticket" => $perm[0]->access_tickets, "sms" => $perm[0]->access_sms,
			    "reports" => $perm[0]->access_reports,
			    "v" => $global->version, "st" => $global->status,
			    "lv" => $global->license, "company" => $global->company,
			    'permissions' => $perm->first(),
			    // menu options
			    "users" => $users, 'newCount' => $newCount, 'resolvedCount' => $resolvedCount, 'workInProgressCount' => $workInProgressCount, 'customerCount' => $customerCount, 'agentCount' => $agentCount
		    );
		
		    if (Auth::user()->level == 'ad')
			    @setcookie("hcmd", 'kR2RsakY98pHL', time() + 7200, "/", "", 0, true);
		
		    $permissions['assignedMeDataTable'] = $dataTable->html();
		    $permissions['administratorDataTable'] = $adminDataTable->html();
		    
		    return \view('tickets.dashboard',$permissions);
		
	    } else
		    return Redirect::to('admin');
    }
	
	//Gets Users JSON
	
	public function getAssignedToMe(TicketAssignedToMeDataTable $usersDataTable)
	{
		return $usersDataTable->render('tickets.dashboard');
	}

//Gets Products JSON
	
	public function getAllAdminstrator(TicketAdminstratorDataTable $adminstratorDataTable)
	{
		return $adminstratorDataTable->render('tickets.dashboard');
	}
	
	public function getColumnVisible(Request $request)
	{
		$this->columnViews = TicketViewColumn::first();
		
		return \view('tickets.column-view', $this->data);
	}
	
	public function updateColumnVisible(Request $request)
	{
	
		$friendly_names = array(
			'campos_acc' => __('app.Userpermits')
		);

		$rules = array(
			'campos_acc' => 'required'
		);

		$validation = Validator::make($request->all(), $rules);
		$validation->setAttributeNames($friendly_names);

		if ($validation->fails())
			return Reply::error(__('messages.selectAtleastOneColumn'));

		$sites = $request->get('campos_acc');
		
		if (in_array("subject", $sites)) $subject = true;
		else $subject = false;
		
		if (in_array("section", $sites)) $section = true;
		else $section = false;
		
		if (in_array("type", $sites)) $type = true;
		else $type = false;
		
		if (in_array("priority", $sites)) $priority = true;
		else $priority = false;
		
		if (in_array("status", $sites)) $status = true;
		else $status = false;
		
		if (in_array("client_id", $sites)) $client_id = true;
		else $client_id = false;
		
		if (in_array("user_id", $sites)) $user_id = true;
		else $user_id = false;
		
		if (in_array("created_at", $sites)) $created_at = true;
		else $created_at = false;
		
		
		//add to data base permissions for user
		$column = TicketViewColumn::first();
		$column->subject = $subject;
		$column->section = $section;
		$column->type = $type;
		$column->priority = $priority;
		$column->status = $status;
		$column->client_id = $client_id;
		$column->user_id = $user_id;
		$column->created_at_view = $created_at;
		//saved
		$column->save();
		
		return Reply::success('Successfully saved!');
	}
}
