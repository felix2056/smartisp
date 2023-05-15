<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddReceivedByColumnInPaymentNewsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('payment_news', function (Blueprint $table) {
            $table->unsignedInteger('received_by')->nullable();
            $table->foreign('received_by')->references('id')->on('users')->onUpdate(null)->onDelete(null);
        });

        $payments = \App\models\PaymentNew::all();
        $admin = \App\User::where('level', 'ad')->where('status', 1)->first();

        if($admin) {
            foreach($payments as $payment) {
                $payment->received_by = $admin->id;
                $payment->save();
            }
        }

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('payment_news', function (Blueprint $table) {
        });
    }
}
