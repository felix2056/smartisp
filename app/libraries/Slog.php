<?php
namespace App\libraries;
use App\models\Logg;
use Illuminate\Support\Facades\Auth;

/**
* Guardar Logs
*/
class Slog
{
	//metodo para añadir logs a la BD
	function save($men,$type,$value=''){
            $log = new Logg();
            $log->detail = $men.' '.$value;
            $log->user = auth()->user()->username;
            $log->type = $type;
            $log->save();
	}
	//metodo para añadir logs a la BD
	function saveTo($men,$type,$value='',$username){
            $log = new Logg();
            $log->detail = $men.' '.$value;
            $log->user = $username;
            $log->type = $type;
            $log->save();
	}

	//metodo para añadir logs no incluye usuario
	function savenotuser($men,$type,$value=''){

		   $log = new Logg();
         $log->detail = $men.' '.$value;
         $log->user = "System";
         $log->type = $type;
         $log->save();

	}


}
