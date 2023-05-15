<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddManuallyBlockColumnInClientServicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('client_services', function (Blueprint $table) {
            $table->boolean('manually_cortado')->default(0);
        });
        
        
        Schema::create('cortado_reasons', function (Blueprint $table) {
        	$table->increments('id');
	        $table->integer('client_id');
	        $table->foreign('client_id')->references('id')->on('clients')->onUpdate('cascade')->onDelete('cascade');
	        $table->unsignedBigInteger('service_id')->nullable();
	        $table->foreign('service_id')->references('id')->on('client_services')->onUpdate('cascade')->onDelete('cascade');
	        $table->text('reason')->nullable();
	        $table->enum('status', ['active', 'blocked'])->default('active');
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
        Schema::table('client_services', function (Blueprint $table) {
            $table->dropColumn('manually_cortado');
        });
    }
}
