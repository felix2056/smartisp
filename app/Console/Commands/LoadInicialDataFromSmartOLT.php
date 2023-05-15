<?php

namespace App\Console\Commands;

use App\libraries\Helpers;
use App\libraries\SmartOLT;
use App\models\Client;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Redis;
use Cache;

class LoadInicialDataFromSmartOLT extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'load-data-inicial-smarolt';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get data of assignments from SmartOLT, and save into smartisp database';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {

        $todas = Redis::keys('*');
        $listado_onus_detalle = collect();
        foreach($todas as $onu){
            $onu_informacion = Redis::get($onu);
            $listado_onus_detalle->push(collect(json_decode($onu_informacion)));
        }

        foreach ($listado_onus_detalle as $onu){
            /**find client into smartisp**/
            $client = Client::with('service')->where('name',$onu['name'])->get();
            /**only continue if exist one people with this name**/
            if($client->count() == 1){
                /**only continue if exist one servicefor this client**/
                if($client->first()->service->count() == 1){
                    echo "\n";
                    echo "load SN ".$onu['sn'].'to client '.$client->first()->name."\n";
                    $service = $client->first()->service->first();
                    $service->smartolt_sn = $onu['sn'];
                    $service->save();
                }
            }

        }

    }
}
