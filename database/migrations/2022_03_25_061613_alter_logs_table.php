<?php

use App\models\Logg;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterLogsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
	    Schema::table('logs', function (Blueprint $table) {
		    $table->unsignedInteger('user_id')->nullable();
		    $table->foreign('user_id')->references('id')->on('users')->onUpdate(null)->onDelete(null);
		    
		    $table->integer('client_id')->nullable();
		    $table->foreign('client_id')->references('id')->on('clients')->onUpdate(null)->onDelete(null);
	    });
	    
	    Logg::chunkById(5, function ($logs) {
	    	foreach($logs as $log) {
	    		$admin = \App\models\User::where('username', $log->user)->first();
	    		if($admin) {
				    $log->user_id = $admin->id;
				    $log->save();
			    }
	    		
		    }
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
