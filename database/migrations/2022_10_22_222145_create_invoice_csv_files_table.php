<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInvoiceCsvFilesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('invoice_csv_files', function (Blueprint $table) {
            $table->id();
            $table->dateTime('date');
            $table->string('refint_num', 15)->nullable();
            $table->string('fisc_num', 15)->nullable();
            $table->string('type_doc')->default('Factura');
            $table->enum('status', ['STANDBY', 'IMPRESO', 'ERROR_IMP', 'DUPLICATED', 'REMOVED'])->default('STANDBY')->comment("impreso = printed");
            $table->string('printer', 13)->nullable();
            $table->string('doc_refer', 15)->nullable();
            $table->string('numz', 4)->nullable();
            $table->string('file_name', 32)->nullable();
            $table->binary('inv_content')->nullable();
            $table->dateTime('removed_at')->nullable();
            $table->timestamps();
        });


        \Illuminate\Support\Facades\DB::statement("ALTER TABLE `invoice_csv_files` CHANGE `updated_at` `updated_at` TIMESTAMP on update CURRENT_TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP;");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('invoice_csv_files');
    }
}
