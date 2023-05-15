<?php

namespace App\Http\Controllers;
use App\Classes\Reply;
use App\models\GlobalSetting;
use App\models\User;
use App\models\WalletPayment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\View;
use App\models\PaymentNew;
use App\Http\Resources\ReportResource;
use App\models\Box;
use App\models\Client;
use App\models\BillCustomer;
use App\models\Transaction;
use Carbon\Carbon;

class ReportsController extends BaseController
{

    public function __construct()
    {
//        $this->beforeFilter('auth');  //bloqueo de acceso
    }

    public function getIndex()
    {
        $id = Auth::user()->id;
        $level = Auth::user()->level;
        $perm = DB::table('permissions')->where('user_id', '=', $id)->get();
        $access = $perm[0]->access_reports;
        //control permissions only access super administrator (sa)
        if ($level == 'ad' || $access == true) {


            $data = [
                'Cash',
                'Bank Transfer',
                'PayPal',
                'Stripe',
                'Directo Pago',
                'Other',
                'Total',
	            'Wallet'
            ];

            $global = GlobalSetting::all()->first();
            $admins = User::where('email' ,'!=', 'support@smartisp.us')->get();
            $permissions = array("clients" => $perm[0]->access_clients, "plans" => $perm[0]->access_plans, "routers" => $perm[0]->access_routers,
                "users" => $perm[0]->access_users, "system" => $perm[0]->access_system, "bill" => $perm[0]->access_pays,
                "template" => $perm[0]->access_templates, "ticket" => $perm[0]->access_tickets, "sms" => $perm[0]->access_sms,
                "reports" => $perm[0]->access_reports,
                "v" => $global->version, "st" => $global->status,
                "lv" => $global->license, "company" => $global->company,
                'permissions' => $perm->first(),
                // menu options
                'admins' => $admins,'data' => $data
            );

            if (Auth::user()->level == 'ad')
                setcookie("hcmd", 'kR2RsakY98pHL', time() + 7200, "/", "", 0, true);


            $contents = View::make('reports.index', $permissions);
            $response = Response::make($contents, 200);
            $response->header('Expires', 'Tue, 1 Jan 1980 00:00:00 GMT');
            $response->header('Cache-Control', 'no-store, no-cache, must-revalidate, post-check=0, pre-check=0');
            $response->header('Pragma', 'no-cache');
            return $response;
        } else
            return Redirect::to('admin');
    }


    //metodo para listar ingresos
    public function postList(Request $request)
    {

        $date = $request->get('extra_search');
        $admin = $request->get('admin');


        if (empty($date)) {

            $reports = PaymentNew::select('id', 'client_id', 'way_to_pay', 'date', 'amount', 'received_by')->whereNull('deleted_at')->with([
                'client' => function($query) {
                    $query->select('id', 'name');
                }, 'received'
            ])->get();

            $entries = Box::
                selectRaw('id,name,router_id,detail,type as way_to_pay,date_reg as date,amount')
                ->where('type', 'ou')
                ->with('router:id,name')->get();
            
//            $walletPayments = WalletPayment::selectRaw('id,client_id,"Wallet" as way_to_pay,created_at as date,amount,user_id')->with([
//	            'client' => function($query) {
//		            $query->select('id', 'name');
//	            }, 'received'
//            ])->get();

        } else {

            $string = explode('|', $date);

            $date1 = $string[0];
            $date2 = $string[1];

            $date1 = str_replace('/', '-', $date1);
            $date2 = str_replace('/', '-', $date2);

            $from = date("Y-m-d", strtotime($date1));
            $to = date("Y-m-d", strtotime($date2));


            $reports = PaymentNew::
                select('id', 'client_id', 'way_to_pay', 'date', 'amount', 'received_by')
                ->whereNull('deleted_at')
                ->whereBetween('date', array($from, $to))
                ->with([
                    'client' => function($query) {
                        $query->select('id', 'name');
                    }, 'received'
                ]);

                if($admin == 'all') {
                    $reports = $reports->get();
                } else {
                    $reports = $reports->where('received_by', $admin)->get();
                }
	
//	        $walletPayments = WalletPayment::selectRaw('id,client_id,"Wallet" as way_to_pay,created_at as date,amount,user_id')->with([
//		        'client' => function($query) {
//			        $query->select('id', 'name');
//		        }, 'received'
//	        ])->whereDate('created_at', '>=', $from)->whereDate('created_at', '<=', $to);
//
//	        if($admin == 'all') {
//		        $walletPayments = $walletPayments->get();
//	        } else {
//		        $walletPayments = $walletPayments->where('user_id', $admin)->get();
//	        }
            
            $entries = Box::
                selectRaw('id,name,router_id,detail,type as way_to_pay,date_reg as date,amount,user_id')
                ->whereBetween('date_reg', array($from, $to))
                ->where('type', 'ou')
                ->with('router:id,name', 'user');

                if($admin == 'all') {
                    $entries = $entries->get();
                } else {
                    $entries = $entries->where('user_id', $admin)->get();
                }
        }

        $data = collect();
        $data = $data->merge($reports)->merge($entries);

        return ReportResource::collection($data);
    }

