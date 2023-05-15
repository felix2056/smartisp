<?php
namespace App\libraries;
/**
* Rocket - Verificar configuraciones
*/
class CheckConfig
{

  //funccion para verificar si esta activo los avisos el el router
  function checkadv($router){

  	$adv = ControlRouter::find($router);

  	if($adv->adv==1)
		return  false;
	else
		return true;

  }

}


