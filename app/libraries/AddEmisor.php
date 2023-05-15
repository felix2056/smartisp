<?php

namespace App\libraries;

use Illuminate\Support\Facades\DB;

/**
 * Client add DB
 */
class AddEmisor {

    //metodo para añadir cliente a la BD
	function add($data) {

		$en = new Pencrypt();

		$id = DB::table('emisor')->insertGetId(
			array('ruc' => $data['ruc'], 'razonSocial' => $data['razonSocial'], 'nombreComercial' => $data['nombreComercial'], 'direccion' => $data['direccion'], 'status_cont' => $data['status_cont'])
		);

		return $id;
	}



}
