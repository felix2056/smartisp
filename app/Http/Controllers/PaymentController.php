<?php

namespace App\Http\Controllers;

use App\Classes\Reply;
use App\DataTables\PaymentDataTable;
use App\Http\Requests\Payment\StoreRequest;
use App\Http\Requests\Payment\UpdateRequest;
use App\libraries\Helpers;
use App\models\Client;
use App\models\GlobalSetting;
use App\models\PaymentNew;
use App\models\Transaction;
use App\models\WalletPayment;
use App\Service\CommonService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\View;
use Illuminate\Validation\ValidationException;
use Yajra\DataTables\Facades\DataTables;
use App\models\BillCustomer;
class PaymentController extends Controller
{
	public function __construct()
	{
		$this->middleware(function ($request, $next) {
			$this->username = auth()->user()->username;
			$this->userId = auth()->user()->id;
			return $next($request);
		});
	}
    /**
     * @param Request $request
     * @param $clientId
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function create(Request $request, $clientId)
	{
		return view('billing.create-payment', ['clientId' => $clientId]);
	}

    /**
     * @param Request $request
     * @param $clientId
     * @return mixed
     * @throws \Exception
     */
    public function postList(Request $request, $clientId)
	{
		$payments = PaymentNew::join('clients', 'clients.id', '=', 'payment_news.client_id')
		->select('payment_news.id', 'payment_news.way_to_pay', 'payment_news.date', 'payment_news.amount', 'payment_news.commentary')
		->where('payment_news.client_id', $clientId);

		return Datatables::of($payments)
		->addColumn('action', function ($row) {

			$html='';
			if(PermissionsController::hasAnyRole('pagos_editar')){
				$html.='<a href="javascript:;" onclick="editPayment(\''.$row->id.'\')" title="Edit"><span class="glyphicon glyphicon-edit"></span></a>&nbsp;';
			}
			if(PermissionsController::hasAnyRole('pagos_eliminar')){
				$html.=' <a href="javascript:;" onclick="deletePayment(\''.$row->id.'\')" title="Remove"><span class="glyphicon glyphicon-trash"></span></a>';
			}
			if($html==''){
				$html='Sin permisos';
			}

			return $html;
		})
		->editColumn('date', function ($row) {
			return Carbon::parse($row->date)->format('m/d/Y');
		})
		->rawColumns(['action'])
		->make(true);
	}

