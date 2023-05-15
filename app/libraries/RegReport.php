<?php
namespace App\libraries;
use App\models\PaymentRecord;
use Illuminate\Support\Facades\Auth;

/**
* Mikrotik - Tratamiento de errores
*/
class RegReport
{
	function add($data,$pay_id=0) {

                $report = new PaymentRecord;
                $report->user_id = Auth::user()->id;
                $report->name = $data['name'];
                $report->router_id = $data['router_id'];
                $report->box_id = $data['box_id'];
                $report->payment_id = $pay_id;
                $report->detail = $data['detail'];
                $report->type = $data['type'];
                $report->date = date('Y-m-d');
                $report->amount = $data['amount'];
                $report->save();
	}
}
