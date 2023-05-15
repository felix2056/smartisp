<?php
namespace App\libraries;
/**
* INTERSEPTOR FUNCTIONS
*/
class Intersep
{

	public static function replace($input,$remplace){

		if(empty($input)){
			return $remplace;
		}else{
			return $input;
		}

	}
}
