<?php
namespace App\Http\Controllers;

use Illuminate\Support\Facades\Storage;
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

class PayValidaController extends BaseController
{
    public function paymentWithPayValida($invoiceId) {

        $user = CheckUser::isLogin();

        if ($user == 1) return Redirect::to('/');
        
        $client = Client::where('email', '=', $user)->orWhere('dni', '=', $user)->first();
        // $invoice = BillCustomer::findOrFail($request->get('invoiceId'));
        $invoice = BillCustomer::findOrFail($invoiceId);

        if(empty($invoice)) return redirect(route('portal.bill'));

        $global = GlobalSetting::first();
        $fixedHash = $global->pay_valida_fixed_hash;
        $merchantID = $global->pay_valida_merchant_id;

        $country = '345';
        $money = 'USD';
        $order = 'PV' . time() . $client->id;
        $tomorrow = new \DateTime('tomorrow');
        $checksum = hash('sha512', $client->email . $country . $order . $money . $invoice->total_pay . $fixedHash);

        try {
            $postData = [
                'merchant' => $merchantID,
                'iva' => "0",
                'money' => $money,
                'country' => intval($country),
                'order' => $order,
                'description' => "Bill {$invoice->num_bill} Period {$invoice->period}",
                'recurrent' => false,
                'email' => $client->email,
                'amount' => $invoice->total_pay,
                'expiration' => $tomorrow->format('d/m/Y'),
                'checksum' => $checksum,
            ];

            if($global->pay_valida_mode == "sandbox") {
                $url = 'https://api-test.payvalida.com/api/v3/porders';
            } else {
                $url = 'https://api.payvalida.com/api/v3/porders';
            }

            // for sending data as json type
            $fields = json_encode($postData);

            $curl = curl_init();

            curl_setopt_array($curl, array(
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => "",
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => "POST",
                CURLOPT_POSTFIELDS => $fields,
                CURLOPT_HTTPHEADER => array(
                    "Content-Type: application/json"
                ),
            ));

            $result = curl_exec($curl);
            curl_close($curl);

            $response = json_decode($result, true);
            Log::debug(json_encode($response));

            if(isset($response['DATA']) && $response['DATA']['checkout']) {

                $invoice->payment_id = $response['DATA']['PVordenID'];
                $invoice->save();
                
                return redirect('//' . $response['DATA']['checkout']);
            }

        } catch (\Exception $ex) {
            throw $ex;
            \Session::put('error','Some error occur, sorry for inconvenient');
        }

        \Session::put('error','Some error occur, sorry for inconvenient');
        return redirect(route('portal.bill'));
    }

    public function paymentNotification(Request $request) {
        $global = GlobalSetting::first();
        $fixedHash = $global->pay_valida_fixed_hash_notification;
        $checksum = hash('sha512', $request->get('po_id') . $request->get('status') . $fixedHash);
        if(empty($fixedHash) || $request->get('pv_checksum') == $checksum) {

            $invoice = BillCustomer::where(['payment_id' => $request->get('pv_po_id')])->first();

            if(!empty($invoice) && $request->get('status') == 'approved') {
                DB::beginTransaction();
                
                $payment = new  PaymentNew();
                $payment->way_to_pay = 'Pay Valida';
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

            return response()->json(['status' => 'OK']);
        }
        
        $resp = $request->all();
        $resp['my_checksum'] = $checksum;
        $resp['pay_valida_fixed_hash_notification'] = $fixedHash;
        $file = Storage::put( 'payValidaPaymentNotification.txt', json_encode($resp));
        return response()->json(['status' => 'Invalid checksum']);
    }
}
