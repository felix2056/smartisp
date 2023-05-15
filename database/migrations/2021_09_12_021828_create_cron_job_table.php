<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCronJobTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cron_jobs', function (Blueprint $table) {
            $table->id();
            $table->string('command');
            $table->text('description')->nullable();
            $table->string('interval')->nullable();
            $table->timestamps();
        });
        
        $cron = \App\models\CronJob::create([
        	'command' => 'generate-invoice',
        	'description' => 'Generate client\'s invoices',
        	'interval' => '30 1 * * *',
        ]);
        
        $cron = \App\models\CronJob::create([
        	'command' => 'clean:backup',
        	'description' => 'This command is for removing old backup',
        	'interval' => '0 22 * * 6,0',
        ]);
        
        $cron = \App\models\CronJob::create([
        	'command' => 'generate:recurring-invoice',
        	'description' => 'Generate automatic recurring invoices',
        	'interval' => '0 3 * * *',
        ]);
        
        $cron = \App\models\CronJob::create([
        	'command' => 'set-status',
        	'description' => 'Set client status Cortado if client balance is negative',
        	'interval' => '0 0 * * *',
        ]);
        
        $cron = \App\models\CronJob::create([
        	'command' => 'set-mikrotik-rules',
        	'description' => 'Set mikrotik rules for blocked clients if removed from mikrotik by mistake',
        	'interval' => '0 */2 * * *',
        ]);
        
        $cron = \App\models\CronJob::create([
        	'command' => 'router:status',
        	'description' => 'This cron will update router status every hour',
        	'interval' => '0 */2 * * *',
        ]);
        
        $cron = \App\models\CronJob::create([
        	'command' => 'set-client-address-list',
        	'description' => 'Check client is into address list or not',
        	'interval' => '0 */2 * * *',
        ]);
        
        
        
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('cron_jobs');
    }
}
