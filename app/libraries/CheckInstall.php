<?php
namespace App\libraries;
use App\models\AdvSetting;
use App\models\GlobalSetting;
/**
*  Check Install sistem
*/
class CheckInstall
{

    /**
     * @return bool
     */
    public static function check()
    {

    	//comprobamos si existe el archivo de configuración user_conf.php
    	$file = file_exists(config_path().'/db_conf.php');

		if ($file) {

			//veridicamos si es la primera vez que accede al sistema
    		$global = GlobalSetting::all()->first();

	    	if ($global->status=='in') {
	    		//configuramos automaticamente el portal cliente
	    		$ip = new Getip();
	    		$adv = AdvSetting::find(1);
	    		$adv->routers_adv = '1';
	    		$adv->ip_server = $ip->getServer();
	    		$adv->server_path = 'aviso';
	    		$adv->save();
	    		//actualizamos el global settings
	    		$global->status = 'ac';
	    		$global->save();
	    	}

			return false;

		}else{
			return true;
		}

    }

}
