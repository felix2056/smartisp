<?php
namespace App\libraries;
/**
* PROXY FUNCTIONS
*/
class Proxy
{
	//metodo para añadir registros en proxy deudores (proxy)
	public static function proxy_add($API,$Address,$urlp,$url,$comment){
//		$API->write("/ip/proxy/access/add",false);
//		$API->write("=src-address=".$Address,false);
//		$API->write("=dst-host=!".$urlp,false);
//		$API->write("=action=".'deny',false);
//		$API->write("=comment=".$comment,false);
//		$API->write("=redirect-to=".$url,true);
//		$READ = $API->read(false);
//		$ARRAY = $API->parseResponse($READ);
//		return $ARRAY;
        return [];
	}

	//metodo para eliminar registro en arp
	public static function proxy_remove($API,$id){

//		$API->write("/ip/proxy/access/remove",false);
//		$API->write("=.id=".$id,true);
//		$READ = $API->read(false);
//		$ARRAY = $API->parseResponse($READ);
//
//		return $ARRAY;
        return [];
	}

	//metodo para editar registro en arp
	public static function proxy_set($API,$id,$Address,$urlp,$url,$comment){

//		$API->write("/ip/proxy/access/set",false);
//		$API->write("=.id=".$id,false);
//		$API->write("=src-address=".$Address,false);
//		$API->write("=dst-host=!".$urlp,false);
//		$API->write("=redirect-to=".$url,false);
//		$API->write("=comment=".$comment,true);
//
//		$READ = $API->read(false);
//		$ARRAY = $API->parseResponse($READ);
//		return $ARRAY;
        return [];

	}

	//metodo para buscar y recuperar la ID de un registro en access
	public static function proxy_get_id($API,$Address){

//		$ID = $API->comm('/ip/proxy/access/print', array(
//		".proplist" => ".id",
//		"?src-address" => $Address
//		));
//
//		if(count($ID)>0)
//			return $ID;
//		else
			return "notFound";
	}

	//metodo para habilitar web proxy
	public static  function enable_proxy($API){

//		$API->write("/ip/proxy/set",false);
//		$API->write("=enabled=true",false);
//		$API->write("=port=3128",true);
//		$READ = $API->read(false);
//		$ARRAY = $API->parseResponse($READ);
//
//		return $ARRAY;
        return [];
	}

	//metodo para desabilitar web proxy
	public static function disable_proxy($API){

//		$API->write("/ip/proxy/set",false);
//		$API->write("=enabled=false",false);
//		$API->write("=port=3128",true);
//
//		$READ = $API->read(false);
//		$ARRAY = $API->parseResponse($READ);
//
//		return $ARRAY;
        return [];
	}

}
