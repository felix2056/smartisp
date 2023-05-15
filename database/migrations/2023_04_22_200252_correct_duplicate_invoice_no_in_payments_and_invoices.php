<?php

use App\models\BillCustomer;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CorrectDuplicateInvoiceNoInPaymentsAndInvoices extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        BillCustomer::select('id', 'num_bill', 'paid_on')
            ->chunkById(5, function ($invoices) {
            foreach ($invoices as $invoice) {
                // fetch payment for this invoice
                $payments = \App\models\PaymentNew::where('date', $invoice->paid_on)->where('num_bill', $invoice->num_bill)->get();

                // update invoice_num for the invoice by id as this the id is always unique
                $invoiceNum = $invoice->id;
                $invoice->num_bill = $invoiceNum;
                $invoice->save();

                // update num_bill one by one for each payment
                foreach($payments as $payment) {
                    $payment->num_bill = $invoiceNum;
                    $payment->save();

                }

            }
        });

        $lastInvoice = BillCustomer::latest('id')->first();

        // update global settings for further use
        $globalSetting = \App\models\GlobalSetting::first();

        $globalSetting->num_bill = $lastInvoice->num_bill;
        $globalSetting->save();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {

    }
}
