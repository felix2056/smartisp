<?php
namespace App\libraries;
/**
* ADDRESSES FUNCTIONS
*/
class Address
{
	//metodo para agregar ip/address a mikrotik
	public static function address_add($API,$address,$interface){

		$API->write('/ip/address/add',false);
		$API->write('=address='.$address,false);
		$API->write('=interface='.$interface,true);
		$READ = $API->read(false);
		$ARRAY = $API->parse_response($READ);
	    return $ARRAY;
	}

	//metodo para buscar y recuperar la ID de la ip según la dirección ip
	public static function address_get_id($API,$address,$interface){

		$ID = $API->comm('/ip/address/print', array(
        ".proplist" => ".id",
        "?address" => $address,
        "?interface" => $interface
    	));

    	if(count($ID)>0)
			return $ID;
		else
			return "notFound";

	}

	//metodo para eliminar ip/red
	public static function address_remove($API,$id){

		$API->write("/ip/address/remove",false);
		$API->write("=.id=".$id,true);
		$READ = $API->read(false);
		$ARRAY = $API->parse_response($READ);

		return $ARRAY;
	}



}
