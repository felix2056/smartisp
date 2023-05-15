<?php
namespace App\libraries;
/**
* QUEUE TYPE FUNCTIONS
*/
class QueueType
{

	public static function add_queuetype($API,$name,$rate,$burst,$burst_threshold,$burst_time,$op){

		$burst_time = $burst_time == 0 ? '10' : $burst_time;

		$API->write("/queue/type/add", false);
		$API->write("=name=".$name,false);
		$API->write("=kind=pcq",false);
		$API->write("=pcq-rate=".$rate,false);
		$API->write("=pcq-burst-rate=".$burst,false);
		$API->write("=pcq-burst-threshold=".$burst_threshold,false);
		$API->write("=pcq-burst-time=".$burst_time,false);

		if ($op=='DOWN') {
			$API->write("=pcq-classifier=dst-address",true);

		}else{
			$API->write("=pcq-classifier=src-address",true);
		}

		$READ = $API->read(false);
		$ARRAY = $API->parseResponse($READ);

		return $ARRAY;
	}

	public static function set_queuetype($API,$id,$name,$rate,$burst,$burst_threshold,$burst_time,$op){

		$burst_time = $burst_time == 0 ? '10' : $burst_time;

		$API->write("/queue/type/set", false);
		$API->write("=.id=".$id,false);
		if ($name!=null) {
			$API->write("=name=".$name,false);
		}
		$API->write("=kind=pcq",false);
		$API->write("=pcq-rate=".$rate,false);
		$API->write("=pcq-burst-rate=".$burst,false);
		$API->write("=pcq-burst-threshold=".$burst_threshold,false);
		$API->write("=pcq-burst-time=".$burst_time,false);

		if ($op=='DOWN') {
			$API->write("=pcq-classifier=dst-address",true);

		}else{
			$API->write("=pcq-classifier=src-address",true);
		}

		$READ = $API->read(false);
		$ARRAY = $API->parseResponse($READ);

		return $ARRAY;

	}

	public static function delete_queuetype($API,$id){

		$API->write("/queue/type/remove", false);
		$API->write("=.id=".$id,true);
		$READ = $API->read(false);
		$ARRAY = $API->parseResponse($READ);

		return $ARRAY;

	}

	public static function find_queuetype_list($API,$name){

		$ID = $API->comm('/queue/type/print', array(
		".proplist" => ".id",
		"?name" => $name
		));

		//verificamos si el usuario esta en el router
		if(count($ID)>0)
			return $ID;
		else
			return "notFound";
	}

}