    /**
     * @param StoreRequest $request
     * @return array|string[]
     * @throws ValidationException
     */
    public function store(StoreRequest $request)
	{
        if($request->has('id_pago') && $request->id_pago) {
            $payment = PaymentNew::where('id_pago', $request->id_pago)->first();

            if($payment) {
                $validator = \Validator::make([], []);
                $validator->getMessageBag()->add('id_pago', 'The id pago has already been taken.');
                throw new ValidationException($validator);
            }
        }

		DB::beginTransaction();
		
		// Get client details
		$client = Client::find($request->client_id);
		$clientBalance = round($client->wallet_balance, 2);
		
		// Add new payment
		$payment = new  PaymentNew();
		$payment->way_to_pay = $request->way_to_pay;
		$payment->date = Carbon::parse($request->date)->format('Y-m-d');
		$payment->amount = $request->amount;
		$payment->memo = $request->memo;
		
		if($request->has('id_pago')) {
			$payment->id_pago = $request->id_pago;
		}
		
		$payment->commentary = $request->commentary;
		$payment->note = $request->note;
		$payment->client_id = $request->client_id;
		$payment->num_bill = '-';
		$payment->received_by = \auth()->user()->id;
		$payment->save();
		
		// Add transactions
		$transaction = new Transaction();
		$transaction->client_id = $request->client_id;
		$transaction->amount = $request->amount;// store amount on the basis of adjustable amount
		$transaction->account_balance = round($clientBalance + $request->amount, 2);
		$transaction->category = 'payment';
		$transaction->quantity = 1;
		$transaction->description = $request->commentary;
		$transaction->date = Carbon::parse($request->date)->format('Y-m-d');
		$transaction->save();
		
		// update wallet balance by the amount
		$client->wallet_balance = round($clientBalance + $request->amount, 2);
		$client->save();
		
		$paymentId = $payment->id;
		CommonService::log("#$paymentId Pago agregado: ", $this->username, 'success' , $this->userId, $client->id);
		DB::commit();

		return Reply::success('Payment Successfully added.');
	}
    /**
     * @param StoreRequest $request
     * @return array|string[]
     * @throws ValidationException
     */
    /*public function store(StoreRequest $request)
	{
        if($request->id_pago != '') {
            $payment = PaymentNew::where('id_pago', $request->id_pago)->first();

            if($payment) {
                $validator = \Validator::make([], []);
                $validator->getMessageBag()->add('id_pago', 'The id pago has already been taken.');
                throw new ValidationException($validator);
            }

        }

		DB::beginTransaction();

        //Find invoice
		$invoices = BillCustomer::where('client_id', $request->client_id)
            // ->whereMonth('start_date', Carbon::now()->month)
		->where('status', 3)
		->orderBy('total_pay', 'asc')->get();

        // Get client details
		$client = Client::with('billing_settings')
		->find($request->client_id);

		// This is required for maintaining the client balance in transactions
		$clientBalance = round($client->balance, 2);

        // Maintain client account balance
		$client->balance = round($client->balance + $request->amount, 2);
		$client->save();

		// check if there are invoices
		if($invoices->count() > 0) {

		    // Check if client balance is greater or equal to 0. if o or greater then 0 that means here we need to
            //pay all the unpaid invoices.
		    if($client->balance >= 0) {

                $advancePayment = $request->amount;
                foreach($invoices as $key => $invoice) {

                    // Calculate total amount client paying
                    $totalClientPaying = round(((float) $request->amount) + (float) $client->adjustable_amount, 2);

                    // Check if amount client paying is more then invoice total payment or not
                    if($totalClientPaying >= round($invoice->total_pay, 2)) {

                        // Add new payment
                        $payment = new  PaymentNew();
                        $payment->way_to_pay = $request->way_to_pay;
                        $payment->date = Carbon::parse($request->date)->format('Y-m-d');
                        $payment->amount = round(abs($invoice->total_pay) - abs($client->adjustable_amount), 2);// Subtract adjustable amount from invoice total payment

                        if($request->has('id_pago')) {
                            $payment->id_pago = $request->id_pago;
                        }

                        $payment->commentary = $request->commentary;
                        $payment->note = $request->note;
                        $payment->client_id = $request->client_id;
                        $payment->num_bill = (!empty($invoice)) ? $invoice->num_bill : '-';
                        $payment->received_by = \auth()->user()->id;
                        $payment->save();

                        // Add transactions for the payment
                        $transaction = new Transaction();
                        $transaction->client_id = $request->client_id;

                        $transaction->amount = round(abs($invoice->total_pay) - abs($client->adjustable_amount), 2);// Subtract adjustable amount from invoice total payment

                        $transaction->account_balance = ($clientBalance - $client->adjustable_amount) + $invoice->total_pay;//Calculate account balance for track the transaction and cross verification
                        $transaction->category = 'payment';
                        $transaction->quantity = 1;
                        $transaction->description = $request->commentary;
                        $transaction->date = Carbon::parse($request->date)->format('Y-m-d');
                        $transaction->save();

                        $invoice->status = 1;
                        $invoice->paid_on = Carbon::now()->format('Y-m-d');

                        if (Carbon::createFromFormat('Y-m-d', $invoice->period)->lessThan(Carbon::now())) {
                            $invoice->status = 4;
                        }

                        $invoice->save();

                        // calculate advance payment
                        $advancePayment = round((float) $advancePayment - (float) $payment->amount, 2);

                        // Recalculate client balance
                        $clientBalance = ($clientBalance - $client->adjustable_amount) + $invoice->total_pay;

                        $client->adjustable_amount = 0;
                    }
                }

                // set adjustable amount to be 0
                $client->adjustable_amount = 0;

                if($advancePayment > 0) {
                    // Add new payment
                    $payment = new  PaymentNew();
                    $payment->way_to_pay = $request->way_to_pay;
                    $payment->date = Carbon::parse($request->date)->format('Y-m-d');
                    $payment->amount = round($advancePayment, 2);// Save if there any advance payment
                    $payment->memo = $request->memo;
                    $payment->commentary = $request->commentary;
                    $payment->note = $request->note;

                    if($request->has('id_pago')) {
                        $payment->id_pago = $request->id_pago;
                    }

                    $payment->client_id = $request->client_id;
                    $payment->received_by = \auth()->user()->id;
                    $payment->save();

                    // Add transactions
                    $transaction = new Transaction();
                    $transaction->client_id = $request->client_id;
                    $transaction->amount = round($advancePayment, 2);// Save if there any advance payment
                    $transaction->account_balance = $clientBalance + $advancePayment;//Calculate account balance for track the transaction and cross verification
                    $transaction->category = 'payment';
                    $transaction->quantity = 1;
                    $transaction->description = $request->commentary;
                    $transaction->date = Carbon::parse($request->date)->format('Y-m-d');
                    $transaction->save();

                    // if advance payment then add that into adjustable amount so next time when invoice get paid then
                    // this amount can be used
                    $client->adjustable_amount = round($advancePayment, 2);;
                }

                $client->save();

                // If all the invoices get paid then activate the services
                foreach($client->service as $service) {
                    if($service->status == 'de') {
                        $clientServiceController = new ClientServiceController();
                        $request = new Request([
                            'id'   => $service->id,
                        ]);
                        $ok = $clientServiceController->postBanService($request, $service->id);
//                        if(json_decode($ok->getContent())[0]->msg == 'unbanned') {
//                            $service->status = 'ac';
//                            $service->save();
//                        }
                    }
                }
            }
		    else {
                // Calculate total client paying
                $totalClientPaying = ((float) $request->amount) + (float) $client->adjustable_amount;

                foreach($invoices as $invoice) {
                	// check if client total paying is more then invoice total pay
                    if($totalClientPaying >= $invoice->total_pay) {
	
                    	
                    	// here are two different scenarios so we are doing this. in 1 case $checkAdjustable is going to positive and in another negative
	                    $checkAdjustable = round((float) $totalClientPaying - (float) $invoice->total_pay, 2);
	                    $checkAdjustableAmount  = round($checkAdjustable - (float) $client->adjustable_amount, 2);
	
	                    $clientBalance = round($clientBalance +  $invoice->total_pay, 2);

                        // if client have already adjustment amount then amount should be request amount otherwise invoice total pay
	                    // adjustable amount and request amount are the actual amount that client is going to pay
//                        $amount = ($client->adjustable_amount > 0) ? round($request->amount, 2) :$invoice->total_pay;//in one condition this is working
                        $amount = ($checkAdjustableAmount < 0) ? round($request->amount, 2) :$invoice->total_pay;//in one condition this is working
//                        $amount = $invoice->total_pay;//another condition this solution working
                        
                        // Add new payment
                        $payment = new  PaymentNew();
                        $payment->way_to_pay = $request->way_to_pay;
                        $payment->date = Carbon::parse($request->date)->format('Y-m-d');
                        $payment->amount = $amount;
                        $payment->memo = $request->memo;

                        if($request->has('id_pago')) {
                            $payment->id_pago = $request->id_pago;
                        }

                        $payment->commentary = $request->commentary;
                        $payment->note = $request->note;
                        $payment->client_id = $request->client_id;
                        $payment->num_bill = (!empty($invoice)) ? $invoice->num_bill : '-';
                        $payment->received_by = \auth()->user()->id;
                        $payment->save();

                        // Add transactions
                        $transaction = new Transaction();
                        $transaction->client_id = $request->client_id;
                        $transaction->amount = $amount;// store amount on the basis of adjustable amount
                        $transaction->account_balance = $clientBalance;
                        $transaction->category = 'payment';
                        $transaction->quantity = 1;
                        $transaction->description = $request->commentary;
                        $transaction->date = Carbon::parse($request->date)->format('Y-m-d');
                        $transaction->save();

                        $invoice->status = 1;
                        $invoice->paid_on = Carbon::now()->format('Y-m-d');

                        if(Carbon::createFromFormat('Y-m-d', $invoice->period)->lessThan(Carbon::now())) {
                            $invoice->status = 4;
                        }

                        $invoice->save();
	                    
                        // Recalculate total client paying
                        $totalClientPaying = round((float) $totalClientPaying - (float) $invoice->total_pay, 2);
                        // Set client adjustable amount by the remaining total client paying
                    }
                }

                // Calculate adjustable amount
                $adjustedAmount  = round($totalClientPaying - (float) $client->adjustable_amount, 2);

                // Save adjustable amount here the remaining total client paying amount is the adjustable amount
                $client->adjustable_amount = $totalClientPaying;
                $client->save();

                // Check if adjusted amount is greater then 0 that means there we need to add payment entry
                if($adjustedAmount > 0) {
                    // Add new payment
                    $payment = new  PaymentNew();
                    $payment->way_to_pay = $request->way_to_pay;
                    $payment->date = Carbon::parse($request->date)->format('Y-m-d');
                    $payment->amount = $adjustedAmount;//Store request amount
                    $payment->memo = $request->memo;

                    if($request->has('id_pago')) {
                        $payment->id_pago = $request->id_pago;
                    }

                    $payment->commentary = $request->commentary;
                    $payment->note = $request->note;
                    $payment->client_id = $request->client_id;
                    $payment->received_by = \auth()->user()->id;
                    $payment->num_bill = (!empty($invoice)) ? $invoice->num_bill : '-';
                    $payment->save();

                    // Add transactions
                    $transaction = new Transaction();
                    $transaction->client_id = $request->client_id;
                    $transaction->amount = $adjustedAmount;
                    $transaction->account_balance = $clientBalance + $adjustedAmount; // Subtract request amount from client balance here $client balance always be in negative
                    $transaction->category = 'payment';
                    $transaction->quantity = 1;
                    $transaction->description = $request->commentary;
                    $transaction->date = Carbon::parse($request->date)->format('Y-m-d');
                    $transaction->save();
                }

            }
		}
		else {
		        // When user have balance in his/her account and adding payment then payment will added as advance payment
                // Add new payment
                $payment = new  PaymentNew();
                $payment->way_to_pay = $request->way_to_pay;
                $payment->date = Carbon::parse($request->date)->format('Y-m-d');
                $payment->amount = round($request->amount, 2);// Save if there any advance payment
                $payment->memo = $request->memo;

                if($request->has('id_pago')) {
                    $payment->id_pago = $request->id_pago;
                }

                $payment->commentary = $request->commentary;
                $payment->note = $request->note;
                $payment->client_id = $request->client_id;
                $payment->received_by = \auth()->user()->id;
                $payment->save();

                // Add transactions
                $transaction = new Transaction();
                $transaction->client_id = $request->client_id;
                $transaction->amount = round($request->amount, 2);// Save if there any advance payment
                $transaction->account_balance = $clientBalance + $request->amount;//Calculate account balance for track the transaction and cross verification
                $transaction->category = 'payment';
                $transaction->quantity = 1;
                $transaction->description = $request->commentary;
                $transaction->date = Carbon::parse($request->date)->format('Y-m-d');
                $transaction->save();

                // if advance payment then add that into adjustable amount so next time when invoice get paid then
                // this amount can be used
                $client->adjustable_amount = round((float) $client->adjustable_amount + (float) $request->amount, 2);

        }

        $suspend = $client->suspend_client;

		// If client balance is less then 0 then deactivate all the services
		if((float)$client->balance < 0) {
            if(now()->startOfDay()->greaterThan($suspend->expiration)) {
                foreach ($client->service as $service) {
                    $service->status = 'de';
                    $service->save();
                }
            }
		}

		$client->save();

		DB::commit();

		return Reply::success('Payment Successfully added.');
	}*/

