<?php

namespace App\Http\Controllers;

use App\Classes\Reply;
use App\DataTables\TransactionDataTable;
use App\libraries\Helpers;
use App\models\BillCustomer;
use App\models\Client;
use App\models\GlobalSetting;
use App\models\SuspendClient;
use App\models\Transaction;
use App\models\WalletPayment;
use App\Service\CommonService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\View;
use Yajra\DataTables\Facades\DataTables;
class TransactionController extends BaseController
{
	public function __construct()
	{
		$this->middleware(function ($request, $next) {
			$this->username = auth()->user()->username;
			$this->userId = auth()->user()->id;
			return $next($request);
		});
	}
    //metodo para listar clientes
    public function postList(Request $request, $clientId)
    {
        $transactions = DB::table('transactions')
        ->join('clients', 'clients.id', '=', 'transactions.client_id')
        ->select(
            'transactions.id',
            'transactions.created_at',
            DB::raw("(CASE WHEN category='service' THEN amount ELSE '-' END) as debit"),
            DB::raw("(CASE WHEN category='payment' THEN amount WHEN category='refund' THEN amount ELSE '-'  END) as credit"),
            'transactions.account_balance',
            'transactions.description',
            'transactions.category',
            'transactions.quantity',
            'transactions.date'
        )
        ->where('client_id', $clientId);

        return Datatables::of($transactions)
        ->addColumn('action', function ($row) {
            $html='';

            if(PermissionsController::hasAnyRole('tran_facturacion_editar')){
                $html.='<a href="javascript:;" onclick="editTransaction(\'' . $row->id . '\')" title="Edit"><span class="glyphicon glyphicon-edit"></span></a>&nbsp;';
            }

            if(PermissionsController::hasAnyRole('tran_facturacion_eliminar')){
                $html.='<a href="javascript:;" onclick="deleteTransaction(\'' . $row->id . '\')" title="Remove"><span class="glyphicon glyphicon-trash"></span></a>';
            }

            if($html==''){
                $html='Sin permisos';
            }

            return $html;
        })
        ->editColumn('date', function ($row) {
            return Carbon::parse($row->date)->format('Y-m-d');
        })
        ->rawColumns(['action'])
        ->make(true);
    }

    public function edit(Request $request, $id)
    {
        $transaction = Transaction::find($id);
        return view('billing.edit-transaction', ['transaction' => $transaction]);
    }

    public function delete(Request $request, $id)
    {
        Transaction::destroy($id);

        return Reply::success(__('messages.transactionsuccessfullydeleted'));
    }

    public function toBillView($id)
    {
        return view('billing.to-charge', ['id' => $id]);
    }

