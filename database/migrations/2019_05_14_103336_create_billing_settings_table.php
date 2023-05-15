<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBillingSettingsTable extends Migration
{
    /**
     * cra 55 #100-51 oficina 706 edificio blue garden home center
     * ****     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('billing_settings', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('client_id');
            $table->enum('billing_status', ['0', '1'])->default('1');
            $table->string('billing_type')->default('1');
            $table->string('billing_payment_method')->default('1');
            $table->string('billing_date')->default('1');
            $table->string('billing_due_date')->default('15');
            $table->string('billing_grace_period')->default('0');
            $table->float('billing_min_balance')->default('0.00');
            $table->float('billing_partner_percent')->default('0.00');
            $table->enum('billing_create_invoice', ['0', '1'])->default('0');
            $table->enum('billing_auto_pay_invoice', ['0', '1'])->default('1');
            $table->enum('billing_send_notification', ['0', '1'])->default('0');
            $table->string('billing_name')->nullable();
            $table->string('billing_address')->nullable();
            $table->string('billing_zip_code')->nullable();
            $table->string('billing_city')->nullable();
            $table->enum('invoice_status', ['0', '1'])->default('0');
            $table->string('invoice_request_auto_day')->default('1');
            $table->string('invoice_request_auto_type')->default('1');
            $table->string('invoice_request_auto_period')->default('0');
            $table->date('invoice_request_auto_next')->nullable();
            $table->enum('reminder_status', ['0', '1'])->default('0');
            $table->string('reminder_type')->default('0');
            $table->string('reminder_day_1')->default('1');
            $table->string('reminder_day_2')->default('1');
            $table->string('reminder_day_3')->default('1');
            $table->enum('reminder_payment_status', ['0', '1'])->default('0');
            $table->float('reminder_payment_value')->default('0.00');
            $table->string('reminder_payment_comment')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('billing_settings');
    }
}
