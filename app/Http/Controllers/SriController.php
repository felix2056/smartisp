<?php

namespace App\Http\Controllers;

use Config;
use App\Classes\Reply;
use App\libraries\Helpers;
use App\models\ExportHistory;
use App\models\GlobalSetting;
use App\models\Router;
use App\models\Sri;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\View;
use Madnest\Madzipper\Madzipper;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade as PDF;

class SriController extends Controller {

    /**
     * Show the profile for the given user.
     *
     * @param  int  $id
     * @return View
     */
    public function getIndex()
    {
        $id = Auth::user()->id;
        $level = Auth::user()->level;
        $perm = DB::table('permissions')->where('user_id', '=', $id)->get();
        $access = $perm[0]->access_clients;
        //control permissions only access super administrator (sa)
        if ($level == 'ad' || $access == true) {
            $global = GlobalSetting::all()->first();

            $GoogleMaps = Helpers::get_api_options('googlemaps');

            if (count($GoogleMaps) > 0) {
                $key = $GoogleMaps['k'];
            } else {
                $key = 0;
            }



            $invoices = Sri::select('id_factura', 'id_error', 'mensaje', 'informacionAdicional', 'tipo', 'claveAcceso', 'estado', 'updated_at')->get();

            $invoices_no_autorizadas = $invoices->where('estado', 'NO AUTORIZADO');
            $invoices_devueltas = $invoices->where('estado', 'DEVUELTA');
            $invoices_autorizadas = $invoices->where('estado', 'AUTORIZADO');
            $invoices_recibidas = $invoices->where('estado', 'RECIBIDA');


            $data = [];

            $data['No Autorizadas'] = [
                'quantity' => $invoices_no_autorizadas->count(),
                'total' => 0
            ];
            $data['Devueltas'] = [
                'quantity' => $invoices_devueltas->count(),
                'total' => 0
            ];
            $data['autorizadas'] = [
                'quantity' => $invoices_autorizadas->count(),
                'total' => 0
            ];
            $data['recibidas'] = [
                'quantity' => $invoices_recibidas->count(),
                'total' => 0
            ];
            $data['Total'] = [
                'quantity' => $invoices->count(),
                'total' => 0
            ];


            $permissions = array("clients" => $perm[0]->access_clients, "plans" => $perm[0]->access_plans, "routers" => $perm[0]->access_routers,
                "users" => $perm[0]->access_users, "system" => $perm[0]->access_system, "bill" => $perm[0]->access_pays,
                "template" => $perm[0]->access_templates, "ticket" => $perm[0]->access_tickets, "sms" => $perm[0]->access_sms,
                "reports" => $perm[0]->access_reports,
                "v" => $global->version, "st" => $global->status, "map" => $key,
                "lv" => $global->license, "company" => $global->company, 'data' => $data,
                'permissions' => $perm->first(),
                // menu options
            );



            if (Auth::user()->level == 'ad')
                @setcookie("hcmd", 'kR2RsakY98pHL', time() + 7200, "/", "", 0, true);

            $contents = View::make('sri.index', $permissions);
            $response = Response::make($contents, 200);
            $response->header('Expires', 'Tue, 1 Jan 1980 00:00:00 GMT');
            $response->header('Cache-Control', 'no-store, no-cache, must-revalidate, post-check=0, pre-check=0');
            $response->header('Pragma', 'no-cache');
            return $response;
        } else
            return Redirect::to('admin');
    }

