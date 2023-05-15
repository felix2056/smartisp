<?php
namespace App\libraries;
use App\models\BillCustomer;
use App\models\Client;
use App\models\GlobalSetting;
use App\models\PaymentRecord;
use App\models\Plan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

/**
* REgister pay for client
*/
class RegPay
{
	//metodo para aumentar el contador de clientes de routers
	function add($client_id,$ex_date,$planCost,$iva,$plan_id,$router_id,$cant,$months){

		$numBill = GlobalSetting::all()->first();
		$nbill = $numBill->num_bill + 1;
		$numBill->num_bill = $nbill;
		$numBill->save();

		$plan = Plan::find($plan_id);
		$iva = $plan->iva;
		$Totalcost = ($planCost * $cant);
		$TC = $Totalcost + ($iva*($Totalcost/100));

		$after_date = strtotime ( '+'.$cant.' month' , strtotime ($ex_date));
		$after_date = date ( 'Y-m-d' , $after_date);

		$num = new Numbill();

		$id = DB::table('payments')->insertGetId(
				array('client_id' => $client_id, 'pay_date' => date('Y-m-d'),
				'amount' => $planCost, 'iva' => $iva,
				'total_amount' => $TC, 'num_bill' => $num->get_format($nbill), 'month_pay' => $months,
				'plan_id' => $plan_id, 'router_id' => $router_id, 'expiries_date' => $ex_date, 'after_date' => $after_date, 'created_at' => date("Y-m-d H:i:s"),
				'updated_at' => date("Y-m-d H:i:s"))
		);

		$client = Client::find($client_id);

		$report = new PaymentRecord();
		$report->user_id = Auth::user()->id;
        $report->name = $client->name;
        $report->router_id = $router_id;
        $report->box_id = 0;
        $report->payment_id = $id;
        $report->detail = 'Pago por servicio de internet | nombre del plan: '.$plan->name.' | meses pagados: '.$months;
        $report->type = 'se';
        $report->date = date('Y-m-d');
        $report->amount = $TC;
        $report->save();

        $df = strtotime($ex_date);
	    $afd = strtotime($after_date);


        //registramos la factura al cliente
        $bill = new BillCustomer();
        $bill->num_bill = $num->get_format($nbill);
        $bill->period = date('d/m/Y',$afd);
        $bill->release_date = date('Y-m-d');
        $bill->expiration_date = $ex_date;
        $bill->total_pay = $TC;
        $bill->cost = $planCost;
        $bill->iva = $iva;
        $bill->status = 1;
        $bill->client_id = $client_id;
        $bill->open = 0;
        $bill->save();

	}

}
