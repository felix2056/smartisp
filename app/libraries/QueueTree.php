<?php
namespace App\libraries;
/**
* QUEUE TREE FUNCTIONS
*/
class QueueTree
{

	//crear reglas parent
	public static function create_parent($API,$name,$priority){

		$API->write("/queue/tree/add",false);
		$API->write("=name=".$name,false);
		$API->write("=parent=global",false);
		$API->write("=queue=default",false);
		$API->write("=priority=".$priority,true);
		$READ = $API->read(false);
		$ARRAY = $API->parseResponse($READ);
		return $ARRAY;

	}

	//obtener parent
	public static function get_parent($API,$name){

		$ID = $API->comm('/queue/tree/print', array(
	    ".proplist" => ".id",
	    "?name" => $name
	    ));

		if(count($ID)>0)
			return $ID;
		else
			return "notFound";
	}

	//eliminar reglas parent
	public static function delete_parent($API,$id){

		$API->write('/queue/tree/remove', false);
		$API->write('=.id='.$id,true);
		$READ = $API->read(false);
		$ARRAY = $API->parseResponse($READ);

		return $ARRAY;
	}

	//crear regla dentro de parent
	public static function create_child($API,$name,$parent,$packetMarks,$queueType,$priority,$limitAt,$maxLim,$bl,$bth,$bt){

		$bt = $bt == 0 ? '10' : $bt;

		$API->write("/queue/tree/add",false);
		$API->write("=name=".$name,false);
		$API->write("=parent=".$parent,false);
		$API->write("=packet-mark=".$packetMarks,false);
		$API->write("=limit-at=".$limitAt,false);
		$API->write("=queue=".$queueType,false);
		$API->write("=max-limit=".$maxLim,false);
		$API->write("=burst-limit=".$bl,false);
		$API->write("=burst-threshold=".$bth,false);
		$API->write("=burst-time=".$bt,false);
		$API->write("=priority=".$priority,true);
		$READ = $API->read(false);
		$ARRAY = $API->parseResponse($READ);
		return $ARRAY;

	}

	//setear regla child
	public static function set_child($API,$id,$name,$packetMarks,$queueType,$limitAt,$maxLim,$bl,$bth,$bt,$priority){

		$bt = $bt == 0 ? '10' : $bt;

		$API->write("/queue/tree/set",false);
		$API->write("=.id=".$id,false);

		if ($name!=null) {
			$API->write("=name=".$name,false);
		}
		if ($packetMarks!=null) {
			$API->write("=packet-mark=".$packetMarks,false);
		}
		if ($queueType!=null) {
			$API->write("=queue=".$queueType,false);
		}

		$API->write("=limit-at=".$limitAt,false);
		$API->write("=max-limit=".$maxLim,false);
		$API->write("=burst-limit=".$bl,false);
		$API->write("=burst-threshold=".$bth,false);
		$API->write("=burst-time=".$bt,false);
		$API->write("=priority=".$priority,true);
		$READ = $API->read(false);
		$ARRAY = $API->parseResponse($READ);
		return $ARRAY;

	}

}
