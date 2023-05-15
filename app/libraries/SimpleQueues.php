<?php
namespace App\libraries;
////////////////// SIMPLE QUEUES FUNCTIONS //////////////////////////
class SimpleQueues
{
	//metodo para añadir colas simples
	public static function simple_add($API,$nameClient,$target,$maxlimit,$bl,$bth,$bt,$limitat,$priority,$comment){

		$target.='/32';
		$API->write("/queue/simple/add",false);
		$API->write("=name=".$nameClient,false);
		$API->write('=target='.$target,false); // IP
		$API->write('=max-limit='.$maxlimit,false); // 2M/2M [TX/RX]
		$API->write('=burst-limit='.$bl,false);
		$API->write('=burst-threshold='.$bth,false);
		$API->write('=burst-time='.$bt,false);
		$API->write('=limit-at='.$limitat,false);
		$API->write('=priority='.$priority,false);
		$API->write('=comment='.$comment,true); // comentario
		$READ = $API->read(false);
		$ARRAY = $API->parseResponse($READ);

		return $ARRAY;
	}

	//metodo para eliminar colas simples
	public static function simple_remove($API,$id){

		$API->write("/queue/simple/remove",false);
		$API->write("=.id=".$id,true);
		$READ = $API->read(false);
		$ARRAY = $API->parseResponse($READ);

		return $ARRAY;
	}

	//metodo para editar colas simples
	public static function simple_set($API,$id,$nameClient,$maxlimit,$Address,$bl,$bth,$bt,$limitat,$priority,$comment){

		$Address.='/32';
		$API->write("/queue/simple/set",false);
		$API->write("=.id=".$id,false);
		$API->write("=name=".$nameClient,false);
		$API->write("=target=".$Address,false);
		$API->write('=max-limit='.$maxlimit,false);
		$API->write('=burst-limit='.$bl,false);
		$API->write('=burst-threshold='.$bth,false);
		$API->write('=burst-time='.$bt,false);
		$API->write('=limit-at='.$limitat,false);
		$API->write('=priority='.$priority,false);
		$API->write('=comment='.$comment,true); // comentario
		$READ = $API->read(false);
		$ARRAY = $API->parseResponse($READ);
		return $ARRAY;
	}

	//metodo para blockear colas simples
	public static function simple_block($API,$id,$maxlimit,$bl,$bth,$bt,$limitat,$priority,$comment){

		$API->write("/queue/simple/set",false);
		$API->write("=.id=".$id,false);
		$API->write('=max-limit='.$maxlimit,false);
		$API->write('=burst-limit='.$bl,false);
		$API->write('=burst-threshold='.$bth,false);
		$API->write('=burst-time='.$bt,false);
		$API->write('=limit-at='.$limitat,false);
		$API->write('=priority='.$priority,false);
		$API->write('=comment='.$comment,true); // comentario
		$READ = $API->read(false);
		$ARRAY = $API->parseResponse($READ);
		return $ARRAY;
	}

	//metodo para buscar y recuperar la ID colas simples
	public static function simple_get_id($API,$target,$name){

		$target.='/32';


		$ID = $API->comm('/queue/simple/print', array(
		".proplist" => ".id",
		"?name" => $name
		));

		//verificamos si el usuario esta en el router
		if(count($ID)>0){
			return $ID;
		}
		else{

			$ID = $API->comm('/queue/simple/print', array(
			".proplist" => ".id",
			"?target" => $target
			));

			if (count($ID)>0) {

				return $ID;

			}else{
				return "notFound";
			}

		}


	}

	//metodo para verificar duplicidad de registro en colas simples
	public static function simple_check($API,$target){
		$target.='/32';
		$API->write("/queue/simple/print",false);
		$API->write('?target='.$target,true);
	    $READ = $API->read(false);
		$ARRAY = $API->parseResponse($READ);

		if(count($ARRAY)>0)
			return true;

		return false;

	}

}
