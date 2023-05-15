<?php

namespace App\Console\Commands;

use App\models\BillCustomer;
use App\models\GlobalSetting;
use App\models\InvoiceCsvFile;
use App\models\PaymentNew;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class GenerateCsvPaidInvoices extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'generate-csv';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command is used for generate csv files for paid invoices of current month';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        // generate csv file for current month for al paid invoices.
        $month = Carbon::now()->month;

        BillCustomer::with('client', 'invoice_items')
            ->where('status', '!=', 3)
            ->where('csv_generated', '=', 0)
            ->whereRaw("MONTH(release_date)=$month")
            ->chunkById(5, function ($invoices) {
                foreach($invoices as $print) {
                    if($print->total_pay > 0) {
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
                    }


                    $print->csv_generated = 1;
                    $print->save();

                };
            });
    }
}
