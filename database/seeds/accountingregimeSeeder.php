<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class accountingregimeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        DB::table('accountingregime')->insert(
            [
                'cod'=>'0',
                'Description'=>'No especificado'
            ]
        );
        DB::table('accountingregime')->insert(
            [
                'cod'=>'04',
                'Description'=>'Régimen Simple'
            ]
        );
        DB::table('accountingregime')->insert(
            [
                'cod'=>'05',
                'Description'=>'Régimen Ordinario'
            ]
        );
        DB::table('accountingregime')->insert(
            [
                'cod'=>'48',
                'Description'=>'Impuestos sobre las venta IVA'
            ]
        );
        DB::table('accountingregime')->insert(
            [
                'cod'=>'49',
                'Description'=>'No responsable de IVA'
            ]
        );
    }
}
