<?php
namespace App\libraries;
/**
* DHCP FUNCTIONS
*/
class Dhcp
{

	//metodo para añadir dhcp leases
	public static function dhcp_add($API,$Address,$mac,$comment){

	    $API->write("/ip/dhcp-server/lease/add",false);
	    $API->write("=address=".$Address,false);
	    $API->write("=mac-address=".$mac,false);
	    $API->write("=comment=".$comment,true);
	    $READ = $API->read(false);
		$ARRAY = $API->parseResponse($READ);
	    return $ARRAY;
	}

	//metodo para eliminar dhcp leases
	public static function dhcp_remove($API,$id){

		$API->write("/ip/dhcp-server/lease/remove",false);
		$API->write("=.id=".$id,true);
		$READ = $API->read(false);
		$ARRAY = $API->parseResponse($READ);

		return $ARRAY;
	}

	//metodo para editar registro en dhcp leases
	public static function dhcp_set($API,$id,$mac,$Address,$comment){

		$API->write("/ip/dhcp-server/lease/set",false);
		$API->write("=.id=".$id,false);
		$API->write("=address=".$Address,false);
		$API->write("=mac-address=".$mac,false);
		$API->write("=comment=".$comment,true);
		$READ = $API->read(false);
		$ARRAY = $API->parseResponse($READ);
	    return $ARRAY;

	}

	//metodo para reseteo de velocidades dhcp
	public static function dhcp_reset_rate($API,$id){

		$API->write("/ip/dhcp-server/lease/set",false);
		$API->write("=.id=".$id,false);
		$API->write("=rate-limit=",true);
		$READ = $API->read(false);
		$ARRAY = $API->parseResponse($READ);
	    return $ARRAY;

	}

	//metodo para buscar y recuperar la ID de un registro en dhcp
	public static function dhcp_get_id($API,$Address,$mac){

		$API->write("/ip/dhcp-server/lease/print",false);
		$API->write('?address='.$Address,true);
	    $READ = $API->read(false);
		$ID = $API->parseResponse($READ);

		if(count($ID)>0){
			return $ID;

		}else{

			$API->write("/ip/dhcp-server/lease/print",false);
			$API->write('?mac-address='.$mac,true);
		    $READ = $API->read(false);
			$ID= $API->parseResponse($READ);

			if(count($ID)>0)
				return $ID;
			else
				return "notFound";
		}

	}

	//metodo para añadir dhcp rate limit
	public static function dhcp_add_rate($API,$Address,$mac,$maxlimit,$bl,$bth,$bt,$comment){

		$API->write("/ip/dhcp-server/lease/add",false);
	    $API->write("=address=".$Address,false);
	    $API->write("=mac-address=".$mac,false);
	    $API->write("=rate-limit=".$maxlimit.' '.$bl.' '.$bth.' '.$bt,false);
	    $API->write("=comment=".$comment,true);
	    $READ = $API->read(false);
		$ARRAY = $API->parseResponse($READ);
	    return $ARRAY;
	}

	//metodo para editar registro en dhcp rate limit
	public static function dhcp_rate_set($API,$id,$mac,$Address,$maxlimit,$bl,$bth,$bt,$comment){

		$API->write("/ip/dhcp-server/lease/set",false);
		$API->write("=.id=".$id,false);
		$API->write("=address=".$Address,false);
		$API->write("=mac-address=".$mac,false);
		$API->write("=rate-limit=".$maxlimit.' '.$bl.' '.$bth.' '.$bt,false);
		$API->write("=comment=".$comment,true);
		$READ = $API->read(false);
		$ARRAY = $API->parseResponse($READ);
	    return $ARRAY;

	}

}
