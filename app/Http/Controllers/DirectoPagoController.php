<?php
namespace App\Http\Controllers;

use Illuminate\Support\Facades\Crypt;
use App\ClientPayment;
use App\Helper\Reply;
use App\Invoice;
use App\libraries\Burst;
use App\libraries\Chkerr;
use App\libraries\CheckUser;
use App\libraries\CountClient;
use App\libraries\GetPlan;
use App\libraries\Helpers;
use App\libraries\Mikrotik;
use App\libraries\Mkerror;
use App\libraries\RocketCore;
use App\libraries\RouterConnect;
use App\libraries\Slog;
use App\models\BillCustomer;
use App\models\Client;
use App\models\ClientService;
use App\models\ControlRouter;
use App\models\GlobalSetting;
use App\models\PaymentNew;
use App\models\radius\Radgroupcheck;
use App\models\radius\Radusergroup;
use App\Payment;
use App\PaymentGatewayCredentials;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Validator;
use URL;
use Session;
use Redirect;

use Stripe\Charge;
use Stripe\Customer;
use Stripe\Stripe;
use App\Traits\StripeSettings;

class DirectoPagoController extends BaseController
{

    private function getPaymentsUrl($path = '') {
        $global = GlobalSetting::first();
        if($global->directo_pago_mode == "sandbox") {
            $baseURL = 'https://api-sbx.dlocalgo.com/v1/payments';
        } else {
            $baseURL = 'https://api.dlocalgo.com/v1/payments';
        }

        return $baseURL . '/' . $path;
    }

    private function sendRequest($url, $fields = null) {
        $global = GlobalSetting::first();
        $apiKey = $global->directo_pago_api_key;
        $apiSecret = $global->directo_pago_secret_key;

        $ch = curl_init($url);
        curl_setopt(
            $ch,
            CURLOPT_HTTPHEADER,
            array(
                'Content-Type: application/json', // if the content type is json
                "Authorization: Bearer {$apiKey}:{$apiSecret}" // if you need token in header
            )
        );
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 2);

        if(!empty($fields)) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, $fields);
        }

        $result = curl_exec($ch);
        curl_close($ch);
        return json_decode($result, true);
    }

    public function paymentWithDirectoPago($invoiceId) {

        $user = CheckUser::isLogin();

        if ($user == 1) return Redirect::to('/');

        $client = Client::where('email', '=', $user)->orWhere('dni', '=', $user)->first();
        $invoice = BillCustomer::findOrFail($invoiceId);
        $global = GlobalSetting::first();
        $apiKey = $global->directo_pago_api_key;
        $apiSecret = $global->directo_pago_secret_key;
        $cryptInvoice = Crypt::encryptString($invoiceId);

        try {

            $postData = [
                'amount' => $invoice->total_pay,
                'currency' => 'USD',
                'success_url' => route('portal.bill'),
                'back_url' => route('portal.bill'),
                'notification_url' => route('directopagoresponse', ['invoiceId' => $cryptInvoice]),
                'payer' => [
                    'email' => $client->email
                ]
            ];

            $url = $this->getPaymentsUrl();
            $fields = json_encode($postData);
            $response = $this->sendRequest($url, $fields);

            Log::debug(json_encode($response));

            if(isset($response['redirect_url'])) {

                $invoice->payment_id = $response['id'];
                $invoice->save();

                return redirect($response['redirect_url']);
            }

            \Session::put('error','Some error occur, sorry for inconvenient');
        } catch (\Exception $ex) {
            throw $ex;
            \Session::put('error','Some error occur, sorry for inconvenient');
        }

        return redirect(route('portal.bill'));
    }

    public function paymentNotification(Request $request, $invoiceId) {
        try {
            $invoiceId = Crypt::decryptString($invoiceId);
        } catch(\Exception $e) {
            return response()->json(['success' => false]);
        }

        $global = GlobalSetting::first();
        $apiKey = $global->directo_pago_api_key;
        $apiSecret = $global->directo_pago_secret_key;

        $invoice = BillCustomer::findOrFail($invoiceId);

        $payment_id = $request->get('payment_id');
        $url = $this->getPaymentsUrl($payment_id);
        $response = $this->sendRequest($url);

        if($response['status'] == 'PAID') {
            DB::beginTransaction();
            
            $payment = new  PaymentNew();
            $payment->way_to_pay = 'Directo Pago';
            $payment->date = Carbon::now()->format('Y-m-d');
            $payment->amount = $invoice->total_pay;
            $payment->client_id = $invoice->client_id;
            $payment->num_bill = $invoice->num_bill;
            $payment->save();

            // Get client details
            $client = Client::with('billing_settings')
                ->find($invoice->client_id);

            // Add transactions
            $transaction = new \App\models\Transaction();
            $transaction->client_id = $invoice->client_id;
            $transaction->amount = $invoice->total_pay;
            $transaction->account_balance = $client->balance + $invoice->total_pay*100;
            $transaction->category = 'payment';
            $transaction->quantity = 1;
            $transaction->date = Carbon::now()->format('Y-m-d');
            $transaction->save();

            if($invoice) {
                // Maintain client account balance
                $client->balance = round($client->balance + $invoice->total_pay, 2);

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

                if((float)$client->balance >= 0) {
                    foreach($client->service as $service) {

                        if($service->status == 'de') {
                            $clientServiceController = new ClientServiceController();
                            $request = new Request([
                                'id'   => $service->id,
                            ]);

                            $ok = $clientServiceController->postBanService($request, $service->id);

                            $service->status = 'ac';
                            $service->save();
                        }

                    }
                }


                DB::commit();
            }
        }

        return response()->json(['success' => true]);
    }
}
