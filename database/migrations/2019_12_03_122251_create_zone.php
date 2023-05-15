<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use App\models\Zone;
class CreateZone extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('zone', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('name')->unique();
            $table->timestamps();
        });

        $insert = new Zone;
        $insert->name='Zone 1';
        $insert->save();

        $insert = new Zone;
        $insert->name='Zone 2';
        $insert->save();

        $insert = new Zone;
        $insert->name='Zone 3';
        $insert->save();

        $insert = new Zone;
        $insert->name='Zone 4';
        $insert->save();

        $insert = new Zone;
        $insert->name='Zone 5';
        $insert->save();
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('zone');
    }
}
