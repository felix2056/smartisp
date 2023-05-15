<?php
namespace App\Http\Controllers;

/** All Paypal Details class **/

use App\libraries\CheckUser;
use App\models\BillCustomer;
use App\models\Client;
use App\models\GlobalSetting;
use App\models\PaymentNew;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\View;

class PayuController extends BaseController
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {

    }

    /**
     * Show the application paywith paypalpage.
     *
     * @return \Illuminate\Http\Response
     */
    public function paymentWithPayu(Request $request, $invoiceId)
    {

	    $user = CheckUser::isLogin();

        if ($user == 1)
            return Redirect::to('/');

        $client = Client::where('email', '=', $user)->orWhere('dni', '=', $user)->get();
	    $settings = GlobalSetting::all()->first();
	    $invoice = BillCustomer::findOrFail($invoiceId);

	    $merchantId = $settings['payu_merchant_id'];
        $apiKey = $settings['payu_api_key'];
        $accountId = $settings['payu_account_id'];
        $payu_mode = $settings['payu_mode'];
        if($payu_mode == 'sandbox') {
            $payu_url = "https://sandbox.checkout.payulatam.com/ppp-web-gateway-payu/";
        } else {
            $payu_url = "https://checkout.payulatam.com/ppp-web-gateway-payu/";
        }
        $referenceCode = "SmartIsp".rand(123, 9999999).rand(111111, 9999999);
        $amount = $invoice['total_pay'];
        $currency = $settings->nmoney;

        $signature = md5($apiKey.'~'.$merchantId.'~'.$referenceCode.'~'.$amount.'~'.$currency);
	    $data = array(
            "user" => $user,
            "name" => $client[0]->name,
            "email" => $client[0]->email,
            "company" => $settings->company,
            "photo" => $client[0]->photo,
            'merchantId' => $merchantId,
            'apiKey' => $apiKey,
            'accountId' => $accountId,
            'referenceCode' => $referenceCode,
            'amount' => $amount,
            'currency' => $currency,
            'signature' => $signature,
            'payu_url' => $payu_url,
            'invoiceId' => $invoiceId,
            'payu_mode' => $payu_mode
        );

        $contents = View::make('payu.index', $data);
        $response = Response::make($contents, 200);
        $response->header('Expires', 'Tue, 1 Jan 1980 00:00:00 GMT');
        $response->header('Cache-Control', 'no-store, no-cache, must-revalidate, post-check=0, pre-check=0');
        $response->header('Pragma', 'no-cache');
        return $response;
    }

	public function payuresponse()
	{
		$response = $_REQUEST;
		$description = explode('-', $response['description']); // Getting the invoice id from description
		$invoice = BillCustomer::findOrFail($description[1]);

		$settings = GlobalSetting::all()->first();
        $payu_mode = $settings['payu_mode'];

		$response['current_datetime'] = date('Y-m-d H:i:s');
		$response['payment_mode_enabled'] = $payu_mode;
		$data_final = array_merge($response);
		$data_final = json_encode($response).PHP_EOL;

		if($response['lapResponseCode'] == 'APPROVED' && $invoice->status == 3){ // do the process only if it is APPROVED

			$total_paid = $response['TX_VALUE']; // Total paid through PayU

			if($total_paid < $invoice->total_pay){
				\Session::put('error','Some error occur, sorry for inconvenient');
				return \redirect('portal/bills');
			}

			// Add new payment
	        $payment = new  PaymentNew();
	        $payment->way_to_pay = 'PayU';
	        $payment->date = Carbon::now()->format('Y-m-d');
	        $payment->amount = $invoice->total_pay;
	        $payment->client_id = $invoice->client_id;
	        $payment->num_bill = $invoice->num_bill;
            $payment->received_by = \auth()->user()->id;
	        $payment->save();

	        // Get client details
	        $client = Client::with('billing_settings')->find($invoice->client_id);

	        // Add transactions
	        $transaction = new \App\models\Transaction();
	        $transaction->client_id = $invoice->client_id;
	        $transaction->amount = $invoice->total_pay;
	        $transaction->account_balance = $client->wallet_balance;
	        $transaction->category = 'payment';
	        $transaction->quantity = 1;
	        $transaction->date = Carbon::now()->format('Y-m-d');
	        $transaction->save();

	        if($invoice) {
	            // Maintain client account balance
	            $client->balance = round($client->balance + $invoice->total_pay, 2);
	            $client->status = 'ac';
	            if((float)$client->balance < 0) {
	                $client->status = 'de';
	            }
	            $client->save();

	            $totalPaymentAmount = PaymentNew::where('client_id', $invoice->client_id)
	                ->where('num_bill', $invoice->num_bill)
	                ->get()->sum('amount');

	            if($totalPaymentAmount >= $invoice->total_pay ) {
	                $invoice->status = 1;
	                $invoice->paid_on = Carbon::now()->format('Y-m-d');
	            }

	            if(Carbon::createFromFormat('Y-m-d', $invoice->period)->lessThan(Carbon::now())) {
	                $invoice->status = 4;
	            }
	            $invoice->save();
	        }

	        \Session::put('success','Payment success, it will be reflected on your account soon.');
			return \redirect('portal/bills');

		} elseif ($response['lapResponseCode'] == 'APPROVED' && $invoice->status != 3){ //
			\Session::put('success','Payment success, it should be reflected on your account soon.');
			return \redirect('portal/bills');
		} else {
			\Session::put('error','Some error occur, sorry for inconvenient');
			return \redirect('portal/bills');
		}
    }

    /*
    This will be called automatically from payu on the confirmation of the payment.
    */
	public function payuconfirmation()
	{
		$response = $_REQUEST;
		$description = explode('-', $response['description']); // Getting the invoice id from description
		$invoice = BillCustomer::findOrFail($description[1]);

		$settings = GlobalSetting::all()->first();
        $payu_mode = $settings['payu_mode'];

		$response['current_datetime'] = date('Y-m-d H:i:s');
		$response['payment_mode_enabled'] = $payu_mode;
		$data_final = array_merge($response);
		$data_final = json_encode($response).PHP_EOL;
		$fp = fopen(base_path('resources/views/payu/').'payulog.txt', 'a');
		fwrite($fp, $data_final);

		if($response['lapResponseCode'] == 'APPROVED' && $invoice->status == 3){ // do the process only if it is APPROVED

			$total_paid = $response['TX_VALUE']; // Total paid through PayU

			if($total_paid < $invoice->total_pay){
				\Session::put('error','Some error occur, sorry for inconvenient');
				return \redirect('portal/bills');
			}

			// Add new payment
	        $payment = new  PaymentNew();
	        $payment->way_to_pay = 'PayU';
	        $payment->date = Carbon::now()->format('Y-m-d');
	        $payment->amount = $invoice->total_pay;
	        $payment->client_id = $invoice->client_id;
	        $payment->num_bill = $invoice->num_bill;
            $payment->received_by = \auth()->user()->id;
	        $payment->save();

	        // Get client details
	        $client = Client::with('billing_settings')->find($invoice->client_id);

	        // Add transactions
	        $transaction = new \App\models\Transaction();
	        $transaction->client_id = $invoice->client_id;
	        $transaction->amount = $invoice->total_pay;
	        $transaction->account_balance = $client->wallet_balance;
	        $transaction->category = 'payment';
	        $transaction->quantity = 1;
	        $transaction->date = Carbon::now()->format('Y-m-d');
	        $transaction->save();

	        if($invoice) {
	            // Maintain client account balance
	            $client->balance = round($client->balance + $invoice->total_pay, 2);
	            $client->status = 'ac';

	            if((float)$client->balance < 0) {
	                $client->status = 'de';
	            }
	            $client->save();

	            $totalPaymentAmount = PaymentNew::where('client_id', $invoice->client_id)
	                ->where('num_bill', $invoice->num_bill)
	                ->get()->sum('amount');

	            if($totalPaymentAmount >= $invoice->total_pay ) {
	                $invoice->status = 1;
	                $invoice->paid_on = Carbon::now()->format('Y-m-d');
	            }

	            if(Carbon::createFromFormat('Y-m-d', $invoice->period)->lessThan(Carbon::now())) {
	                $invoice->status = 4;
	            }
	            $invoice->save();
	        }

	        //\Session::put('success','Payment success');
			//return \redirect('portal/bills');
			die;

		} elseif ($response['lapResponseCode'] == 'APPROVED' && $invoice->status != 3){ //
			//\Session::put('success','Payment success, it will be reflected on your account soon.');
			//return \redirect('portal/bills');
			die;
		} else {
			//\Session::put('error','Some error occur, sorry for inconvenient');
			//return \redirect('portal/bills');
			die;
		}
    }

	/*
	  * It is in no use but kept it for reference, don't remove it
	 */
	public function payuresponseSavedCode()
	{
		$response = $_REQUEST;
		$description = explode('-', $response['description']); // Getting the invoice id from description
		$invoice = BillCustomer::findOrFail($description[1]);
		$response['current_datetime'] = date('Y-m-d H:i:s');
		$data_final = array_merge($response);
		$data_final = json_encode($response) . PHP_EOL;
		$fp = fopen(base_path('resources/views/payu/') . 'payulog.txt', 'a');
		fwrite($fp, $data_final);
		dd($response);
		if ($response['lapResponseCode'] == 'APPROVED' && $invoice->status == 3) { // do the process only if it is APPROVED
			$total_paid = $response['TX_VALUE']; // Total paid through PayU
			if ($total_paid < $invoice->total_pay) {
				\Session::put('error', 'Some error occur, sorry for inconvenient');
				return \redirect('portal/bills');
			}
			// Add new payment
			$payment = new  PaymentNew();
			$payment->way_to_pay = 'PayU';
			$payment->date = Carbon::now()->format('Y-m-d');
			$payment->amount = $invoice->total_pay;
			$payment->client_id = $invoice->client_id;
			$payment->num_bill = $invoice->num_bill;
            $payment->received_by = \auth()->user()->id;
			$payment->save();
			// Get client details
			$client = Client::with('billing_settings')->find($invoice->client_id);
			// Add transactions
			$transaction = new \App\models\Transaction();
			$transaction->client_id = $invoice->client_id;
			$transaction->amount = $invoice->total_pay;
			$transaction->account_balance = $client->balance + $invoice->total_pay;
			$transaction->category = 'payment';
			$transaction->quantity = 1;
			$transaction->date = Carbon::now()->format('Y-m-d');
			$transaction->save();
			if ($invoice) {
				// Maintain client account balance
				$client->balance = round($client->balance + $invoice->total_pay, 2);
				$client->status = 'ac';
				if ((float)$client->balance < 0) {
					$client->status = 'de';
				}
				//$client->save();
				$totalPaymentAmount = PaymentNew::where('client_id', $invoice->client_id)
					->where('num_bill', $invoice->num_bill)
					->get()
					->sum('amount');
				if ($totalPaymentAmount >= $invoice->total_pay) {
					$invoice->status = 1;
					$invoice->paid_on = Carbon::now()->format('Y-m-d');
				}
				if (Carbon::createFromFormat('Y-m-d', $invoice->period)->lessThan(Carbon::now())) {
					$invoice->status = 4;
				}
				$invoice->save();
			}
			\Session::put('success', 'Payment success');
			return \redirect('portal/bills');
		}
		elseif ($response['lapResponseCode'] == 'APPROVED' && $invoice->status != 3) { //
			\Session::put('success', 'Payment success, it will be reflected on your account soon.');
			return \redirect('portal/bills');
		}
		else {
			\Session::put('error', 'Some error occur, sorry for inconvenient');
			return \redirect('portal/bills');
		}
	}

}
