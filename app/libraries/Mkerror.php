<?php
namespace App\libraries;
/**
* Mikrotik - Tratamiento de errores
*/
class Mkerror
{
	function process_error($ARRAY) {
	    $encode = array();
    		if(is_array($ARRAY)) {
        		if (array_key_exists('!trap', $ARRAY)) {
            		foreach ($ARRAY as $er) {
                		foreach($er as $m){
							$new = array('msg' =>'mkerror','message' => $m['message']);
            				$encode[] = $new;
                		}
            		}
            		return $encode;
        		}
    		}
    		else
       			return false;
	}
}
