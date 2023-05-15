<?php
namespace App\Http\Controllers;

/** All Paypal Details class **/

use App\models\BillCustomer;
use App\models\Client;
use App\models\PaymentNew;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;
use PayPal\Api\Amount;
use PayPal\Api\Item;
use PayPal\Api\ItemList;
use PayPal\Api\Payer;
use PayPal\Api\Payment;
use PayPal\Api\PaymentExecution;
use PayPal\Api\RedirectUrls;
use PayPal\Api\Transaction;
use PayPal\Auth\OAuthTokenCredential;
use PayPal\Rest\ApiContext;
use App\Traits\PayPalSettings;

class PaypalController extends BaseController
{
    use PayPalSettings;

    private $_api_context;
    private $paypal_conf = null;
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->setPayPalConfigs();

        /** setup PayPal api context **/
        $paypal_conf = Config::get('paypal');

        $this->_api_context = new ApiContext(new OAuthTokenCredential($paypal_conf['client_id'], $paypal_conf['secret']));
        $this->_api_context->setConfig($paypal_conf['settings']);
        $this->pageTitle = 'Paypal';

        $this->paypal_conf = $paypal_conf;
    }

    /**
     * Show the application paywith paypalpage.
     *
     * @return \Illuminate\Http\Response
     */
    public function payWithPaypal()
    {
        return view('paywithpaypal', $this->data);
    }

    /**
     * Store a details of payment with paypal.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function paymentWithpaypal(Request $request, $invoiceId)
    {
        $invoice = BillCustomer::findOrFail($invoiceId);
        //        \Session::put(,'Connection timeout');

        $payer = new Payer();
        $payer->setPaymentMethod('paypal');

        $item_1 = new Item();

        $item_1->setName('Payment for invoice #'.$invoice->num_bill) /** item name **/
        ->setCurrency('USD')
        ->setQuantity(1)
        ->setPrice($invoice->total_pay); /** unit price **/

        $item_list = new ItemList();
        $item_list->setItems(array($item_1));

        $amount = new Amount();
        $amount->setCurrency('USD')
        ->setTotal($invoice->total_pay);

        $transaction = new Transaction();
        $transaction->setAmount($amount)
            ->setItemList($item_list)
            ->setDescription('Payment for invoice #'. $invoice->num_bill);

        $redirect_urls = new RedirectUrls();
        $redirect_urls->setReturnUrl(route('paypal.status')) /** Specify return URL **/
        ->setCancelUrl(route('paypal.status'));

        $payment = new Payment();
        $payment->setIntent('Sale')
            ->setPayer($payer)
            ->setRedirectUrls($redirect_urls)
            ->setTransactions(array($transaction));

        try {

            $payment->create($this->_api_context);
        } catch (\PayPal\Exception\PPConnectionException $ex) {
            if (\Config::get('app.debug')) {
                \Session::put('error','Connection timeout');
                return \redirect('portal/bills');
                /** echo "Exception: " . $ex->getMessage() . PHP_EOL; **/
                /** $err_data = json_decode($ex->getData(), true); **/
                /** exit; **/
            } else {
                \Session::put('error','Some error occur, sorry for inconvenient');
                return \redirect('portal/bills');
                /** die('Some error occur, sorry for inconvenient'); **/
            }
        }

        foreach($payment->getLinks() as $link) {
            if($link->getRel() == 'approval_url') {
                $redirect_url = $link->getHref();
                break;
            }
        }

        /** add payment ID to session **/
        Session::put('paypal_payment_id', $payment->getId());
        Session::put('invoice_id', $invoice->id);

        //Save details in database and redirect to paypal

        if(isset($redirect_url)) {
            /** redirect to paypal **/
            return Redirect::away($redirect_url);
        }

        \Session::put('error','Unknown error occurred');
        return \redirect('portal/bills');;
    }

    public function getPaymentStatus(Request $request)
    {
        /** Get the payment ID before session clear **/
        $payment_id = Session::get('paypal_payment_id');
        $invoice_id = Session::get('invoice_id');

        /** clear the session payment ID **/
        Session::forget('paypal_payment_id');
        if (empty($request->PayerID) || empty($request->token)) {
            \Session::put('error','Payment failed');
            return \redirect('portal/bills');
        }
        $payment = Payment::get($payment_id, $this->_api_context);
        /** PaymentExecution object includes information necessary **/
        /** to execute a PayPal account payment. **/
        /** The payer_id is added to the request query parameters **/
        /** when the user is redirected from paypal back to your site **/
        $execution = new PaymentExecution();
        $execution->setPayerId($request->PayerID);
        /**Execute the payment **/
        $result = $payment->execute($execution, $this->_api_context);

        if ($result->getState() == 'approved') {

            /** it's all right **/
            /** Here Write your database logic like that insert record or value in database if you want **/

            //Find invoice
            $invoice= BillCustomer::find(\session()->get('invoice_id'));

            // Add new payment
            $payment = new  PaymentNew();
            $payment->way_to_pay = 'PayPal';
            $payment->date = Carbon::parse($request->date)->format('Y-m-d');
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
            $transaction->account_balance = $client->wallet_balance;
            $transaction->category = 'payment';
            $transaction->quantity = 1;
            $transaction->date = Carbon::parse($request->date)->format('Y-m-d');
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

            Session::put('success','Payment success');
            return \redirect('portal/bills');
        }
        Session::put('error','Payment failed');

       return \redirect('portal/bills');
    }

}
