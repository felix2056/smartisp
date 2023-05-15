<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddColumnMapMarkerIconToOdbSplitterTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('odb_splitter', function (Blueprint $table) {
            $table->json('map_marker_icon');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('odb_splitter', function (Blueprint $table) {
            $table->dropColumn('map_marker_icon');
        });
    }
}
