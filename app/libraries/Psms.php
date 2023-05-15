<?php
namespace App\libraries;
/**
*  SMS Mikrotik FUNCTIONS
*/
class Psms
{

	public static function set_channel($API,$port,$channel,$enabled){

        //buscamos y verificamos la propiedad
        $API->write("/tool/sms/print",true);
        $READ = $API->read(false);
        $ARRAY = $API->parseResponse($READ);

        if ($ARRAY[0]['receive-enabled']=="false") {
			$API->write("/tool/sms/set",false);
			$API->write("=port=".$port,false);
			if ($enabled == 1) {
				$API->write("=receive-enabled=yes",false);
			}

			$API->write("=channel=".$channel,true);
			$READ = $API->read(false);

		}

		if ($ARRAY[0]['receive-enabled']=="true") {
			//desactivamos caso contrario no nos dejara cambiar los valores
			$API->write("/tool/sms/set",false);
			$API->write("=receive-enabled=no",true);
			$READ = $API->read(false);
			sleep(2);
			//cambiamos los valores
			$API->write("/tool/sms/set",false);
			$API->write("=port=".$port,false);
			if ($enabled == 1) {
				$API->write("=receive-enabled=yes",false);
			}
			$API->write("=channel=".$channel,true);
			$READ = $API->read(false);
		}

	}


	public static function send_sms($API,$port,$channel,$phone,$message){

			$API->write("/tool/sms/send",false);
            $API->write("=port=".$port,false);
            $API->write("=channel=".$channel,false);
            $API->write("=phone-number=".$phone,false);
            $API->write("=message=".$message,true);
            $READ = $API->read(false);
            $ARRAY = $API->parseResponse($READ);
            return $ARRAY;
	}

	public static function get_sms($API){

			$API->write("/tool/sms/inbox/print",true);
			$READ = $API->read(false);
            $ARRAY = $API->parseResponse($READ);
            return $ARRAY;
	}

}
