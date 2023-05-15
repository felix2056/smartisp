<?php

namespace App\Http\Controllers;
use App\DataTables\TemplateDataTable;
use App\libraries\Helpers;
use App\models\GlobalSetting;
use App\models\Template;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\View;

class TemplatesController extends BaseController
{


    public function __construct()
    {
//        $this->beforeFilter('auth');  //bloqueo de acceso
    }

    public function getIndex(TemplateDataTable $dataTable)
    {
        $id = Auth::user()->id;
        $level = Auth::user()->level;
        $perm = DB::table('permissions')->where('user_id', '=', $id)->get();
        $access = $perm[0]->access_templates;
        //control permissions only access super administrator (sa)
        if ($level == 'ad' || $access == true) {


            $global = GlobalSetting::all()->first();

            $permissions = array("clients" => $perm[0]->access_clients, "plans" => $perm[0]->access_plans, "routers" => $perm[0]->access_routers,
                "users" => $perm[0]->access_users, "system" => $perm[0]->access_system, "bill" => $perm[0]->access_pays,
                "template" => $perm[0]->access_templates, "ticket" => $perm[0]->access_tickets, "sms" => $perm[0]->access_sms,
                "reports" => $perm[0]->access_reports,
                "v" => $global->version, "st" => $global->status,
                "lv" => $global->license, "company" => $global->company,
                'permissions' => $perm->first(),
                // menu options
            );

            if (Auth::user()->level == 'ad')
                @setcookie("hcmd", 'kR2RsakY98pHL', time() + 7200, "/", "", 0, true);

            return $dataTable->render('temes.index', $permissions);

        } else
        return Redirect::to('admin');
    }


    public function postList()
    {
        $temp = DB::table('templates')->select('name', 'registered', 'type', 'id')->get();

        return Response::json($temp);
    }

    //metodo para listar templates del tipo aviso para el editor visual
    public function postListvisual()
    {
        $temp = DB::table('templates')->where('type', '=', 'screen')->orWhere('type', '=', 'contract')->select('name', 'registered', 'type', 'id')->get();


        return Response::json($temp);
    }

    //metodo para listar templates del tipo email y hotspot
    public function postListhtml()
    {
        $temp = DB::table('templates')->where('type', '=', 'html')->orWhere('type', 'hotspot')->select('name', 'registered', 'type', 'id')->get();

        return Response::json($temp);
    }

    //metodo para listar los templates segun el tipo
    public function postListtem(Request $request)
    {

        $tem = $request->get('tem');

        $temp = Template::where(['type' => $tem, 'status' => 1])->select('name', 'id', 'type')->get();
        $temp->map(function ($template) {
            if ($template->type == 'whatsapp') {
                $template->name = __('app.' . $template->name);
            }

            return $template;
        });
        if (count($temp) == 0)
            return Response::json(array('msg' => 'notemplates'));

        return Response::json($temp);

    }

    public function postDelete(Request $request)
    {

        $template = Template::find($request->get('id'));

        if (is_null($template)) {

            return Response::json(array("msg" => 'error'));
        }

        if ($template->system == true) {

            return Response::json(array("msg" => 'errortemp'));
        }

        //eliminamos primero el archivo
        $fileName = $template->filename;
        $file = resource_path() . "/views/templates/$fileName";

        unlink($file);

        //eliminamos de la Base de datos
        $template->delete();
        return Response::json(array("msg" => 'success'));
    }

    public function postSeteme(Request $request)
    {
        $name = $request->get('name');
        $template = Template::where('name', '=', $name)->first();

        if($template) {
            return $template->content;
        }

        return '';
    }

    public function postSetype(Request $request)
    {
        $name = $request->get('name');
        $type = Template::where('name', '=', $name)->get();

        return Response::json(array("type" => $type[0]->type));
    }

