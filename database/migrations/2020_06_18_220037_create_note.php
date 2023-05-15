<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateNote extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('note', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('invoices_dian_id');
            $table->date('date');
            $table->time('hour');
            $table->string('prefix');
            $table->integer('number');
            $table->string('cude');
            $table->string('filename');
            $table->mediumtext('qr');
            $table->unsignedInteger('conceptonote_cod');
            $table->decimal('subtotal', 12, 2);
            $table->decimal('totaltax', 12, 2);
            $table->decimal('total', 12, 2);
            $table->text('observaciones');
            $table->integer('typeoperation_cod');
            $table->integer('note_type');
            $table->enum('status_note', ['accepted', 'rejected'])->default('rejected');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('note');
    }
}
