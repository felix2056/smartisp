<?php

namespace App\Http\Controllers;

use App\libraries\CheckInstall;
use App\models\GlobalSetting;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\View;

class LoginClientController extends BaseController
{

    public function login()
    {

        if (CheckInstall::check()) {
            exit();
        }

        //control de licencia
        $global = GlobalSetting::all()->first();
        //verificamos si el cliente tine activa la session o el rememberme
        if (Session::has('Ruser')) {
            return redirect('/portal');

        } elseif (isset($_COOKIE["Ruser"])) {

            return redirect('/portal');
        } else {

            $global = GlobalSetting::all()->first();
            $contents = View::make('auth.loginClient')->with("company", $global->company);
            $response = Response::make($contents, 200);
            $response->header('Expires', 'Tue, 1 Jan 1980 00:00:00 GMT');
            $response->header('Cache-Control', 'no-store, no-cache, must-revalidate, post-check=0, pre-check=0');
            $response->header('Pragma', 'no-cache');
            return $response;

        }
    }

}