    /**
     * @param Request $request
     * @param $paymentId
     * @return \Illuminate\Contracts\Foundation\Application|\Illuminate\Contracts\View\Factory|\Illuminate\Contracts\View\View
     */
    public function edit(Request $request, $paymentId)
	{
		$payment = PaymentNew::find($paymentId);
		return view('billing.edit-payment', ['clientId' => $request->client_id, 'payment' => $payment]);
	}

    /**
     * @param UpdateRequest $request
     * @param $id
     * @return array|string[]
     * @throws ValidationException
     */
    public function update(UpdateRequest $request, $id)
	{
        if($request->has('id_pago')) {
            $payment = PaymentNew::where('id_pago', $request->id_pago)->where('id', '!=', $id)->first();

            if($payment) {
                $validator = \Validator::make([], []);
                $validator->getMessageBag()->add('id_pago', 'The id pago has already been taken.');
                throw new ValidationException($validator);
            }

        }

		DB::beginTransaction();

		$payment = PaymentNew::find($id);

		$payment->way_to_pay = $request->way_to_pay;
		$payment->date = Carbon::parse($request->date)->format('Y-m-d');
		$payment->memo = $request->memo;
		$payment->id_pago = $request->id_pago;
		$payment->commentary = $request->commentary;
		$payment->note = $request->note;
		$payment->client_id = $request->client_id;

		$payment->save();

		CommonService::log("#$id Pago actualizado: ", $this->username, 'success' , $this->userId, $payment->client_id);
		DB::commit();

		return Reply::success('Payment Successfully updated.');
	}

