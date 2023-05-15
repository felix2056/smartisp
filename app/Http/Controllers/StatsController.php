<?php

namespace App\Http\Controllers;

use App\libraries\Stats;
use App\models\GlobalSetting;
use App\models\PaymentRecord;
use App\models\Ticket;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\View;
use App\models\PaymentNew;
class StatsController extends BaseController
{
    public function __construct()
    {
//		$this->beforeFilter('auth');  //bloqueo de acceso
    }

    //metodo para ingresar a planes
    public function getIndex()
    {
        $id = Auth::user()->id;
        $level = Auth::user()->level;
        $perm = DB::table('permissions')->where('user_id', '=', $id)->get();
        $access = $perm[0]->access_reports;
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

            $contents = View::make('stats.index', $permissions);
            $response = Response::make($contents, 200);
            $response->header('Expires', 'Tue, 1 Jan 1980 00:00:00 GMT');
            $response->header('Cache-Control', 'no-store, no-cache, must-revalidate, post-check=0, pre-check=0');
            $response->header('Pragma', 'no-cache');
            return $response;
        } else
        return Redirect::to('admin');
    }


    //metod para obtener estadisticas de ingresos por internet
    public function getInternet()
    {

        $year = date('Y');
        $st = new Stats;

        $datos = $st->get_months_year($year, 'se');

        $global = GlobalSetting::all()->first();

        $data = array(
            'ene' => $datos[0],
            'feb' => $datos[1],
            'mar' => $datos[2],
            'abr' => $datos[3],
            'may' => $datos[4],
            'jun' => $datos[5],
            'jul' => $datos[6],
            'ago' => $datos[7],
            'sep' => $datos[8],
            'oct' => $datos[9],
            'nov' => $datos[10],
            'dic' => $datos[11],
            'money' => $global->nmoney
        );

        return Response::json($data);

    }

    // metodo para obtener informacion general economico
    public function getGeneral()
    {


        $year = date('Y');

        $st = new Stats;

        $ser = $st->get_months_year($year, 'se');
        // $in = $st->get_months_year($year, 'in');
        $ou = $st->get_months_year($year, 'ou');

        $global = GlobalSetting::all()->first();

        $data = array(
            'in_ene' => $ser[0],
            'in_feb' => $ser[1],
            'in_mar' => $ser[2],
            'in_abr' => $ser[3],
            'in_may' => $ser[4],
            'in_jun' => $ser[5],
            'in_jul' => $ser[6],
            'in_ago' => $ser[7],
            'in_sep' => $ser[8],
            'in_oct' => $ser[9],
            'in_nov' => $ser[10],
            'in_dic' => $ser[11],

            'ou_ene' => $ou[0],
            'ou_feb' => $ou[1],
            'ou_mar' => $ou[2],
            'ou_abr' => $ou[3],
            'ou_may' => $ou[4],
            'ou_jun' => $ou[5],
            'ou_jul' => $ou[6],
            'ou_ago' => $ou[7],
            'ou_sep' => $ou[8],
            'ou_oct' => $ou[9],
            'ou_nov' => $ou[10],
            'ou_dic' => $ou[11],

            't_ene' => round($ser[0] - $ou[0], 2),
            't_feb' => round($ser[1] - $ou[1], 2),
            't_mar' => round($ser[2] - $ou[2], 2),
            't_abr' => round($ser[3] - $ou[3], 2),
            't_may' => round($ser[4] - $ou[4], 2),
            't_jun' => round($ser[5] - $ou[5], 2),
            't_jul' => round($ser[6] - $ou[6], 2),
            't_ago' => round($ser[7] - $ou[7], 2),
            't_sep' => round($ser[8] - $ou[8], 2),
            't_oct' => round($ser[9] - $ou[9], 2),
            't_nov' => round($ser[10] - $ou[10], 2),
            't_dic' => round($ser[11] - $ou[11], 2),

            'money' => $global->nmoney
        );

        return Response::json($data);

    }

    //metodo para recuperar ganacias y gastos totales por año
    public function getPeryears()
    {

        $startDate = date('Y');
        $futureDate = date('Y', strtotime('+1 year', strtotime($startDate)));

        $st = new Stats;

        $global = GlobalSetting::all()->first();

        $ac_input = $st->get_per_years($startDate, 'se');

        $ac_out = $st->get_per_years($startDate, 'ou');

        $fu_input = $st->get_per_years($futureDate, 'se');

        $fu_out = $st->get_per_years($futureDate, 'ou');

        $data = array(

            "ac_input" => $ac_input,
            "ac_output" => $ac_out,
            "fu_input" => $fu_input,
            "fu_output" => $fu_out,
            "ac_year" => $startDate,
            "fu_year" => $futureDate,
            "money" => $global->nmoney
        );

        return Response::json($data);
    }

     //metodo para obtener la cantitad total de ingresos
    public function getTotalcounters_all()
    {

        $Tinputs = PaymentNew::select('id', 'amount', 'deleted_at')->whereNull('deleted_at')->get()->sum('amount');
        $Toutputs = DB::table('boxes')->where('type', '=', 'ou')->sum('amount');
        $global = GlobalSetting::all()->first();

        $total = round($Tinputs - $Toutputs, 2);

        $data = array(
            'success' => true,
            'total_in' => round($Tinputs, 2),
            'total_out' => round($Toutputs, 2),
            'total' => $total,
            'simbol' => $global->smoney
        );

        return Response::json($data);
    }


    // //metodo para obtener porcentajes de pagos por servicio y pendientes
    // public function getPayed_vieja()
    // {

    //     $in = PaymentRecord::where('type', 'se')->orWhere('type', 'in')->sum('amount');
    //     $out = PaymentRecord::where('type', 'ou')->sum('amount');
    //     $global = GlobalSetting::all()->first();

    //     $data = array(
    //         "prepay" => number_format($out, 2),
    //         "payed" => number_format($in, 2),
    //         "money" => $global->nmoney
    //     );

    //     return Response::json($data);

    // }

    //metodo para obtener porcentajes de pagos por servicio y pendientes
    public function getPayed(Request $request)
    {

//     $in = PaymentNew::select('id', 'amount', 'deleted_at')->whereNull('deleted_at')->get()->sum('amount');
//     $out = DB::table('boxes')->where('type', '=', 'ou')->sum('amount');
//     $global = GlobalSetting::all()->first();
//
//     $data = array(
//        "prepay" => number_format($out, 2),
//        "payed" => number_format($in, 2),
//        "money" => $global->nmoney
//    );
        $year = $request->get('year');
        $in = PaymentNew::select('id', 'amount', 'date', 'deleted_at')
            ->whereNull('deleted_at')
            ->where(DB::raw('YEAR(date)'), $year)
            ->orderBy('date')
            ->get()
            ->groupBy(function ($val) {
                return Carbon::parse($val->date)->format('n');
            });

        $out = DB::table('boxes')
            ->where('type', '=', 'ou')
            ->where(DB::raw('YEAR(date_reg)'), $year)
            ->get()
            ->groupBy(function ($val) {
            return Carbon::parse($val->date_reg)->format('n');
        });

        $incomeData = [];

        for( $i = 1; $i <=12; $i++) {
            if($in->contains(function ($value, $key) use($i) {
                return $key == $i;
            })) {
                $income = round($in[$i]->sum('amount'), 2);
                array_push($incomeData, $income);
            } else {
                array_push($incomeData, 0);
            }
        }
        $expenseData = [];
        for( $i = 1; $i <=12; $i++) {
            if($out->contains(function ($value, $key) use($i) {
                return $key == $i;
            })) {
                $expense = round($out[$i]->sum('amount'));
                array_push($expenseData, $expense);
            } else {
                array_push($expenseData, 0);
            }
        }

        $global = GlobalSetting::all()->first();

        $data = array(
            "income" => $incomeData,
            "expense" => $expenseData,
            "money" => $global->nmoney
        );
     return Response::json($data);

 }
 
    // Get ticket chart stats
    public function getTicketStats(Request $request)
    {
    	
        $year = $request->get('year');
		$resolved = Ticket::where(DB::raw('YEAR(created_at)'), $year)
			->where('status', 'resolved')
			->orderBy('created_at')
			->get()
			->groupBy(function ($val) {
				return Carbon::parse($val->created_at)->format('n');
			});
		
		$resolvedData = [];
		
	    for( $i = 1; $i <=12; $i++) {
		    if($resolved->contains(function ($value, $key) use($i) {
			    return $key == $i;
		    })) {
			    $resolve = $resolved[$i]->count();
			    array_push($resolvedData, $resolve);
			    
		    } else {
			    array_push($resolvedData, 0);
		    }
	    }
	    
		$new = Ticket::where(DB::raw('YEAR(created_at)'), $year)
			->where('status', 'new')
			->orderBy('created_at')
			->get()
			->groupBy(function ($val) {
				return Carbon::parse($val->created_at)->format('n');
			});
	
	    $newData = [];
	
	    for( $i = 1; $i <=12; $i++) {
		    if($new->contains(function ($value, $key) use($i) {
			    return $key == $i;
		    })) {
			    $resolve = $new[$i]->count();
			    array_push($newData, $resolve);
			
		    } else {
			    array_push($newData, 0);
		    }
	    }

	    $workInProgress = Ticket::where(DB::raw('YEAR(created_at)'), $year)
			->where('status', 'work_in_progress')
			->orderBy('created_at')
			->get()
			->groupBy(function ($val) {
				return Carbon::parse($val->created_at)->format('n');
			});
	
	    $workInProgressData = [];
	
	    for( $i = 1; $i <=12; $i++) {
		    if($workInProgress->contains(function ($value, $key) use($i) {
			    return $key == $i;
		    })) {
			    $resolve = $workInProgress[$i]->count();
			    array_push($workInProgressData, $resolve);
			
		    } else {
			    array_push($workInProgressData, 0);
		    }
	    }
//
	    $waitingOnCustomer = Ticket::where(DB::raw('YEAR(created_at)'), $year)
			->where('status', 'waiting_on_customer')
			->orderBy('created_at')
			->get()
			->groupBy(function ($val) {
				return Carbon::parse($val->created_at)->format('n');
			});
	
	
	    $waitingOnCustomerData = [];
	
	    for( $i = 1; $i <=12; $i++) {
		    if($waitingOnCustomer->contains(function ($value, $key) use($i) {
			    return $key == $i;
		    })) {
			    $resolve = $waitingOnCustomer[$i]->count();
			    array_push($waitingOnCustomerData, $resolve);
			
		    } else {
			    array_push($waitingOnCustomerData, 0);
		    }
	    }
//
	    $waitingOnAgent = Ticket::where(DB::raw('YEAR(created_at)'), $year)
			->where('status', 'waiting_on_agent')
			->orderBy('created_at')
			->get()
			->groupBy(function ($val) {
				return Carbon::parse($val->created_at)->format('n');
			});
	
	    $waitingOnAgentData = [];
	
	    for( $i = 1; $i <=12; $i++) {
		    if($waitingOnAgent->contains(function ($value, $key) use($i) {
			    return $key == $i;
		    })) {
			    $resolve = $waitingOnAgent[$i]->count();
			    array_push($waitingOnAgentData, $resolve);
			
		    } else {
			    array_push($waitingOnAgentData, 0);
		    }
	    }
//
        
        $data = array(
            "new" => $newData,
            "workInProgress" => $workInProgressData,
            "resolved" => $resolvedData,
            "waitingOnCustomer" => $waitingOnCustomerData,
            "waitingOnAgent" => $waitingOnAgentData,
        );
     return Response::json($data);

 }
}
