<?php

namespace App\Http\Controllers;

use App\models\GlobalSetting;
use App\models\Router;
use App\models\SmsInbox;
use App\models\Ticket;
use Illuminate\Support\Facades\Response;
use DB;

class GetNotificationUserController extends BaseController
{

    public function __construct()
    {
//		$this->beforeFilter('auth');  //bloqueo de acceso
    }

    public function postData()
    {

    	$user = auth()->user();
    	
    	if($user->level == 'us') {
		    $tickets = Ticket::where('read_admin', 0)->where('user_id', $user->id)->count();
	    } else {
		    $tickets = Ticket::where('read_admin', 0)->count();
	    }
    	
        $routers = Router::where('status', 'of')->count();
        $sms = DB::table('sms')->where('type', '2')->where('msg_status', '0')->count();
        $global = GlobalSetting::all()->first();

        $data = array(
            'success' => true,
            'tickets' => $tickets,
            'trouters' => $routers,
            'tsms' => $sms,
            'license' => $global->status
        );

        echo json_encode($data);
    }

    public function postNtclose()
    {

        $global = GlobalSetting::all()->first();
        $global->message = 'none';
        $global->save();

        return Response::json(["success" => true]);
    }

}
