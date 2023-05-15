<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTicketColumnViewTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ticket_column_view', function (Blueprint $table) {
            $table->id();
            $table->boolean('subject')->default(1);
            $table->boolean('section')->default(1);
            $table->boolean('status')->default(1);
            $table->boolean('user_id')->default(1);
            $table->boolean('client_id')->default(1);
            $table->boolean('created_at_view')->default(1);
            $table->boolean('priority')->default(1);
            $table->boolean('type')->default(1);
            $table->timestamps();
        });
        
        
        \App\models\TicketViewColumn::create([
        	"subject" => 1,
        	"section" => 1,
        	"status" => 1,
        	"user_id" => 1,
        	"client_id" => 1,
        	"created_at_view" => 1,
        	"priority" => 1,
        	"type" => 1,
        ]);
        
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ticket_column_view');
    }
}
