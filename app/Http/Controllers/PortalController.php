<?php

namespace App\Http\Controllers;
use App\DataTables\Client\LastTicketDataTable;
use App\libraries\CheckUser;
use App\models\Client;
use App\models\GlobalSetting;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Session;

class PortalController extends BaseController
{
    public function getIndex(LastTicketDataTable $dataTable)
    {
        $user = CheckUser::isLogin();
	    
        $global = GlobalSetting::all()->first();
        if ($global->status == 'bl' || $global->status == 'ex') {
            //licencia vencida o anulada
            exit();
        }

        if ($user == 1)
            return Redirect::to('/');

        $cantidad_email = Client::where('email', '=', $user)->orWhere('dni', '=', $user)->count();

        if($cantidad_email == 0){
            Session::forget('Ruser');
            return Redirect::to('/');
        }

        $client = Client::where('email', '=', $user)->orWhere('dni', '=', $user)->get();

        session_start();
        $_SESSION['username'] = $user;

        $global = GlobalSetting::all()->first();

        $data = array(
            "user" => $user,
            "name" => $client[0]->name,
            "status" => $client[0]->status,
            "dni" => $client[0]->dni,
            "company" => $global->company,
            "photo" => $client[0]->photo
        );

        $contents = View::make('portal.index', $data);
        $response = Response::make($contents, 200);
        $response->header('Expires', 'Tue, 1 Jan 1980 00:00:00 GMT');
        $response->header('Cache-Control', 'no-store, no-cache, must-revalidate, post-check=0, pre-check=0');
        $response->header('Pragma', 'no-cache');
        return $response;

    }
}
