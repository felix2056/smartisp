<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class typetaxpayerSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        DB::table('typetaxpayer')->insert(
            [
                'cod'=>'0',
                'Description'=>'Sin definir'
            ]
        );
        DB::table('typetaxpayer')->insert(
            [
                'cod'=>'1',
                'Description'=>'Persona Jurídica'
            ]
        );
        DB::table('typetaxpayer')->insert(
            [
                'cod'=>'2',
                'Description'=>'Persona Natural'
            ]
        );
    }
}
