<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateInstall extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        $path = base_path('.env');
        if (file_exists($path)) {
            file_put_contents($path, str_replace(
                'QUEUE_CONNECTION='.'sync', 'QUEUE_CONNECTION='.'database', file_get_contents($path)
            ));
            
            $text = file_get_contents($path);
            $addLine = "QUEUE_DRIVE=datababase"."\n";
            if(strpos($text, $addLine) === false) {
                file_put_contents($path, $addLine, FILE_APPEND);
            }

            file_put_contents($path, str_replace(
                'QUEUE_DRIVE='.'database', 'QUEUE_DRIVE='.'database', file_get_contents($path)
            ));
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
