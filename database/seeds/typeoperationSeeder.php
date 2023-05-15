<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class typeoperationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        DB::table('typeoperation')->insert(
            [
                'cod'=>'1',
                'Description'=>'Producción'
            ]
        );
        DB::table('typeoperation')->insert(
            [
                'cod'=>'2',
                'Description'=>'Prueba'
            ]
        );
    }
}