    public function toBill($id)
    {
        // if unpaid create based on request params
        $invoice = 0;
        $startDate = '';
        $endDate = '';
        $data = [];

        $toBillDate = request()->ToBill['toBillDate'];
        $toTxDate = request()->ToBill['toBillTransactionDate'];
        $period = request()->ToBill['period'];

        $toBill = Carbon::parse($toBillDate);
        $toBillMonth = Carbon::parse($toBillDate)->month;

        // get latest invoice
        $invoice = BillCustomer::select('id', 'start_date', 'release_date', 'expiration_date', 'created_at')->where('client_id', $id)->where('recurring_invoice', 'no')->latest()->first();

        // show charged if invoice is already created for the month
        if ($invoice !== null) {
            $invoice_expire_date = Carbon::parse($invoice->expiration_date);

            if($toBill->lessThanOrEqualTo($invoice_expire_date)) {
                $view = '<div class="alert alert-warning" role="alert">'.__('messages.alltheservicesofthisPeriodhavebeenCharged').'</div>';
                return Reply::success(['view' => $view, 'invoice_data' => $data]);
            }

            $invoice_start_month = Carbon::parse($invoice->start_date)->month;
            $invoice_expire_month = Carbon::parse($invoice->expiration_date)->month;

            // create array of months
            $invoice_month_arr = [];

            for ($i = $invoice_start_month; $i < $invoice_expire_month; $i++) {
                array_push($invoice_month_arr, $i);
            }

            if (in_array($toBillMonth, $invoice_month_arr)) {
                $view = '<div class="alert alert-warning" role="alert">'.__('app.alltheservicesofthisPeriodhavebeenCharged').'</div>';
                return Reply::success(['view' => $view, 'invoice_data' => $data]);
            }
        }

        $clients = Client::with(['service', 'service.plan', 'service.router'])->where('id', $id)->first();

        $billing_date = $clients->billing_settings->billing_date;

        $rows = '';
        $total = 0;
        $totalPlanCost = 0;
        $totalPlanVat = 0;

        foreach($clients->service as $client) {
            $planCost = 0;
            // if ($toBill->greaterThanOrEqualTo($client->date_in)) {
            if ($invoice !== null) {
                $startDate = Carbon::parse($invoice->expiration_date)->addDay();
            }
            else {
                $startDate = $client->date_in;
            }

            if ($period > 1) {
                $endDate = $toBill->clone()->addMonth($period)->day($billing_date)->subDay();
            } else {
                $endDate = $toBill->clone()->endOfMonth();
                if ($billing_date > 1) {
                    if (Carbon::createFromFormat('d', $billing_date)->lessThanOrEqualTo(Carbon::now()) || $startDate->day == $billing_date) {
                        $endDate = $toBill->clone()->add(1, 'month')->day($billing_date);
                    }
                    else {
                        $endDate = $toBill->clone()->day($billing_date)->subDay();
                    }
                }
            }

            $startMonth = $startDate->month;
            $endMonth = $endDate->month;

            $currentDate = $startDate->clone();

            while ($currentDate->lessThanOrEqualTo($endDate)) {
                if ($currentDate->month === $startMonth || $currentDate->month === $endMonth) {
                    if ($startMonth === $endMonth) {
                        $no_of_days = $endDate->day - $startDate->day + 1;
                        $planCost += $client->plan->cost * ($no_of_days / $startDate->daysInMonth);
                    } else {
                        if ($currentDate->month === $startMonth) {
                            $no_of_days = $startDate->daysInMonth - $startDate->day + 1;
                            $days_in_month = $startDate->daysInMonth;
                        }
                        if ($currentDate->month === $endMonth) {
                            $no_of_days = $endDate->day - $endDate->clone()->startOfMonth()->day + 1;
                            $days_in_month = $endDate->daysInMonth;
                        }

                        if($endDate->diffInDays($startDate) >=30) {
                            $planCost += $client->plan->cost;
                        } else {
                            $planCost += $client->plan->cost * ($no_of_days / $days_in_month);
                        }

                    }
                } else {
                    $planCost += $client->plan->cost;
                }

                if($endDate->diffInDays($startDate) >=30) {
//                    $currentDate->endOfMonth()->add(1, 'month');
                    $currentDate->startOfMonth()->add(1, 'month');
                } else {
                    $currentDate->startOfMonth()->add(1, 'month');
                }
            }

            if($endDate->diffInDays($startDate) >= 30 && $period <= 1) {
                $planCost = round($client->plan->cost, 2);
            } else {
                $planCost = round($planCost, 2);
            }

            $planVAT = $client->plan->iva;
            $serviceTotal = $planCost + ($planCost * $planVAT) / 100;
            $total += $planCost + ($planCost * $planVAT) / 100;
            $totalPlanCost += $planCost;
            $totalPlanVat += $planVAT;

            $planDetails[] = [
                'start_date' => $startDate->format('Y-m-d'),
                'end_date' => $endDate->format('Y-m-d'),
                'plan_id' => $client->plan->id,
                'plan' => $client->plan,
                'plan_cost' => $planCost,
                'plan_vat' => $planVAT,
                'total' => $serviceTotal,
            ];

            $rows .= '<tr>
            <td>
            ' . $client->plan->name . '<br>' . $startDate->format('Y-m-d') . ' - ' . $endDate->format('Y-m-d') . '
            </td>
            <td>
            ' . number_format($planCost, 2) . '
            </td>
            <td>
            ' . $planVAT . ' %
            </td>
            <td>
            $ ' . number_format($serviceTotal, 2) . '
            </td>
            </tr>
            ';
        }

        $view = '<table class="table">
            <thead>
            <tr>
            <th>Description</th>
            <th>Price</th>
            <th>VAT</th>
            <th>Total</th>
            </tr>
            </thead>
            <tbody>'.$rows.'<tr>
            <td colspan="5" align="right">Total: <b>' . number_format($total, 2) . '</b></td>
            </tr></tbody>
            </table>';


        if(! $toTxDate && $toTxDate == '') {
            $toTxDate = $endDate->format('Y-m-d');
        }


        if($total>0){
            $data = [
                'client_id' => $id,
                'payment_date' => $toTxDate,
                'total' => $total,
                'start_date' => $startDate->format('Y-m-d'),
                'end_date' => $endDate->format('Y-m-d'),
                'quantity' => $period >= 1 ? $period : 1,
                'price' => $totalPlanCost,
                'totalVat' => round($totalPlanVat/$clients->service->count()),
                'planDetails' => $planDetails
            ];

        }else{
            $data = [];
            $view = '<div class="alert alert-warning" role="alert">'.__('app.alltheservicesofthisPeriodhavebeenCharged').'</div>';
        }

        return Reply::success(['view' => $view, 'invoice_data' => $data]);
    }

