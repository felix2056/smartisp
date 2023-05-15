<?php

namespace App\Http\Controllers;
use App\libraries\Validator;
use App\models\GlobalSetting;
use App\models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\View;

class ProfileController extends BaseController
{

    public function __construct()
    {
//        $this->beforeFilter('auth');  //bloqueo de acceso
    }

    public function getIndex()
    {

        $level = Auth::user()->level;
        $access = Auth::user()->status;

        //control permissions only access super administrator (sa)
        if ($level == 'ad' || $access == true) {
            $id = Auth::user()->id;

            $global = GlobalSetting::all()->first();

            $perm = DB::table('permissions')->where('user_id', '=', $id)->get();
            $permissions = array("clients" => $perm[0]->access_clients, "plans" => $perm[0]->access_plans, "routers" => $perm[0]->access_routers,
                "users" => $perm[0]->access_users, "system" => $perm[0]->access_system, "bill" => $perm[0]->access_pays,
                "template" => $perm[0]->access_templates, "ticket" => $perm[0]->access_tickets, "sms" => $perm[0]->access_sms, "reports" => $perm[0]->access_reports,
                "v" => $global->version, "st" => $global->status,
                "lv" => $global->license, "company" => $global->company,
                'permissions' => $perm->first(),
                // menu options
            );

            if (Auth::user()->level == 'ad')
                @setcookie("hcmd", 'kR2RsakY98pHL', time() + 7200, "/", "", 0, true);

            $contents = View::make('profile.index', $permissions);
            $response = Response::make($contents, 200);
            $response->header('Expires', 'Tue, 1 Jan 1980 00:00:00 GMT');
            $response->header('Cache-Control', 'no-store, no-cache, must-revalidate, post-check=0, pre-check=0');
            $response->header('Pragma', 'no-cache');
            return $response;
        } else
            return Redirect::to('admin');

    }

    //metodo para actualizar
    public function postUpdate(Request $request)
    {
        if(env('IS_DEMO', 'no') == 'yes') {
            return Response::json(['msg' => 'error', 'errors' => [__('messages.featureNotAvailabeInDemoVersion')]]);
        }

        $my_id = Auth::user()->id;
        $lv = Auth::user()->level;
        if ($lv == 'ad') {
            $friendly_names = array(
                'username' => __('app.username'),
                'name' => __('app.name'),
                'email' => 'Email',
                'phone' => __('app.telephone'),
                'password' => __('app.new').' '.__('app.password'),
                'password_confirmation' => __('app.Repeatpassword')

            );

            $rules = array(
                'username' => 'required|min:3|unique:users,username,' . $my_id,
                'name' => 'required|min:2',
                'email' => 'required|email|unique:users,email,' . $my_id,
                'phone' => 'required|numeric|min:2|unique:users,phone,' . $my_id,
                'password' => 'min:3|confirmed',
                'password_confirmation' => 'min:3'
            );
        } else {

            $friendly_names = array(
                'name' => __('app.name'),
                'email' => 'Email',
                'phone' => __('app.telephone'),
                'password' => __('app.new').' '.__('app.password'),
                'password_confirmation' => __('app.Repeatpassword')

            );

            $rules = array(

                'name' => 'required|min:2',
                'email' => 'required|email|unique:users,email,' . $my_id,
                'phone' => 'required|numeric|min:2|unique:users,phone,' . $my_id,
                'password' => 'min:3|confirmed',
                'password_confirmation' => 'min:3'
            );

        }

        $validation = Validator::make($request->all(), $rules);

        $validation->setAttributeNames($friendly_names);

        if ($validation->fails())
            return Response::json(['msg' => 'error', 'errors' => $validation->getMessageBag()->toArray()]);



        $profile = User::find($my_id);
        $file = $request->file('file');
        //verificamos si esta subiendo un archivo

        if (empty($file)) {
            //no esta subiendo archivo // verificamos si esta cambiando el pass
            if ($request->get('password')) {

                $new_pass = $request->get('password');
                if ($lv == 'ad')
                    $profile->username = $request->get('username');

                $profile->name = $request->get('name');
                $profile->email = $request->get('email');
                $profile->phone = $request->get('phone');
                $profile->password = Hash::make($new_pass);
                $profile->save();
                return Response::json(array('msg' => 'success'));
            } else {
                if ($lv == 'ad')
                    $profile->username = $request->get('username');

                $profile->name = $request->get('name');
                $profile->email = $request->get('email');
                $profile->phone = $request->get('phone');
                $profile->save();
                return Response::json(array('msg' => 'success'));
            }
        } else {
            // si esta subiendo archivo //verificamos si esta camiando el pass
            if ($request->get('password')) {

                $destinationPath = public_path() . '/assets/avatars/';
                $url_photo = $file->getClientOriginalName();
                $upload_success = $file->move($destinationPath, $file->getClientOriginalName());

                if ($upload_success) {

                    $new_pass = $request->get('password');
                    if ($lv == 'ad')
                        $profile->username = $request->get('username');

                    $profile->name = $request->get('name');
                    $profile->email = $request->get('email');
                    $profile->phone = $request->get('phone');
                    $profile->password = Hash::make($new_pass);
                    $profile->photo = $url_photo;
                    $profile->save();

                    return Response::json(array('msg' => 'success'));
                } else {
                    return Response::json(array('msg' => 'error'));
                }

            } else {

                $destinationPath = public_path() . '/assets/avatars/';
                $url_photo = $file->getClientOriginalName();
                $upload_success = $file->move($destinationPath, $file->getClientOriginalName());
                if ($upload_success) {
                    if ($lv == 'ad')
                        $profile->username = $request->get('username');

                    $profile->name = $request->get('name');
                    $profile->email = $request->get('email');
                    $profile->phone = $request->get('phone');
                    $profile->photo = $url_photo;
                    $profile->save();
                    return Response::json(array('msg' => 'success'));
                } else {
                    return Response::json(array('msg' => 'error'));
                }
            }
        }
    }
}