    //metodo para recuperar montos de reportes
    public function postAmount(Request $request)
    {
        $date = $request->get('extra_search');
        $admin = $request->get('admin');

        $data = $this->calculateAmount($date, $admin);

        return Response::json($data);

    }

    //metodo para eliminar registros
    public function postDelete(Request $request)
    {
        $date = $request->get('extra_search');
        $typepay = $request->get('typepay');
        $id = $request->get('id');

        if ($typepay === 'ou') {
            $report = Box::find($id);

            $report->delete();
        }
        else {
            $payment = PaymentNew::find($id);

            $client = Client::find($payment->client_id);
            $client->balance -= $payment->amount;
            $client->save();

            if(!is_null($payment->num_bill)) {
                $invoice = BillCustomer::where('num_bill', $payment->num_bill)->first();

                $invoice->status = 3;
                $invoice->save();
            }

            // Add transactions
            $transaction = new Transaction();
            $transaction->client_id = $client->id;
            $transaction->amount = $payment->amount;
            $transaction->account_balance = $client->balance;
            $transaction->category = 'service';
            $transaction->quantity = 1;
            $transaction->date = Carbon::now()->format('Y-m-d');
            $transaction->save();

            PaymentNew::destroy($id);
        }


        $data = $this->calculateAmount($date);
        return Response::json(array_merge($data, array('msg' => 'success')));
    }

    public function calculateAmount($date, $admin)
    {
        if (empty($date)) {

            // $entries = PaymentRecord::where('type', '=', 'in')->orWhere('type', '=', 'se')->sum('amount');
            $entries = PaymentNew::select('id', 'amount', 'deleted_at')->whereNull('deleted_at')->sum('amount');
            $outs = Box::where('type', '=', 'ou')->sum('amount');
        } else {

            $string = explode('|', $date);

            $date1 = $string[0];
            $date2 = $string[1];

            $date1 = str_replace('/', '-', $date1);
            $date2 = str_replace('/', '-', $date2);

            $from = date("Y-m-d", strtotime($date1));
            $to = date("Y-m-d", strtotime($date2));

            $entries = PaymentNew::select('id', 'amount', 'deleted_at')
                ->whereNull('deleted_at')
                ->whereBetween('date', array($from, $to));

                if($admin == 'all') {
                    $entries = $entries->sum('amount');
                } else {
                    $entries = $entries->where('received_by', $admin)->sum('amount');
                }

            $outs = Box::where('type', '=', 'ou')
                ->whereBetween('date_reg', array($from, $to));

                if($admin == 'all') {
                    $outs = $outs->sum('amount');
                } else {
                    $outs = $outs->where('user_id', $admin)->sum('amount');
                }

        }

        $global = GlobalSetting::all()->first();
        $total = round($entries - $outs, 2);
        $data = array(
            'success' => true,
            'total_in' => round($entries, 2),
            'total_out' => round($outs, 2),
            'total' => $total,
            'simbol' => $global->smoney
        );

        return $data;
    }

