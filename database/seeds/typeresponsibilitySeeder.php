<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class typeresponsibilitySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        DB::table('typeresponsibility')->insert(
            [
                'cod'=>'O-13',
                'Description'=>'Gran contribuyente'
            ]
        );
        DB::table('typeresponsibility')->insert(
            [
                'cod'=>'O-15',
                'Description'=>'Autorretenedor'
            ]
        );
        DB::table('typeresponsibility')->insert(
            [
                'cod'=>'O-23',
                'Description'=>'Agente de retención IVA'
            ]
        );
        DB::table('typeresponsibility')->insert(
            [
                'cod'=>'O-47',
                'Description'=>'Régimen simple de tributación'
            ]
        );
        DB::table('typeresponsibility')->insert(
            [
                'cod'=>'ZZ',
                'Description'=>'No aplica'
            ]
        );
    }
}
