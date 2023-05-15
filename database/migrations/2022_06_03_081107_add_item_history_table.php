<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddItemHistoryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('inv_item_history', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('item_id');
            $table->foreign('item_id')->references('id')->on('inv_product_items')->onUpdate(null)->onDelete(null);
            $table->string('status', 255)->nullable();
            $table->text('notes');
            $table->dateTime('date_time');
            $table->enum('current_status', ['in_use', 'not_in_use'])->default('not_in_use');
            $table->timestamps();
        });

        Schema::table('inv_item_history', function (Blueprint $table) {
            $table->integer('client_id')->nullable();
            $table->foreign('client_id')->references('id')->on('clients')->onUpdate('cascade')->onDelete('cascade');
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
