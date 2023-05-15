<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTreePriorityColumnInClientServicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('client_services', function (Blueprint $table) {
            $table->unsignedInteger('tree_priority')->nullable();
        });

        $plans = \App\models\Plan::all();
        $routers = \App\models\Router::all();

        foreach($routers as $router) {

            foreach($plans as $plan) {
                $clientServices = \App\models\ClientService::where('plan_id', $plan->id)->where('router_id', $router->id)->get();
                if($clientServices->count() > 0) {
                    $priority = $clientServices->count() / 128;

                    if($priority < 0) {
                        \App\models\ClientService::where('plan_id', $plan->id)->where('router_id', $router->id)->update(['tree_priority' => 0]);
                    } else {
                        $maxPrority = floor($priority);
                        $take = 128;
                        $skip = 0;
                        for($i = 0; $i <= $maxPrority; $i++) {
                            $datas = \App\models\ClientService::where('plan_id', $plan->id)->where('router_id', $router->id)->take($take)->skip($skip)->get();

                            foreach($datas as $data) {
                                $data->tree_priority = $i;
                                $data->save();
                            }

                            $skip += $take;
                        }
                    }

                }
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
        Schema::table('client_services', function (Blueprint $table) {
            $table->dropColumn('tree_priority');
        });
    }
}
