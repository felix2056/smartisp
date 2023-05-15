<?php
namespace App\libraries;
/**
* Reformat - Parse array form mikrotik
*/
class Reformat
{

	function array_change($array)
	{
		$orig=".id";
		$new="id";

    	foreach ( $array as $k => $v )
        	$return[ $k === $orig ? $new : $k ] = ( is_array( $v ) ? $this->array_change($v) : $v );
    	return $return;
	}

}
