<?php

namespace App\Http\Controllers;
use App\libraries\CheckUser;
use App\libraries\Pencrypt;
use App\libraries\Validator;
use App\models\Client;
use App\models\GlobalSetting;
use App\models\Language;
use App\models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\View;

class ProfileCashdeskController extends BaseController
{

    public function getIndex()
    {
		$user = auth()->guard('cashdesk')->user();
        $global = GlobalSetting::all()->first();
	    $languages = Language::where('status', 'enabled')->get();
	
	    $data = array(
            "user" => $user,
            "company" => $global->company,
            "languages" => $languages,
        );

        $contents = View::make('cashdesk.profile.index', $data);
        $response = Response::make($contents, 200);
        $response->header('Expires', 'Tue, 1 Jan 1980 00:00:00 GMT');
        $response->header('Cache-Control', 'no-store, no-cache, must-revalidate, post-check=0, pre-check=0');
        $response->header('Pragma', 'no-cache');
        return $response;
    }

    public function postUpdate(Request $request)
    {
    	$user = auth()->guard('cashdesk')->user();
        $friendly_names = array(

            'password' => __('app.new'). ' ' . __('app.password'),
            'password_confirmation' => __('app.Repeatpassword')
        );

        $rules = array(

            'password' => 'min:3|confirmed',
            'password_confirmation' => 'min:3'
        );

        $validation = Validator::make($request->all(), $rules);

        $validation->setAttributeNames($friendly_names);

        if ($validation->fails())
            return Response::json(['msg' => 'error', 'errors' => $validation->getMessageBag()->toArray()]);

        $profile = User::where('email', '=', $user->email)->first();

        //verificamos si esta subiendo un archivo
            //no esta subiendo archivo // verificamos si esta cambiando el pass
        if ($request->get('password')) {
            $profile->password = bcrypt($request->get('password'));
            $profile->locale = $request->language;
            $profile->save();
            return Response::json(array('msg' => 'success'));
        } else
            return Response::json(array('msg' => 'success'));
    }

}
