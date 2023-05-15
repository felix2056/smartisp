<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFactelTable extends Migration {

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('factel', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('certificado_digital');
            $table->string('pass_certificado');
            $table->integer('status');
            $table->timestamp('updated_at')->nullable();
        });
        
        
        
        
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::dropIfExists('factel');
    }

}
