<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPriorityColumnInTicketsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
    	
    	\Illuminate\Support\Facades\DB::statement("ALTER TABLE `tickets` CHANGE `status` `status` ENUM('new','work_in_progress','resolved','waiting_on_customer','waiting_on_agent','cl','op') CHARACTER SET utf8 COLLATE utf8_spanish2_ci NULL DEFAULT 'new';");
    	
        Schema::table('tickets', function (Blueprint $table) {
            $table->enum('type', ['question', 'incident', 'problem', 'feature_request', 'lead'])->default('question');
            $table->enum('priority', ['low', 'medium', 'high', 'urgent'])->default('low');
        });
    	
    	$tickets = \App\models\Ticket::where('status', 'op')->update([
    		'status' => 'work_in_progress'
	    ]);
    	
    	$tickets = \App\models\Ticket::where('status', 'cl')->update([
    		'status' => 'resolved'
	    ]);
    }
    /*TODO: replace op by work_in_progress and cl by resolved*/

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('tickets', function (Blueprint $table) {
            //
        });
    }
}