    public function postCreate(Request $request)
    {
        $content = $request->get('data');
        $template = $request->get('tem');
        $name = $request->get('name');
        $tp = $request->get('tp');

        $global = GlobalSetting::all()->first();
        //comprobamos si esta intentando crear un nuevo template

        if ($template == 'new') {

            if (empty($name))
                return Response::json(array("msg" => 'error'));

            //verificamos si esta creando un aviso tipo pantalla o email

            $fileName = str_replace(' ', '_', $name);
            $file = resource_path() . "/views/templates/$fileName" . ".blade.php";

            $head = '<!DOCTYPE html>
            <html lang="es">
            <head>
            <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
            <meta charset="utf-8" />
            <title>' . $name . ' | ' . $global->company . '</title>
            <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0" />
            <link rel="stylesheet" href="assets/css/bootstrap.min.css" />
            </head>
            <body>';

            $footer = '<script src="'.asset('assets/js/jquery.min.js').'"></script>
            <script src="'.asset('assets/js/bootstrap.min.js').'"></script>
            </body>
            </html>';
            $code = $head . $content . $footer;
            $fp = fopen($file, "w") or die("Unable to open file!");
            fwrite($fp, $code);
            fclose($fp);
            chmod($file, 0777);  //changed to add the zero
            //no hay se encuentra la plantilla creamos nuevo
            $teme = new Template;
            $teme->name = $name;
            $teme->registered = date("Y-m-d");
            $teme->type = $tp;
            $teme->system = false;
            $teme->filename = $fileName . '.blade.php';
            $teme->content = $content;
            $teme->save();

            return Response::json(array("msg" => 'success'));

        } elseif ($template == 'none') {
            return Response::json(array("msg" => 'none'));
        } else {
            //significa que esta editando una plantilla, actualizamos
            $teme = Template::where('name', '=', $template)->get();

            $file = resource_path() . "/views/templates/" . $teme[0]->filename;

            $head = '<!DOCTYPE html>
            <html lang="es">
            <head>
            <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1" />
            <meta charset="utf-8" />
            <title>' . $teme[0]->name . ' | ' . $global->company . '</title>
            <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0" />
            <link rel="stylesheet" href="assets/css/bootstrap.min.css" />
            </head>
            <body>' . "\n";

            $footer = "\n" . '<script src="'.asset('assets/js/jquery.min.js').'"></script>
            <script src="'.asset('assets/js/bootstrap.min.js').'"></script>
            </body>
            </html>';
            $code = $head . $content . $footer;

            $fp = fopen($file, "w") or die("Unable to open file!");
            fwrite($fp, $code);
            fclose($fp);
            chmod($file, 0777);  //changed to add the zero

            $teme[0]->registered = date("Y-m-d");
            $teme[0]->type = $tp;
            $teme[0]->content = $content;
            $teme[0]->save();

            return Response::json(array("msg" => 'updated'));
        }

    }//end function

    //metodo para guardar plantilas html
    public function postHtml(Request $request)
    {
        $content = $request->get('data');

        $template = $request->get('tem');
        $name = $request->get('name');
        $tp = $request->get('tp');

        //controlamos los caracteres no exedan los 160 caracteres
        if ($tp == 'sms') {
            if (strlen($content) > 160) {
                return Response::json(array("msg" => "maxcharacters"));
            }
        }

        //comprobamos si esta intentando crear un nuevo template

        if ($template == 'new') {

            if (empty($name))
                return Response::json(array("msg" => 'error'));
            //verificamos si esta creando un aviso tipo pantalla o email
            $fileName = str_replace(' ', '_', $name);
            $file = resource_path() . "/views/templates/$fileName.blade.php";

            $fp = fopen($file, "w") or die("Unable to open file!");

            fwrite($fp, $content);
            fclose($fp);
            chmod($file, 0777);  //changed to add the zero
            //no hay se encuentra la plantilla creamos nuevo
            $teme = new Template;
            $teme->name = $name;
            $teme->registered = date("Y-m-d");
            $teme->type = $tp;
            $teme->system = false;
            $teme->filename = $fileName . '.blade.php';
            $teme->content = $content;
            $teme->save();

            return Response::json(array("msg" => 'success'));

        } elseif ($template == 'none') {
            return Response::json(array("msg" => 'none'));
        } elseif ($tp == 'whatsapp') {

            $teme = Template::where('name', $template)->first();
            $data = Helpers::get_whatsappcloud_api_options();

            Helpers::updateWhatsappTemplates($teme, $data['access_token']);

            $teme->registered = date("Y-m-d");
            $teme->content = $content;
            $teme->save();
            return Response::json(array("msg" => 'updated'));
        } else {
            //significa que esta editando una plantilla, actualizamos
            $teme = Template::where('name', '=', $template)->get();
            $file = resource_path() . "/views/templates/" . $teme[0]->filename;

            $fp = fopen($file, "w") or die("Unable to open file!");

            fwrite($fp, $content);
            fclose($fp);
//            chmod($file, 0777);  //changed to add the zero

            $teme[0]->registered = date("Y-m-d");
            $teme[0]->type = $tp;
            $teme[0]->content = $content;
            $teme[0]->save();
            return Response::json(array("msg" => 'updated'));
        }
    }
}//end class