    public function toBillCreate()
    {
        $validator = Validator::make(request()->all(), [
            'client_id' => 'required',
            'payment_date' => 'required|date',
            'start_date' => 'required|date',
            'end_date' => 'required|date',
            'total' => 'required',
            'quantity' => 'required',
            'price' => 'required',
        ]);

        if ($validator->fails()) {
            return Reply::error('Transaction cannot be performed.');
        }
        DB::beginTransaction();
        // create invoice and transaction based on client settings
        $client = Client::with(['service', 'service.plan:id,name,iva,cost', 'billing_settings:id,client_id,billing_create_invoice,billing_auto_pay_invoice,billing_grace_period,billing_due_date'])
        ->where('id', request()->client_id)
        ->first();

        $gracePeriod = $client->billing_settings->billing_grace_period;
	
	    // set invoice cortado date for block service for this client
	    $cortadoDate = null;
	    
	    if($client->billing_settings) {
		    $cortadoDate = Carbon::createFromFormat('d', $client->billing_settings->billing_due_date)->format('Y-m-d');
	    }
	    
	    $invoice = BillCustomer::where('client_id', request()->client_id)->where('billing_type', 'recurring')->first();

	    if(!$invoice) {
		    $cortadoDate = Carbon::parse(request()->end_date)->addDays($client->billing_settings->billing_due_date)->format('Y-m-d');
	    }
	    
        // create invoice, transaction and update client's account balance
        $invoice_data = [
            'client_id' => request()->client_id,
            'total_pay' => request()->total,
            'num_bill' => CommonService::getBillNumber(),
            'release_date' => Carbon::now()->format('Y-m-d'),
            'period' => Carbon::parse(request()->end_date)->add('days', $gracePeriod)->format('Y-m-d'),
            'start_date' => request()->start_date,
            'expiration_date' => request()->end_date,
            'iva' => request()->totalVat,
            'cost' => request()->price,
            'billing_type' => 'recurring',
	        'cortado_date' => $cortadoDate
        ];
	    
	    $invoice_data['status'] = 3;
	    
        if ((float) $client->wallet_balance >= (float) request()->total) {
            $invoice_data['status'] = 2;
            $invoice_data['paid_on'] = request()->payment_date;
	        $client->wallet_balance -= request()->total;
        } else {
	        $client->balance = round($client->balance - request()->total, 2);
        }

        

        $client->save();

//        $clientSuspend = SuspendClient::where('client_id', $client->id)->first();

//        $clientSuspend->expiration = Carbon::createFromFormat('d', $client->billing_settings->billing_due_date)->format('Y-m-d');
//        $clientSuspend->save();

        $transaction_data = [
            'client_id' => request()->client_id,
            'amount' => request()->total,
            'category' => 'service',
            'date' => request()->payment_date,
            'quantity' => request()->quantity,
            'account_balance' => $client->wallet_balance,
            'description' => 'Generated bills',
        ];

        $transaction = $client->transactions()->create($transaction_data);

        $invoice = $client->invoices()->create($invoice_data);

        $invoice_items_data = [];
        foreach(request()->planDetails as $key => $item) {
            $invoice_items_data[$key] = [
                'bill_customer_id' => $invoice->id,
                'plan_id' => $item['plan_id'],
                'period_from' => $item['start_date'],
                'period_to' => $item['end_date'],
                'quantity' => 1,
                'unit' => 1,
                'price' => $item['plan_cost'],
                'iva' => $item['plan_vat'],
                'total' => $item['total'],
                'description' => $item['plan']['name'],
            ];
        }

        // create invoice_items
        $invoice->invoice_items()->insert($invoice_items_data);
	
	    if($invoice->status != 3) {
		    CommonService::addWalletPayment($client->id, $invoice_data['num_bill'], request()->total, \auth()->user()->id);
	    }

        $invoiceNumber = $invoice->num_bill;
	    CommonService::log("#$invoiceNumber Factura creada ", $this->username, 'success' , $this->userId);
        
        DB::commit();
        return Reply::success('Invoice created successfully.', ['account_balance' => round($client->balance, 2), 'wallet_balance' => round($client->wallet_balance, 2)]);
    }