    public function filterTotals(Request $request)
    {
        $date = $request->get('extra_search');
        $admin = $request->get('admin');
	
	    $reports = PaymentNew::select('id', 'client_id', 'way_to_pay', 'date', 'amount', 'received_by')
		    ->whereNull('deleted_at')
		    ->with([
			    'client' => function($query) {
				    $query->select('id', 'name');
			    }, 'received'
		    ]);
	    if (!empty($date)) {
		    $string = explode('|', $date);
		
		    $date1 = $string[0];
		    $date2 = $string[1];
		
		    $date1 = str_replace('/', '-', $date1);
		    $date2 = str_replace('/', '-', $date2);
		
		    $from = date("Y-m-d", strtotime($date1));
		    $to = date("Y-m-d", strtotime($date2));
		    
		    $reports = $reports->whereBetween('date', array($from, $to));
	    }
	    
	    if($admin == 'all') {
	        $reports = $reports->get();
	    } else {
		    $reports = $reports->where('received_by', $admin)->get();
	    }
	    
        $cash = $reports->where('way_to_pay', 'Cash');
        $bankTransfer = $reports->where('way_to_pay', 'Bank Transfer');
        $payPal = $reports->where('way_to_pay', 'PayPal');
        $stripe = $reports->where('way_to_pay', 'Stripe');
        $directoPago = $reports->where('way_to_pay', 'Directo Pago');
        $payvalida = $reports->where('way_to_pay', 'Pay Valida');
        $other = $reports->where('way_to_pay', 'Other');
        $data = [];

        $data['Cash'] = [
            'quantity' => $cash->count(),
            'total' => $cash->sum('amount'),
        ];

        $data['Bank Transfer'] = [
            'quantity' => $bankTransfer->count(),
            'total' => $bankTransfer->sum('amount'),
        ];

        $data['PayPal'] = [
            'quantity' => $payPal->count(),
            'total' => $payPal->sum('amount'),
        ];

        $data['Stripe'] = [
            'quantity' => $stripe->count(),
            'total' => $stripe->sum('amount'),
        ];

        $data['Directo Pago'] = [
            'quantity' => $directoPago->count(),
            'total' => $directoPago->sum('amount'),
        ];

        $data['Pay Valida'] = [
            'quantity' => $payvalida->count(),
            'total' => $payvalida->sum('amount'),
        ];

        $data['Other'] = [
            'quantity' => $other->count(),
            'total' => $other->sum('amount'),
        ];
	
        $walletPayments = WalletPayment::
        selectRaw('amount')->with([
	        'client' => function($query) {
		        $query->select('id', 'name');
	        }, 'received'
        ]);

        if(!empty($date)) {
        	$walletPayments = $walletPayments->whereDate('created_at', '>=', $from)->whereDate('created_at', '<=', $to);
        }
        
        if($admin == 'all') {
	        $walletPayments = $walletPayments->get();
        } else {
	        $walletPayments = $walletPayments->where('user_id', $admin)->get();
        }
        
        $data['Total'] = [
            'quantity' => $cash->count() + $bankTransfer->count() + $payPal->count() + $stripe->count() + $directoPago->count() + $payvalida->count() + $other->count() + $walletPayments->count(),
            'total' => $cash->sum('amount') + $bankTransfer->sum('amount') + $payPal->sum('amount') + $stripe->sum('amount') + $directoPago->sum('amount') + $payvalida->sum('amount') + $other->sum('amount'),
        ];
	
	
	    $data['Wallet'] = [
		    'quantity' => $walletPayments->count(),
		    'total' => $walletPayments->sum('amount'),
	    ];
	    
        $global = GlobalSetting::first();
        $view = view('reports/list', compact('data', 'global'))->render();

        return Reply::dataOnly(['view' => $view]);
    }

}