    /**
     * @param Request $request
     * @param $id
     * @return array|string[]
     */
    public function delete(Request $request, $id)
	{
		DB::beginTransaction();
		$payment = PaymentNew::find($id);

		$client = Client::find($payment->client_id);
		$client->balance = round($client->balance - $payment->amount, 2);

		if(!is_null($payment->num_bill)) {
			$invoice = BillCustomer::where('num_bill', $payment->num_bill)->first();

			if($invoice) {
				$invoice->status = 3;
				$invoice->save();

                $unpaidInvoicesSum = BillCustomer::where('client_id', $client->id)->where('status', 3)->get()->sum('total_pay');

                $client->adjustable_amount = round(round(abs($unpaidInvoicesSum) - abs($client->balance) , 2) , 2);

                if($client->adjustable_amount < 0) {
                    $client->adjustable_amount = 0;
                }
			}
		} else {
            $client->adjustable_amount = 0;
        }

        $client->save();

        // delete transaction
		$transaction = Transaction::select('id')
		->where([
			'client_id' => $client->id,
			'amount' => $payment->amount,
			'date' => $payment->date,
			'category' => 'payment'
		])->first();

		if($transaction) {
			$transaction->delete();
		}

		PaymentNew::destroy($id);
		$nameClient = $client->name;
		CommonService::log("#$id Pago eliminado: $nameClient", $this->username, 'success' , $this->userId, $client->id);
		DB::commit();

		return Reply::success('Payment Successfully deleted.');
	}

