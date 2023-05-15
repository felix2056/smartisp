<?php

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class typedocSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        //
        DB::table('typedoc')->insert(
            [
                'cod'=>'0',
                'Description'=>'Sin definir'
            ]
        );
        DB::table('typedoc')->insert(
            [
                'cod'=>'11',
                'Description'=>'Registro civil'
            ]
        );
        DB::table('typedoc')->insert(
            [
                'cod'=>'12',
                'Description'=>'Tarjeta de identidad'
            ]
        );
        DB::table('typedoc')->insert(
            [
                'cod'=>'13',
                'Description'=>'Cédula de ciudadanía'
            ]
        );
        DB::table('typedoc')->insert(
            [
                'cod'=>'21',
                'Description'=>'Tarjeta de extranjería'
            ]
        );
        DB::table('typedoc')->insert(
            [
                'cod'=>'22',
                'Description'=>'Cédula de extranjería'
            ]
        );
        DB::table('typedoc')->insert(
            [
                'cod'=>'31',
                'Description'=>'NIT'
            ]
        );
        DB::table('typedoc')->insert(
            [
                'cod'=>'41',
                'Description'=>'Pasaporte'
            ]
        );
        DB::table('typedoc')->insert(
            [
                'cod'=>'42',
                'Description'=>'Documento de identificación extranjero'
            ]
        );
        DB::table('typedoc')->insert(
            [
                'cod'=>'50',
                'Description'=>'NIT de otro país'
            ]
        );
        DB::table('typedoc')->insert(
            [
                'cod'=>'91',
                'Description'=>'NUIP *'
            ]
        );
    }
}
