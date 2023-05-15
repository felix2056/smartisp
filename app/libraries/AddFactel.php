<?php

namespace App\libraries;

use Illuminate\Support\Facades\DB;

/**
 * Client add DB
 */
class AddFactel {

    //metodo para añadir cliente a la BD
    function add($data) {

        $en = new Pencrypt();

        $id = DB::table('factel')->insertGetId(
                array('certificado_digital' => $data['certificado_digital'], 'pass_certificado' => $data['pass_certificado'], 'status' => $data['status'])
        );

        return $id;
    }



}
