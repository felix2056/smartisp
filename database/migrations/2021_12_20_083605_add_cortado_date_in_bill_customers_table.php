<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCortadoDateInBillCustomersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('bill_customers', function (Blueprint $table) {
            $table->date('cortado_date')->nullable();
        });
        
        // fetch all invoices of last 3 months so se can set coratdo date of them
	    $startDate = \Carbon\Carbon::now()->subMonths(3)->startOfMonth()->format('Y-m-d');
        \App\models\BillCustomer::with('client', 'client.billing_settings')
	        ->where('release_date' , '>=', $startDate)
	        ->whereNotNull('client_id')
	        ->chunk(50, function($invoices) {
	            foreach($invoices as $invoice) {
	                if($invoice->client->billing_settings) {
				        $billingDue = $invoice->client->billing_settings->billing_due_date;
				        // calculate cortado date
	                    $cortadoDate = \Carbon\Carbon::parse($invoice->release_date)->addDays($billingDue)->format('Y-m-d');
	                    $invoice->cortado_date = $cortadoDate;
	                    $invoice->save();
			        }
		        }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('bill_customers', function (Blueprint $table) {
            $table->dropColumn('cortado_date');
        });
    }
}
