<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInvoiceSettingTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('invoice_setting', function (Blueprint $table) {
            $table->id();
            $table->string('provider_name');
            $table->string('apikey');
            $table->string('apikey_sandbox');
            $table->unsignedTinyInteger('is_live')->default(0);
            $table->unsignedTinyInteger('is_active')->default(0);
            $table->string('rfc', 15);
            $table->string('product_code', 50);
            $table->string('unit_code', 50);
            $table->string('serie')->nullable();
            $table->string('folio')->default(0)->nullable();

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
        Schema::dropIfExists('invoice_setting');
    }
}
