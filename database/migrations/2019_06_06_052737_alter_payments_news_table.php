<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterPaymentsNewsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::disableForeignKeyConstraints();

        \Illuminate\Support\Facades\DB::statement('ALTER TABLE `payment_news` DROP FOREIGN KEY `payment_news_client_id_foreign`;');
        \Illuminate\Support\Facades\DB::statement('ALTER TABLE `payment_news` CHANGE `client_id` `client_id` INT(11) NULL DEFAULT NULL;');
        Schema::table('payment_news', function (Blueprint $table) {
            $table->foreign('client_id')->references('id')->on('clients')->onUpdate('set null')->onDelete('set null');
        });


        \Illuminate\Support\Facades\DB::statement('ALTER TABLE `transactions` DROP FOREIGN KEY `transactions_client_id_foreign`;');
        \Illuminate\Support\Facades\DB::statement('ALTER TABLE `transactions` CHANGE `client_id` `client_id` INT(11) NULL DEFAULT NULL;');
        Schema::table('transactions', function (Blueprint $table) {
            $table->foreign('client_id')->references('id')->on('clients')->onUpdate('set null')->onDelete('set null');
        });



        Schema::enableForeignKeyConstraints();
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
