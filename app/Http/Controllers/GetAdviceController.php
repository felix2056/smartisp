<?php

namespace App\Http\Controllers;
use App\models\GlobalSetting;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Response;
class GetAdviceController extends BaseController
{


    public function adv()
    {

        return Redirect::to('/')->with('adv',true);
    }

    public function getIndex()
    {
        //verificamos si el cliente tine activa la session o el rememberme
        $global = GlobalSetting::all()->first();
        $contents = View::make('auth.loginClient')->with("company", $global->company);
        $response = Response::make($contents, 200);
        $response->header('Expires', 'Tue, 1 Jan 1980 00:00:00 GMT');
        $response->header('Cache-Control', 'no-store, no-cache, must-revalidate, post-check=0, pre-check=0');
        $response->header('Pragma', 'no-cache');
        return $response;
    }

}