    public function getBillNumber()
    {
        $globalSettings = GlobalSetting::first();

        if ($globalSettings->num_bill > 1) {
            $num =  $globalSettings->num_bill;

            $globalSettings->num_bill = $globalSettings->num_bill + 1;
            $globalSettings->save();

            return $num;
        }

        $num =  $globalSettings->num_bill;

        $globalSettings->num_bill = $globalSettings->num_bill + 1;
        $globalSettings->save();

        return $num;

    }

    //metodo para ingresar a planes
    public function getIndex(TransactionDataTable $dataTable)
    {
        $id = Auth::user()->id;
        $level = Auth::user()->level;
        $perm = DB::table('permissions')->where('user_id', '=', $id)->get();
        $access = $perm[0]->access_clients;
        //control permissions only access super administrator (sa)
        if ($level == 'ad' || $access == true) {
            $global = GlobalSetting::all()->first();

            $GoogleMaps = Helpers::get_api_options('googlemaps');

            if (count($GoogleMaps) > 0) {
                $key = $GoogleMaps['k'];
            } else {
                $key = 0;
            }

            $permissions = array("clients" => $perm[0]->access_clients, "plans" => $perm[0]->access_plans, "routers" => $perm[0]->access_routers,
                "users" => $perm[0]->access_users, "system" => $perm[0]->access_system, "bill" => $perm[0]->access_pays,
                "template" => $perm[0]->access_templates, "ticket" => $perm[0]->access_tickets, "sms" => $perm[0]->access_sms,
                "reports" => $perm[0]->access_reports,
                "v" => $global->version, "st" => $global->status, "map" => $key,
                "lv" => $global->license, "company" => $global->company,
                'permissions' => $perm->first(),
                // menu options
            );

            if (Auth::user()->level == 'ad') {
                @setcookie("hcmd", 'kR2RsakY98pHL', time() + 7200, "/", "", 0, true);
            }

            return $dataTable->render('transactions.index', $permissions);

        } else {
            return Redirect::to('admin');
        }

    }

    //metodo para listar clientes
    public function postLists(Request $request)
    {
        $date = $request->get('extra_search');

        if($date) {
            $string = explode('|', $date);

            $date1 = $string[0];
            $date2 = $string[1];

            $date1 = str_replace('/', '-', $date1);
            $date2 = str_replace('/', '-', $date2);

            $from = date("Y-m-d", strtotime($date1));
            $to = date("Y-m-d", strtotime($date2));
        }

        $transactions = DB::table('transactions')
        ->join('clients', 'clients.id', '=', 'transactions.client_id')
        ->select(
            'transactions.id',
            'transactions.created_at',
            DB::raw("(CASE WHEN category='service' THEN amount ELSE '-' END) as debit"),
            DB::raw("(CASE WHEN category='payment' THEN amount WHEN category='refund' THEN amount ELSE '-'  END) as credit"),
            'clients.name',
            'transactions.description',
            'transactions.category',
            'transactions.quantity',
            'transactions.date'
        );

        if($date) {
            $transactions = $transactions->whereBetween('transactions.date', [$from, $to]);
        }

        return Datatables::of($transactions)
        ->addColumn('action', function ($row) {

           $html='';
           if(PermissionsController::hasAnyRole('tran_finanzas_editar')){
            $html.='<a href="javascript:;" onclick="editTransaction(\'' . $row->id . '\')" title="Edit"><span class="glyphicon glyphicon-edit"></span></a>&nbsp;';
        }

        if(PermissionsController::hasAnyRole('tran_finanzas_eliminar')){
            $html.='<a href="javascript:;" onclick="deleteTransaction(\'' . $row->id . '\')" title="Remove"><span class="glyphicon glyphicon-trash"></span></a>';
        }

        if($html==''){
            $html='Sin permisos';
        }

        return $html;


    })
        ->editColumn('date', function ($row) {
            return Carbon::parse($row->date)->format('Y-m-d');
        })
        ->rawColumns(['action'])
        ->make(true);
    }

