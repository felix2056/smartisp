<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddNumbillColumnInPaymentsnews extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('payment_news', function (Blueprint $table) {
            $table->string('num_bill')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('payment_news', function (Blueprint $table) {
            if (Schema::hasColumn('payment_news', 'num_bill')) {
                $table->removeColumn('num_bill');
            }
        });
    }
}