    public function postLists(Request $request)
    {
	    $date = $request->get('extra_search');
	
	    if (!empty($date)) {
		    $string = explode('|', $date);
		
		    $date1 = $string[0];
		    $date2 = $string[1];
		
		    $date1 = str_replace('/', '-', $date1);
		    $date2 = str_replace('/', '-', $date2);
		
		    $from = date("Y-m-d", strtotime($date1));
		    $to = date("Y-m-d", strtotime($date2));
	    }
	    
        $invoices = Sri::select('id', 'id_factura', 'id_error', 'mensaje', 'informacionAdicional', 'tipo', 'claveAcceso', 'estado', 'updated_at');
	    
	    if(!empty($date)) {
	    	$invoices = $invoices->whereDate('updated_at', '>=', $from)->whereDate('updated_at', '<=', $to);
	    }

        return DataTables::of($invoices)
                        ->addColumn('action', function ($row) {
                            return '
                    <a href="javasscript:;" title="Remove" onclick="removeSri(\'' . $row->id . '\');return false;"><span class="glyphicon glyphicon-trash"></span></a>';
                        })
                        ->editColumn('updated_at', function ($row) {
                            return Carbon::parse($row->updated_at)->format('F j, Y');
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

    public function delete($id)
    {
        Sri::destroy($id);

        return Reply::success('History successfully deleted.');
    }

    public function exportInvoices(Request $request)
    {
        $dateRange = preg_split("/\|/", $request->get('date-range'));

        $from = $dateRange['0'];
        $to = $dateRange['1'];
        $invoices = Sri::select('id', 'id_factura', 'id_error', 'mensaje', 'informacionAdicional', 'tipo', 'estado', 'updated_at')->get();

        if ($invoices->count() < 1) {
            return Reply::error('No invoices found !');
        }

        switch ($request->export_type) {
            case 'type_csv':

                $headers = array(
                    "Content-type" => "text/csv",
                    "Content-Disposition" => "attachment; filename=invoices.csv",
                    "Pragma" => "no-cache",
                    "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
                    "Expires" => "0"
                );

                $columns = array('ID', 'id_factura', 'id_error', 'mensaje', 'informacionAdicional', 'tipo', 'claveAcceso', 'estado', 'fecha_emision');

                $callback = function() use ($invoices, $columns, $request, $from, $to) {

                    $file = fopen('php://output', 'w');
                    fputcsv($file, $columns);
                    foreach ($invoices as $review) {
                        fputcsv($file, array($review->id, $review->id_factura, $review->id_error, $review->mensaje, $review->informacionAdicional, $review->tipo, $review->claveAcceso, $review->estado, $review->updated_at));
                    }
                    fclose($file);
                };
                return response()->streamDownload($callback, 'invoices-' . date('d-m-Y-H:i:s') . '.csv', $headers);
                break;

            case 'type_pdf_archive':
                $zipper = new Madzipper;
                $files = [];
                $global = GlobalSetting::all()->first();
                foreach ($invoices as $invoice) {
                    $costIva = $invoice->iva * ($invoice->cost / 100);
                    $costIva = round($costIva, 2);

                    $df = strtotime($invoice->expiration_date);
                    $pd = strtotime($invoice->release_date);
                    $sd = strtotime($invoice->start_date);
                    $period = strtotime($invoice->period);

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
                        "invoice_items" => $invoice->invoice_items
                    );
                    //marcamos como visto la factura del cliente

                    $pdf = PDF::loadView("templates.Factura_cliente", $data);

                    if (!\File::exists(public_path('invoices/'))) {
                        $result = \File::makeDirectory(public_path('invoices/'), 0775, true);
                    }

                    $files[] = 'invoices/invoice-' . $invoice->num_bill . '.pdf';

                    $pdf->save('invoices/invoice-' . $invoice->num_bill . '.pdf');
                }

                if (\File::exists(public_path('invoices/invoices.zip'))) {
                    unlink('invoices/invoices.zip');
                }

                $zipper->zip('invoices/invoices.zip')->add($files);
                $zipper->close();

                $history = new ExportHistory();
                $history->type = $request->export_type;
                $history->from = Carbon::parse($from)->format('Y-m-d');
                $history->to = Carbon::parse($to)->format('Y-m-d');
                $history->router = $request->routers;
                $history->location = $request->location;
                $history->payment_type = $request->way_to_pay;
                $history->status = $request->status;

                $history->save();

                foreach ($files as $file) {
                    unlink($file);
                }

                return response()->download(public_path('invoices/invoices.zip'));

                break;

            case 'type_pdf':
                return $this->typePdf($request, $invoices, $from, $to);
                break;

            default:
                # code...
                break;
        }
    }

    public function checkInvoices(Request $request)
    {
        $dateRange = preg_split("/\|/", $request->get('date-range'));

        $from = $dateRange['0'];
        $to = $dateRange['1'];

        $invoicesCount = Sri::select('id')
            ->whereDate('updated_at', '>=', Carbon::parse($from))
            ->whereDate('updated_at', '<=', Carbon::parse($to));

        if ($request->status !== 'any') {
            $invoicesCount = $invoicesCount->where('estado', $request->status);
        }

        $count = $invoicesCount->count();
        $message = 'Total invoices are ' . $count;
        return Reply::success($message, ['total' => $count]);
    }

    public function exportInvoicePopup() {
        $this->routers = Router::all();
        return view('sri.export');
    }

}
