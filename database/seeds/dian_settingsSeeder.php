<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class dian_settingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        DB::table('dian_settings')->insert(
            [
                'id'=>'1',
                'typeoperation_cod'=>'2',
                'softwarename'=>'smartisp',
                'softwareid'=>'',
                'softwarepin'=>'12345',
                'tecnicalkey'=>'',
                'testsetid'=>'',
                'resolutiondate'=>'2019-01-19',
                'resolutiondatestar'=>'2019-01-19',
                'resolutiondateend'=>'2030-01-19',
                'resolutionnumber'=>'18760000001',
                'prefix'=>'SETP',
                'numberstart'=>'990000000',
                'numberend'=>'995000000',
                'prefixnc'=>'NC',
                'numberstartnc'=>'0',
                'numberendnc'=>'999999999',
                'prefixnd'=>'ND',
                'numberstartnd'=>'0',
                'numberendnd'=>'999999999',
                'fes'=>'0',
                'ncs'=>'0',
                'nds'=>'0',
                'zips'=>'0',
                'year'=>'2019',
                'typedoc_cod'=>'31',
                'identificacion'=>'',
                'businessname'=>'',
                'tradename'=>'',
                'typetaxpayer_cod'=>'1',
                'accountingregime_cod'=>'0',
                'typeresponsibility_cod'=>'ZZ',
                'economicactivity_cod'=>'0',
                'municipio_cod'=>'05001',
                'direction'=>''
            ]
        );
        
    }
}
