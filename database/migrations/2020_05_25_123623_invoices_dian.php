<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class InvoicesDian extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::create('invoices_dian', function (Blueprint $table) {
            $table->collation = 'utf8mb4_bin';
            $table->bigIncrements('id');
            $table->integer('bill_customers_id')->default(0);
            $table->string('resolutionnumber');
            $table->integer('client_id');
            $table->date('date');
            $table->time('hour')->nullable();
            $table->string('typeoperation_cod');
            $table->string('payment_cod');
            $table->string('prefix');
            $table->string('number');
            $table->string('cufe');
            $table->mediumText('qr');
            $table->string('filename');
            $table->string('email')->nullable();;
            $table->string('phone')->nullable();;
            $table->string('municipio_cod');
            $table->string('address')->nullable();;
            $table->string('bill_number')->nullable();
            $table->string('nmoney');
            $table->string('subtotal');
            $table->string('totaltax');
            $table->string('total');
            $table->date('payment_date');
            $table->string('xero_id');
            $table->enum('use_transactions', ['0', '1'])->default('0');
            $table->enum('status', ['unpaid', 'paid(account_balance)', 'paid', 'late', 'removed'])->default('unpaid');
            $table->enum('status_dian', ['accepted', 'rejected'])->default('rejected');
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
        //
    }
}
