<?php
namespace App\libraries;
/**
* NumBill - Numeración para facturas 0001
*/
class Numbill
{

	function get_format($numero,$ceros=4) {
		$order_diez = explode(".",$numero);
		$dif_diez = $ceros - strlen($order_diez[0]);
		for($m = 0 ; $m < $dif_diez; $m++)
		{
		        @$insertar_ceros .= 0;
		}

		if($dif_diez==0)
			return $numero;

		return $insertar_ceros .= $numero;
	}

}
