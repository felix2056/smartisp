<?php

namespace App\Http\Controllers;
use App\Classes\Reply;
use App\DataTables\CronJobDataTable;
use App\libraries\Helpers;
use App\models\CronJob;
use App\models\GlobalSetting;
use App\models\Factel;
use App\models\Language;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;

class CronJobsController extends BaseController
{
	public function getIndex(CronJobDataTable $dataTable)
	{
		$id = Auth::user()->id;
		$level = Auth::user()->level;
		$perm = DB::table('permissions')->where('user_id', '=', $id)->get();
		$access = $perm[0]->access_system;
		    //control permissions only access super administrator (sa)
		if ($level == 'ad' || $access == true) {
		
		    $global = GlobalSetting::all()->first();
		    $GoogleMaps = Helpers::get_api_options('googlemaps');
		
		    if (count($GoogleMaps) > 0) {
		        $key = $GoogleMaps['k'];
		    } else {
		        $key = 0;
		    }
		    
		    $factel = Factel::all()->first();
		    $status_factel = 1;
		    
		    if (!empty($factel)) {
		        $status_factel=$factel->status;
		    }
		
		    $languages = Language::where('status', 'enabled')->get();
		    $permissions = array("clients" => $perm[0]->access_clients, "plans" => $perm[0]->access_plans, "routers" => $perm[0]->access_routers,
		        "users" => $perm[0]->access_users, "system" => $perm[0]->access_system, "bill" => $perm[0]->access_pays,
		        "template" => $perm[0]->access_templates, "ticket" => $perm[0]->access_tickets, "sms" => $perm[0]->access_sms,
		        "reports" => $perm[0]->access_reports,
		        "v" => $global->version, "st" => $global->status, "map" => $key,
		        "lv" => $global->license, "company" => $global->company,
		        "status_factel"=>$status_factel,"languages"=>$languages,"global"=>$global,
		        'permissions' => $perm->first(),
		    );
		
		    if (Auth::user()->level == 'ad')
		        @setcookie("hcmd", 'kR2RsakY98pHL', time() + 7200, "/", "", 0, true);

		    return  $dataTable->render('cron.index', $permissions);
		} else
			return Redirect::to('admin');
	
	}
	
	public function fireCron($id)
	{
	
	    $cron = CronJob::find($id);
		
		Artisan::queue("$cron->command");
		
		return Reply::success("Cron fired successfully. Please wait while queue is processed.");
	}
	
}
