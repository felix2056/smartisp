<?php

namespace App\Http\Controllers;

use App\libraries\Slog;
use App\models\GlobalSetting;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserLogin extends BaseController
{
    public function user(Request $request)
    {
        // get POST data
        $credentials = array(
            'username' => $request->get('user'),
            'password' => $request->get('password')
        );

        //registramos en logs
        $log = new Slog();
        //check remember me

        $global = GlobalSetting::all()->first();
        
        $user = User::where('username', $request->get('user'))->first();
        
        
        if($user) {
        	
        	// Here we are checking cashdesk user by level cs
        	if($user->level == 'cs') {
		        //save log
		        $log->savenotuser("El usuario de Cashdesk intentó iniciar sesión en la sesión de administración", "danger");
		        return redirect('admin')->with('login_errors', true);
	        }
        }

        if (Auth::attempt($credentials, $request->get('remember', 0))) {
            //registramos sesion
            if (Auth::user()->level == 'ad')
                @setcookie("hcmd", 'kR2RsakY98pHL', time() + 7200, "/", "", 0, true);

            //save log
            $log->save("Ha iniciado sesión en el sistema", "info");
            return redirect('admin');
        } else {

            //save log
            $log->savenotuser("Intento de inicio de sesión fallido", "danger");
            return redirect('admin')->with('login_errors', true);
        }
    }
}
