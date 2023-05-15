<?php
namespace App\libraries;
/**
* Recalculate speed
*/
class RecalculateSpeed
{

		public static function speed($speed,$num_clients,$suffix){

			//recalculamos la velocidad
            $speed = str_replace('k', '', $speed);

			$speed = $speed*$num_clients;

			if ($suffix) { //añadimos sufijo 'k' a la velocidad

				$speed=$speed.'k';

			}

			return $speed;


		}

}
