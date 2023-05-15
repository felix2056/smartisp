<?php
namespace App\libraries;
/**
* Mikrotik - Conversion de bytes a kb,mb,gb,tb.
*/
class Mkconvert
{
	function get_parse($size) {
	     $units = array('', 'k', 'M');
         for ($i = 0; $size > 999; $i++) { $size /= 1000; }
         return round($size, 2).$units[$i];
	}

	function get_parse_sq($size){

		 $units = array('', 'k', 'M');

         for ($i = 0; $size > 999; $i++) {
          $size /= 1000;
      	}

      	 $unit = $units[$i];

      	 if ($unit=='k') {
      	 	return round($size, 2);
      	 }
      	 if ($unit=='M') {

      	 	$size = round($size, 2);
      	 	$total = $size*1000;

      	 	return $total;
      	 }



	}

	function get_reverse($size){


		$ARRAY = explode(' 0', $size[1]);

		return $ARRAY[0];
	}
}