    /**
     * @param PaymentDataTable $dataTable
     * @return \Illuminate\Http\RedirectResponse|mixed
     */
    public function getIndex(PaymentDataTable $dataTable)
	{
		$id = Auth::user()->id;
		$level = Auth::user()->level;
		$perm = DB::table('permissions')->where('user_id','=',$id)->get();
		$access = $perm[0]->access_clients;
        //control permissions only access super administrator (sa)
		if($level=='ad' || $access == true)
		{
			$global = GlobalSetting::all()->first();

			$GoogleMaps = Helpers::get_api_options('googlemaps');

			if (count($GoogleMaps)>0) {
				$key = $GoogleMaps['k'];
			} else {
				$key = 0;
			}

			$payments = PaymentNew::all();

			$cash = $payments->where('way_to_pay', 'Cash');
			$bankTransfer = $payments->where('way_to_pay', 'Bank Transfer');
			$payPal = $payments->where('way_to_pay', 'PayPal');
			$stripe = $payments->where('way_to_pay', 'Stripe');
			$directoPago = $payments->where('way_to_pay', 'Directo Pago');
            $payvalida = $payments->where('way_to_pay', 'Pay Valida');
			$other = $payments->where('way_to_pay', 'Other');
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
			])->get();
			
			
			$data['total'] = [
				'quantity' => $walletPayments->count() + $cash->count() + $bankTransfer->count() + $payPal->count() + $stripe->count() + $other->count(),
				'total' => $cash->sum('amount') + $bankTransfer->sum('amount') + $payPal->sum('amount') + $stripe->sum('amount') + $other->sum('amount') ,
			];
			
			
			$data['Wallet'] = [
				'quantity' => $walletPayments->count(),
				'total' => $walletPayments->sum('amount'),
			];
			
			
			$permissions = array("clients"=> $perm[0]->access_clients, "plans" => $perm[0]->access_plans, "routers" => $perm[0]->access_routers,
				"users" => $perm[0]->access_users, "system" => $perm[0]->access_system, "bill" => $perm[0]->access_pays,
				"template" => $perm[0]->access_templates, "ticket" => $perm[0]->access_tickets, "sms" => $perm[0]->access_sms,
				"reports" => $perm[0]->access_reports,
				"v" => $global->version, "st" => $global->status, "map" => $key,
				"lv" => $global->license, "company" => $global->company,
                'permissions' => $perm->first(),
                // menu options
				'data' => $data, 'global' => $global,
			);

