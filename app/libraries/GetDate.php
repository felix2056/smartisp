<?php
namespace App\libraries;
/**
* Rocket - Tratamiento y visualizacion de errores
*/
class GetDate
{
    function parse_date($date){

        if($date>12)
            $date=($date-12);

        switch ($date) {
        case 1:
            return $dt = 'Enero';
        break;
        case 2:
            return $dt = 'Febrero';
        break;
        case 3:
            return $dt = 'Marzo';
        break;
        case 4:
            return $dt = 'Abril';
        break;
        case 5:
            return $dt = 'Mayo';
        break;
        case 6:
            return $dt = 'Junio';
        break;
        case 7:
            return $dt = 'Julio';
        break;
        case 8:
            return $dt = 'Agosto';
        break;
        case 9:
            return $dt = 'Septiembre';
        break;
        case 10:
            return $dt = 'Octubre';
        break;
        case 11:
            return $dt = 'Noviembre';
        break;
        case 12:
            return $dt = 'Diciembre';
        break;
        }
    }

    function get_date($cant,$sum=1){
        $dates = date('n');
        $dates = $dates + $sum;
        for ($i=0; $i < $cant; $i++) {

            if(isset($df))
                $df .=' '.$this->parse_date($dates);
            else
                $df = $this->parse_date($dates);

            $dates++;
        }

        return $df;
    }

}