    public function cancelChargeView($id)
    {
        return view('billing.cancel-charge', ['id' => $id]);
    }

    public function cancelCharge($id)
    {
        $client = Client::find($id);
        $lastInvoice = $client->invoices()->latest()->first();

        if (!$lastInvoice) {
            return Reply::error('No last invoice found');
        }

        $transaction_data = [
            'client_id' => $client->id,
            'category' => 'refund',
            'date' => Carbon::now()->format('Y-m-d'),
            'quantity' => 1,
            'description' => 'Cancel last invoice',
        ];

//        if (count($lastInvoice->payments) === 0) {
//            $client->balance += $lastInvoice->total_pay;
//            $transaction_data['amount'] = $lastInvoice->total_pay;
//        } else {
            if (in_array($lastInvoice->status, [1, 2, 4])) {
//                $client->balance -= $lastInvoice->total_pay;
                $client->wallet_balance += $lastInvoice->total_pay;
                $transaction_data['amount'] = $lastInvoice->total_pay;
                
                WalletPayment::where('client_id', $client->id)->where('num_bill', $lastInvoice->num_bill)->delete();
            } else {
                $client->balance = round($client->balance + $lastInvoice->total_pay, 2);
                $transaction_data['amount'] = $lastInvoice->total_pay;
            }

            // remove payments
            foreach ($lastInvoice->payments as $payment) {
                $payment->delete();
            };
//        }

        $client->save();

        $transaction_data['account_balance'] = $client->wallet_balance;

        // create refund transaction
        $client->transactions()->create($transaction_data);

        // remove invoice
        $lastInvoice->delete();
	
	    CommonService::log("Factura cancelada ", $this->username, 'success' , $this->userId);

//        if (Carbon::parse($lastInvoice->start_date)->month === Carbon::now()->month) {
//            $clientSuspend = SuspendClient::where('client_id', $client->id)->first();
//
//            $clientSuspend->expiration = Carbon::now()->format('Y-m-d');
//            $clientSuspend->save();
//        }

        return Reply::success('Last charge cancelled successfully.', ['account_balance' => round($client->balance, 2), 'wallet_balance' => round($client->wallet_balance, 2)]);
    }


    public function filterTotals(Request $request)
    {
        $date = $request->extra_search;

        if($date) {
            $string = explode('|', $date);

            $date1 = $string[0];
            $date2 = $string[1];

            $date1 = str_replace('/', '-', $date1);
            $date2 = str_replace('/', '-', $date2);

            $from = date("Y-m-d", strtotime($date1));
            $to = date("Y-m-d", strtotime($date2));
        }

        if($date) {
            $transactions = Transaction::whereNotNull('client_id')->whereBetween('transactions.date', [$from, $to])->get();
        } else {
            $transactions = Transaction::whereNotNull('client_id')->get();
        }

        $credit = $transactions->where('category', 'payment');
        $debit = $transactions->where('category', 'service');

        $data['debit'] = [
            'quantity' => $debit->count(),
            'total' => $debit->sum('amount'),
        ];

        $data['credit'] = [
            'quantity' => $credit->count(),
            'total' => $credit->sum('amount'),
        ];

        $data['total'] = [
            'quantity' => $debit->count() + $credit->count(),
            'total' => $debit->sum('amount') - $credit->sum('amount'),
        ];

        $view = view('transactions/list', compact('data'))->render();

        return Reply::dataOnly(['view' => $view]);
    }
}
