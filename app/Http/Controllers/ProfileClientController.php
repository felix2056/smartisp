<?php

namespace App\Http\Controllers;
use App\libraries\CheckUser;
use App\libraries\Pencrypt;
use App\libraries\Validator;
use App\models\Client;
use App\models\GlobalSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\View;

class ProfileClientController extends BaseController
{

    public function getIndex()
    {
        $user = CheckUser::isLogin();

        if ($user == 1)
            return Redirect::to('/');

        $client = Client::where('email', '=', $user)->orWhere('dni', '=', $user)->get();
        $global = GlobalSetting::all()->first();
        $data = array(
            "user" => $user,
            "name" => $client[0]->name,
            "status" => $client[0]->status,
            "dni" => $client[0]->dni,
            "company" => $global->company,
            "photo" => $client[0]->photo,
            "register" => $client[0]->created_at,
            "email" => $client[0]->email,
            "phone" => $client[0]->phone
        );

        $contents = View::make('profileClient.index', $data);
        $response = Response::make($contents, 200);
        $response->header('Expires', 'Tue, 1 Jan 1980 00:00:00 GMT');
        $response->header('Cache-Control', 'no-store, no-cache, must-revalidate, post-check=0, pre-check=0');
        $response->header('Pragma', 'no-cache');
        return $response;
    }

    public function postUpdate(Request $request)
    {
        $user = CheckUser::isLogin();

        if ($user == 1)
            return Redirect::to('/');

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

        $profile = Client::where('email', '=', $user)->orWhere('dni', '=', $user)->get();

        $file = $request->file('file');
        $en = new Pencrypt();
        //verificamos si esta subiendo un archivo

        if (empty($file)) {
            //no esta subiendo archivo // verificamos si esta cambiando el pass
            if ($request->get('password')) {
                $profile[0]->password = $en->encode($request->get('password'));
                $profile[0]->save();
                return Response::json(array('msg' => 'success'));
            } else
                return Response::json(array('msg' => 'success'));
        } else {
            // si esta subiendo archivo //verificamos si esta camiando el pass
            if ($request->get('password')) {

                $destinationPath = public_path() . '/assets/avatars/';
                $url_photo = $file->getClientOriginalName();
                $upload_success = $file->move($destinationPath, $file->getClientOriginalName());

                if ($upload_success) {

                    $profile[0]->password = $en->encode($request->get('password'));
                    $profile[0]->photo = $url_photo;
                    $profile[0]->save();

                    return Response::json(array('msg' => 'success'));
                } else {
                    return Response::json(array('msg' => 'error'));
                }
            } else {

                $destinationPath = public_path() . '/assets/avatars/';
                $url_photo = $file->getClientOriginalName();
                $upload_success = $file->move($destinationPath, $file->getClientOriginalName());
                if ($upload_success) {
                    $profile[0]->photo = $url_photo;
                    $profile[0]->save();
                    return Response::json(array('msg' => 'success'));
                } else {
                    return Response::json(array('msg' => 'error'));
                }
            }
        }
    }

}
