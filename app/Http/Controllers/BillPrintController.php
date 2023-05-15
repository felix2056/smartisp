<?php

namespace App\Http\Controllers;
use App\libraries\Numbill;
use App\models\BillCustomer;
use App\models\Client;
use App\models\GlobalSetting;
use App\models\InvoiceCsvFile;
use App\models\Payment;
use App\models\PaymentNew;
use App\models\Template;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\View;
use App\Classes\Reply;
use Mail;
// use PDF;

use Barryvdh\DomPDF\Facade as PDF;



class BillPrintController extends BaseController
{

    public function __construct()
    {
//        $this->beforeFilter('auth');  //bloqueo de acceso
    }

    public function SendInvoicePDF($id)
    {

        $invoice = BillCustomer::select('id','num_bill','expiration_date','total_pay','release_date','period','client_id','iva','cost','open','status','start_date')
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

            return Reply::error('Factura no encontrada');
        }

        if ($invoice) {

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
//                "subida" => $client->plan->upload,
//                "descarga" => $client->plan->download,
//                "plan" => $client->plan->name,
                "service" => $client->service,
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

            Mail::send('templates.Factura_cliente', $data, function($message) use ($data, $global) {
                $message->from($global->email, $global->company);
                $message->to($data['emailCliente'],$data['emailCliente'])->subject('Factura');
            });

            return Reply::success('Factura enviada con exito.');

        }
    }

    public function showInvoicePDF($id)
    {

        $invoice = BillCustomer::select('id','num_bill','expiration_date','total_pay','release_date','period','client_id','iva','cost','open','status','start_date')
        ->where('id', $id)
        ->with([
            'client' => function($query) {
                $query->select('id','name','address','phone','email','dni')
                ->with([
                    'billing_settings:id,client_id,billing_address'
                ])
                ->get();
            },
            'invoice_items',
            'client.service'
        ])->first();

        $client = $invoice->client;

        if (empty($id)) {
            return "Factura no encontrada";
        }

        if ($invoice) {

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
                "vatNumber" => $client->dni,
                "emailCliente" => $client->email,
                "dniCliente" => $client->dni,
                "fechaPago" => date('d/m/Y', $pd),
                "vencimiento" => date('d/m/Y', $df),
                "numFactura" => $invoice->num_bill,
//                "subida" => $client->plan->upload,
//                "descarga" => $client->plan->download,
//                "plan" => $client->plan->name,
                "service" => $client->service,
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
                "gen" => false,
                "global" => $global
            );
            //marcamos como visto la factura del cliente

            $invoice->open = 1;
            $invoice->save();

            $view = $global->factura_template ? $global->factura_template->filename : Template::where('type', 'invoice')->first()->filename;

            $view = explode('.', $view);
            if (request()->has('download')) {
                set_time_limit(0);
                $pdf = PDF::loadView("templates/$view[0]", $data);
                return $pdf->download('invoice.pdf');
            }


            $html = View::make("templates/$view[0]", $data);
            return $html;

        }
    }


    public function printInvoicePDF($id)
    {
        $invoice = BillCustomer::select('id','num_bill','expiration_date','total_pay','release_date','period','client_id','iva','cost','open','status','start_date')
        ->where('id', $id)
        ->with([
            'client' => function($query) {
                $query->select('id','name','address','phone','email','dni')
                ->with([
                    'billing_settings:id,client_id,billing_address'
                ])
                ->get();
            },
            'invoice_items',
            'client.service'
        ])->first();

        $client = $invoice->client;

        if (empty($id)) {
            return "Factura no encontrada";
        }

        if ($invoice) {

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
//                "subida" => $client->plan->upload,
//                "descarga" => $client->plan->download,
//                "plan" => $client->plan->name,
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
                "global" => $global
            );
            //marcamos como visto la factura del cliente

            $invoice->open = 1;
            $invoice->save();

            $view = $global->factura_template ? $global->factura_template->filename : Template::where('type', 'invoice')->first()->filename;
            $view = explode('.', $view);
            $html = View::make("templates/$view[0]", $data);
            return $html;
        }
    }

    public function download($id)
    {
        set_time_limit(0);
        $invoice = BillCustomer::find($id);

        if (empty($id)) {
            return "Factura no encontrada";
        }
        $client = Client::with('billing_settings')->find($invoice->client_id);
        //buscamos todos los datos
        $print = DB::table('bill_customers')
        ->join('clients', 'clients.id', '=', 'bill_customers.client_id')
        ->join('plans', 'plans.id', '=', 'clients.plan_id')
        ->select('bill_customers.num_bill', 'bill_customers.expiration_date', 'bill_customers.total_pay',
            'bill_customers.release_date', 'bill_customers.period', 'plans.upload', 'plans.download',
            'plans.name As planname', 'bill_customers.cost', 'bill_customers.iva', 'clients.name As client', 'clients.address',
            'clients.phone', 'clients.email', 'clients.dni')->where('bill_customers.id', $id)->where('bill_customers.client_id', $client->id)->get();

        if (count($print) > 0) {

            $global = GlobalSetting::all()->first();

            $costIva = $print[0]->iva * ($print[0]->cost / 100);
            $costIva = round($costIva, 2);

            $df = strtotime($print[0]->expiration_date);
            $pd = strtotime($print[0]->release_date);

            $data = array(
                "cliente" => $print[0]->client,
                "direccionCliente" => ($client->billing_settings->billing_address) ? $client->billing_settings->billing_address : $print[0]->address,
                "telefonoCliente" => $print[0]->phone,
                "emailCliente" => $print[0]->email,
                "dniCliente" => $print[0]->dni,
                "fechaPago" => date('d/m/Y', $pd),
                "vencimiento" => date('d/m/Y', $df),
                "numFactura" => $print[0]->num_bill,
                "vatNumber" => $client->dni,
                "subida" => $print[0]->upload,
                "descarga" => $print[0]->download,
                "plan" => $print[0]->planname,
                "costo" => $print[0]->cost,
                "total" => $print[0]->total_pay,
                "Smoneda" => $global->smoney,
                "moneda" => $global->nmoney,
                "hastafecha" => $print[0]->period,
                "empresa" => $global->company,
                "iva" => $costIva,
                "paid" => true,
                "gen" => false
            );
            //marcamos como visto la factura del cliente

            $invoice->open = 1;
            $invoice->save();

            $view = $global->factura_template ? $global->factura_template->filename : Template::where('type', 'invoice')->first()->filename;
            $view = explode('.', $view);

            $pdf = PDF::loadView("templates/$view[0]", $data);
            return $pdf->download('Factura_cliente.pdf');
        }
    }

    public function downloadCsv($id)
    {
        set_time_limit(0);
        $invoice = BillCustomer::find($id);

        if (empty($id)) {
            return "Factura no encontrada";
        }
        $client = Client::with('billing_settings')->find($invoice->client_id);
        //buscamos todos los datos
        $print = BillCustomer::with('invoice_items')
        ->join('clients', 'clients.id', '=', 'bill_customers.client_id')
        ->select('bill_customers.id','bill_customers.num_bill', 'bill_customers.expiration_date', 'bill_customers.total_pay',
            'bill_customers.release_date', 'bill_customers.period', 'bill_customers.cost', 'bill_customers.iva', 'clients.name As client', 'clients.address',
            'clients.phone', 'clients.email', 'clients.dni')->where('bill_customers.id', $id)->where('bill_customers.client_id', $client->id)->first();

        if ($print) {

            $payments = PaymentNew::where('num_bill', $print->num_bill)->get();

            $global = GlobalSetting::all()->first();

            $costIva = $print->iva * ($print->cost / 100);
            $costIva = round($costIva, 2);

            $df = strtotime($print->expiration_date);
            $pd = strtotime($print->release_date);

            $data = array(
                "cliente" => $print->client,
                "direccionCliente" => ($client->billing_settings->billing_address) ? $client->billing_settings->billing_address : $print->address,
                "telefonoCliente" => $print->phone,
                "emailCliente" => $print->email,
                "dniCliente" => $print->dni,
                "fechaPago" => date('d/m/Y', $pd),
                "vencimiento" => date('d/m/Y', $df),
                "numFactura" => $print->num_bill,
                "vatNumber" => $client->dni,
                "costo" => $print->cost,
                "total" => $print->total_pay,
                "Smoneda" => $global->smoney,
                "moneda" => $global->nmoney,
                "hastafecha" => $print->period,
                "empresa" => $global->company,
                "iva" => $costIva,
                "ivaPlan" => $print->iva,
                "paid" => true,
                "gen" => false,
                "global" => $global
            );
            //marcamos como visto la factura del cliente

            $invoice->open = 1;
            $invoice->save();

            $fileName = "Invoice_".Carbon::now()->format('Y_m_d_H_i_s').'.csv';


            $headers = array(
                "Content-type"        => "text/csv",
                "Content-Disposition" => "attachment; filename=$fileName",
                "Pragma"              => "no-cache",
                "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
                "Expires"             => "0"
            );

            $callback = function() use($data, $print, $payments) {
                $file = fopen('php://output', 'w');
                $delimiter = ';';
                try {
                    fputcsv($file, ["FACTURA:",$data['numFactura'], 'SmartISP'], $delimiter);
                    fputcsv($file, ["CLIENTE:", $data['cliente']], $delimiter);
                    fputcsv($file, ["ID CLIENTE (RIF/C.I.):", $data['dniCliente']], $delimiter);
                    fputcsv($file, ["DIRECCION:", $data['direccionCliente']], $delimiter);
                    fputcsv($file, ["TELEFONO:", $data['telefonoCliente']], $delimiter);
                    fputcsv($file, ["DESCRIPCION.............................", "CODIGO................", "CANT..... ", "% tax (IVA)..", "PRECIO UNIT........."], $delimiter);

                    foreach($print->invoice_items as $item) {
                        if($item->plan) {
                            fputcsv($file, ["Plan ".$item->plan->name, "", "1", $item->iva, $item->price, $item->total], $delimiter);

                        } else {
                            fputcsv($file, ["No Plan", "", "1", $item->iva, $item->price, $item->total], $delimiter);

                        }
                    }

                    $directoPago = $payments->where('way_to_pay', 'Directo Pago')->sum('amount');
                    $cash = $payments->where('way_to_pay', 'Cash')->sum('amount');
                    $paypal = $payments->where('way_to_pay', 'PayPal')->sum('amount');
                    $payu = $payments->where('way_to_pay', 'PayU')->sum('amount');
                    $stripe = $payments->where('way_to_pay', 'Stripe')->sum('amount');

                    fputcsv($file, [$data['hastafecha']], $delimiter);
                    fputcsv($file, ["Total:", '', $data['total']], $delimiter);
                    fputcsv($file, ["EFECTIVO-BS (Directo pago):", $directoPago ?? 0.00], $delimiter);
                    fputcsv($file, ["CHEQUES:", 0.00], $delimiter);
                    fputcsv($file, ["TARJ/DEBITO:", 0.00], $delimiter);
                    fputcsv($file, ["PAGO MOVIL:", 0.00], $delimiter);
                    fputcsv($file, ["TRANSFEREN-Bs:", 0.00], $delimiter);
                    fputcsv($file, ["TRANSF-IGTF:", 0.00], $delimiter);
                    fputcsv($file, ["PAYPAL-IGTF:", $paypal ?? 0.00], $delimiter);
                    fputcsv($file, ["CASH-USD-IGTF:", $cash ?? 0.00], $delimiter);
                    fputcsv($file, ["STRIPE-USD-IGTF:", $stripe ?? 0.00], $delimiter);
                    fputcsv($file, ["PAYU-USD-IGTF:", $payu ?? 0.00], $delimiter);
                    fputcsv($file, ["CREDITO:", 0.00], $delimiter);
                    fputcsv($file, ["NOTA 1:", ''], $delimiter);
                    fputcsv($file, ["NOTA 2:", ''], $delimiter);
                    fputcsv($file, ["NOTA 3:", ''], $delimiter);

                } catch (\Exception $exception) {
                    throw $exception;
                }

                fclose($file);
            };


            return response()->stream($callback, 200, $headers);
        }
    }

    public function sendToFiscal($id)
    {
        set_time_limit(0);
        $invoice = BillCustomer::find($id);

        if (empty($id)) {
            return "Factura no encontrada";
        }
        $client = Client::with('billing_settings')->find($invoice->client_id);
        //buscamos todos los datos
        $print = BillCustomer::with('client', 'invoice_items')
            ->where('status', '!=', 3)
            ->where('csv_generated', '=', 0)->first();

        if ($print) {

            //marcamos como visto la factura del cliente

            $payments = PaymentNew::where('num_bill', $print->num_bill)->get();
            $client = $print->client;
            $global = GlobalSetting::all()->first();

            $costIva = $print->iva * ($print->cost / 100);
            $costIva = round($costIva, 2);

            $df = strtotime($print->expiration_date);
            $pd = strtotime($print->release_date);

            $data = array(
                "cliente" => $client->name,
                "direccionCliente" => ($client->billing_settings->billing_address) ? $client->billing_settings->billing_address : $print->address,
                "telefonoCliente" => $print->phone,
                "emailCliente" => $print->email,
                "dniCliente" => $print->dni,
                "fechaPago" => date('d/m/Y', $pd),
                "vencimiento" => date('d/m/Y', $df),
                "numFactura" => $print->num_bill,
                "vatNumber" => $client->dni,
                "costo" => $print->cost,
                "total" => $print->total_pay,
                "Smoneda" => $global->smoney,
                "moneda" => $global->nmoney,
                "hastafecha" => $print->period,
                "empresa" => $global->company,
                "iva" => $costIva,
                "ivaPlan" => $print->iva,
                "paid" => true,
                "gen" => false,
                "global" => $global
            );

            $invoiceNUm = $data['numFactura'];

            $fileName = "Invoice_$invoiceNUm._".Carbon::now()->format('Y_m_d_H_i_s').'.csv';

            // output up to 5MB is kept in memory, if it becomes bigger it will
            // automatically be written to a temporary file
            $file = fopen('php://temp/maxmemory:'. (5*1024*1024), 'r+');
            $delimiter = ';';
            try {
                fputcsv($file, ["FACTURA:",$data['numFactura'], 'SmartISP'], $delimiter);
                fputcsv($file, ["CLIENTE:", $data['cliente']], $delimiter);
                fputcsv($file, ["ID CLIENTE (RIF/C.I.):", $data['dniCliente']], $delimiter);
                fputcsv($file, ["DIRECCION:", $data['direccionCliente']], $delimiter);
                fputcsv($file, ["TELEFONO:", $data['telefonoCliente']], $delimiter);
                fputcsv($file, ["DESCRIPCION.............................", "CODIGO................", "CANT..... ", "% tax (IVA)..", "PRECIO UNIT........."], $delimiter);

                foreach($print->invoice_items as $item) {
                    if($item->plan) {
                        fputcsv($file, ["Plan ".$item->plan->name, "", "1", $item->iva, $item->price, $item->total], $delimiter);

                    } else {
                        fputcsv($file, ["No Plan", "", "1", $item->iva, $item->price, $item->total], $delimiter);

                    }
                }

                $directoPago = $payments->where('way_to_pay', 'Directo Pago')->sum('amount');
                $cash = $payments->where('way_to_pay', 'Cash')->sum('amount');
                $paypal = $payments->where('way_to_pay', 'PayPal')->sum('amount');
                $payu = $payments->where('way_to_pay', 'PayU')->sum('amount');
                $stripe = $payments->where('way_to_pay', 'Stripe')->sum('amount');

                fputcsv($file, [$data['hastafecha']], $delimiter);
                fputcsv($file, ["Total:", '', $data['total']], $delimiter);
                fputcsv($file, ["EFECTIVO-BS (Directo pago):", $directoPago ?? 0.00], $delimiter);
                fputcsv($file, ["CHEQUES:", 0.00], $delimiter);
                fputcsv($file, ["TARJ/DEBITO:", 0.00], $delimiter);
                fputcsv($file, ["PAGO MOVIL:", 0.00], $delimiter);
                fputcsv($file, ["TRANSFEREN-Bs:", 0.00], $delimiter);
                fputcsv($file, ["TRANSF-IGTF:", 0.00], $delimiter);
                fputcsv($file, ["PAYPAL-IGTF:", $paypal ?? 0.00], $delimiter);
                fputcsv($file, ["CASH-USD-IGTF:", $cash ?? 0.00], $delimiter);
                fputcsv($file, ["STRIPE-USD-IGTF:", $stripe ?? 0.00], $delimiter);
                fputcsv($file, ["PAYU-USD-IGTF:", $payu ?? 0.00], $delimiter);
                fputcsv($file, ["CREDITO:", 0.00], $delimiter);
                fputcsv($file, ["NOTA 1:", ''], $delimiter);
                fputcsv($file, ["NOTA 2:", ''], $delimiter);
                fputcsv($file, ["NOTA 3:", ''], $delimiter);

            } catch (\Exception $exception) {
                throw $exception;
            }


            // Put the content directly in file into the disk
            Storage::disk('public')->put($fileName, $file);
            fclose($file);

            // store data in invoice_csv_files
            $invoiceCsv = new InvoiceCsvFile();
            $invoiceCsv->date = Carbon::now()->format('Y-m-d H:i:s');
            $invoiceCsv->refint_num = $data['numFactura'];
            $invoiceCsv->fisc_num = '';
            $invoiceCsv->type_doc = 'Factura';
            $invoiceCsv->status = 'STANDBY';
            $invoiceCsv->printer = '';
            $invoiceCsv->doc_refer = '';
            $invoiceCsv->numz = '';
            $invoiceCsv->file_name = $fileName;
            $invoiceCsv->inv_content = $file;
            $invoiceCsv->save();

            $print->csv_generated = 1;
            $print->save();
        }

        return Reply::success('Successfully sent');
    }

    public function printbill($id)
    {

        if (empty($id)) {
            return "Factura no encontrada";
        }
        //buscamos todos los datos
        $print = DB::table('payments')
        ->join('clients', 'clients.id', '=', 'payments.client_id')
        ->join('plans', 'plans.id', '=', 'payments.plan_id')
        ->select('payments.num_bill', 'payments.expiries_date', 'payments.total_amount',
            'payments.pay_date', 'payments.month_pay', 'payments.after_date',
            'plans.name As planname', 'payments.amount', 'payments.iva', 'plans.upload', 'plans.download', 'clients.name As client', 'clients.dni As dni', 'clients.address', 'clients.dni',
            'clients.phone', 'clients.email')->where('payments.id', $id)->get();

        if (count($print) > 0) {

            $global = GlobalSetting::all()->first();

            $fd = strtotime($print[0]->pay_date);
            $df = strtotime($print[0]->expiries_date);
            $afd = strtotime($print[0]->after_date);

            $num = new Numbill();
            $costIva = $print[0]->iva * ($print[0]->amount / 100);
            $costIva = round($costIva, 2);

            $data = array(
                "cliente" => $print[0]->client,
                "direccionCliente" => $print[0]->address,
                "telefonoCliente" => $print[0]->phone,
                "emailCliente" => $print[0]->email,
                "fechaPago" => date('d/m/Y', $fd),
                "vencimiento" => date('d/m/Y', $df),
                "numFactura" => $num->get_format($print[0]->num_bill),
                "vatNumber" => $print[0]->dni,
                "subida" => $print[0]->upload,
                "descarga" => $print[0]->download,
                "hastafecha" => date('d/m/Y', $afd),
                "dniCliente" => $print[0]->dni,
                "plan" => $print[0]->planname,
                "costo" => $print[0]->amount,
                "total" => $print[0]->total_amount,
                "Smoneda" => $global->smoney,
                "moneda" => $global->nmoney,
                "numpagos" => $print[0]->month_pay,
                "empresa" => $global->company,
                "iva" => $costIva,
                "paid" => true,
                "gen" => false
            );

            $view = $global->factura_template ? $global->factura_template->filename : Template::where('type', 'invoice')->first()->filename;
            $view = explode('.', $view);

            $html = View::make("templates/$view[0]", $data);

            return $html;
        }
    }

}
