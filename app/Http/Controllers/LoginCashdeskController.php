<?php

namespace App\Http\Controllers;

use App\libraries\CheckInstall;
use App\libraries\Pencrypt;
use App\models\GlobalSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\View;

class LoginCashdeskController extends BaseController
{

    public function login()
    {
        if (Session::has('Cuser')) {
            return redirect('/cashdesk/dashboard');

        } elseif (isset($_COOKIE["Cuser"])) {

            return redirect('/cashdesk/dashboard');
        } else {

            $global = GlobalSetting::all()->first();
            $contents = View::make('auth.loginCashdesk')->with("company", $global->company);
            $response = Response::make($contents, 200);
            $response->header('Expires', 'Tue, 1 Jan 1980 00:00:00 GMT');
            $response->header('Cache-Control', 'no-store, no-cache, must-revalidate, post-check=0, pre-check=0');
            $response->header('Pragma', 'no-cache');
            return $response;

        }
    }
	
	public function postLogin(Request $request)
	{
		// get POST data
		$credentials = array(
			'username' => $request->get('user'),
			'password' => $request->get('password')
		);
		
		if (empty($credentials['username']) || empty($credentials['password'])) {
			return redirect('/cashdesk/login');
		}
		
		$Auth = DB::table('users')
			->where('users.email', '=', $credentials['username'])
			->orWhere('users.username', '=', $credentials['username'])
			->first();
		
		if ($Auth) {
			$en = new Pencrypt();
			if (Auth::guard('cashdesk')->attempt($credentials, $request->get('remember', 0))) {
				//valido
				Session::put('Cuser', $en->encode($credentials['username']));
				//remenver me for ever
				if (!empty($request->get('remember')) && $request->get('remember') == 1) {
					setcookie("Cuser", $en->encode($credentials['username']), time() + 60 * 60 * 24 * 6004, "/");
				}
				
				return redirect('/cashdesk/dashboard');
				
			} else {
				//no valido
				return redirect('/cashdesk/login')->with('login_errors', true);
			}
		} else {
			//no valido
			return redirect('/cashdesk/login')->with('login_errors', true);
		}
		
	}

}
