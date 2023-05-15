<?php
namespace App\libraries;
class CronTabs{

	function addCron($command,$path){

		$output = shell_exec('crontab -l');
		file_put_contents('/tmp/RocketCut.txt', $output.$command.PHP_EOL);
		echo exec('crontab /tmp/RocketCut.txt');
	}

	function deleteCron(){

		shell_exec('crontab -u www-data -r');
	}

}

?>