			if(Auth::user()->level=='ad')
				@setcookie("hcmd", 'kR2RsakY98pHL', time()+7200,"/","",0, true);

			return $dataTable->render('payments.index',$permissions);
		}
		else
			return Redirect::to('admin');
	}

    /**
     * @param Request $request
     * @return mixed
     */
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

        $global = GlobalSetting::all()->first();

        if($date) {
            $payments = PaymentNew::whereNotNull('client_id')->whereBetween('payment_news.date', [$from, $to])->get();
        } else {
            $payments = PaymentNew::whereNotNull('client_id')->get();
        }

        $cash = $payments->where('way_to_pay', 'Cash');
        $bankTransfer = $payments->where('way_to_pay', 'Bank Transfer');
        $payPal = $payments->where('way_to_pay', 'PayPal');
        $stripe = $payments->where('way_to_pay', 'Stripe');
        $directoPago = $payments->where('way_to_pay', 'Directo Pago');
        $payvalida = $payments->where('way_to_pay', 'Pay Valida');
        $other = $payments->where('way_to_pay', 'Other');
	
	    $walletPayments = WalletPayment::
	    selectRaw('amount')->with([
		    'client' => function($query) {
			    $query->select('id', 'name');
		    }, 'received'
	    ]);
	
	    if($date) {
		    $walletPayments = $walletPayments->whereDate('created_at', '>=', $from)->whereDate('created_at', '<=', $to);
	    }
	
	    $walletPayments = $walletPayments->get();
        
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
        
        
        $data['total'] = [
            'quantity' => $walletPayments->count() + $cash->count() + $bankTransfer->count() + $payPal->count() + $stripe->count() + $directoPago->count() + $payvalida->count() + $other->count(),
            'total' => $cash->sum('amount') + $bankTransfer->sum('amount') + $payPal->sum('amount') + $stripe->sum('amount') + $directoPago->sum('amount') + $payvalida->sum('amount') + $other->sum('amount'),
        ];
	
	
	    $data['Wallet'] = [
		    'quantity' => $walletPayments->count(),
		    'total' => $walletPayments->sum('amount'),
	    ];
	    
        $view = view('payments/list', compact('data', 'global'))->render();

        return Reply::dataOnly(['view' => $view]);
    }
}
