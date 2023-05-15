<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\models\BillingSettings;
class GenerateFacturaAutimatica extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $allB=BillingSettings::all();
        
        foreach ($allB as $billing) {
            $billing->billing_auto_pay_invoice='1';  
            $billing->save();         
        }
       
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
