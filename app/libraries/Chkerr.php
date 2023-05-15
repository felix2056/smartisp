<?php
namespace App\libraries;
use Illuminate\Support\Facades\Response;

/**
* Rocket - Tratamiento y visualizacion de errores
*/
class Chkerr
{
    function check($array){

        if($array){
            //verificamos si es un array
            if(is_array($array)){
                if($array[0]['msg']=='mkerror')
                    return Response::json($array);
            }
            else{

                if($array=='ok') return false;
                if($array=='duplicate_arp' || $array=='duplicate_simpleq' || $array=='duplicate_dhcp' || $array=='hot_duplicate') return $this->show('erroripduplicate');


                return false;
            }
        }//end control errors
        else
            return false;

    }

	function show($string) {
	   return Response::json(array(array('msg'=> $string)));
	}



}
