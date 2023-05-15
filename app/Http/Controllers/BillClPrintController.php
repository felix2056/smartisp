<?php

namespace App\Http\Controllers;

use App\libraries\CheckUser;
use App\models\BillCustomer;
use App\models\GlobalSetting;
use Barryvdh\DomPDF\Facade as PDF;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\View;

class BillClPrintController extends BaseController
{

	public function printbill($id)
	{

		$user = CheckUser::isLogin();
		if ($user == 1) {
			return Redirect::to('/');
		}

		if (empty($id)) {
			return "Factura no encontrada";
		}

		$invoice = BillCustomer::select('id', 'num_bill', 'expiration_date', 'total_pay', 'release_date', 'period', 'client_id', 'iva', 'cost', 'open', 'status', 'start_date')
		->where('id', $id)
            ->with([
                'client' => function($query) {
                    $query->select('id','name','address','phone','email','dni')
                        ->with([
                            'billing_settings:id,client_id,billing_address'
                        ])
                        ->get();
                },
                'invoice_items'
            ])->first();

		$client = $invoice->client;

		if (empty($id)) {
			return "Factura no encontrada";
		}
		$cant_i=$invoice->count();
		if ($cant_i > 0) {

			$global = GlobalSetting::all()->first();

			$df = strtotime($invoice->expiration_date);
			$pd = strtotime($invoice->release_date);
			$sd = strtotime($invoice->start_date);
			$period = strtotime($invoice->period);

			$costIva = ($invoice->iva * $invoice->cost) / 100;
			$costIva = number_format($costIva, 2);

			$data = array(
				"cliente" => $client->name,
				"direccionCliente" => ($client->billing_settings->billing_address) ? $client->billing_settings->billing_address : $client->address,
				"telefonoCliente" => $client->phone,
				"emailCliente" => $client->email,
				"dniCliente" => $client->dni,
				"fechaPago" => date('d/m/Y', $pd),
				"vencimiento" => date('d/m/Y', $df),
				"numFactura" => $invoice->num_bill,
                "vatNumber" => $client->dni,
                "service" => $client->service,
//				"subida" => $client->plan->upload,
//				"descarga" => $client->plan->download,
//				"plan" => $client->plan->name,
				"costo" => $invoice->cost,
				"total" => $invoice->total_pay,
				"Smoneda" => $global->smoney,
				"moneda" => $global->nmoney,
				"hastafecha" => date('d/m/Y', $period),
				"empresa" => $global->company,
				"iva" => $costIva,
				"start_date" => date('d/m/Y', $sd),
				"status" => $invoice->status,
				"gen" => false,
				"invoice_items" => $invoice->invoice_items,
			);
            //marcamos como visto la factura del cliente

			$invoice->open = 1;
			$invoice->save();

			$html = View::make("templates.Factura_cliente", $data);
			return $html;
		}
	}

	public function download($id)
	{

		$user = CheckUser::isLogin();
		if ($user == 1) {
			return Redirect::to('/');
		}

		$invoice = BillCustomer::select('id','num_bill','expiration_date','total_pay','release_date','period','client_id','iva','cost','open','status','start_date')
		->where('id', $id)
		->with([
			'client' => function($query) {
				$query->select('id','name','address','phone','email','dni')
				->with([
					'plan:id,name,upload,download',
					'billing_settings:id,client_id,billing_address'
				])
				->get();
			},
			'invoice_items'
		])->first();

		$client = $invoice->client;

		if (empty($id)) {
			return "Factura no encontrada";
		}

		$cant_i=$invoice->count();
		if ($cant_i > 0) {

			$global = GlobalSetting::all()->first();

			$df = strtotime($invoice->expiration_date);
			$pd = strtotime($invoice->release_date);
			$sd = strtotime($invoice->start_date);
			$period = strtotime($invoice->period);

			$costIva = ($invoice->iva * $invoice->cost) / 100;
			$costIva = number_format($costIva, 2);

			$data = array(
				"cliente" => $client->name,
				"direccionCliente" => ($client->billing_settings->billing_address) ? $client->billing_settings->billing_address : $client->address,
				"telefonoCliente" => $client->phone,
				"emailCliente" => $client->email,
				"dniCliente" => $client->dni,
				"fechaPago" => date('d/m/Y', $pd),
				"vencimiento" => date('d/m/Y', $df),
				"numFactura" => $invoice->num_bill,
                "vatNumber" => $client->dni,
                "service" => $client->service,
//				"subida" => $client->plan->upload,
//				"descarga" => $client->plan->download,
//				"plan" => $client->plan->name,
				"costo" => $invoice->cost,
				"total" => $invoice->total_pay,
				"Smoneda" => $global->smoney,
				"moneda" => $global->nmoney,
				"empresa" => $global->company,
				"iva" => $costIva,
				"hastafecha" => date('d/m/Y', $period),
				"start_date" => date('d/m/Y', $sd),
				"status" => $invoice->status,
				"invoice_items" => $invoice->invoice_items,
				"gen" => false
			);
            //marcamos como visto la factura del cliente

			$invoice->open = 1;
			$invoice->save();

			$pdf = PDF::loadView("templates.Factura_cliente", $data);
			return $pdf->download('invoice.pdf');

		}

	}

}
