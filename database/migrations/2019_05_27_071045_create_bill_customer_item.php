<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBillCustomerItem extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bill_customer_item', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('bill_customer_id');
            $table->string('description');
            $table->date('period_from')->nullable();
            $table->date('period_to')->nullable();
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
        Schema::dropIfExists('bill_customer_item');
    }
}
