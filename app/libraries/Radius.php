<?php

namespace App\libraries;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

class Radius
{

    // funcion para agregar la configuracion del radius al mkt
    public static function radius_add($API,$secret,$radius_server){
//		Log::debug("Inside  radius add funtion");
//		Log::debug(env('DB_HOST_RADIUS'));
//		Log::debug("Inside  radius add funtion");
//		Log::debug(env('DB_HOST_RADIUS', $_SERVER['SERVER_ADDR']));
//		Log::debug($_SERVER['SERVER_ADDR']);

		$ip = $radius_server;
        $API->write("/radius/add",false);
        $API->write("=service=login,ppp",false);
        $API->write("=secret=".$secret,false);
        $API->write('=address='.$ip,true); // IP
        $READ = $API->read(false);
        $ARRAY = $API->parseResponse($READ);


        $API->write("/ppp/aaa/set",false);
        $API->write("=use-radius=yes",false);
        $API->write("=accounting=yes",false);
        $API->write("=use-circuit-id-in-nas-port-id=yes",true);
        $READ = $API->read(false);
        $ARRAY = $API->parseResponse($READ);

        $API->write("/radius/incoming/set",false);
        $API->write("=accept=yes",true);
        $READ = $API->read(false);
        $ARRAY = $API->parseResponse($READ);


        /**por ahora seteamos el default asi hasta que hablemos con Jerson**/
        /*$API->write("/ppp/profile/print",false);
        $API->write('?name=default',true); // IP
        $READ = $API->read(false);
        $ARRAY = $API->parseResponse($READ);

        foreach($ARRAY as $radius){
            $id = $radius['.id'];
            $API->write("/ppp/profile/set",false);
            $API->write("=.id=".$id,false);
            $API->write("=local-address=".$gateway,true);
            $READ = $API->read(false);
            $ARRAY = $API->parseResponse($READ);
        }*/

        return;

    }

    public static function radius_delete($API,$secret,$radius_server = null){
        set_time_limit(0);

        Artisan::call('cache:clear');
        Artisan::call('config:clear');

        if(is_null($radius_server))
            $radius_server = env('DB_HOST_RADIUS');

        $API->write("/ppp/aaa/set",false);
        $API->write("=use-radius=no",false);
        $API->write("=use-circuit-id-in-nas-port-id=no",false);
        $API->write("=accounting=no",true);
        $READ = $API->read(false);
        $ARRAY = $API->parseResponse($READ);

        $API->write("/radius/print",false);
        $API->write('?address='.$radius_server,false); // IP
        $API->write('?secret='.$secret,true); // IP
        $READ = $API->read(false);
        $ARRAY = $API->parseResponse($READ);

        foreach($ARRAY as $radius){
            $id = $radius['.id'];
            $API->write("/radius/remove",false);
            $API->write("=.id=".$id,true);
            $READ = $API->read(false);
            $ARRAY = $API->parseResponse($READ);
        }

        $API->write("/radius/incoming/set",false);
        $API->write("=accept=no",true);
        $READ = $API->read(false);
        $ARRAY = $API->parseResponse($READ);

        return $ARRAY;

    }

    public static function radius_update($API,$secret){


        $API->write("/radius/print",false);
        $API->write('?address='.env('DB_HOST_RADIUS'),true); // IP
        $READ = $API->read(false);
        $ARRAY = $API->parseResponse($READ);

        foreach($ARRAY as $radius){
            $id = $radius['.id'];
            $API->write("/radius/set",false);
            $API->write("=.id=".$id,false);
            $API->write("=secret=".$secret,true);
            $READ = $API->read(false);
            $ARRAY = $API->parseResponse($READ);
        }
        return $ARRAY;

    }

    public static function radius_update_ip($API,$old_ip,$new_ip,$secret){

        $API->write("/radius/print",false);
        $API->write('?address='.$old_ip,true); // IP
        $READ = $API->read(false);
        $ARRAY = $API->parseResponse($READ);

        foreach($ARRAY as $radius){
            $id = $radius['.id'];
            $API->write("/radius/set",false);
            $API->write("=.id=".$id,false);
            $API->write("=secret=".$secret,false);
            $API->write("=address=".$new_ip,true);
            $READ = $API->read(false);
            $ARRAY = $API->parseResponse($READ);
        }
        return $ARRAY;

    }

}
