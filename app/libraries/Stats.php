<?php
namespace App\libraries;
use App\models\PaymentRecord;
use Carbon\Carbon;
use App\models\Box;
use App\models\PaymentNew;

/**
* statistics procesor
*/
class Stats
{
	//metodo para aumentar el contador de clientes de routers
	function get_months_year($year,$type){

        if ($type === 'ou') {
            for ($month=1; $month < 13; $month++) {
                $from = Carbon::create($year, $month, 1, 0, 0, 0);
                $to = $from->clone()->lastOfMonth();

                $data[] = Box::where('type', $type)->whereBetween('date_reg', array($from, $to))->sum('amount');
            }
        }
        else {
            for ($month=1; $month < 13; $month++) {
                $from = Carbon::create($year, $month, 1, 0, 0, 0);
                $to = $from->clone()->lastOfMonth();

                $data[] = PaymentNew::whereBetween('date', array($from, $to))->sum('amount');
            }
        }

		return $data;
	}

	//metod para lestar por años
	function get_per_years($year,$type){
        $start = Carbon::create($year, 1, 1, 0, 0, 0);
        $end = $start->clone()->month(12)->endOfMonth();

        if ($type === 'ou') {
            $total = Box::where('type', $type)->whereBetween('date_reg', array($start, $end))->sum('amount');
        }
        else {
            $total = PaymentNew::whereBetween('date', array($start, $end))->sum('amount');
        }

		return $total;

	}

	//metodo ara listar por mes solo pago por servicio de internet
	function get_per_month(){

		$year = date("Y-m");
		$m = date("m");
	 	$start = $year."-01"; //inicio del mes de enero
		$end = date("Y-m-t", strtotime("$year-$m-01")); //fin del mes de diciembre

		$total = PaymentRecord::where('type','=','se')->whereBetween('date', array($start, $end))->sum('amount');



		return $total;

	}

}
