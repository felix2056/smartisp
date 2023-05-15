<?php
namespace App\libraries;
/**
* INTERFACES FUNCTIONS
*/
class Interf
{
	//metodo para setear interface habilitar ARP
	public static function interface_set_arp($API,$type,$id,$arp){

		$API->write('/interface/'.$type.'/set',false);
		$API->write('=.id='.$id,false);
		$API->write('=arp='.$arp,true);
		$READ = $API->read(false);
		$ARRAY = $API->parseResponse($READ);
	    return $ARRAY;
	}

	//metodo para buscar y recuperar la ID de la interfaz según el nombre
	public static function interface_get_id($API,$name){
		$ID = $API->comm('/interface/print', array(
        ".proplist" => ".id",
        "?name" => $name
    	));

    	if(count($ID)>0)
			return $ID;
		else
			return "notFound";
	}

	//metodo para buscar el tipo de interfaz
	public static function interface_get_type($API,$interface){
		$type = $API->comm('/interface/print', array("?name" => $interface));
		if(count($type)>0)
			return $type;
		else
			return "notFound";
	}


}
