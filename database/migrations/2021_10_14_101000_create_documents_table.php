<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateDocumentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
	        $table->unsignedInteger('uploaded_by')->nullable();
	        $table->foreign('uploaded_by')->references('id')->on('users')->onUpdate(null)->onDelete(null);
	        $table->integer('client_id');
	        $table->foreign('client_id')->references('id')->on('clients')->onUpdate(null)->onDelete(null);
	        $table->string('title');
	        $table->text('description')->nullable();
	        $table->boolean('visible_to_client')->default(0);
	        $table->string('document_name');
	        $table->enum('type', ['document', 'contract'])->default('document');
	        $table->longText('contract_content')->nullable();
	        $table->string('template_id')->nullable();
	        $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('documents');
    }
}
