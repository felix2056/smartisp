<?php
namespace App\libraries;
////////////////// SIMPLE QUEUES FUNCTIONS //////////////////////////
class SimpleQueuesTree
{

		public static function simple_parent_get_id($API,$name){

			$ID = $API->comm('/queue/simple/print', array(".proplist" => ".id", "?name" => $name));

			//verificamos si el usuario esta en el router
			if(count($ID)>0){
				return $ID;
			}else{
				return "notFound";
			}

		}

		public static function simple_child_get_id($API,$name){

			$API->write("/queue/simple/print",false);
			$API->write('?name='.$name,true);
		    $READ = $API->read(false);
			$ID = $API->parseResponse($READ);

			if(count($ID)>0){
				return $ID;
			}else{
				return "notFound";
			}

		}

		public static function simple_parent_remove($API,$id){

			$API->write("/queue/simple/remove",false);
			$API->write("=.id=".$id,true);
			$READ = $API->read(false);
			$ARRAY = $API->parseResponse($READ);

			return $ARRAY;
		}

		public static function add_simple_child($API,$nameClient,$target,$parent,$maxlimit,$bl,$bth,$bt,$limitat,$priority,$comment){

			$target.='/32';
			$API->write("/queue/simple/add",false);
			$API->write("=name=".$nameClient,false);
			$API->write('=target='.$target,false);
			$API->write('=max-limit='.$maxlimit,false);
			$API->write('=burst-limit='.$bl,false);
			$API->write('=burst-threshold='.$bth,false);
			$API->write('=burst-time='.$bt,false);
			$API->write('=limit-at='.$limitat,false);
			$API->write('=priority='.$priority,false);
			$API->write('=queue=pcq-upload-default/pcq-download-default',false);
			$API->write('=parent='.$parent,false);
			$API->write('=comment='.$comment,true); // comentario
			$READ = $API->read(false);
			$ARRAY = $API->parseResponse($READ);

			return $ARRAY;

		}

		public static function set_simple_child($API,$id,$nameClient,$maxlimit,$Address,$parent,$bl,$bth,$bt,$limitat,$priority,$comment){

			$Address.='/32';
			$API->write("/queue/simple/set",false);
			$API->write("=.id=".$id,false);
			$API->write("=name=".$nameClient,false);
			$API->write("=target=".$Address,false);
			$API->write("=max-limit=".$maxlimit,false);
			$API->write("=limit-at=".$limitat,false);
			$API->write("=burst-limit=".$bl,false);
			$API->write("=burst-threshold=".$bth,false);
			$API->write("=burst-time=".$bt,false);
			$API->write("=parent=".$parent,false);
			$API->write("=priority=".$priority,false);
			$API->write("=queue=pcq-upload-default/pcq-download-default",false);
			$API->write("=comment=".$comment,true); // comentario

			$READ = $API->read(false);
			$ARRAY = $API->parseResponse($READ);

			return $ARRAY;

		}


		public static function add_simple_parent($API,$name,$targets,$maxlimit,$bl,$bth,$bt,$limitat,$priority,$comment){


			$API->write("/queue/simple/add",false);
			$API->write("=name=".$name,false);
			$API->write('=target='.$targets,false);
			$API->write('=max-limit='.$maxlimit,false);
			$API->write('=burst-limit='.$bl,false);
			$API->write('=burst-threshold='.$bth,false);
			$API->write('=burst-time='.$bt,false);
			$API->write('=limit-at='.$limitat,false);
			$API->write('=priority='.$priority,false);
			$API->write('=queue=pcq-upload-default/pcq-download-default',false);
			$API->write('=comment='.$comment,true); // comentario
			$READ = $API->read(false);
			$ARRAY = $API->parseResponse($READ);

			return $ARRAY;

		}

		public static function set_simple_parent($API,$id,$name,$maxlimit,$targets,$bl,$bth,$bt,$limitat,$priority,$comment){

			$API->write("/queue/simple/set",false);
			$API->write("=.id=".$id,false);
			$API->write("=name=".$name,false);
			$API->write("=target=".$targets,false);
			$API->write('=max-limit='.$maxlimit,false);
			$API->write('=burst-limit='.$bl,false);
			$API->write('=burst-threshold='.$bth,false);
			$API->write('=burst-time='.$bt,false);
			$API->write('=limit-at='.$limitat,false);
			$API->write('=priority='.$priority,false);
			$API->write('=queue=pcq-upload-default/pcq-download-default',false);
			$API->write('=comment='.$comment,true); // comentario
			$READ = $API->read(false);
			$ARRAY = $API->parseResponse($READ);
			return $ARRAY;


		}


}
