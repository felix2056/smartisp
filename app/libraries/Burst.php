<?php
namespace App\libraries;
/**
* Mikrotik - Tratamiento de rafagas
*/
class Burst
{
    public static function get_percent_kb($speed_up,$speed_down,$percent){

        //extraemos los porcentajes
        $speed_up = ($speed_up*$percent)/100;
        $speed_down = ($speed_down*$percent)/100;
        return ['upload' => $speed_up,'download' => $speed_down];
    }



    //obtener todos los burst mas limit_at
    public static function get_all_burst($speed_up,$speed_down,$percent_bl,$percent_bth,$percent_lat){


        $burst_limit = self::get_percent_kb($speed_up,$speed_down,$percent_bl);
        $burst_threshold = self::get_percent_kb($speed_up,$speed_down,$percent_bth);
        $limit_at = self::get_percent_kb($speed_up,$speed_down,$percent_lat);


            //burst limit upload
            $blu = $burst_limit['upload'] == 0 ? 0 : ($speed_up + round($burst_limit['upload'])).'k';
            //burst limit download
            $bld = $burst_limit['download'] == 0 ? 0 : ($speed_down + round($burst_limit['download'])).'k';
            //burst threshold upload
            $btu = $burst_threshold['upload'] == 0 ? 0 : round($burst_threshold['upload']).'k';
            //burst threshold download
            $btd = $burst_threshold['download'] == 0 ? 0 : round($burst_threshold['download']).'k';


            //Limit At upload
            $limit_at_upload = round($limit_at['upload']).'k';
            //Limit At download
            $limit_at_download = round($limit_at['download']).'k';


        return ['blu' => $blu,'bld' => $bld,'btu' => $btu,'btd' => $btd,'lim_at_up' => $limit_at_upload,'lim_at_down' => $limit_at_download];

    }

    //velocidad de up y down mayor a 64k
    public static function check_speed($speed,$limit=false,$spl=64)
    {

        if(is_numeric($speed)){
            if($limit){
                if($speed>=$spl)
                    return true;
                 else
                    return false;
            }
            return true;
        }
        else
            return false;
    }

}
