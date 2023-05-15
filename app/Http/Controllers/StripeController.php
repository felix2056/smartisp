<?php
namespace App\Http\Controllers;

use App\ClientPayment;
use App\Helper\Reply;
use App\Invoice;
use App\models\BillCustomer;
use App\models\Client;
use App\models\PaymentNew;
use App\Payment;
use App\PaymentGatewayCredentials;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Validator;
use URL;
use Session;
use Redirect;

use Stripe\Charge;
use Stripe\Customer;
use Stripe\Stripe;
use App\Traits\StripeSettings;

class StripeController extends BaseController
{
    use StripeSettings;
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->setStripeConfigs();
        /** setup Stripe credentials **/
        Stripe::setApiKey(config('services.stripe.secret'));
        $this->pageTitle = 'Stripe';
    }

    /**
     * Store a details of payment with paypal.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function paymentWithStripe(Request $request, $invoiceId)
    {
        $invoice = BillCustomer::findOrFail($invoiceId);

        $tokenObject  = $request->get('token');
        $token  = $tokenObject['id'];
        $email  = $tokenObject['email'];

        try {
            $customer = Customer::create(array(
                'email' => $email,
                'source'  => $token
            ));

            $charge = Charge::create(array(
                'customer' => $customer->id,
                'amount'   => $invoice->total_pay*100,
                'currency' => 'USD'
            ));

        } catch (\Exception $ex) {
            \Session::put('error','Some error occur, sorry for inconvenient');
            return \redirect('portal/bills');
        }

        DB::beginTransaction();
        // Add new payment
        $payment = new  PaymentNew();
        $payment->way_to_pay = 'Stripe';
        $payment->date = Carbon::now()->format('Y-m-d');
        $payment->amount = $invoice->total_pay;
        $payment->client_id = $invoice->client_id;
        $payment->num_bill = $invoice->num_bill;
        $payment->received_by = \auth()->user()->id;
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

            /*$client->status = 'ac';

            if((float)$client->balance < 0) {
                $client->status = 'de';
            }*/

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

        \Session::put('success','Payment success');
        return \App\Classes\Reply::redirect(route('portal.bill'));
    }
}
