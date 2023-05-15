<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateClientServicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('client_services', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('client_id');
//            $table->foreign('client_id')->references('id')->on('clients')->onUpdate('cascade')->onDelete('cascade');
            $table->string('ip', 15);
            $table->string('mac', 20)->default('00:00:00:00:00:00')->nullable();
            $table->date('date_in');
            $table->unsignedInteger('plan_id');
//            $table->foreign('plan_id')->references('id')->on('plans')->onUpdate('cascade')->onDelete('cascade');
            $table->unsignedInteger('router_id');
//            $table->foreign('router_id')->references('id')->on('routers')->onUpdate('cascade')->onDelete('cascade');
            $table->string('status');
            $table->string('user_hot')->nullable();
            $table->string('pass_hot')->nullable();
            $table->string('typeauth')->nullable();
            $table->boolean('onmikrotik')->default(1);
            $table->timestamps();
        });

        $clients = \App\models\Client::all();
        foreach($clients as $client) {
            $service = new \App\models\ClientService();
            $service->client_id = $client->id;
            $service->ip = $client->ip;
            $service->mac = $client->mac;
            $service->date_in = $client->date_in;
            $service->plan_id = $client->plan_id;
            $service->router_id = $client->router_id;
            $service->status = $client->status;
            $service->user_hot = $client->user_hot;
            $service->pass_hot = $client->pass_hot;
            $service->typeauth = $client->typeauth;
            $service->onmikrotik = $client->onmikrotik;
            $service->save();
        }

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('client_services');
    }
}
