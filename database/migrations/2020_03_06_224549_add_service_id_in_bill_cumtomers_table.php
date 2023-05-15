<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddServiceIdInBillCumtomersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('bill_customers', function (Blueprint $table) {
            $table->unsignedBigInteger('service_id')->nullable()->after('client_id');
            $table->foreign('service_id')->references('id')->on('client_services')->onUpdate('cascade')->onDelete('cascade');
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
            //
        });
    }
}
