<?php

namespace App\Http\Controllers;

use Config;
use App\Classes\Reply;
use App\libraries\Helpers;
use App\models\BillCustomerItem;
use App\models\ExportHistory;
use App\models\GlobalSetting;
use App\models\Router;
use App\models\Sri;
use App\models\Transaction;
use App\models\Emisor;
use App\models\Factel;
use App\models\PuntoEmision;
use App\models\Establecimientos;
use Chumper\Zipper\Zipper;
use Illuminate\Http\Request;
use App\models\Invoice;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\View;
use Yajra\DataTables\Facades\DataTables;
use Carbon\Carbon;
use App\models\BillCustomer;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use App\models\Client;
use App\models\SuspendClient;
use Barryvdh\DomPDF\Facade as PDF;
use App\Http\Requests\Payment\StoreRequest;
use App\models\PaymentNew;
use App\Http\Controllers\PermissionsController;
use Illuminate\Support\Facades\Input;


class PuntoController extends Controller {

    /**
     * Show the profile for the given user.
     *
     * @param  int  $id
     * @return View
     */
    public function getIndex() {


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
            $pto_emision = PuntoEmision::select('id', 'id_establecimiento', 'nombre', 'codigo')->get();

            $data['punto_emision'] = [
                'quantity' => $pto_emision->count(),
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

            $contents = View::make('puntoEmision.index', $permissions);
            $response = Response::make($contents, 200);
            $response->header('Expires', 'Tue, 1 Jan 1980 00:00:00 GMT');
            $response->header('Cache-Control', 'no-store, no-cache, must-revalidate, post-check=0, pre-check=0');
            $response->header('Pragma', 'no-cache');
            return $response;
        } else
            return Redirect::to('admin');
    }

    public function postLists() {
        $invoices = PuntoEmision::select('punto_emision.id', 'establecimientos.codigo as id_establecimiento', 'punto_emision.nombre', 'punto_emision.codigo')
                ->join('establecimientos','punto_emision.id_establecimiento','=','establecimientos.id')
                ->get();
        
        return DataTables::of($invoices)
                        ->addColumn('action', function ($row) {
                            return '<div class="hidden-sm hidden-xs action-buttons">
                            <a class = "green editar" href="javasscript:;" title="Edit" onclick="editarPtoEmision(\'' . $row->id . '\');return false;"><span class="glyphicon glyphicon-edit"></span></a>'
                            . '<a class= "red del" href="javasscript:;" title="Edit" onclick="eliminarPtoEmision(\'' . $row->id . '\');return false;"><span class="glyphicon glyphicon-edit"></span></a></div>';
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
    
    
     // Metodo Eliminar Puntos de Emsion
    public function deletePtoEmision($id) {
        PuntoEmision::destroy($id);
        return Reply::success('Punto de Emision successfully deleted.');
    }
    

    /**
     * Display the specified resource.
     *
     * @param  \App\Invoice  $invoice
     * @return \Illuminate\Http\Response
     */
    public function showInvoice($id) {
        
        $invoice = PuntoEmision::select('punto_emision.id', 'punto_emision.id_establecimiento', 'punto_emision.nombre', 'punto_emision.codigo')
                ->join('establecimientos','punto_emision.id_establecimiento','=','establecimientos.id')
                ->where('punto_emision.id', $id)
                ->first();
        
        $establecimientos = Establecimientos::select('id', 'codigo', 'url', 'nombreComercial', 'direccion')->get();
        return view('puntoEmision.show', ['invoice_data' => $invoice,'establecimiento_data'=> $establecimientos]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Invoice  $invoice
     * @return \Illuminate\Http\Response
     */
    public function newInvoice() {
        $establecimientos = Establecimientos::select('id', 'codigo', 'url', 'nombreComercial', 'direccion')->get();
        return view('puntoEmision.new', ['establecimiento_data' => $establecimientos]);
    }

    public function delete($id) {
        Sri::destroy($id);
        return Reply::success('History successfully deleted.');
    }

    //metodo para crear planes simple queues
    public function postCreate(Request $request) {
        
        $establecimiento = $request->input('val_establecimiento');
        $nombre = $request->input('val_nombre');
        $codigo = $request->input('val_codigo');
        $accion = $request->input('accion');
        
        ;
        
        
        //buscamos
        $ptoEmision = DB::table('punto_emision')->where('codigo', $codigo)->first();
        
        if ($ptoEmision == null) {
            //guardamos

            $id = DB::table('punto_emision')->insertGetId(array(
                'id_establecimiento' => $establecimiento,
                'nombre' => $nombre,
                'codigo' => $codigo
            ));
            
            return Reply::success('Punto de Emision Agregado Correctamente.');
        } else {
            if ($accion == 'crear') {
                return Reply::error('Punto de Emision ya existe');
            } else {
                $id = DB::table('punto_emision')
                        ->where('codigo', $codigo)
                        ->update(array('nombre' => $nombre,
                            'codigo' => $codigo,
                            'id_establecimiento' => $establecimiento,
                        )
                );
                return Reply::success('Establecimiento Actualizado Correctamente.');
            }
        }



        /*
         * *        $id = DB::table('establecimientos')->insertGetId(array(
          'codigo' => Input::get('codigo'),
          'url' => Input::get('val_url'),
          'upload' => Input::get('nombreComercial'),
          'num_clients' => Input::get('direccion'),
          'cost' => Input::get('cost'),
          'iva' => $iva,
          //burst
          'burst_limit' => Input::get('bl'),
          'burst_threshold' => Input::get('bth'),
          'burst_time' => Input::get('bt'),
          'priority' => Input::get('priority'),
          'limitat' => Input::get('limitat'),
          'aggregation' => Input::get('aggregation', 1)
          ));

         */
        //creamos smart bandwidth por defecto



        return Response::json(array('msg' => 'success'));
    }

    public function exportInvoices(Request $request) {

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
                $zipper = new Zipper();
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

    public function checkInvoices(Request $request) {
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
