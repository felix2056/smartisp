<?php

namespace App\Http\Controllers;

use App\Classes\Reply;
use App\models\ExportHistory;
use App\models\GlobalSetting;
use Madnest\Madzipper\Madzipper;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;
use App\models\BillCustomer;
use Barryvdh\DomPDF\Facade as PDF;

class ExportHistoryController extends BaseController
{
    public function postList()
    {
        $history = ExportHistory::select('id', 'created_at', 'type');

        return DataTables::of($history)
        ->addColumn('action', function ($row) {
            return '<a href="'.route('export-history.download', ['id' => $row->id]).'" target="_blank" title="Download"><span class="glyphicon glyphicon-download"></span></a>&nbsp;
            <a href="javasscript:;" title="Remove" onclick="removeHistory(\''.$row->id.'\');return false;"><span class="glyphicon glyphicon-trash"></span></a>';
        })
        ->editColumn('created_at', function ($row) {
            return Carbon::parse($row->created_at)->format('F j, Y');
        })
        ->editColumn('type', function ($row) {
            switch ($row->type) {
                case 'type_csv':
                return 'A CSV File';
                break;

                case 'type_pdf_archive':
                return 'PDF in files';
                break;

                case 'type_pdf':
                return 'A PDF File';
                break;

                default:
                return '-';
                break;
            }

        })
        ->addColumn('state', function ($row) {
            return 'Ready..';
        })
        ->rawColumns(['action'])
        ->make(true);
    }

    public function download ($id)
    {
        $history = ExportHistory::find($id);

        $invoicesCount = BillCustomer::leftJoin('payment_news', 'payment_news.num_bill', 'bill_customers.num_bill')
        ->join('clients', 'clients.id', 'bill_customers.client_id')
        ->join('routers', 'routers.id', 'clients.router_id')
        ->join('plans', 'plans.id', '=', 'clients.plan_id')
        ->select('bill_customers.id','bill_customers.num_bill', 'bill_customers.expiration_date', 'bill_customers.total_pay',
            'bill_customers.release_date', 'bill_customers.period', 'plans.upload', 'plans.download',
            'plans.name As planname', 'bill_customers.cost', 'bill_customers.iva', 'clients.name As client', 'clients.address',
            'clients.phone', 'clients.email', 'clients.dni', 'clients.balance')
        ->whereDate('start_date', '>=', Carbon::parse($history->from))
        ->whereDate('start_date', '<=', Carbon::parse($history->to));

        if($history->router !== 0) {
            $invoicesCount = $invoicesCount->where('clients.router_id', $history->router);
        }

        if($history->location !== 'any') {
            $invoicesCount = $invoicesCount->where('routers.location','like', '%'.$history->location.'%');
        }

        if($history->payment_type !== 'any') {
            $invoicesCount = $invoicesCount->where('payment_news.way_to_pay', $history->payment_type);
        }

        if($history->status !== 'any') {
            $invoicesCount = $invoicesCount->where('bill_customers.status', $history->status);
        }

        $invoices = $invoicesCount->groupBy('bill_customers.num_bill')->get();

        if($invoices->count() < 1) {
            return Reply::error('No invoices found !');
        }

        switch ($history->type) {
            case 'type_csv':

            $headers = array(
                "Content-type" => "text/csv",
                "Content-Disposition" => "attachment; filename=invoices.csv",
                "Pragma" => "no-cache",
                "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
                "Expires" => "0"
            );

            $columns = array('ID', 'Customer Name', 'Phone Number', 'Email', 'Address', 'Duration', 'Suspensión', 'Plan', 'DNI', 'IVA', 'Cost', 'Total');

            $callback = function() use ($invoices, $columns, $history)
            {
                $file = fopen('php://output', 'w');
                fputcsv($file, $columns);

                foreach($invoices as $review) {

                    $costIva = $review->iva * ($review->balance / 100);
                    $costIva = round($costIva, 2);

                    $expire = strtotime($review->expiration_date);
                    $release = strtotime($review->release_date);

                    fputcsv($file, array($review->id, $review->client, $review->phone, $review->email, $review->address, $release.' to '. $expire, $expire, $review->planname, $review->dni, $costIva, $review->balance, $review->total_pay));
                }
                fclose($file);

            };
            return response()->streamDownload($callback, 'invoices-' . date('d-m-Y-H:i:s').'.csv', $headers);
            break;

            case 'type_pdf_archive':
            $zipper = new Madzipper;
            $files = [];
            $global = GlobalSetting::all()->first();
            foreach($invoices as $invoice) {
                $costIva = $invoice->iva * ($invoice->balance / 100);
                $costIva = round($costIva, 2);

                $df = strtotime($invoice->expiration_date);
                $pd = strtotime($invoice->release_date);

                $data = array(
                    "cliente" => $invoice->client,
                    "direccionCliente" => $invoice->address,
                    "telefonoCliente" => $invoice->phone,
                    "emailCliente" => $invoice->email,
                    "dniCliente" => $invoice->dni,
                    "fechaPago" => date('d/m/Y', $pd),
                    "vencimiento" => date('d/m/Y', $df),
                    "numFactura" => $invoice->num_bill,
                    "subida" => $invoice->upload,
                    "descarga" => $invoice->download,
                    "plan" => $invoice->planname,
                    "costo" => $invoice->balance,
                    "total" => $invoice->total_pay,
                    "Smoneda" => $global->smoney,
                    "moneda" => $global->nmoney,
                    "hastafecha" => $invoice->period,
                    "empresa" => $global->company,
                    "iva" => $costIva,
                    "paid" => true,
                    "gen" => false
                );
                    //marcamos como visto la factura del cliente

                $pdf = PDF::loadView("templates.Factura_cliente", $data);

                if(!\File::exists(public_path('invoices/'))) {
                    $result = \File::makeDirectory(public_path('invoices/'), 0775, true);
                }

                $files[] = 'invoices/invoice-'.$invoice->num_bill.'.pdf';

                $pdf->save('invoices/invoice-'.$invoice->num_bill.'.pdf');


            }

            if(\File::exists(public_path('invoices/invoices.zip'))) {
                unlink('invoices/invoices.zip');
            }

            $zipper->zip('invoices/invoices.zip')->add($files);
            $zipper->close();

            foreach ($files as $file) {
                unlink($file);
            }


            return response()->download(public_path('invoices/invoices.zip'));
            break;

            case 'type_pdf':
            return $this->typePdf($invoices);
            break;

            default:
                # code...
            break;
        }


    }

