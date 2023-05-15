<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class CreateConceptonota extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('conceptonota', function (Blueprint $table) {
            $table->increments('id');
            $table->string('cod');
            $table->string('name');
            $table->string('type');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent();
        });
        DB::insert('INSERT INTO conceptonota (id,cod,name,type) VALUES (?,?,?,?)', [null,'1','Devolución de parte de los bienes; no aceptación de partes del servicio','1']);
        DB::insert('INSERT INTO conceptonota (id,cod,name,type) VALUES (?,?,?,?)', [null,'2','Anulación de factura electrónica','1']);
        DB::insert('INSERT INTO conceptonota (id,cod,name,type) VALUES (?,?,?,?)', [null,'3','Rebaja total aplicada','1']);
        DB::insert('INSERT INTO conceptonota (id,cod,name,type) VALUES (?,?,?,?)', [null,'4','Descuento total aplicado','1']);
        DB::insert('INSERT INTO conceptonota (id,cod,name,type) VALUES (?,?,?,?)', [null,'5','Rescisión: nulidad por falta de requisitos','1']);
        DB::insert('INSERT INTO conceptonota (id,cod,name,type) VALUES (?,?,?,?)', [null,'6','Otros','1']);
        DB::insert('INSERT INTO conceptonota (id,cod,name,type) VALUES (?,?,?,?)', [null,'1','Intereses','2']);
        DB::insert('INSERT INTO conceptonota (id,cod,name,type) VALUES (?,?,?,?)', [null,'2','Gastos por cobrar','2']);
        DB::insert('INSERT INTO conceptonota (id,cod,name,type) VALUES (?,?,?,?)', [null,'3','Cambio del valor','2']);
        DB::insert('INSERT INTO conceptonota (id,cod,name,type) VALUES (?,?,?,?)', [null,'4','Otro','2']);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('conceptonota');
    }
}