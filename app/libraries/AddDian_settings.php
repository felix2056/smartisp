<?php

namespace App\libraries;

use Illuminate\Support\Facades\DB;

/**
 * dian_settings add DB
 */
class AddDian_settings {

    //metodo para añadir dian_settings a la BD
	function add($data) {

		$en = new Pencrypt();

		$id = DB::table('dian_settings')->insertGetId(
			array(
				'typeoperation_cod' => $data['typeoperation_cod'],
				'testsetid' => $data['testsetid'],
				'tecnicalkey' => $data['tecnicalkey'],
				'softwarename' => $data['softwarename'],
				'softwareid' => $data['softwareid'],
				'softwarepin' => $data['softwarepin'],
				'resolutiondate' => $data['resolutiondate'],
				'resolutiondatestar' => $data['resolutiondatestar'],
				'resolutiondateend' => $data['resolutiondateend'],
				'resolutionnumber' => $data['resolutionnumber'],
				'prefix' => $data['prefix'],
				'numberstart' => $data['numberstart'],
				'numberend' => $data['numberend'],
				'prefixnc' => $data['prefixnc'],
				'numberstartnc' => $data['numberstartnc'],
				'numberendnc' => $data['numberendnc'],
				'prefixnd' => $data['prefixnd'],
				'numberstartnd' => $data['numberstartnd'],
				'numberendnd' => $data['numberendnd'],
				'typedoc_cod' => $data['typedoc_cod'],
				'identificacion' => $data['identificacion'],
				'businessname' => $data['businessname'],
				'tradename' => $data['tradename'],
				'typetaxpayer_cod' => $data['typetaxpayer_cod'],
				'accountingregime_cod' => $data['accountingregime_cod'],
				'typeresponsibility_cod' => $data['typeresponsibility_cod'],
				'economicactivity_cod' => $data['economicactivity_cod'],
				'municipio_cod' => $data['municipio_cod'],
				'direction' => $data['direction']
			)
		);

		return $id;
	}



}
