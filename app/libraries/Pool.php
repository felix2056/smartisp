<?php
namespace App\libraries;
/**
* POOL IP FUNCTIONS
*/
class Pool
{

	//metodo para listar todos los pool de ip en mikrotik
	public static function get_pool_list($API){

		$API->write("/ip/pool/print",true);
		$READ = $API->read(false);
		$ARRAY = $API->parseResponse($READ);
		return $ARRAY;
	}

	//metodo para registar pool de ips
	public static function add_pool($API,$name,$range){

		$API->write("/ip/pool/add",false);
		$API->write("=name=".$name,false);
		$API->write("=ranges=".$range,true);
		$READ = $API->read(false);
		$ARRAY = $API->parseResponse($READ);
		return $ARRAY;
	}

}
