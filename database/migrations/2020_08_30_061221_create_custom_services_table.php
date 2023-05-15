<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCustomServicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('recurring_invoices', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('client_id');
            $table->foreign('client_id')->references('id')->on('clients')->onUpdate('cascade')->onDelete('cascade');
            $table->string('note');
            $table->longText('memorandum')->nullable();
            $table->enum('frequency', ['week', 'month', 'year']);
            $table->decimal('price');
            $table->dateTime('start_date');
            $table->dateTime('end_date')->nullable();
            $table->dateTime('next_pay_date')->nullable();
            $table->dateTime('expiration_date')->nullable();
            $table->enum('status', ['enable', 'disable'])->default('enable');
            $table->enum('service_status', ['active', 'block'])->default('active');
            $table->timestamps();
        });

        Schema::create('recurring_invoice_items', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('recurring_invoice_id');
            $table->foreign('recurring_invoice_id')->references('id')->on('recurring_invoices')->onUpdate('cascade')->onDelete('cascade');
            $table->string('description');
            $table->unsignedInteger('quantity');
            $table->unsignedInteger('unit')->nullable();
            $table->decimal('price', 10, 2);
            $table->decimal('iva', 8, 2);
            $table->decimal('total', 12, 2);
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
        Schema::dropIfExists('custom_services');
    }
}
