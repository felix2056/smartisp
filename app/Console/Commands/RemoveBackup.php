<?php

namespace App\Console\Commands;

use App\Http\Controllers\BackupsController;
use App\libraries\Files;
use App\libraries\MySQLBackup;
use App\libraries\Slog;
use App\models\Logg;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RemoveBackup extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'clean:backup';
	
	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'This command is for removing old backup';
	
	/**
	 * Create a new command instance.
	 *
	 * @return void
	 */
	public function __construct()
	{
		parent::__construct();
	}
	
	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function handle()
	{
		Log::debug('start Remove Backup');
		$date = Carbon::now();
		$startWeek = $date->copy()->startOfWeek();
		$endWeek = $date->copy()->endOfWeek();
		
		$this->removeRangeBackup($startWeek->format('d-m-Y'), $endWeek->format('d-m-Y'));
	}
	
	public function removeRangeBackup($start, $end)
	{
		while (strtotime($start) <= strtotime($end)) {
			$fileName = 'backup_smartisp_'.$start.'.zip';
			
			if(\File::exists(public_path('assets/backups/'.$fileName))) {
				echo "$fileName\n";
				$file = new Files();
				$file->Delete(public_path('assets/backups/' . $fileName));
			}
			$start = date ("d-m-Y", strtotime("+1 day", strtotime($start)));
		}
		
		$this->createBackup();
	}
	
	public function createBackup() {
		$host = DB::connection()->getConfig('host');
		$user = DB::connection()->getConfig('username');
		$password = DB::connection()->getConfig('password');
		$database = DB::connection()->getConfig('database');
		
		try {
			
			$Dump = new MySQLBackup($host, $user, $password, $database);
			
			$Dump->setFilename(public_path('assets/backups/backup_smartisp_' . date('d-m-Y')));
			$Dump->setCompress('zip'); // zip | gz | gzip
			$Dump->setDownload(false);
			$Dump->dump();
			
			$log = new Logg();
			$log->detail = "Se ha creado una copia de seguridad: success";
			$log->user = 'System';
			$log->type = "success";
			$log->save();
			
			echo 'success';
			
			
		} catch (\Exception $e) {
			
			echo $e->getMessage();
			
		}
	}
}
