<?php

namespace App\Http\Controllers;
use App\libraries\BackupMysql;
use App\libraries\Files;
use App\libraries\MySQLBackup;
use App\libraries\Slog;
use App\libraries\UploadHandler;
use App\models\GlobalSetting;
use App\Service\CommonService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\View;

class BackupsController extends BaseController
{
	public function __construct()
	{
		parent::__construct();
		$this->middleware(function ($request, $next) {
			$this->username = auth()->user()->username;
			$this->userId = auth()->user()->id;
			return $next($request);
		});
	}

    public function getIndex()
    {
        $id = Auth::user()->id;
        $level = Auth::user()->level;
        $perm = DB::table('permissions')->where('user_id', '=', $id)->get();
        $access = $perm[0]->access_system;
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

            $contents = View::make('backups.index', $permissions);
            $response = Response::make($contents, 200);
            $response->header('Expires', 'Tue, 1 Jan 1980 00:00:00 GMT');
            $response->header('Cache-Control', 'no-store, no-cache, must-revalidate, post-check=0, pre-check=0');
            $response->header('Pragma', 'no-cache');
            return $response;
        } else
            return Redirect::to('admin');

    }


    public function postList()
    {
        if(!\File::exists(public_path('assets/backups/'))) {
            \File::makeDirectory(public_path('assets/backups/'), 0775, true);
        }

        $backups = Files::listdir("assets/backups");

        return Response::json($backups);

    }

    public function postCreate()
    {


        $host = DB::connection()->getConfig('host');
        $user = DB::connection()->getConfig('username');
        $password = DB::connection()->getConfig('password');
        $database = DB::connection()->getConfig('database');

        try {

            $Dump = new MySQLBackup($host, $user, $password, $database);

            $Dump->setFilename('assets/backups/backup_smartisp_' . date('d-m-Y'));
            $Dump->setCompress('zip'); // zip | gz | gzip
            $Dump->setDownload(false);
            $Dump->dump();
            
	        CommonService::log("Se ha creado una copia de seguridad", $this->username, 'success', $this->userId );
            return json_encode(['msg' => 'success']);


        } catch (\Exception $e) {

            return json_encode(['msg' => 'error', 'errors' => [$e->getMessage()]]);

        }


    }

    public function postDelete(Request $request)
    {

        $file = new Files();

        if ($file->Delete('assets/backups/' . $request->get('file'))) {

	        CommonService::log("Se ha creado una copia de seguridad", $this->username, 'success', $this->userId );

            return json_encode(['msg' => 'success']);
        } else {
            return json_encode(['msg' => 'errordelete']);
        }

    }

    public function postUpload()
    {


        $upload_handler = new UploadHandler(array(
            'user_dirs' => false,
            'upload_dir' => 'assets/backups/',
            'inline_file_types' => 'zip'
        ));

	    CommonService::log("Se ha subido una copia de seguridad", $this->username, 'success', $this->userId );

    }

    public function postRestore(Request $request)
    {


        $host = DB::connection()->getConfig('host');
        $user = DB::connection()->getConfig('username');
        $password = DB::connection()->getConfig('password');
        $database = DB::connection()->getConfig('database');

        try {

            $bk = new BackupMysql('en', 'assets/backups/');

            $bk->setMysql(['host' => $host, 'user' => $user, 'pass' => $password, 'dbname' => $database]);

            $bk->restore($request->get('file')); //restore an dump database

	        CommonService::log("Se ha restaurado una copia de seguridad", $this->username, 'success', $this->userId );

            return json_encode(['msg' => 'success']);


        } catch (\Exception $e) {

            return json_encode(['msg' => 'error', 'errors' => [$e->getMessage()]]);
        }


    }


}
