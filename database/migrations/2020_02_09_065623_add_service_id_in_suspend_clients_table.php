<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddServiceIdInSuspendClientsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('suspend_clients', function (Blueprint $table) {
            $table->unsignedBigInteger('service_id')->nullable();
            $table->foreign('service_id')->references('id')->on('client_services')->onUpdate('cascade')->onDelete('cascade');
        });

        $clients = \App\models\Client::with('service')->get();
        foreach($clients as $client) {

            $suspend = \App\models\SuspendClient::where('client_id', $client->id)->where('router_id', $client->router_id)->first();
            if ($suspend) {
                $suspend->service_id = $client->service[0]->id;
                $suspend->save();
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('suspend_clients', function (Blueprint $table) {
            //
        });
    }
}
