<?php

namespace App\Http\Controllers;

use App\DataTables\LogDataTable;
use App\models\GlobalSetting;
use App\models\Logg;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\View;

class LogsController extends BaseController
{

    public function __construct()
    {
//		$this->beforeFilter('auth');  //bloqueo de acceso
    }

    public function getIndex(LogDataTable $dataTable)
    {
        $id = Auth::user()->id;
        $level = Auth::user()->level;
        $perm = DB::table('permissions')->where('user_id', '=', $id)->get();
        $access = $perm[0]->access_system;
        //control permissions only access super administrator (sa)
        if ($level == 'ad' || $access == true) {

            $global = GlobalSetting::all()->first();
			$loggedUsers = Logg::select('user')->distinct()->get();
            $permissions = array("clients" => $perm[0]->access_clients, "plans" => $perm[0]->access_plans, "routers" => $perm[0]->access_routers,
                "users" => $perm[0]->access_users, "system" => $perm[0]->access_system, "bill" => $perm[0]->access_pays,
                "template" => $perm[0]->access_templates, "ticket" => $perm[0]->access_tickets, "sms" => $perm[0]->access_sms,
                "reports" => $perm[0]->access_reports,
                "v" => $global->version, "st" => $global->status,
                "lv" => $global->license, "company" => $global->company,
                'permissions' => $perm->first(),
                'loggedUsers' => $loggedUsers,
                // menu options
                
            );

            if (Auth::user()->level == 'ad')
                @setcookie("hcmd", 'kR2RsakY98pHL', time() + 7200, "/", "", 0, true);

            return $dataTable->render('logs.index', $permissions);
        } else
            return redirect('admin');
    }

    public function postList()
    {
        $logs = DB::table('logs')->orderBy('created_at', 'desc')->get();
        return Response::json($logs);
    }
}
