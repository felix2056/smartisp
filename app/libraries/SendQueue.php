<?php
namespace App\libraries;
use App\models\QueuedProcess;

/**
*  PROCCESS QUEUES LIST FUNCTIONS
*/
class SendQueue
{

	//method for mikrotik proccess onlock client
	function unlock_client_mikrotik($data){

		//registramos
		$QUEUES = new QueuedProcess();

		$values = array('c' => $data['client_id']);

		$QUEUES->type = 'mikrotik';
		$QUEUES->process = 'unlock';
		$QUEUES->detail = $data['detail'];
		$QUEUES->values = json_encode($values,true);
		$QUEUES->save();
	}


}