    private function typePdf($invoices)
    {
        $global = GlobalSetting::all()->first();
        $html = '<!DOCTYPE html>
        <html lang="es">
        <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
        <meta charset="utf-8" />
        <title>{{@$empresa}} - @lang("app.facturano") {{@$numFactura}}</title>
        <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0" />
        <style>

        body {
            margin: 15px;
            padding: 0;
            background-color: #ffffff;
        }

        body,td,input,select {
            font-family: Tahoma;
            font-size: 11px;
            color: #000000;
        }

        form {
            margin: 0px;
        }

        a {
            font-size: 14px;
            color: #1E598A;
            padding: 10px;
        }

        a:hover {
            text-decoration: none;
        }

        .textcenter {
            text-align: center;
        }

        .textright {
            text-align: right;
        }

        .wrapper {
            margin: 0 auto;
            padding: 10px 20px 70px 20px;
            width: 600px;
            background-color: #fff;
            border: 1px solid #ccc;
            -moz-border-radius: 6px;
            -webkit-border-radius: 6px;
            -o-border-radius: 6px;
            border-radius: 6px;
        }

        .header {
            margin: 0 0 15px 0;
            width: 100%;
        }

        .addressbox {
            height: 100px;
            padding: 10px;
            background-color: #fff;
            border: 1px solid #ccc;
            color: #000;
            overflow: hidden;
        }

        table.items {
            width: 100%;
            background-color: #ccc;
            border-spacing: 0;
            border-collapse: separate;
            border-left: 1px solid #ccc;
        }

        table.items tr.title td {
            margin: 0;
            padding: 2px 5px;
            line-height: 16px;
            background-color: #efefef;
            border: 1px solid #ccc;
            border-bottom: 0;
            border-left: 0;
            font-size: 12px;
            font-weight: bold;
        }

        table.items td {
            margin: 0;
            padding: 2px;
            line-height: 15px;
            background-color: #fff;
            border: 1px solid #ccc;
            border-bottom: 0;
            border-left: 0;
        }

        table.items tr:last-child td {
            border-bottom: 1px solid #ccc;
        }

        .row {
            margin: 15px 0;
        }

        .title {
            font-size: 16px;
            font-weight: bold;
        }

        .subtitle {
            font-size: 13px;
            font-weight: bold;
        }

        .unpaid {
            font-size: 20px;
            color: #cc0000;
            font-weight: bold;
        }

        .paid {
            font-size: 20px;
            color: #779500;
            font-weight: bold;
        }

        .refunded {
            font-size: 20px;
            color: #224488;
            font-weight: bold;
        }

        .cancelled {
            font-size: 16px;
            color: #cccccc;
            font-weight: bold;
        }

        .collections {
            font-size: 16px;
            color: #ffcc00;
            font-weight: bold;
        }

        .creditbox {
            margin: 0 auto 15px auto;
            padding: 10px;
            border: 1px dashed #cc0000;
            font-weight: bold;
            background-color: #FBEEEB;
            text-align: center;
            width: 95%;
            color: #cc0000;
        }
        .ba{
           background-color:#EFEFEF;
       }
       </style>
       </head>

       <body>';
       foreach($invoices as $invoice) {
        $costIva = $invoice->iva * ($invoice->balance / 100);
        $costIva = round($costIva, 2);

        $df = strtotime($invoice->expiration_date);
        $pd = strtotime($invoice->release_date);

        $data = array(
            "cliente" => $invoice->client,
            "direccionCliente" => $invoice->address,
            "telefonoCliente" => $invoice->phone,
            "emailCliente" => $invoice->email,
            "dniCliente" => $invoice->dni,
            "fechaPago" => date('d/m/Y', $pd),
            "vencimiento" => date('d/m/Y', $df),
            "numFactura" => $invoice->num_bill,
            "subida" => $invoice->upload,
            "descarga" => $invoice->download,
            "plan" => $invoice->planname,
            "costo" => $invoice->balance,
            "total" => $invoice->total_pay,
            "Smoneda" => $global->smoney,
            "moneda" => $global->nmoney,
            "hastafecha" => $invoice->period,
            "empresa" => $global->company,
            "iva" => $costIva,
            "paid" => true,
            "gen" => false
        );
            //marcamos como visto la factura del cliente
        $view = view('templates.multiple', $data)->render();
        $html .= $view;
    }

    $html .= '</body></html>';

    $pdf = PDF::loadHTML($html);
    return $pdf->download('invoices.pdf');
}

public function delete ($id)
{
    ExportHistory::destroy($id);

    return Reply::success('History successfully deleted.');
}
}
