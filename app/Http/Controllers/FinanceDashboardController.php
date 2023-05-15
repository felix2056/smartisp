<?php

namespace App\Http\Controllers;

use App\Classes\Reply;
use App\libraries\Helpers;
use App\models\BillCustomer;
use App\models\GlobalSetting;
use App\models\PaymentNew;
use App\models\Transaction;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\View;

class FinanceDashboardController extends BaseController
{
//metodo para ingresar a finance dashboard
    public function getIndex()
    {
        $id = Auth::user()->id;
        $level = Auth::user()->level;
        $perm = DB::table('permissions')->where('user_id','=',$id)->get();
        $access = $perm[0]->access_clients;
        //control permissions only access super administrator (sa)
        if($level=='ad' || $access == true)
        {
            $global = GlobalSetting::first();

            $GoogleMaps = Helpers::get_api_options('googlemaps');

            if (count($GoogleMaps)>0) {
                $key = $GoogleMaps['k'];
            } else {
                $key = 0;
            }

            $currentMonthInvoices = BillCustomer::whereMonth('release_date', \Carbon\Carbon::now()->month)->whereYear('release_date', \Carbon\Carbon::now()->year)->get();
            $lastMonthInvoices = BillCustomer::whereMonth('release_date', \Carbon\Carbon::now()->subMonth(1)->month)->whereYear('release_date', \Carbon\Carbon::now()->subMonth(1)->year)->get();


            $currentMonth = Transaction::whereMonth('date', \Carbon\Carbon::now()->month)->whereYear('date', \Carbon\Carbon::now()->year)->get();
            $lastMonth = Transaction::whereMonth('date', \Carbon\Carbon::now()->subMonth(1)->month)->whereYear('date', \Carbon\Carbon::now()->subMonth(1)->year)->get();
            $transactions = Transaction::all();
            $payments = PaymentNew::all();
            $invoices = BillCustomer::all();

            $permissions = array("clients"=> $perm[0]->access_clients, "plans" => $perm[0]->access_plans, "routers" => $perm[0]->access_routers,
                "users" => $perm[0]->access_users, "system" => $perm[0]->access_system, "bill" => $perm[0]->access_pays,
                "template" => $perm[0]->access_templates, "ticket" => $perm[0]->access_tickets, "sms" => $perm[0]->access_sms,
                "reports" => $perm[0]->access_reports,
                "v" => $global->version, "st" => $global->status, "map" => $key,
                "lv" => $global->license, "company" => $global->company,
                'permissions' => $perm->first(),
                // menu options
                'transactions' => $transactions,
                'payments' => $payments, 'invoices' => $invoices, 'currentMonth' => $currentMonth, 'lastMonth' => $lastMonth, 'currentMonthInvoices' => $currentMonthInvoices, 'lastMonthInvoices' => $lastMonthInvoices, 'global' => $global,
            );



            if(Auth::user()->level=='ad')
                @setcookie("hcmd", 'kR2RsakY98pHL', time()+7200,"/","",0, true);

            $contents = View::make('finance.index',$permissions);
            $response = Response::make($contents, 200);
            $response->header('Expires', 'Tue, 1 Jan 1980 00:00:00 GMT');
            $response->header('Cache-Control', 'no-store, no-cache, must-revalidate, post-check=0, pre-check=0');
            $response->header('Pragma', 'no-cache');
            return $response;
        }
        else
            return Redirect::to('admin');
    }

    public function financeDashboardStats(Request $request)
    {
        $searchString = $request->date;

        if(!isset($searchString)) {
            $searchString = Carbon::now()->startOfMonth()->format('d-m-Y') . ' | ' . Carbon::now()->endOfMonth()->format('d-m-Y');
        }

        $string = explode('|', $searchString);

        $date1 = $string[0];
        $date2 = $string[1];

        $date1 = str_replace('/', '-', $date1);
        $date2 = str_replace('/', '-', $date2);

        $from = date("Y-m-d", strtotime($date1));
        $to = date("Y-m-d", strtotime($date2));

        $this->currentMonthInvoices = BillCustomer::whereDate('release_date', '>=', $from)->whereDate('release_date', '<=', $to)->get();
        $this->lastMonthInvoices = BillCustomer::whereDate('release_date', '>=', Carbon::parse($from)->subMonth()->format('Y-m-d'))->whereDate('release_date', '<=', Carbon::parse($to)->subMonth()->format('Y-m-d'))->get();


        $this->currentMonth = Transaction::whereDate('date', '>=', $from)->whereDate('date', '<=', $to)->get();
        $this->lastMonth = Transaction::whereDate('date', '>=', Carbon::parse($from)->subMonth()->format('Y-m-d'))->whereDate('date', '<=', Carbon::parse($to)->subMonth()->format('Y-m-d'))->get();

//        $this->transactions = Transaction::select('total')->where('category', 'payment')->get()->sum('total');

        $view = view('finance/finance-stat',  $this->data)->render();

        return Reply::dataOnly(['view' => $view]);

    }
}
