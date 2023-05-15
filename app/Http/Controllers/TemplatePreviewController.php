<?php

namespace App\Http\Controllers;
use App\models\GlobalSetting;
use App\models\Template;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\View;

class TemplatePreviewController extends BaseController
{
    public function __construct()
    {
//        $this->beforeFilter('auth');  //bloqueo de acceso
    }

    public function viewIndex(Request $request)
    {
        $id = Auth::user()->id;
        $level = Auth::user()->level;
        $perm = DB::table('permissions')->where('user_id', '=', $id)->get();
        $access = $perm[0]->access_templates;
        //control permissions only access super administrator (sa)
        if ($level == 'ad' || $access == true) {

            if (Auth::user()->level == 'ad')
                @setcookie("hcmd", 'kR2RsakY98pHL', time() + 7200, "/", "", 0, true);

            $template = Template::where('id', '=', $request->get('id'))->select('name', 'content', 'type')->get();
            $global = GlobalSetting::all()->first();

            if ($template[0]->type == 'screen') {

                $head = '<!DOCTYPE html>
<html lang="es">
<head>
<meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
<meta charset="utf-8" />
<title>' . $template[0]->name . ' | ' . $global->company . '</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0" />
<link rel="stylesheet" href="assets/css/bootstrap.min.css" />
</head>
<body>' . "\n";

                $footer = "\n" . '<script src="'.asset('assets/js/jquery.min.js').'"></script>
<script src="'.asset('assets/js/bootstrap.min.js').'"></script>
</body>
</html>';
                $code = $head . $template[0]->content . $footer;
                $data = $code;
            }

            if ($template[0]->type == 'invoice' || $template[0]->type == 'sms' || $template[0]->type == 'email' || $template[0]->type == 'contract')
                $data = $template[0]->content;

            if ($template[0]->type == 'whatsapp' && $template[0]->name == 'payment_reminder_sms') {
                $data = $template[0]->content;
            }

            $contents = View::make('template_preview.index')->with('html', $data);
            $response = Response::make($contents, 200);
            $response->header('Expires', 'Tue, 1 Jan 1980 00:00:00 GMT');
            $response->header('Cache-Control', 'no-store, no-cache, must-revalidate, post-check=0, pre-check=0');
            $response->header('Pragma', 'no-cache');
            return $response;
        } else
            return Redirect::to('admin');
    }

}
