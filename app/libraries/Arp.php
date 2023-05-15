<?php
namespace App\libraries;
/**
*  ARP LIST FUNCTIONS
*/
class Arp
{

	//metodo para añadir registro en arp
	public static function arp_add($API,$Address,$mac,$lan,$comment){

		$API->write("/ip/arp/add",false);
		$API->write("=address=".$Address,false);
		$API->write("=mac-address=".$mac,false);
		$API->write("=interface=".$lan,false);
		$API->write("=comment=".$comment,true);
		$READ = $API->read(false);
		$ARRAY = $API->parseResponse($READ);

		return $ARRAY;
	}

	//metodo para eliminar registro en arp
	public static function arp_remove($API,$id){

		$API->write("/ip/arp/remove",false);
		$API->write("=.id=".$id,true);
		$READ = $API->read(false);
		$ARRAY = $API->parseResponse($READ);
		return $ARRAY;
	}

	//metodo para editar registro en arp
	public static function arp_set($API,$id,$mac,$Address,$comment){

		$API->write("/ip/arp/set",false);
		$API->write("=.id=".$id,false);
		$API->write("=comment=".$comment,false);
		$API->write("=address=".$Address,false);
		$API->write("=mac-address=".$mac,true);
		$READ = $API->read(false);
		$ARRAY = $API->parseResponse($READ);
		return $ARRAY;
	}

	//metodo para activar/desactivar ip en arp
	public static function arp_block($API,$id,$op,$comment){

		$API->write("/ip/arp/set",false);
		$API->write("=.id=".$id,false);
		$API->write("=disabled=".$op,false);
		$API->write("=comment=".$comment,true);
		$READ = $API->read(false);
		$ARRAY = $API->parseResponse($READ);
		return $ARRAY;
	}

	//metodo para buscar y recuperar la ID de un registro en arp
	public static function arp_get_id($API,$Address){

		$ID = $API->comm('/ip/arp/print', array(
		".proplist" => ".id,dynamic",
		"?address" => $Address
		));

		if(count($ID)>0)
			return $ID;
		else
			return "notFound";
	}


}


