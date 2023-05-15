<?php

namespace App\Console\Commands;

use App\libraries\Helpers;
use App\libraries\SmartOLT;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Redis;
use Cache;

class GetDataSmartOLT extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'get-data-smarolt';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Get data of ONUs and OLTs from API SmartOLT, and save into cache';

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
        /**Agregar al update y al install**/
        // TODO --> sudo apt install redis-server
        // nano /etc/redis/redis.conf --> poner lo que dice abajo
        // requirepass root --> siendo root la pass
        /**comandos basicos*/
//        Redis::set($onu->sn,json_encode((array)($onu))); --> porque hay que guardar en json
//        Redis::keys('*'); --> para obtener todos
//        Redis::keys('olId_*'); --> para obtener todos de una olt especifica
//        Redis::get('aca_la_fey_del_registro'); --> para obtener uno solo
//        Redis::flushDB(); --> para eliminar completo uno solo



        /**Si NO tenemos activa la opcion de smartolt*/
        $smartolt =  Helpers::get_api_options('smartolt');
        if(!isset($smartolt['c']))
            return ;

        /**Si tenemos activa la opcion de smartolt*/
        /**primero eliminamos la cache con la informacion para llenarla nuevamente**/
        $all_onus_cache =  Redis::flushDB();

        $smartolt = new SmartOLT();
        /**GET olt**/
        $res = $smartolt->consumir_api_smartolt('GET','api/system/get_olts');
        $olt_list= json_decode($res->getBody()->getContents())->response;
        foreach ($olt_list as $olt){
            $olt_id = $olt->id;
            /**Always get details to ONUS of OLT**/
            $res = $smartolt->consumir_api_smartolt('GET','api/onu/get_all_onus_details?olt_id='.$olt_id);
            $onus = json_decode($res->getBody()->getContents())->onus;
            foreach ($onus as $onu){
                Redis::set($olt_id.'_'.$onu->sn,json_encode((array)($onu)));
            }
        }

    }
}
