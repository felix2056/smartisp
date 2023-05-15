<?php
namespace App\libraries;
use App\models\Network;

/**
*  Enable or disabled IP USED FOR CLIENTS
*/
class StatusIp
{

	public function is_used_ip($ip,$clientid,$op){

		$set = Network::where('ip','=',$ip)->get();
		if ($op==true) {
			$set[0]->is_used = '1';
			$set[0]->client_id = $clientid;
			$set[0]->save();
		}else{
			$set[0]->is_used = '0';
			$set[0]->client_id = '0';
			$set[0]->save();
		}
	}

	public function refresh_ip($ip,$clientid){
		$set = Network::where('ip','=',$ip)->get();

		if (count($set)>0) {
			$set[0]->is_used = '1';
			$set[0]->client_id = $clientid;
			$set[0]->save();
		}
	}
}
