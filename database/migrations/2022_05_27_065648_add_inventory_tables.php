<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddInventoryTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('inv_vendors', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name', 50);
            $table->timestamps();
        });

        Schema::create('inv_supplier', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name', 50);
            $table->string('contact_name', 50);
            $table->string('email', 60);
            $table->string('phone', 15);
            $table->string('address', 200)->nullable();
            $table->boolean('tax_included')->default(0);
            $table->timestamps();
        });

        Schema::create('inv_products', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name', 50);
            $table->unsignedBigInteger('vendor_id');
            $table->foreign('vendor_id')->references('id')->on('inv_vendors')->onUpdate(null)->onDelete(null);
            $table->string('photo', 60)->nullable();
            $table->double('sell_price', 10, 2)->default(0.00);
            $table->double('rent_price', 10, 2)->default(0.00);
            $table->timestamps();
        });

        Schema::create('inv_supplier_invoice', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('supplier_id');
            $table->foreign('supplier_id')->references('id')->on('inv_supplier')->onUpdate(null)->onDelete(null);
            $table->string('file', 60)->nullable();
            $table->integer('invoice_number', );
            $table->dateTime('invoice_date')->nullable();
            $table->double('amount')->nullable();
            $table->timestamps();
        });

        Schema::create('inv_product_items', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('product_id');
            $table->foreign('product_id')->references('id')->on('inv_products')->onUpdate(null)->onDelete(null);
            $table->unsignedBigInteger('supplier_invoice_id')->nullable();
            $table->foreign('supplier_invoice_id')->references('id')->on('inv_supplier_invoice')->onUpdate(null)->onDelete(null);
            $table->string('bar_code')->nullable();
            $table->string('serial_code')->nullable();
            $table->string('photo', 60)->nullable();
            $table->text('notes')->nullable();
            $table->integer('tax')->default(0);
            $table->integer('quantity')->default(0);
            $table->double('amount', 10, 2)->default(0.00);
            $table->double('amount_with_tax', 10, 2)->default(0.00);
            $table->enum('mark', ['New', 'Used', 'Broken'])->default('New');
            $table->enum('status', ['In Stock', 'Assigned', 'Returned', 'Sold', 'Rented', 'Internal Usages'])->default('In Stock');
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
