<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddCreditColumnInUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
    	
        Schema::create('user_credits', function (Blueprint $table) {
            $table->bigIncrements('id');
	        $table->unsignedInteger('user_id');
	        $table->foreign('user_id')->references('id')->on('users');
	        $table->unsignedDouble('credit', 10, 2)->default(0);
	        $table->text('comment')->nullable();
	        $table->timestamps();
        });
        
        Schema::table('users', function (Blueprint $table) {
	        $table->unsignedDouble('balance', 10, 2)->default(0);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
    
    }
}
