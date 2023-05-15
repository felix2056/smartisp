<?php

namespace App\Http\Controllers;

use App\DataTables\InvoiceDataTable;
use App\libraries\Chkerr;
use App\models\RecurringInvoice;
use App\models\WalletPayment;
use App\Service\CommonService;
use Config;
use App\Classes\Reply;
use App\libraries\Helpers;
use App\models\BillCustomerItem;
use App\models\ExportHistory;
use App\models\GlobalSetting;
use App\models\ClientService;
use App\models\Router;
use App\models\Transaction;
use App\models\Emisor;
use App\models\Dian_settings;
use App\models\Sri;
use App\models\Factel;
use App\models\PuntoEmision;
use App\models\Establecimientos;
use App\models\Secuenciales;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\View;
use Illuminate\Validation\ValidationException;
use Madnest\Madzipper\Madzipper;
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
use App\Http\Controllers\Municipio;
use DOMDocument;
use Stenfrank\UBL21dian\XAdES\SignInvoice;
use Stenfrank\UBL21dian\Templates\SOAP\GetStatusZip;
use Stenfrank\UBL21dian\Templates\SOAP\SendTestSetAsync;
use Stenfrank\UBL21dian\Templates\SOAP\SendBillSync;
use ZipArchive;

use App\Classes\SignMx;
use App\models\InvoiceSettings;
use Exception;
use Stenfrank\UBL21dian\Templates\SOAP\SendBillAsync;

class InvoiceController extends BaseController {
    private $xml_colombia;
    private $cufe;
    private $qr;

    public function __construct()
    {
        parent::__construct();
        $this->middleware(function ($request, $next) {
            $this->username = auth()->user()->username;
            $this->userId = auth()->user()->id;
            return $next($request);
        });
    }
    /**
     * @param InvoiceDataTable $dataTable
     * @return \Illuminate\Http\RedirectResponse|mixed
     */
    public function index(InvoiceDataTable $dataTable) {

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

            $permissions = array("clients" => $perm[0]->access_clients, "plans" => $perm[0]->access_plans, "routers" => $perm[0]->access_routers,
                "users" => $perm[0]->access_users, "system" => $perm[0]->access_system, "bill" => $perm[0]->access_pays,
                "template" => $perm[0]->access_templates, "ticket" => $perm[0]->access_tickets, "sms" => $perm[0]->access_sms,
                "reports" => $perm[0]->access_reports,
                "v" => $global->version, "st" => $global->status, "map" => $key,
                "lv" => $global->license, "company" => $global->company,
                'permissions' => $perm->first(),
                // menu options
            );



            if (Auth::user()->level == 'ad')
                @setcookie("hcmd", 'kR2RsakY98pHL', time() + 7200, "/", "", 0, true);

            return $dataTable->render('invoices.index', $permissions);

        } else
            return Redirect::to('admin');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create($clientId) {
        return view('billing.create-invoice', ['clientId' => $clientId]);
    }

    public function postLists(Request $request) {

        $date = $request->get('extra_search');

        if($date) {
            $string = explode('|', $date);

            $date1 = $string[0];
            $date2 = $string[1];

            $date1 = str_replace('/', '-', $date1);
            $date2 = str_replace('/', '-', $date2);

            $from = date("Y-m-d", strtotime($date1));
            $to = date("Y-m-d", strtotime($date2));
        }


        $invoices = BillCustomer::select('bill_customers.id', 'clients.name', 'bill_customers.num_bill', 'bill_customers.release_date', 'bill_customers.total_pay', 'bill_customers.paid_on', 'bill_customers.status', 'bill_customers.created_at','clients.id as client_id')
            ->join('clients', 'clients.id', 'bill_customers.client_id');
        if($date) {
            $invoices = $invoices->whereBetween('bill_customers.release_date', array($from, $to));

        }


        return DataTables::of($invoices)
            ->addColumn('action', function ($row) {
                $actions = '';

                if (!in_array($row->status, [1, 2, 4])) {
                    if (PermissionsController::hasAnyRole('factura_finanzas_pagar')) {
                        $actions = '<a href="javascript:void(0)" onclick="payInvoice(' . $row->id . ')" title="Pay Invoice"><span class="glyphicon glyphicon-ok"></span></a>&nbsp;    ';
                    }
                }


                $actions = $actions . '
            <a href="javascript:void(0)" onclick="showInvoice(' . $row->id . ')" title="View Invoice">
            <span class="glyphicon glyphicon-info-sign"></span>
            </a>
            &nbsp;
            <a href="' . route('invoice.showPDF', ['id' => $row->id]) . '" target="_blank" title="View Invoice">
            <span class="glyphicon glyphicon-file"></span>
            </a>
            <a href="' . route('invoice.print', ['id' => $row->id]) . '" target="_blank" title="Print Invoice">
            <span class="glyphicon glyphicon-print"></span>
            </a>
            <a href="' . route('invoice.showPDF', ['id' => $row->id, 'download' => 'true']) . '" title="Download">
            <span class="glyphicon glyphicon-download"></span>
            </a>&nbsp;  ';
                $actions .= '<a href="javascript:void(0)" onclick="sendEmail(' . $row->id . ')" title="'.__('app.sendinvoice').'"><span class="glyphicon glyphicon-send"></span></a>';



                if (!in_array($row->status, [1, 2, 4])) {



                    if (PermissionsController::hasAnyRole('factura_finanzas_editar')) {
                        if ($row->invoice_items->count() > 0) {
                            $actions .= '
                        <a href="javascript:void(0)" onclick="editInvoice(' . $row->id . ')" title="Edit Invoice">
                        <span class="glyphicon glyphicon-edit"></span>
                        </a>';
                        } else {
                            $actions .= '
                        <a href="javascript:void(0)" onclick="editCustomInvoice(' . $row->id . ')" title="Edit Invoice">
                        <span class="glyphicon glyphicon-edit"></span>
                        </a>';
                        }
                    }
                    if (PermissionsController::hasAnyRole('factura_finanzas_eliminar')) {
                        $actions .= '<a href="javascript:void(0)" onclick="deleteInvoice(' . $row->id . ')" title="Delete Invoice"><span class="glyphicon glyphicon-trash"></span></a>';
                    }
                } else {

                    if ($row->status == 2) {
                        if (PermissionsController::hasAnyRole('factura_finanzas_eliminar')) {
                            $actions .= '<a href="javascript:void(0)" onclick="deleteInvoice(' . $row->id . ')" title="Delete Invoice"><span class="glyphicon glyphicon-trash"></span></a>';
                        }
                    } else {

                        $actions = $actions . '
                    <a href="javascript:void(0)" onclick="editInvoicePayment(' . $row->id . ')" title="Edit Payment">
                    <span class="glyphicon glyphicon-pencil"></span>
                    </a>
                    <a href="javascript:void(0)" onclick="deleteInvoicePayment(' . $row->id . ')" title="Delete Payment"><span class="glyphicon glyphicon-remove"></span></a>';
                        $emisor = Factel::all();
                        $empresa = $emisor->first();
                        if($empresa->status== 2){
                            $invoice = Sri::where('id_factura', $row->id)->where('tipo','=','10')->where('estado','=','AUTORIZADO')->get();
                        }elseif($empresa->status== 3){
                            $invoice = Sri::where('id_factura', $row->id)->where('tipo','=','3')->where('estado', 'signed')->get();
                        }else{
                            $invoice = Sri::where('id_factura', $row->id)->where('estado','=','AUTORIZADO')->get();
                        }

                        if (!empty($empresa)) {
                            $status = $empresa->status;
                            if ($status != 1) {
                                if($status == 0){
                                    if(empty($invoice[0])){
                                        $actions .= '<a href="javascript:void(0)" onclick="send_sri(' . $row->id . ')" title="Send SRI"><span class="glyphicon glyphicon-envelope"></span></a>';
                                    }else{
                                        $actions .= '<a href="javascript:void(0)"  title="Send SRI"><span class="glyphicon glyphicon-envelope" style="color:gray"></span></a>';
                                    }
                                }else if($status == 2){
                                    if(empty($invoice[0])){
                                        $actions .= '<a href="javascript:void(0)" onclick="send_DIAN(' . $row->id . ')" title="Send DIAN"><span class="glyphicon glyphicon-envelope"></span></a>';
                                    }else{
                                        $actions .= '<a href="javascript:void(0)"  title="Send DIAN"><span class="glyphicon glyphicon-envelope" style="color:gray"></span></a>';
                                        $actions .= '<a href="javascript:void(0)" onclick="send_Note_DIAN(' . $row->id . ')" title="Send Note DIAN" style="margin-left: 5px;"><span class="glyphicon glyphicon-comment"></span></a>';
                                    }
                                }elseif($status == 3 ){
                                    if(empty($invoice[0])){
                                        $actions .= '<a href="javascript:void(0)" onclick="send_SAT(' . $row->id . ')" title="Send SAT"><span class="glyphicon glyphicon-envelope" style="margin:0 5px"></span></a>';
                                    }else{
                                        $actions .= '<a href="javascript:void(0)"  title="Send SAT"><span class="glyphicon glyphicon-envelope" style="color:gray;margin:0 5px"></span></a>';
                                        $actions .= '<a target="_blank" href="' . route('invoice_mx.payment.file') . '?doc_type=pdf&doc_id=' . $invoice[0]->informacionAdicional . '" title="Get SAT PDF"><span class="glyphicon glyphicon-print" style="margin:0 5px"></span></a>';
                                        $actions .= '<a target="_blank" href="' . route('invoice_mx.payment.file') . '?doc_type=xml&doc_id=' . $invoice[0]->informacionAdicional . '" title="Get SAT XML"><span class="glyphicon glyphicon-save-file" style="margin:0 5px"></span></a>';
                                    }
                                }
                            }
                        }
                        //    $actions .= '<a href="javascript:void(0)"  title="Send SRI"><span class="glyphicon glyphicon-envelope" style="color:gray"></span></a>';

                    }
                }
                return $actions;
            })

            ->editColumn('name', function ($row) {
                return '<a href="' . route('billing', $row->client_id) . '#bill">' . $row->name . '</a>';
            })
            ->editColumn('release_date', function ($row) {
                return Carbon::parse($row->release_date)->format('d/m/Y');
            })
            ->editColumn('paid_on', function ($row) {
                if ($row->paid_on === null) {
                    return '--';
                }
                return Carbon::parse($row->paid_on)->format('d/m/Y');
            })
            ->editColumn('status', function ($row) {
                $badge = '';
                $label = [
                    'paid' => 'success',
                    'unpaid' => 'warning',
                    'late' => 'danger'
                ];
                switch ($row->status) {
                    case '1':
                        $badge = [
                            'status' => 'paid',
                            'label' => $label['paid']
                        ];
                        break;

                    case '2':
                        $badge = [
                            'status' => 'paid (account balance)',
                            'label' => $label['paid']
                        ];
                        break;

                    case '3':
                        $badge = [
                            'status' => 'Unpaid',
                            'label' => $label['unpaid']
                        ];
                        break;

                    case '4':
                        $badge = [
                            'status' => 'paid',
                            'label' => $label['paid']
                        ];
                        break;

                    default:
                        # code...
                        break;
                }

                $status = '<label class="label label-' . $badge['label'] . '">
    <font style="vertical-align: inherit;">
    <font style="vertical-align: inherit;">
    ' . ucFirst($badge['status']) . '
    </font>
    </font>
    </label>';

                return $status;
            })
            ->rawColumns(['action', 'name' ,'status'])
            ->make(true);
    }


    public function recurringInvoiceList(Request $request, $id) {

        $date = $request->get('extra_search');

        if($date) {
            $string = explode('|', $date);

            $date1 = $string[0];
            $date2 = $string[1];

            $date1 = str_replace('/', '-', $date1);
            $date2 = str_replace('/', '-', $date2);

            $from = date("Y-m-d", strtotime($date1));
            $to = date("Y-m-d", strtotime($date2));
        }

        $invoices = RecurringInvoice::where('client_id', $id);

        return DataTables::of($invoices)
            ->addColumn('action', function ($row) {
                $actions = '<a class="blue" onclick="banRecurring('.$row->id.')" href="#" id="' . $row->id . '" title="' . __('app.serviceCut') . '"><i class="ace-icon fa fa-adjust bigger-130"></i></a>
                            <a href="javascript:void(0)" onclick="editRecurringInvoice('.$row->id.')" title="Edit Invoice">
                        <span class="glyphicon glyphicon-edit"></span></a>
                        <a href="javascript:void(0)" onclick="deleteRecurringInvoice('.$row->id.')" title="Delete Invoice"><span class="glyphicon glyphicon-trash"></span></a>
                        <a href="javascript:void(0)" onclick="generateRecurringInvoice('.$row->id.')" title="Generate Invoice"><span class="glyphicon glyphicon-cog"></span></a>
                        ';

                return $actions;
            })
            ->editColumn('service_status', function ($row) {
                if($row->service_status == 'active') {
                    return '<label class="label label-success">' . ucfirst(__('app.'.$row->service_status)) . '</label>';
                } else {
                    return '<label class="label label-danger">' . ucfirst(__('app.'.$row->service_status)) . '</span>';
                }
            })
            ->editColumn('start_date', function ($row) {
                return $row->start_date->format('Y-m-d');
            })
            ->editColumn('end_date', function ($row) {
                if(!$row->end_date) {
                    return '<label class="label label-success">Not set</label>';
                } else {
                    return $row->end_date->format('Y-m-d');
                }
            })
            ->editColumn('next_pay_date', function ($row) {
                if(!$row->next_pay_date) {
                    return '<label class="label label-success">Not set</label>';
                } else {
                    return $row->next_pay_date->format('Y-m-d');
                }
            })

            ->rawColumns(['action' ,'service_status' ,'end_date','next_pay_date'])
            ->make(true);
    }

    public function recurringInvoiceGenerate($id) {
        $invoice = RecurringInvoice::find($id);
        if((is_null($invoice->end_date)) || (!is_null($invoice->end_date) && $invoice->end_date->gt(Carbon::now())) ) {
            if($invoice->start_date->day <= Carbon::now()->day && $invoice->start_date->month <= Carbon::now()->month) {
                $invoice_num = CommonService::getBillNumber();
                $client = Client::find($invoice->client_id);

                $period = '';
                switch ($invoice->frequency) {
                    case 'week':
                        $period = $invoice->start_date->addDays(7)->format('Y-m-d');
                        break;
                    case 'month':
                        $period = $invoice->start_date->addMonths(1)->format('Y-m-d');
                        break;
                    case 'year':
                        $period = $invoice->start_date->addYears(1)->format('Y-m-d');
                        break;
                }


                $latestInvoice = BillCustomer::where('start_date', $invoice->start_date->format('Y-m-d'))
                    ->where('expiration_date', $period)
                    ->where('total_pay', $invoice->price)
                    ->where('recurring_invoice', 'yes')
                    ->where('billing_type', 'recurring')
                    ->first();

                if($latestInvoice) {
                    return Reply::error('Invoice for this recurring invoice is already generated.');
                }


                $invoice_items_data = [];

                $invoice_data = [
                    'num_bill' => $invoice_num,
                    'start_date' => $invoice->start_date->format('Y-m-d'),
                    'billing_type' => 'recurring',
                    'period' => $period,
                    'release_date' => $invoice->start_date->format('Y-m-d'),
                    'expiration_date' => $period,
                    'client_id' => $invoice->client_id,
                    'open' => 0,
                    'note' => $invoice->note,
                    'memo' => $invoice->memorandum,
                    'use_transactions' => 0,
                    'status' => 3,
                    'recurring_invoice' => 'yes'
                ];

                $invoice_data['total_pay'] = $invoice->price;

                if ((float) $client->balance >= (float) $invoice_data['total_pay']) {
                    $invoice_data['status'] = 2;
                    $invoice_data['paid_on'] = Carbon::now()->format('Y-m-d');
                }


                // create invoice
                $recurringInvoices = $client->invoices()->create($invoice_data);

                foreach($invoice->items as $key => $item) {
                    $invoice_items_data[$key]['bill_customer_id'] = $recurringInvoices->id;
                    $invoice_items_data[$key]['description'] = $item->description;
                    $invoice_items_data[$key]['quantity'] = $item->quantity;
                    $invoice_items_data[$key]['unit'] = $item->unit;
                    $invoice_items_data[$key]['price'] = $item->price;
                    $invoice_items_data[$key]['iva'] = $item->iva;
                    $invoice_items_data[$key]['total'] = $item->total;
                }

                $client->balance = round($client->balance - $invoice_data['total_pay'], 2);
                $client->save();

                $transaction_data = [
                    'client_id' => $client->id,
                    'amount' => $invoice_data['total_pay'],
                    'category' => 'recurring',
                    'date' => Carbon::now()->format('Y-m-d'),
                    'quantity' => '1',
                    'account_balance' => $client->balance,
                    'description' => 'Service charges'
                ];

                // create transaction
                $transaction = $client->transactions()->create($transaction_data);

                // create invoice_items
                $recurringInvoices->invoice_items()->insert($invoice_items_data);

                $invoice->next_pay_date = Carbon::parse($period)->addDay();
                $invoice->expiration_date = $period;
                $invoice->save();

                $invoiceNumber = $invoice_data['num_bill'];
                CommonService::log("#$invoiceNumber Factura creada para el cliente", $this->username, 'success' , $this->userId, $invoice->client_id);
                return Reply::success('Invoice generated successfully.');
            }
            return Reply::error('Recurring invoice start date is less then today date.');

        }

        return Reply::error('End date for this invoice is passed away.You can not generate invoice for this recurring invoice');
    }

    public function recurringBan($id)
    {
        $process = new Chkerr();
        $recurringInvoice = RecurringInvoice::find($id);
        if($recurringInvoice->service_status == 'active') {
            $recurringInvoice->service_status = 'block';
            $recurringInvoice->save();

            CommonService::log("La factura recurrente está bloqueada", $this->username, 'danger' , $this->userId, $recurringInvoice->client_id);

            return $process->show('banned');

        } else {
            $recurringInvoice->service_status = 'active';
            $recurringInvoice->save();

            CommonService::log("La factura recurrente está activada", $this->username, 'success' , $this->userId, $recurringInvoice->client_id);
            return $process->show('unbanned');
        }
    }

    public function postList($clientId) {

        $invoices = BillCustomer::join('billing_settings', 'bill_customers.client_id', 'billing_settings.client_id')
	        ->select('bill_customers.id', 'bill_customers.num_bill', 'bill_customers.csv_generated', 'bill_customers.release_date', 'bill_customers.total_pay', 'bill_customers.paid_on', 'bill_customers.status', 'bill_customers.created_at', 'bill_customers.cortado_date', 'billing_settings.billing_grace_period')
            ->where('bill_customers.client_id', $clientId)
            ->groupBy('bill_customers.id');


        $global = $this->global;
        return DataTables::of($invoices)
            ->addColumn('action', function ($row) use($global) {
                $actions = '';

                if (!in_array($row->status, [1, 2, 4])) {
                    if (PermissionsController::hasAnyRole('factura_pagar')) {
                        $actions = '<a href="javascript:void(0)" onclick="payInvoice(' . $row->id . ')" title="Pay Invoice"><span class="glyphicon glyphicon-ok"></span></a>&nbsp;    ';
                    }
                }

                $actions .= '
            <a href="javascript:void(0)" onclick="showInvoice(' . $row->id . ')" title="View Invoice">
            <span class="glyphicon glyphicon-info-sign"></span>
            </a>
            &nbsp;
            <a href="' . route('invoice.showPDF', ['id' => $row->id]) . '" target="_blank" title="View Invoice">
            <span class="glyphicon glyphicon-file"></span>
            </a>
            <a href="' . route('invoice.print', ['id' => $row->id]) . '" target="_blank" title="Print Invoice">
            <span class="glyphicon glyphicon-print"></span>
            </a>
            <a href="' . route('invoice.showPDF', ['id' => $row->id, 'download' => 'true']) . '" title="Download">
            <span class="glyphicon glyphicon-download"></span>
            </a>';


            if($global->activate_ven) {
                $actions .= '<a class="red" target="_blank" href="' . route('invoice.download.csv', ['id' => $row->id]) . '" title="Download Csv"><span class="glyphicon glyphicon-download"></span></a>&nbsp;';
            }


                $actions .= '<a href="javascript:void(0)" onclick="sendEmail(' . $row->id . ')" title="'.__('app.sendinvoice').'"><span class="glyphicon glyphicon-send"></span></a>';

                if (!in_array($row->status, [1, 2, 4])) {
                    if (PermissionsController::hasAnyRole('factura_editar')) {
                        if ($row->invoice_items->count() > 0) {
                            $actions .= '
                        <a href="javascript:void(0)" onclick="editInvoice(' . $row->id . ')" title="Edit Invoice">
                        <span class="glyphicon glyphicon-edit"></span>
                        </a>';
                        } else {
                            $actions .= '
                        <a href="javascript:void(0)" onclick="editCustomInvoice(' . $row->id . ')" title="Edit Invoice">
                        <span class="glyphicon glyphicon-edit"></span>
                        </a>';
                        }
                    }

                    if (PermissionsController::hasAnyRole('factura_eliminar')) {
                        $actions .= '<a href="javascript:void(0)" onclick="deleteInvoice(' . $row->id . ')" title="Delete Invoice"><span class="glyphicon glyphicon-trash"></span></a>';
                    }
                } else {
                    if ($row->status == 2) {
                        if (PermissionsController::hasAnyRole('factura_eliminar')) {
                            $actions .= '<a href="javascript:void(0)" onclick="deleteInvoice(' . $row->id . ')" title="Delete Invoice"><span class="glyphicon glyphicon-trash"></span></a>';
                        }
                    } else {
                        $actions = $actions . '
                    <a href="javascript:void(0)" onclick="editInvoicePayment(' . $row->id . ')" title="Edit Payment">
                    <span class="glyphicon glyphicon-pencil"></span>
                    </a>
                    <a href="javascript:void(0)" onclick="deleteInvoicePayment(' . $row->id . ')" title="Delete Payment"><span class="glyphicon glyphicon-remove"></span></a>';
                        if($global->activate_ven) {
                            if ($row->csv_generated) {
                                $actions .= '<a href="javascript:void(0)"  title="Send Fiscal"><span class="glyphicon glyphicon-download" style="color:gray"></span></a>';
                            } else {
                                $actions .= '<a href="javascript:void(0)" class="green" onclick="send_fiscal(' . $row->id . ')" title="Send Fiscal"><span class="glyphicon glyphicon-download"></span></a>';
                            }
                        }
                    $emisor = Factel::all();
                    $empresa = $emisor->first();

                        if($empresa->status== 2){
                            $invoice = Sri::where('id_factura', $row->id)->where('tipo','=','10')->where('estado','=','AUTORIZADO')->get();
                        }elseif($empresa->status== 3){
                            $invoice = Sri::where('id_factura', $row->id)->where('tipo','=','3')->where('estado', 'signed')->get();
                        }else{
                            $invoice = Sri::where('id_factura', $row->id)->where('estado','=','AUTORIZADO')->get();
                        }

                        if (!empty($empresa)) {
                            $status = $empresa->status;
                            if ($status != 1) {
                                if($status == 0){
                                    if(empty($invoice[0])){
                                        $actions .= '<a href="javascript:void(0)" onclick="send_sri(' . $row->id . ')" title="Send SRI"><span class="glyphicon glyphicon-envelope"></span></a>';
                                    }else{
                                        $actions .= '<a href="javascript:void(0)"  title="Send SRI"><span class="glyphicon glyphicon-envelope" style="color:gray"></span></a>';
                                    }
                                }else if($status == 2){
                                    if(empty($invoice[0])){
                                        $actions .= '<a href="javascript:void(0)" onclick="send_DIAN(' . $row->id . ')" title="Send DIAN"><span class="glyphicon glyphicon-envelope"></span></a>';
                                    }else{
                                        $actions .= '<a href="javascript:void(0)"  title="Send DIAN"><span class="glyphicon glyphicon-envelope" style="color:gray"></span></a>';
                                        $actions .= '<a href="javascript:void(0)" onclick="send_Note_DIAN(' . $row->id . ')" title="Send Note DIAN" style="margin-left: 5px;"><span class="glyphicon glyphicon-comment"></span></a>';
                                    }
                                }elseif($status == 3 ){
                                    if(empty($invoice[0])){
                                        $actions .= '<a href="javascript:void(0)" onclick="send_SAT(' . $row->id . ')" title="Send SAT"><span class="glyphicon glyphicon-envelope" style="margin:0 5px"></span></a>';
                                    }else{
                                        $actions .= '<a href="javascript:void(0)"  title="Send SAT"><span class="glyphicon glyphicon-envelope" style="color:gray;margin:0 5px"></span></a>';
                                        $actions .= '<a target="_blank" href="' . route('invoice_mx.payment.file') . '?doc_type=pdf&doc_id=' . $invoice[0]->informacionAdicional . '" title="Get SAT PDF"><span class="glyphicon glyphicon-print" style="margin:0 5px"></span></a>';
                                        $actions .= '<a target="_blank" href="' . route('invoice_mx.payment.file') . '?doc_type=xml&doc_id=' . $invoice[0]->informacionAdicional . '" title="Get SAT XML"><span class="glyphicon glyphicon-save-file" style="margin:0 5px"></span></a>';
                                    }
                                }
                            }
                        }
                    }
                }
                return $actions;
            })
            ->editColumn('release_date', function ($row) {
                return Carbon::parse($row->release_date)->format('d/m/Y');
            })
            ->editColumn('paid_on', function ($row) {
                if ($row->paid_on === null) {
                    return '--';
                }
                return Carbon::parse($row->paid_on)->format('d/m/Y');
            })
            ->editColumn('cortado_date', function ($row) use($global) {
                $gracePeriod = $row->billing_grace_period;
                $tolerence = $global->tolerance;
                $totalDays = $gracePeriod + $tolerence;
                $cortadoDate = Carbon::parse($row->cortado_date)->addDays($totalDays);
                return $cortadoDate->format('d/m/Y');
            })
            ->editColumn('status', function ($row) {
                $badge = '';
                $label = [
                    'paid' => 'success',
                    'unpaid' => 'warning',
                    'late' => 'danger'
                ];

                switch ($row->status) {
                    case '1':
                        $badge = [
                            'status' => 'paid',
                            'label' => $label['paid']
                        ];
                        break;

                    case '2':
                        $badge = [
                            'status' => 'paid (account balance)',
                            'label' => $label['paid']
                        ];
                        break;

                    case '3':
                        $badge = [
                            'status' => 'Unpaid',
                            'label' => $label['unpaid']
                        ];
                        break;

                    case '4':
                        $badge = [
                            'status' => 'paid',
                            'label' => $label['paid']
                        ];
                        break;

                    default:
                        # code...
                        break;
                }

                $status = '<label class="label label-' . $badge['label'] . '">
            <font style="vertical-align: inherit;">
            <font style="vertical-align: inherit;">
            ' . ucFirst($badge['status']) . '
            </font>
            </font>
            </label>';

                return $status;
            })
            ->rawColumns(['action', 'status'])
            ->make(true);
    }

    public function addOneTimeInvoiceView($id) {
        $this->num_bill = GlobalSetting::select('id', 'num_bill')->first()->num_bill + 1;
        $this->id = $id;

        return view('invoices.create', $this->data);
    }
    public function recurringInvoice($id) {
        $this->id = $id;

        return view('invoices.recurring-create', $this->data);
    }

    /**
     * Store a newly created resource in storage.
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response}
     */
    public function addOneTimeInvoiceCreate(Request $request, $id) {
        $request->request->add([
            'invoice_use_transactions' => $request->invoice_use_transactions ?? '0'
        ]);

        // validate request
        $validator = Validator::make($request->all(), [
            'invoice_date' => 'required|date',
            'invoice_billing_type' => [
                'required',
                Rule::in(['none', 'recurring', 'prepaid-monthly'])
            ],
            'invoice_pay_date' => 'required|date',
            'invoice_bill_num' => 'required|integer',
            'invoice_use_transactions' => [
                'required',
                Rule::in(['0', '1'])
            ],
            'pos.*' => 'required|integer',
            'period_from' => 'required|array|min:1',
            'period_from.*' => 'nullable|date',
            'period_to' => 'required|array|min:1',
            'period_to.*' => 'nullable|date',
            'quantity.*' => 'required|min:1',
            'price.*' => 'required',
            'tax_percent.*' => 'required'
        ]);

        if ($validator->fails()) {
            return $validator->errors()->toArray();
        }

        $client = Client::select('id', 'balance', 'wallet_balance')
            ->where('id', $id)
            ->with(['billing_settings'])
            ->first();


        // set invoice cortado date for block service for this client
        $cortadoDate = null;

        if($client->billing_settings) {
            $cortadoDate = Carbon::createFromFormat('d', $client->billing_settings->billing_due_date)->format('Y-m-d');
        }

        $invoice = BillCustomer::where('client_id', request()->client_id)->where('billing_type', 'recurring')->first();

        if(!$invoice) {
            $cortadoDate = Carbon::parse(request()->end_date)->addDays($client->billing_settings->billing_due_date)->format('Y-m-d');
        }


        $invoice_items_data = [];
        $invoice_data = [
            'num_bill' => $request->invoice_bill_num,
            'billing_type' => $request->invoice_billing_type,
            'period' => Carbon::parse($request->invoice_pay_date)->add($client->billing_settings->billing_grace_perid, 'days')->format('Y-m-d'),
            'release_date' => Carbon::parse($request->invoice_date)->format('Y-m-d'),
            'expiration_date' => Carbon::parse($request->invoice_pay_date)->format('Y-m-d'),
            'client_id' => $client->id,
            'open' => 0,
            'note' => $request->invoice_note,
            'memo' => $request->invoice_memo,
            'xero_id' => $request->invoice_xero_id,
            'use_transactions' => $request->invoice_use_transactions,
            'cortado_date' => $cortadoDate
        ];

        DB::beginTransaction();

        $invoice_data['total_pay'] = array_sum($request->total);

        $filtered_array = array_filter($request->period_from);
        $invoice_data['start_date'] = min($filtered_array);

        if ((float) $client->wallet_balance >= (float) $invoice_data['total_pay']) {
            $invoice_data['status'] =  2;
            $invoice_data['paid_on'] = Carbon::now()->format('Y-m-d');
            $client->wallet_balance -= array_sum($request->total);
        }
        else {
            $invoice_data['status'] = 3;

            $client->balance = round($client->balance - array_sum($request->total), 2);
        }

        // create invoice
        $invoice = $client->invoices()->create($invoice_data);

        // create keys from pos
        foreach ($request->pos as $pos_key => $pos_value) {
            $invoice_item_data = [];
            // loop through request variables except keys
            foreach ($request->all() as $input_field => $input_value) {
                if (gettype($input_value) === 'array' && !in_array($input_field, ['pos', 'id'])) {
                    $invoice_item_data[$input_field] = $input_value[$pos_key];
                }
            }
            $invoice_item_data['bill_customer_id'] = $invoice->id;
            $invoice_item_data['created_at'] = Carbon::now()->format('Y-m-d H:i:s');
            $invoice_item_data['updated_at'] = Carbon::now()->format('Y-m-d H:i:s');
            array_push($invoice_items_data, $invoice_item_data);
        }

//        $client->balance = round($client->balance - $invoice_data['total_pay'], 2);
        $client->save();

//        $clientSuspend = SuspendClient::where('client_id', $client->id)->first();
//
//        $clientSuspend->expiration = $invoice_data['period'];
//        $clientSuspend->save();

        $transaction_data = [
            'client_id' => $client->id,
            'amount' => $invoice_data['total_pay'],
            'category' => 'service',
            'date' => Carbon::now()->format('Y-m-d'),
            'quantity' => '1',
            'account_balance' => $client->wallet_balance,
            'description' => 'Service charges'
        ];

        // create transaction
        $transaction = $client->transactions()->create($transaction_data);

        // create invoice_items
        $invoice->invoice_items()->insert($invoice_items_data);

//        if($invoice->status != 3) {
//	        CommonService::addWalletPayment($client->id, $request->invoice_bill_num, request()->total, \auth()->user()->id);
//        }
        $invoiceNumber = $invoice_data['num_bill'];
        CommonService::log("#$invoiceNumber Factura creada para el cliente", $this->username, 'success' , $this->userId, $invoice->client_id);
        $this->updateNumBill();
        DB::commit();

        return Reply::success(__('messages.invoicecreatedsuccessfully'));
    }

    public function storeRecurringInvoice(\App\Http\Requests\RecurringInvoice\StoreRequest $request, $id)
    {

        $client = Client::find($id);

        $invoice_items_data = [];

        $invoice_data = [
            'start_date' => Carbon::createFromFormat('d/m/Y', $request->start_date)->format('Y-m-d H:i:s'),
            'client_id' => $client->id,
            'memorandum' => $request->invoice_memo,
            'frequency' => $request->frequency,
            'price' => array_sum($request->total),
            'note' => $request->invoice_note,
        ];

        if($request->end_date) {
            $invoice_data['end_date'] = Carbon::createFromFormat('d/m/Y', $request->end_date)->format('Y-m-d H:i:s');
        }

        DB::beginTransaction();

        // create invoice
        $recurringInvoice = $client->recurring_invoices()->create($invoice_data);

        // create keys from pos
        foreach ($request->pos as $pos_key => $pos_value) {
            $invoice_item_data = [];
            // loop through request variables except keys
            foreach ($request->all() as $input_field => $input_value) {
                if (gettype($input_value) === 'array' && !in_array($input_field, ['pos', 'id'])) {
                    $invoice_item_data[$input_field] = $input_value[$pos_key];
                }
            }
            $invoice_item_data['recurring_invoice_id'] = $recurringInvoice->id;
            $invoice_item_data['created_at'] = Carbon::now()->format('Y-m-d H:i:s');
            $invoice_item_data['updated_at'] = Carbon::now()->format('Y-m-d H:i:s');
            array_push($invoice_items_data, $invoice_item_data);
        }

        // create invoice_items
        $recurringInvoice->items()->insert($invoice_items_data);
        DB::commit();

        $startDate = Carbon::createFromFormat('d/m/Y', $request->start_date);
        if($startDate->format('Y-m-d') == Carbon::now()->format('Y-m-d')) {
            $invoice_num = CommonService::getBillNumber();
            $period = '';
            switch ($request->frequency) {
                case 'week':
                    $period = Carbon::createFromFormat('d/m/Y', $request->start_date)->addDays(7)->format('Y-m-d');
                    break;
                case 'month':
                    $period = Carbon::createFromFormat('d/m/Y', $request->start_date)->addMonths(1)->format('Y-m-d');
                    break;
                case 'year':
                    $period = Carbon::createFromFormat('d/m/Y', $request->start_date)->addYears(1)->format('Y-m-d');
                    break;
            }

            $invoice_items_data = [];

            $invoice_data = [
                'num_bill' => $invoice_num,
                'start_date' => Carbon::createFromFormat('d/m/Y', $request->start_date)->format('Y-m-d'),
                'billing_type' => 'recurring',
                'period' => $period,
                'release_date' => Carbon::createFromFormat('d/m/Y', $request->start_date)->format('Y-m-d'),
                'expiration_date' => $period,
                'client_id' => $client->id,
                'open' => 0,
                'note' => $request->invoice_note,
                'memo' => $request->invoice_memo,
                'use_transactions' => 0,
                'recurring_invoice' => 'yes',
            ];

            $invoice_data['total_pay'] = array_sum($request->total);

//            if ((float) $client->balance >= (float) $invoice_data['total_pay']) {
//                $invoice_data['status'] = 2;
//                $invoice_data['paid_on'] = Carbon::now()->format('Y-m-d');
//            }
//
//            $invoice_data['status'] = 3;

            if ((float) $client->wallet_balance >= (float) $invoice_data['total_pay']) {
                $invoice_data['status'] =  2;
                $invoice_data['paid_on'] = Carbon::now()->format('Y-m-d');
                $client->wallet_balance -= array_sum($request->total);
            }
            else {
                $invoice_data['status'] = 3;
                $client->balance = round($client->balance - array_sum($request->total), 2);
            }

            // create invoice
            $invoice = $client->invoices()->create($invoice_data);

            // create keys from pos
            foreach ($request->pos as $pos_key => $pos_value) {
                $invoice_item_data = [];
                // loop through request variables except keys
                foreach ($request->all() as $input_field => $input_value) {
                    if (gettype($input_value) === 'array' && !in_array($input_field, ['pos', 'id'])) {
                        $invoice_item_data[$input_field] = $input_value[$pos_key];
                    }
                }
                $invoice_item_data['bill_customer_id'] = $invoice->id;
                $invoice_item_data['created_at'] = Carbon::now()->format('Y-m-d H:i:s');
                $invoice_item_data['updated_at'] = Carbon::now()->format('Y-m-d H:i:s');
                array_push($invoice_items_data, $invoice_item_data);
            }

//            $client->balance = round($client->balance - $invoice_data['total_pay'], 2);
            $client->save();

//            $clientSuspend = SuspendClient::where('client_id', $client->id)->first();
//
//            $clientSuspend->expiration = $invoice_data['period'];
//            $clientSuspend->save();

            $transaction_data = [
                'client_id' => $client->id,
                'amount' => $invoice_data['total_pay'],
                'category' => 'recurring',
                'date' => Carbon::now()->format('Y-m-d'),
                'quantity' => '1',
                'account_balance' => $client->wallet_balance,
                'description' => 'Service charges'
            ];

            // create transaction
            $transaction = $client->transactions()->create($transaction_data);

            // create invoice_items
            $invoice->invoice_items()->insert($invoice_items_data);

            $recurringInvoice->next_pay_date = Carbon::parse($period)->addDay();
            $recurringInvoice->expiration_date = $period;
            $recurringInvoice->save();
            $invoiceNumber = $invoice_data['num_bill'];
            CommonService::log("#$invoiceNumber factura recurrente creada para el cliente", $this->username, 'success' , $this->userId, $invoice->client_id);

        }

        return Reply::success(__('messages.invoicecreatedsuccessfully'));
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Invoice  $invoice
     * @return \Illuminate\Http\Response
     */
    public function showInvoice($id) {
        $invoice = BillCustomer::select('id', 'client_id', 'num_bill', 'start_date', 'expiration_date', 'iva', 'cost', 'total_pay')
            ->where('id', $id)
            ->with([
                'client' => function($q) {
                    $q->select('id', 'name');
                },
                'invoice_items' => function($query) {
                    $query->select('id', 'bill_customer_id', 'description', 'period_to', 'period_from', 'quantity', 'unit', 'price', 'iva', 'total');
                }
            ])
            ->first();

        $extra_data = [
            'total_without_iva' => 0.0,
            'total_iva' => 0.0
        ];

        if ($invoice->invoice_items->count() > 0) {
            foreach ($invoice->invoice_items as $invoice_item) {
                $extra_data['total_without_iva'] += $invoice_item->quantity * $invoice_item->price;
                $extra_data['total_iva'] += $invoice_item->quantity * $invoice_item->price * $invoice_item->iva / 100;
            }
        } else {
            $extra_data['total_without_iva'] = $invoice->cost;
            $extra_data['total_iva'] = round($invoice->cost * $invoice->iva / 100, 2);
        }

        return view('invoices.show', ['invoice_data' => $invoice, 'extra_data' => $extra_data]);
    }


    public function payInvoiceView($id) {
        $info = BillCustomer::select('id', 'total_pay','client_id')->where('id', $id)->first();
        $this->invoice =$info;
        $this->invoiceId = $id;

        $this->client = Client::find($info->client_id);
        $this->walletBalance = round($this->client->wallet_balance, 2);
//        if($client->status == 'de'){
//            $ok=ClientsController::postBanNew($info->client_id);
//        }
        return view('invoices.pay-invoice', $this->data);
    }

    public function payInvoice(StoreRequest $request) {
        
        if($request->id_pago != '') {
            $payment = PaymentNew::where('id_pago', $request->id_pago)->first();

            if($payment) {

                $validator = \Validator::make([], []);
                $validator->getMessageBag()->add('id_pago', 'The id pago has already been taken.');
                
                throw new ValidationException($validator);
            }

        }

        $invoice = BillCustomer::select('id', 'total_pay', 'num_bill', 'client_id', 'status', 'paid_on', 'period')->findOrFail($request->invoice_id);

        DB::beginTransaction();

        // Get client details
        $client = Client::with('billing_settings')
            ->find($invoice->client_id);


        if($request->has('payByWallet') && $request->payByWallet == "1") {

//	        if($client->wallet_balance < $invoice->total_pay) {
//		        $validator = \Validator::make([], []);
//		        $message = __('messages.walletBalanceGreater');
//		        $validator->getMessageBag()->add('amount', $message);
//		        throw new ValidationException($validator);
//	        }



            if($client->wallet_balance < $invoice->total_pay && $request->restCash > 0)
            {
                $clientWallet = $client->wallet_balance;

                CommonService::addWalletPayment($client->id, $invoice->num_bill, $clientWallet, \auth()->user()->id);

                // add into client wallet
                $client->wallet_balance = round($client->wallet_balance + $request->restCash, 2);

                // add into payment if there restCash more then 0
                $payment = new PaymentNew();
                $payment->way_to_pay = $request->way_to_pay;
                $payment->date = Carbon::parse($request->date)->format('Y-m-d');
                $payment->amount = round($request->restCash, 2);

                $payment->id_pago = $request->id_pago;
                $payment->commentary = $request->commentary;
                $payment->note = $request->note;
                $payment->client_id = $invoice->client_id;
                $payment->num_bill = $invoice->num_bill;
                $payment->received_by = \auth()->user()->id;

                $payment->save();

                // add transaction of restCash payment
                $transaction = new Transaction();
                $transaction->client_id = $invoice->client_id;
                $transaction->amount = $request->restCash;
                $transaction->account_balance = $client->wallet_balance;
                $transaction->category = 'payment';
                $transaction->quantity = 1;
                $transaction->date = Carbon::parse($request->date)->format('Y-m-d');
                $transaction->save();

            } else {
                CommonService::addWalletPayment($client->id, $invoice->num_bill, $invoice->total_pay, \auth()->user()->id);
            }

            // Add transactions
            $transaction = new Transaction();
            $transaction->client_id = $invoice->client_id;
            $transaction->amount = $invoice->total_pay;
            $transaction->account_balance = round($client->wallet_balance - $invoice->total_pay, 2);
            $transaction->category = 'service';
            $transaction->quantity = 1;
            $transaction->date = Carbon::parse($request->date)->format('Y-m-d');
            $transaction->save();


            // Maintain client wallet balance
            $client->wallet_balance = round($client->wallet_balance - $invoice->total_pay, 2);

        } else {


            // Add transactions
            $transaction = new Transaction();
            $transaction->client_id = $invoice->client_id;
            $transaction->amount = $invoice->total_pay;
            $transaction->account_balance = $client->wallet_balance;
            $transaction->category = 'payment';
            $transaction->quantity = 1;
            $transaction->date = Carbon::parse($request->date)->format('Y-m-d');
            $transaction->save();

            // Add new payment
            $payment = new PaymentNew();
            $payment->way_to_pay = $request->way_to_pay;
            $payment->date = Carbon::parse($request->date)->format('Y-m-d');
            $payment->amount = $invoice->total_pay;

            $payment->id_pago = $request->id_pago;
            $payment->commentary = $request->commentary;
            $payment->note = $request->note;
            $payment->client_id = $invoice->client_id;
            $payment->num_bill = $invoice->num_bill;
            $payment->received_by = \auth()->user()->id;

            $payment->save();


//	        // Maintain client wallet balance
//	        $client->wallet_balance = round($client->wallet_balance - $invoice->total_pay, 2);
        }
        $nameClient = $client->name;
        CommonService::log("Pago agregado para el cliente: $nameClient", $this->username, 'success' , $this->userId, $invoice->client_id);

        $global = GlobalSetting::first();
        // Maintain client account balance
        $client->balance = round($client->balance + $invoice->total_pay, 2);

        $client->save();

        $invoice->status = 1;
        $invoice->paid_on = Carbon::now()->format('Y-m-d');

        $invoice->save();
        
        

        // get details for cortado service
        $cortadoDetails = CommonService::getServiceCortadoDate($client->id);
        $billingDueDate = CommonService::getCortadoDateWithTolerence($client->id, $client->billing_settings->billing_grace_period, $global->tolerance);
        $sendInvoice = false;
        foreach($client->service as $service) {
            if($service->status == 'de' && (now()->startOfDay()->lessThanOrEqualTo($billingDueDate) || $cortadoDetails['paid'])) {
                $clientServiceController = new ClientServiceController();
                $request = new Request([
                    'id'   => $service->id,
                ]);
                $ok = $clientServiceController->postBanService($request, $service->id);

                $service->status = 'ac';
                $service->save();
            }

            if(!$cortadoDetails['paid'] && $cortadoDetails['cortado_date'] && $service->status == 'ac') {

                if(now()->startOfDay()->greaterThan($billingDueDate)) {
                    $service->status = 'de';
                    $service->save();
                }
            }
            if($service->send_invoice == '1'){
                    $sendInvoice = true;
            }

        }

        

        
        DB::commit();
        return Reply::success(__('app.invoicePaidSuccessfully'), ['invoice_id' => $invoice->id, 'sendInvoice' => $sendInvoice]);
    }

    public function checkInvoices(Request $request) {
        $dateRange = preg_split("/\|/", $request->get('date-range'));

        $from = $dateRange['0'];
        $to = $dateRange['1'];

        $invoicesCount = BillCustomer::leftJoin('payment_news', 'payment_news.num_bill', 'bill_customers.num_bill')
            ->join('clients', 'clients.id', 'bill_customers.client_id')
            ->join('client_services', 'client_services.client_id', 'bill_customers.client_id')
            ->join('routers', 'routers.id', 'client_services.router_id')
            ->select('bill_customers.id')
            ->whereDate('start_date', '>=', Carbon::parse($from))
            ->whereDate('start_date', '<=', Carbon::parse($to));

        if ($request->routers !== 'any') {
            $invoicesCount = $invoicesCount->where('client_services.router_id', $request->routers);
        }

        if ($request->location !== 'any') {
            $invoicesCount = $invoicesCount->where('routers.location', 'like', '%' . $request->location . '%');
        }

        if ($request->way_to_pay !== 'any') {
            $invoicesCount = $invoicesCount->where('payment_news.way_to_pay', $request->way_to_pay);
        }

        if ($request->status !== 'any') {
            $invoicesCount = $invoicesCount->where('bill_customers.status', $request->status);
        }

        $count = $invoicesCount->count();
        $message = 'Total invoices are ' . $count;
        return Reply::success($message, ['total' => $count]);
    }

    public function exportInvoices(Request $request) {
        $dateRange = preg_split("/\|/", $request->get('date-range'));

        $from = $dateRange['0'];
        $to = $dateRange['1'];

        $invoicesCount = BillCustomer::leftJoin('payment_news', 'payment_news.num_bill', 'bill_customers.num_bill')
            ->join('clients', 'clients.id', 'bill_customers.client_id')
            ->join('client_services', 'client_services.client_id', 'bill_customers.client_id')
            ->join('routers', 'routers.id', 'client_services.router_id')
            ->join('plans', 'plans.id', '=', 'client_services.plan_id')
            ->select('bill_customers.id', 'bill_customers.num_bill', 'bill_customers.expiration_date', 'bill_customers.total_pay', 'bill_customers.release_date', 'bill_customers.period', 'bill_customers.status', 'plans.upload', 'plans.download', 'plans.name As planname', 'bill_customers.cost', 'bill_customers.iva', 'clients.name As client', 'clients.address', 'clients.phone', 'clients.email', 'clients.balance', 'clients.dni')
            ->whereDate('start_date', '>=', Carbon::parse($from))
            ->whereDate('start_date', '<=', Carbon::parse($to));

        if ($request->routers !== 'any') {
            $invoicesCount = $invoicesCount->where('client_services.router_id', $request->routers);
        }

        if ($request->location !== 'any') {
            $invoicesCount = $invoicesCount->where('routers.location', 'like', '%' . $request->location . '%');
        }

        if ($request->way_to_pay !== 'any') {
            $invoicesCount = $invoicesCount->where('payment_news.way_to_pay', $request->way_to_pay);
        }

        if ($request->status !== 'any') {
            $invoicesCount = $invoicesCount->where('bill_customers.status', $request->status);
        }

        $invoices = $invoicesCount->groupBy('bill_customers.num_bill')->get();

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

                $columns = array('ID', 'Customer Name', 'Phone Number', 'Email', 'Address', 'Duration', 'Suspensión', 'Plan', 'DNI', 'IVA', 'Cost', 'Total');

                $callback = function() use ($invoices, $columns, $request, $from, $to) {
                    $file = fopen('php://output', 'w');
                    fputcsv($file, $columns);

                    foreach ($invoices as $review) {

                        $costIva = $review->iva * ($review->balance / 100);
                        $costIva = round($costIva, 2);

                        $expire = strtotime($review->expiration_date);
                        $release = strtotime($review->release_date);

                        fputcsv($file, array($review->id, $review->client, $review->phone, $review->email, $review->address, $release . ' to ' . $expire, $expire, $review->planname, $review->dni, $costIva, $review->balance, $review->total_pay));
                    }
                    fclose($file);


                    $history = new ExportHistory();
                    $history->type = $request->export_type;
                    $history->from = Carbon::parse($from)->format('Y-m-d');
                    $history->to = Carbon::parse($to)->format('Y-m-d');
                    $history->router = $request->routers;
                    $history->location = $request->location;
                    $history->payment_type = $request->way_to_pay;
                    $history->status = $request->status;

                    $history->save();
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
                        "costo" => $invoice->balance,
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

    private function typePdf($request, $invoices, $from, $to) {
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

        body,
        td,
        input,
        select {
            font-family: Tahoma;
            font-size: 1rem;
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

        table.amount_info {
            width: 100%;
        }

        table.amount_info td{
            border: none;
        }

        table.amount_info tr:last-child td {
            border: none;
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
            color: #f89406;
            font-weight: bold;
        }

        .paid, .paid-account{
            font-size: 20px;
            color: #82af6f;
            font-weight: bold;
        }

        .late {
            font-size: 20px;
            color: #d15b47;
            font-weight: bold;
        }

        .remove {
            font-size: 20px;
            color: #777;
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

        .ba {
            background-color: #EFEFEF;
        }

        .ml-5 {
            margin-left: 2rem;
        }
        </style>
        </head>
        <body>';

        foreach ($invoices as $invoice) {
            $costIva = $invoice->iva * ($invoice->balance / 100);
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
                "costo" => $invoice->balance,
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
            $view = view('templates.multiple', $data)->render();
            $html .= $view;
        }

        $html .= '</body></html>';


        $history = new ExportHistory();
        $history->type = $request->export_type;
        $history->from = Carbon::parse($from)->format('Y-m-d');
        $history->to = Carbon::parse($to)->format('Y-m-d');
        $history->router = $request->routers;
        $history->location = $request->location;
        $history->payment_type = $request->way_to_pay;
        $history->status = $request->status;

        $history->save();



        $pdf = PDF::loadHTML($html);
        return $pdf->download('invoices.pdf');
    }

    public function updateNumBill() {
        $globalSettings = GlobalSetting::first();

        if ($globalSettings->num_bill > 1) {
            $globalSettings->num_bill = $globalSettings->num_bill + 1;
            $globalSettings->save();
        }
    }

    public function exportInvoicePopup() {
        $this->routers = Router::all();
        return view('invoices.export', $this->data);
    }

    public function edit($id) {
        $this->invoice = BillCustomer::with('invoice_items')->find($id);

        return view('invoices.edit', $this->data);
    }

    public function editRecurring($id) {
        $this->invoice = BillCustomer::select('id', 'num_bill', 'expiration_date', 'start_date', 'iva', 'cost', 'total_pay', 'note', 'memo', 'xero_id')->find($id);

        return view('invoices.edit-recurring', $this->data);
    }

    public function editRecurringInvoices($id) {
        $this->invoice = RecurringInvoice::with('items')->find($id);

        return view('invoices.recurring-edit', $this->data);
    }

    public function updateRecurringInvoice(\App\Http\Requests\RecurringInvoice\StoreRequest $request, $id, $invoice_id)
    {
        $client = Client::find($id)->first();

        $invoice = RecurringInvoice::find($invoice_id);

        $invoice_items_data = [];

        $invoice_data = [
            'start_date' => Carbon::parse($request->start_date)->format('Y-m-d H:i:s'),
            'client_id' => $client->id,
            'memorandum' => $request->invoice_memo,
            'frequency' => $request->frequency,
            'price' => array_sum($request->total),
            'note' => $request->invoice_note,
        ];

        $invoice_data['end_date'] = $request->end_date ? Carbon::parse($request->end_date)->format('Y-m-d H:i:s') : null;

        DB::beginTransaction();
        $invoice->items()->delete();
        // create invoice
        $invoice->update($invoice_data);

        // create keys from pos
        foreach ($request->pos as $pos_key => $pos_value) {
            $invoice_item_data = [];
            // loop through request variables except keys
            foreach ($request->all() as $input_field => $input_value) {
                if (gettype($input_value) === 'array' && !in_array($input_field, ['pos', 'id'])) {
                    $invoice_item_data[$input_field] = $input_value[$pos_key];
                }
            }
            $invoice_item_data['recurring_invoice_id'] = $invoice->id;
            $invoice_item_data['created_at'] = Carbon::now()->format('Y-m-d H:i:s');
            $invoice_item_data['updated_at'] = Carbon::now()->format('Y-m-d H:i:s');
            array_push($invoice_items_data, $invoice_item_data);
        }

        // create invoice_items
        $invoice->items()->insert($invoice_items_data);

        CommonService::log("#$invoice_id La factura recurrente se actualiza", $this->username, 'success' , $this->userId, $invoice->client_id);
        DB::commit();

        return Reply::success(__('messages.invoicecreatedsuccessfully'));
    }

    public function updateInvoice(Request $request, $id) {
        $request->request->add([
            'invoice_use_transactions' => $request->invoice_use_transactions ?? '0'
        ]);

        // validate request
        $validator = Validator::make($request->all(), [
            'invoice_date' => 'required|date',
            'invoice_pay_date' => 'required|date',
            'invoice_billing_type' => [
                'required',
                Rule::in(['none', 'recurring', 'prepaid-monthly'])
            ],
            'invoice_bill_num' => 'required|integer',
            'invoice_use_transactions' => [
                'required',
                Rule::in(['0', '1'])
            ],
            'pos.*' => 'required|integer',
            'period_from' => 'required|array|min:1',
            'period_from.*' => 'nullable|date',
            'period_to' => 'required|array|min:1',
            'period_to.*' => 'nullable|date',
            'quantity.*' => 'required|min:1',
            'price.*' => 'required',
            'tax_percent.*' => 'required'
        ]);

        if ($validator->fails()) {
            return Reply::error('Fill the form correctly before proceeding.');
        }

        $invoice = BillCustomer::find($id);

        $billAmountBeforeUpdate = $invoice->total_pay;

        $invoice_items_data = [];
        $invoice_data = [
            'num_bill' => $request->invoice_bill_num,
            'billing_type' => $request->invoice_billing_type,
            'period' => Carbon::parse($request->invoice_pay_date)->add($invoice->client->billing_settings->billing_grace_perid, 'days')->format('Y-m-d'),
            'release_date' => Carbon::parse($request->invoice_date)->format('Y-m-d'),
            'expiration_date' => Carbon::parse($request->invoice_pay_date)->format('Y-m-d'),
            'open' => 0,
            'note' => $request->invoice_note,
            'memo' => $request->invoice_memo,
            'xero_id' => $request->invoice_xero_id,
            'use_transactions' => $request->invoice_use_transactions,
        ];

        $invoice_data['total_pay'] = array_sum($request->total);
        ;
        $filtered_array = array_filter($request->period_from);
        $invoice_data['start_date'] = min($filtered_array);

        if ((float) $invoice->client->balance >= (float) $invoice_data['total_pay']) {
            $invoice_data['status'] = 2;
            $invoice_data['paid_on'] = Carbon::now()->format('Y-m-d');
        }

        $invoice_data['status'] = 3;

        DB::beginTransaction();

        // create invoice
        $invoice->update($invoice_data);

        // create keys from pos
        foreach ($request->pos as $pos_key => $pos_value) {
            $invoice_item_data = [];
            // loop through request variables except keys
            foreach ($request->all() as $input_field => $input_value) {
                if (gettype($input_value) === 'array' && !in_array($input_field, ['pos', 'id'])) {
                    $invoice_item_data[$input_field] = $input_value[$pos_key];
                }
            }
            $invoice_item_data['bill_customer_id'] = $invoice->id;
            $invoice_item_data['created_at'] = Carbon::now()->format('Y-m-d H:i:s');
            $invoice_item_data['updated_at'] = Carbon::now()->format('Y-m-d H:i:s');
            array_push($invoice_items_data, $invoice_item_data);
        }

        $diff = $invoice_data['total_pay'] - $billAmountBeforeUpdate;
        $invoice->client->balance = round($invoice->client->balance - $diff, 2);
        $invoice->client->save();

        $clientSuspend = SuspendClient::where('client_id', $invoice->client->id)->first();

        $clientSuspend->expiration = $invoice_data['period'];
        $clientSuspend->save();

        $transaction_data = [
            'client_id' => $invoice->client->id,
            'amount' => $invoice_data['total_pay'],
            'category' => 'service',
            'date' => Carbon::now()->format('Y-m-d'),
            'quantity' => '1',
            'account_balance' => $invoice->client->balance,
            'description' => 'Service charges'
        ];

        // create transaction
        $transaction = $invoice->client->transactions()->create($transaction_data);

        BillCustomerItem::where('bill_customer_id', $invoice->id)->delete();

        // create invoice_items
        $invoice->invoice_items()->insert($invoice_items_data);
        CommonService::log("#$id La factura se actualiza", $this->username, 'success' , $this->userId, $invoice->client_id);
        DB::commit();

        return Reply::success(__('app.invoiceUpdatedSuccessfully'));
    }

    public function updateOneTimeInvoice(Request $request, $id) {
        // validate request
        $validator = Validator::make($request->all(), [
            'invoice_date' => 'required',
            'invoice_bill_num' => 'required|integer',
            'actual_total_pay' => 'required',
        ]);

        if ($validator->fails()) {
            return Reply::error('Fill the form correctly before proceeding.');
        }

        $invoice_date_array = explode(' - ', $request->invoice_date);
        $start_date = $invoice_date_array[0];
        $expiration_date = $invoice_date_array[1];

        $invoice = BillCustomer::find($id);

        $billAmountBeforeUpdate = $invoice->total_pay;

        $invoice_data = [
            'num_bill' => $request->invoice_bill_num,
            'period' => Carbon::parse($expiration_date)->add($invoice->client->billing_settings->billing_grace_perid, 'days')->format('Y-m-d'),
            'expiration_date' => $expiration_date,
            'note' => $request->invoice_note,
            'memo' => $request->invoice_memo,
        ];

        DB::beginTransaction();

        if (!is_null($request->total_pay)) {
            $invoice_data['total_pay'] = $request->total_pay;
        } else {
            $invoice_data['total_pay'] = $request->actual_total_pay;
        }

        $invoice_data['actual_total_pay'] = $request->actual_total_pay;
        $invoice_data['start_date'] = $start_date;

        $diff = $invoice_data['total_pay'] - $billAmountBeforeUpdate;

        if ((float) $invoice->client->balance >= (float) $invoice_data['total_pay']) {
            $invoice_data['status'] = 2;
            $invoice_data['paid_on'] = Carbon::now()->format('Y-m-d');

            $invoice->client->balance = round($invoice->client->balance - $invoice_data['total_pay'], 2);
            $invoice->client->save();
        } else {

            if ($invoice->client->balance > 0) {
                $invoice->client->balance = round($invoice->client->balance - $diff, 2);
            } else {
                $diff = $invoice_data['total_pay'] - $billAmountBeforeUpdate;
                $invoice->client->balance = round($invoice->client->balance - $diff, 2);
            }

            $invoice->client->save();
        }

        $invoice_data['status'] = 3;

        if (!is_null($request->total_pay)) {
            if ($invoice->total_pay > $request->total_pay) {
                $transaction_amount = ($invoice->total_pay - $invoice_data['actual_total_pay']);
            } else {
                $transaction_amount = ($request->total_pay - $invoice_data['actual_total_pay']) * -1;
            }
        } else {
            $transaction_amount = $invoice->total_pay - round($invoice_data['actual_total_pay'], 2);
        }

        $clientSuspend = SuspendClient::where('client_id', $invoice->client->id)->first();

        $clientSuspend->expiration = $invoice_data['period'];
        $clientSuspend->save();

        if ($invoice->total_pay != $request->total_pay) {
            $transaction_data = [
                'client_id' => $invoice->client->id,
                'amount' => abs($transaction_amount),
                'category' => $transaction_amount < 0 ? 'service' : 'refund',
                'date' => Carbon::now()->format('Y-m-d'),
                'quantity' => '1',
                'account_balance' => $invoice->client->balance,
                'description' => $invoice->client->plan->name
            ];
            // create invoice
            $invoice->update($invoice_data);
            // create transaction
            $transaction = $invoice->client->transactions()->create($transaction_data);
        }

        $invoice->update($invoice_data);
        CommonService::log("#$id La factura se actualiza", $this->username, 'success' , $this->userId, $invoice->client_id);
        DB::commit();

        return Reply::success('Invoice updated successfully.');
    }

    public function delete($id) {
        $invoice = BillCustomer::find($id);

        if ($invoice) {
            // delete transaction
            $transaction = Transaction::select('id')
                ->where([
                    'client_id' => $invoice->client_id,
                    'amount' => $invoice->total_pay,
                    'date' => Carbon::parse($invoice->created_at)->format('Y-m-d'),
                    'category' => 'service'
                ])
                ->first();

            if ($transaction) {
                $transaction->delete();
            }



            // Maintain client account balance
            $invoice->client->balance = round($invoice->client->balance + $invoice->total_pay, 2);
            $invoice->client->save();

            BillCustomer::destroy($id);

            CommonService::log("#$id se elimina la factura", $this->username, 'danger' , $this->userId, $invoice->client_id);
            return Reply::success('Invoice successfully deleted');
        }

        return Reply::error('Invoice not found');
    }

    public function recurringDelete($id) {
        $invoice = RecurringInvoice::find($id);

        if ($invoice) {
            // delete transaction
            $invoice->items()->delete();
            RecurringInvoice::destroy($id);

            CommonService::log("#$id Se elimina la factura recurrente", $this->username, 'danger' , $this->userId, $invoice->client_id);
            return Reply::success('Invoice successfully deleted');
        }

        return Reply::error('Invoice not found');
    }

    public function invoicePaymentSend($id) {
        $xml_ecuador = $this->xml($id);

        if($xml_ecuador == "El cliente no tiene asignado un punto de emision"){
            return Reply::error('El cliente no tiene asignado un punto de emision');
        }

        $num_bill = $this->invoice = BillCustomer::select('id', 'num_bill')->where('id', $id)->first();

        $emisor = Factel::all();
        $empresa = $emisor->first();
        $server = $_SERVER['SERVER_NAME'];
        if (!empty($_SERVER['SERVER_PORT'])) {
            $server .= ':' . $_SERVER['SERVER_PORT'];
        }

        $ruta_factura = 'http://' . $server . '/storage/factura.xml';
        // $factura = file_get_contents("http://127.0.0.1:8000/factura.xml");
        $factura = \Storage::disk('public')->get('factura.xml'); //funcionando, obtiene el contenido del archivo xml
        $ruta_certificado = 'http://' . $server . '/' . $empresa->certificado_digital;

        $contraseña = $empresa->pass_certificado;
        $global = GlobalSetting::all()->first();

        if ($global->email == '')
            return Reply::error('Debe configurar los datos de envio de correo (Email SMTP principal)');
        if ($global->password == '')
            return Reply::error('Debe configurar los datos de envio de correo (Email SMTP principal)');
        if ($global->server == '')
            return Reply::error('Debe configurar los datos de envio de correo (Email SMTP principal)');
        if ($global->port == '')
            return Reply::error('Debe configurar los datos de envio de correo (Email SMTP principal)');

        $ruta_respuesta = 'http://localhost:8000/js/lib_firma_sri/example.php';
        $host_email = $global->server;
        $email = $global->email;
        $pass = $global->password;
        $port = $global->port;

        $host_bd = Config::get('database.connections.mysql.host');
        $pass_bd = Config::get('database.connections.mysql.password');
        $user_bd = Config::get('database.connections.mysql.username');
        $database = Config::get('database.connections.mysql.database');
        $port_bd = Config::get('database.connections.mysql.port');

        $data = ['message' => 'Venta generada correctamente',
            'ruta_factura' => $factura,
            'ruta_certificado' => $ruta_certificado,
            'contrasena' => $contraseña,
            'ruta_respuesta' => $ruta_respuesta,
            'host_email' => $host_email,
            'email' => $email,
            'passEmail' => $pass,
            'port' => $port,
            'host_bd' => $host_bd,
            'pass_bd' => $pass_bd,
            'user_bd' => $user_bd,
            'database' => $database,
            'port_bd' => $port_bd,
            'id_factura' => $num_bill->id,
        ];
        return Reply::success('Sending to SRI', $data);
    }
    public function invoice_colombiaPaymentSend($id) {

        $global = GlobalSetting::all()->first();
        if ($global->email == '')
            return Reply::error('Debe configurar los datos de envio de correo (Email SMTP principal)');
        if ($global->email == '')
            return Reply::error('Debe configurar los datos de envio de correo (Email SMTP principal)');
        if ($global->password == '')
            return Reply::error('Debe configurar los datos de envio de correo (Email SMTP principal)');
        if ($global->server == '')
            return Reply::error('Debe configurar los datos de envio de correo (Email SMTP principal)');
        if ($global->port == '')
            return Reply::error('Debe configurar los datos de envio de correo (Email SMTP principal)');

        $bill = BillCustomer::select('bill_customers.id', 'bill_customers.client_id','bill_customers.num_bill', 'bill_customers.iva', 'bill_customers.cost', 'bill_customers.total_pay', 'bill_customers.paid_on','bill_customers.xero_id', 'bill_customers.use_transactions','bill_customers.status', 'bill_customers.created_at','clients.id as client_id','clients.typedoc_cod','clients.dni','clients.name','clients.economicactivity_cod','clients.typeresponsibility_cod','clients.typetaxpayer_cod','clients.municipio_cod','clients.address', 'clients.phone', 'clients.email')
            ->join('clients', 'clients.id', 'bill_customers.client_id')->where('bill_customers.id',$id)
            ->first();
        if ($global->email == '')
            return Reply::error('Debe configurar los datos de envio de correo (Email SMTP principal)');

        if ($bill->municipio_cod == '0'){
            return Reply::error('El municipio del cliente no esta definido');
        }
        if ($bill->typetaxpayer_cod == '0'){
            return Reply::error('El tipo de regimen del cliente no esta definido');
        }
        if($bill->typedoc_cod == '31')
        {
            if (!(count(explode("-",$bill->dni))>1)){
                return Reply::error('Error Falta el dígito de verificación del NIT del cliente');
            }
        }
        else
        {
            if ((count(explode("-",$bill->dni))>1)){
                return Reply::error('La identificación del cliente es incorrecta');
            }
        }

        $id_factura=$this->xmlcolombia($id,$bill);
        $respuestadian=$id_factura[1];
        $id_factura=$id_factura[0];

        if ($respuestadian != 'Aceptada')
            return Reply::error('La DIAN no acepto la factura: '.$respuestadian);

        $emisor = Factel::all();
        $empresa = $emisor->first();
        $server = $_SERVER['SERVER_NAME'];
        if (!empty($_SERVER['SERVER_PORT'])) {
            $server .= ':' . $_SERVER['SERVER_PORT'];
        }

        $ruta_respuesta = '/js/lib_firma_sri/example.php';
        $host_email = $global->server;
        $email = $global->email;
        $pass = $global->password;
        $port = $global->port;

        $host_bd = Config::get('database.connections.mysql.host');
        $pass_bd = Config::get('database.connections.mysql.password');
        $user_bd = Config::get('database.connections.mysql.username');
        $database = Config::get('database.connections.mysql.database');
        $port_bd = Config::get('database.connections.mysql.port');
        //Consulta los datos de la configuraciòn de la factura colombia
        $dian_settings = DB::table('dian_settings')
            ->join('typedoc', 'typedoc.cod', '=', 'dian_settings.typedoc_cod')
            ->join('typetaxpayer', 'typetaxpayer.cod', '=', 'dian_settings.typetaxpayer_cod')
            ->join('municipio', 'municipio.cod', '=', 'dian_settings.municipio_cod')
            ->join('departamento', 'departamento.cod', '=', 'municipio.departamento_cod')
            ->select('departamento.Description AS Departamento','municipio.Description AS Municipio','typedoc.Description AS typedocEmisor','dian_settings.identificacion AS identificationEmisor','dian_settings.businessname AS nameEmisor','dian_settings.tradename','typetaxpayer.Description AS typetaxpayerEmisor','dian_settings.direction AS directionEmisor','dian_settings.resolutiondatestar','dian_settings.numberstart','dian_settings.numberend','dian_settings.resolutionnumber','dian_settings.email','dian_settings.phone')->get()->first();
        //Consulta los datos de la factura
        $fatura = DB::table('invoices_dian AS fv')
            ->join('clients AS c', 'c.id', '=', 'fv.client_id')
            ->join('typedoc', 'typedoc.cod', '=', 'c.typedoc_cod')
            ->join('typetaxpayer', 'typetaxpayer.cod', '=', 'c.typetaxpayer_cod')
            ->join('municipio', 'municipio.cod', '=', 'fv.municipio_cod')
            ->join('departamento', 'departamento.cod', '=', 'municipio.departamento_cod')
            ->where('fv.id', $id_factura)
            ->select('departamento.Description AS Departamento','municipio.Description AS Municipio','fv.cufe','fv.qr','fv.typeoperation_cod','fv.prefix','fv.number','fv.date', 'typedoc.Description AS typedocAdquiriente','c.dni AS identificationAdquiriente','c.name AS nameAdquiriente','typetaxpayer.Description AS typetaxpayerAdquiriente','c.address AS directionAdquiriente','c.email AS emailAdquiriente','c.phone AS phoneAdquiriente','fv.nmoney AS money','fv.subtotal','fv.totaltax AS iva','fv.total','fv.filename')->get()->first();
        //Consulta el detalle de la factura
        $detalle = DB::table('bill_customer_item AS b')
            ->where('b.id', $id)
            ->select('b.plan_id AS cod','b.description','b.quantity','b.price','b.iva','b.total')->get();

        $data = ['message' => 'Venta generada correctamente',
            'cufe'=>$fatura->cufe,
            'qr'=>$fatura->qr,
            'typeoperation_cod'=>$fatura->typeoperation_cod,
            'prefix'=>$fatura->prefix,
            'number'=>$fatura->number,
            'date'=>$fatura->date,
            'typedocEmisor'=>$dian_settings->typedocEmisor,
            'identificationEmisor'=>$dian_settings->identificationEmisor,
            'nameEmisor'=>$dian_settings->nameEmisor,
            'tradename'=>$dian_settings->tradename,
            'typetaxpayerEmisor'=>$dian_settings->typetaxpayerEmisor,
            'directionEmisor'=>$dian_settings->Departamento.'/'.$dian_settings->Municipio.'/'.$dian_settings->directionEmisor,
            'emailEmisor'=>$dian_settings->email,
            'phoneEmisor'=>$dian_settings->phone,
            'typedocAdquiriente'=>$fatura->typedocAdquiriente,
            'identificationAdquiriente'=>$fatura->identificationAdquiriente,
            'nameAdquiriente'=>$fatura->nameAdquiriente,
            'taxnameAdquiriente'=>$fatura->nameAdquiriente,
            'typetaxpayerAdquiriente'=>$fatura->typetaxpayerAdquiriente,
            'directionAdquiriente'=>$fatura->Departamento.'/'.$fatura->Municipio.'/'.$fatura->directionAdquiriente,
            'emailAdquiriente'=>$fatura->emailAdquiriente,
            'phoneAdquiriente'=>$fatura->phoneAdquiriente,
            'detalle'=>$detalle,
            'money'=>$fatura->money,
            'subtotal'=>$fatura->subtotal,
            'iva'=>$fatura->iva,
            'total'=>$fatura->total,
            'resolution_number'=>$dian_settings->resolutionnumber,
            'resolution_desde'=>$dian_settings->numberstart,
            'resolution_hasta'=>$dian_settings->numberend,
            'resolution_date'=>$dian_settings->resolutiondatestar,
            'filename'=>$fatura->filename,
            'correo'=>'',
            'host_email'=>$global->server,
            'email_origen'=>$global->email,
            'passEmail'=>$global->password,
            'port'=>$global->port
        ];
        $data=[];
        return Reply::success('Sending to DIAN', $data);
    }

    public function validar_clave($clave) {

        if ($clave == "") {
            $verificado = false;
            return $verificado;
        }

        $x = 2;
        $sumatoria = 0;
        for ($i = strlen($clave) - 1; $i >= 0; $i--) {
            if ($x > 7) {
                $x = 2;
            }
            $sumatoria = $sumatoria + ($clave[$i] * $x);
            $x++;
        }
        $digito = $sumatoria % 11;
        $digito = 11 - $digito;

        switch ($digito) {
            case 10:
                $digito = "1";
                break;
            case 11:
                $digito = "0";
                break;
        }

        /*
          if (strtolower($digito_v)==$digito){
          $verificado=true;
          } else {
          $verificado=false;
          }

         */

        return $digito;
    }

    public function xml($id) {
        $id_factura = $id;
        $emisor = Emisor::all();
        $empresa = $emisor->first();

        //VARIABLES DE LA FACTURA E INFORMACION TRIBUTARIA DEL EMISOR
        $codigoiva = '2';
        $codigoporcentajeiva = '2';
        $tarifaiva = '12.00';
        $tipoambiente = '2';
        $tipoemisiontributaria = '1';
        $razonsocialtributaria='';
        $nombrecomercialtributaria ='';
        $ructributaria ='';
        if(!isset($empresa->direccion)){
            $empresa->direccion='';
        }
        if(isset($empresa->razonSocial)){
            $razonsocialtributaria = $empresa->razonSocial;
        }
        if(isset($empresa->nombreComercial)){
            $nombrecomercialtributaria = $empresa->nombreComercial;
        }
        if(isset($empresa->ruc)){
            $ructributaria = $empresa->ruc;
        }
        $codigodocumento = '01'; //01 - factura


        $obligadoContabilidad_s = 'NO';
        if (!empty($empresa->status_cont)) {
            $obligadoContabilidad_s = $empresa->status_cont;
        }

        //EMPIEZA DOCUMENTO SE CREA LA RAIZ FACTURA

        $xml = new \DomDocument('1.0', 'UTF-8'); //Se crea el docuemnto
        $factura = $xml->createElement('factura');
        $id = $xml->createAttribute('id');
        $id->value = 'comprobante';
        $factura->appendChild($id);
        $version = $xml->createAttribute('version');
        $version->value = '1.0.0';
        $factura->appendChild($version);
        $factura = $xml->appendChild($factura); // Raiz Factura
        //EMPIEZA INFORMACION TRIBUTARIA

        $infoTributaria = $xml->createElement('infoTributaria'); //primer hijo Informacion tributaria
        $infoTributaria = $factura->appendChild($infoTributaria);
        //HIJOS DE INFORMACION TRIBUTARIA
        $ambiente = $xml->createElement('ambiente', $tipoambiente);  //Info tributaria-ambiente IMPORTANTE1!!! se trae la data del request id en pantalla
        $ambiente = $infoTributaria->appendChild($ambiente);
        $tipoEmision = $xml->createElement('tipoEmision', $tipoemisiontributaria);  //Info tributaria-tipoEmision
        $tipoEmision = $infoTributaria->appendChild($tipoEmision);
        $razonSocial = $xml->createElement('razonSocial', $razonsocialtributaria);  //Info tributaria-razonSocial
        $razonSocial = $infoTributaria->appendChild($razonSocial);
        $nombreComercial = $xml->createElement('nombreComercial', $nombrecomercialtributaria);  //Info tributaria-nombreComercial
        $nombreComercial = $infoTributaria->appendChild($nombreComercial);
        $ruc = $xml->createElement('ruc', $ructributaria);  //Info tributaria-ruc
        $ruc = $infoTributaria->appendChild($ruc);





        $venta = BillCustomer::find($id_factura);



        $cliente = Client::find($venta->client_id);
        $fecha = date("d/m/Y", strtotime($venta->release_date));

        $sequence = Secuenciales::find(1);
        $secuencial = str_pad($sequence->valor + 1, '9', '0', STR_PAD_LEFT);

        $newSequence = $secuencial;
        $updateSequence = Secuenciales::where('id', 1)
            ->update(['valor' => $newSequence]); // this will also update the record

        $tipocompro = "01";

        $random = mt_rand(10000001, 99999999);



        // Agregando puntos de emision y establecimientos de los modulos
        if($cliente->id_punto_emision == null || $cliente->id_punto_emision == ''){
            $numestablecimiento = '001'; //numero establecimiento
            $ptoemi = '001';
        }
        else{

            $puntoEmision = PuntoEmision::select('punto_emision.id', 'punto_emision.id_establecimiento', 'punto_emision.nombre', 'punto_emision.codigo')
                ->where('punto_emision.id', $cliente->id_punto_emision)
                ->first();
            $establecimiento = Establecimientos::select('establecimientos.id', 'establecimientos.nombreComercial', 'establecimientos.codigo')
                ->where('establecimientos.id', $puntoEmision->id_establecimiento)
                ->first();


            $numestablecimiento = $establecimiento->codigo; //numero establecimiento
            $ptoemi = $puntoEmision->codigo;
        }

        $serie_factura = $numestablecimiento.$ptoemi;

        $tipo_emision = '1';

        $clave = date('dmY', strtotime($venta->release_date)) . $codigodocumento . $ructributaria . $tipoambiente . $serie_factura . $secuencial . $random . $tipo_emision;

        $digito_verificador_clave = self::validar_clave($clave);

        $clave_acceso = date('dmY', strtotime($venta->release_date)) . $codigodocumento . $ructributaria . $tipoambiente . $serie_factura . $secuencial . $random . $tipo_emision . $digito_verificador_clave;

        $claveAcceso = $xml->createElement('claveAcceso', $clave_acceso);  //Info tributaria-claveAcceso
        $claveAcceso = $infoTributaria->appendChild($claveAcceso);
        $codDoc = $xml->createElement('codDoc', $codigodocumento);  //Info tributaria-codDoc 01=Factura
        $codDoc = $infoTributaria->appendChild($codDoc);


        $estab = $xml->createElement('estab', $numestablecimiento);  //Info tributaria-estab
        $estab = $infoTributaria->appendChild($estab);
        $ptoEmi = $xml->createElement('ptoEmi', $ptoemi);  //Info tributaria-ptoEmi
        $ptoEmi = $infoTributaria->appendChild($ptoEmi);
        $secuencial = $xml->createElement('secuencial', $secuencial);  //Info tributaria-secuencial
        $secuencial = $infoTributaria->appendChild($secuencial);
        $dirMatriz = $xml->createElement('dirMatriz', $empresa->direccion);  //Info tributaria-dirMatriz
        $dirMatriz = $infoTributaria->appendChild($dirMatriz);

        if($empresa->regimenMicroempresas == 1){
            $regimenMicroempresas = $xml->createElement('contribuyenteRimpe', 'CONTRIBUYENTE RÉGIMEN RIMPE');
            $regimenMicroempresas = $infoTributaria->appendChild($regimenMicroempresas);
        }

        if($empresa->agenteRetencion != null || $empresa->agenteRetencion != ''){
            $agenteRetencion = $xml->createElement('agenteRetencion', $empresa->agenteRetencion);
            $agenteRetencion = $infoTributaria->appendChild($agenteRetencion);
        }

        // TERMINA INFORMACION TRIBUTARIA
        //EMPIEZA INFORMACION FACTURA


        $longitud_dni = strlen($cliente->dni);
        $tipoIdentificacionComprador_value = '';
        if ($longitud_dni == 13) {
            $tipoIdentificacionComprador_value = '04';
        } else {
            $tipoIdentificacionComprador_value = '05';
        }

        $infoFactura = $xml->createElement('infoFactura'); //segundo hijo Info Factura
        $infoFactura = $factura->appendChild($infoFactura);

        //HIJOS DE INFORMACION FACTURA


        $fechaEmision = $xml->createElement('fechaEmision', $fecha);  //Info facura--fechaEmision
        $fechaEmision = $infoFactura->appendChild($fechaEmision);


        $obligadoContabilidad = $xml->createElement('obligadoContabilidad', $obligadoContabilidad_s);  //Info facura--fechaEmision
        $obligadoContabilidad = $infoFactura->appendChild($obligadoContabilidad);
        $tipoIdentificacionComprador = $xml->createElement('tipoIdentificacionComprador', $tipoIdentificacionComprador_value);  //Info facura--obligadoContabilidad
        $tipoIdentificacionComprador = $infoFactura->appendChild($tipoIdentificacionComprador);

        $razonSocialComprador = $xml->createElement('razonSocialComprador', $cliente->name);  //Info facura--razonSocialComprador
        $razonSocialComprador = $infoFactura->appendChild($razonSocialComprador);
        $identificacionComprador = $xml->createElement('identificacionComprador', $cliente->dni);  //Info facura--identificacionComprador
        $identificacionComprador = $infoFactura->appendChild($identificacionComprador);

        $dircomprador = $xml->createElement('direccionComprador', $cliente->address);  //Info facura--fechaEmision
        $dircomprador = $infoFactura->appendChild($dircomprador);

        $totalsiva = $venta->total_pay / 1.12;
        $totalsimpuesto = $totalsiva * 1.12;
        $totalsiva = number_format($totalsimpuesto, 2, '.', ' ');

        $servicio = \App\models\ClientService::find($venta->service_id);

        if($servicio != null){
            if($servicio->plan_id){
                $plan = \App\models\Plan::find($servicio->plan_id);
            }
        }



        $detalles = DB::table('bill_customer_item AS b')
            ->where('b.bill_customer_id', $id_factura)->get();
        $impuestoiva0 = false;
        $impuestoiva12 = false;

        if (empty($detalles[0])) {
            $t_sin_impu = $venta->total_pay / 1.12;
            $totalSinImpuestos = $xml->createElement('totalSinImpuestos', number_format($t_sin_impu, 2, '.', ' '));  //Info facura--totalSinImpuestos
            $totalSinImpuestos = $infoFactura->appendChild($totalSinImpuestos);
        } else {
            $t_sin_impu = $venta->total_pay / 1.12;
            $totalSinImpuestos = $xml->createElement('totalSinImpuestos', number_format($t_sin_impu, 2, '.', ' '));  //Info facura--totalSinImpuestos
            $totalSinImpuestos = $infoFactura->appendChild($totalSinImpuestos);
        }
        $totalDescuento = $xml->createElement('totalDescuento', 0);  //Info facura--totalDescuento
        $totalDescuento = $infoFactura->appendChild($totalDescuento);
        //HIJO DE INFORMACION FACTURA PADRE DE TOTAL IMPUESTOS

        $totalConImpuestos = $xml->createElement('totalConImpuestos');  //Info facura--totalConImpuestos
        $totalConImpuestos = $infoFactura->appendChild($totalConImpuestos);

        //HIJO DE TOTAL CON IMPUESTOS PADRE DE codigo-codigoporcentaje-desceunto adicional-base imponible-valor-valor devolucion iva
        //HIJOS DE TOTAL IMPUESTO
        if ($venta->iva == '0.00') {
            $codigo_porc_iva = 0;
        } else {
            $codigo_porc_iva = 2;
        }

        //$valorDevolucionIva = $xml->createElement('valorDevolucionIva','05');  //Info facura--valorDevolucionIva
        //$valorDevolucionIva = $totalImpuesto->appendChild($valorDevolucionIva);
        //PROPINA E IMPORTE TOTAL
        $propina = $xml->createElement('propina', '0.00');  //Info facura--propina
        $propina = $infoFactura->appendChild($propina);
        if (empty($detalles[0])) {
            $total_iva = 0;
            $total_iva += ($totalsiva * 12 / 100);
            $imTotal = $total_iva + $totalsiva;
            $importeTotal = $xml->createElement('importeTotal', number_format($venta->total_pay, 2, '.', ' '));  //Info facura--importeTotal
            $importeTotal = $infoFactura->appendChild($importeTotal);
        } else {
            $importeTotal = $xml->createElement('importeTotal', number_format($venta->total_pay, 2, '.', ' '));  //Info facura--importeTotal
            $importeTotal = $infoFactura->appendChild($importeTotal);
        }

        //HIJOS INFORMACION DE FACTURA  PADRE DE : forma de pago-total-plazo-unidadtiempo
        $pagos = $xml->createElement('pagos');  //Info facura--pagos
        $pagos = $infoFactura->appendChild($pagos);
        //HIJO DE PAGOS
        $pago = $xml->createElement('pago');  //Info facura--pagos
        $pago = $pagos->appendChild($pago);
        //HIJOS DE PAGO TABLA 24
        $formaPago = $xml->createElement('formaPago', '01');  //Info facura--formaPago tabla 24
        $formaPago = $pago->appendChild($formaPago);
        if (empty($detalles[0])) {
            $total_iva = 0;
            $total_iva += ($venta->total_pay * 12 / 100);
            $total_d = $total_iva + $venta->total_pay;
            $total = $xml->createElement('total', number_format($venta->total_pay, 2, '.', ' '));  //Info facura--total
            $total = $pago->appendChild($total);
        } else {
            $total = $xml->createElement('total', number_format($venta->total_pay, 2, '.', ' '));  //Info facura--total
            $total = $pago->appendChild($total);
        }
        $plazo = $xml->createElement('plazo', '01');  //Info facura--plazo DIAS DEL PAGO
        $plazo = $pago->appendChild($plazo);
        $unidadTiempo = $xml->createElement('unidadTiempo', 'dias');  //Info facura--unidadTiempo
        $unidadTiempo = $pago->appendChild($unidadTiempo);
        //FIN DE INFO FACTURA
        //INICIO DE DETALLES


        if (empty($detalles[0])) {
            //DETALLES PADRE

            $First_Node = $xml->createElement('detalles');  //Info facura--detalles
            $First_Node = $factura->appendChild($First_Node);
            //HIJOS DETALLES PADRE DE: Codigo principal-cod auxil-descripcion-unidadmedida-cantidad-precio-preciosinsub etcetc
            $Second_Node = $xml->createElement('detalle');  //Info facura--detalle
            $Second_Node = $First_Node->appendChild($Second_Node);
            //HIJOS DETALLE
            if($servicio->plan_id){
                $codigoPrincipal = $xml->createElement('codigoPrincipal', $plan->id);  //Info facura--codigoPrincipal
                $codigoPrincipal = $Second_Node->appendChild($codigoPrincipal);
                $descripcion = $xml->createElement('descripcion', $plan->name);  //Info facura--descripcion
                $descripcion = $Second_Node->appendChild($descripcion);
            }
            $cantidad = $xml->createElement('cantidad', 1);  //Info facura--cantidad
            $cantidad = $Second_Node->appendChild($cantidad);
            $precioUnitario = $xml->createElement('precioUnitario', $venta->cost);  //Info facura--precioUnitario
            $precioUnitario = $Second_Node->appendChild($precioUnitario);
            $descuento = $xml->createElement('descuento', 0);  //Info facura--descuento
            $descuento = $Second_Node->appendChild($descuento);
            $precioTotalSinImpuesto = $xml->createElement('precioTotalSinImpuesto', $venta->total_pay);  //Info facura--precioTotalSinImpuesto
            $precioTotalSinImpuesto = $Second_Node->appendChild($precioTotalSinImpuesto);
            //HIJOS DE DETALLE PADRE DE: codgio-codigo porcentaje -tarifa-base etc
            $impuestos = $xml->createElement('impuestos');  //Info facura--impuestos
            $impuestos = $Second_Node->appendChild($impuestos);
            //HIJOS DE IMPUESTOS
            //FOR EACH EN CASO DE APLICAR
            $impuesto = $xml->createElement('impuesto');  //Info facura--impuesto
            $impuesto = $impuestos->appendChild($impuesto);

            $valoriva10 = 0;
            $valoriva12 = 0;

            $codigoporcentajeiva = 2;
            $impuestoiva12 = true;
            $precioIva = 0;
            $precioIva = $venta->total_pay - $venta->cost;

            $precioTotal = $venta->total_pay;
            $codigo = $xml->createElement('codigo', 2);  //Info facura--codigos
            $codigo = $impuesto->appendChild($codigo);
            $codigoPorcentaje = $xml->createElement('codigoPorcentaje', $codigoporcentajeiva);  //Info facura--codigoPorcentajes
            $codigoPorcentaje = $impuesto->appendChild($codigoPorcentaje);
            $tarifa = $xml->createElement('tarifa', 12);  //Info facura--tarifas
            $tarifa = $impuesto->appendChild($tarifa);
            $baseImponible = $xml->createElement('baseImponible', $venta->cost);  //Info facura--baseImponibles
            $baseImponible = $impuesto->appendChild($baseImponible);
            $valor = $xml->createElement('valor', $precioIva);  //Info facura--valor
            $valor = $impuesto->appendChild($valor);



            $impuesto2 = $xml->createElement('totalImpuesto');  //Info facura--impuesto
            $impuesto2 = $totalConImpuestos->appendChild($impuesto2);



            $codigoporcentajeiva = 2;
            $impuestoiva12 = true;
            $precioIva = $venta->total_pay - $venta->cost;
            $precioTotal = $venta->total_pay;
            $codigo = $xml->createElement('codigo', 2);  //Info facura--codigos
            $codigo = $impuesto2->appendChild($codigo);
            $codigoPorcentaje = $xml->createElement('codigoPorcentaje', $codigoporcentajeiva);  //Info facura--codigoPorcentajes
            $codigoPorcentaje = $impuesto2->appendChild($codigoPorcentaje);
            $baseImponible = $xml->createElement('baseImponible', $venta->cost);  //Info facura--baseImponibles
            $baseImponible = $impuesto2->appendChild($baseImponible);
            $tarifa = $xml->createElement('tarifa', 12);  //Info facura--tarifas
            $tarifa = $impuesto2->appendChild($tarifa);
            $valor = $xml->createElement('valor', $precioIva);  //Info facura--valor
            $valor = $impuesto2->appendChild($valor);
        } else {
            // Alexander Cortes 24/08/2021
            $First_Node = $xml->createElement('detalles');  //Info facura--detalles
            $valoriva10 = 0;
            $valoriva12 = 0;
            $precioBase12 = 0;
            foreach ($detalles as $det) {

                $ivadetalle = 0.0;
                $ivadetalle = $det->total;

                //DETALLES PADRE

                $First_Node = $factura->appendChild($First_Node);
                //HIJOS DETALLES PADRE DE: Codigo principal-cod auxil-descripcion-unidadmedida-cantidad-precio-preciosinsub etcetc
                $Second_Node = $xml->createElement('detalle');  //Info facura--detalle
                $Second_Node = $First_Node->appendChild($Second_Node);
                //HIJOS DETALLE
                $codigoPrincipal = $xml->createElement('codigoPrincipal', $det->id);  //Info facura--codigoPrincipal
                $codigoPrincipal = $Second_Node->appendChild($codigoPrincipal);
                $descripcion = $xml->createElement('descripcion', $det->description);  //Info facura--descripcion
                $descripcion = $Second_Node->appendChild($descripcion);
                $cantidad = $xml->createElement('cantidad', $det->quantity);  //Info facura--cantidad
                $cantidad = $Second_Node->appendChild($cantidad);
                $precioUnitario = $xml->createElement('precioUnitario', $det->price);  //Info facura--precioUnitario
                $precioUnitario = $Second_Node->appendChild($precioUnitario);
                $descuento = $xml->createElement('descuento', 0);  //Info facura--descuento
                $descuento = $Second_Node->appendChild($descuento);
                $precioTotalSinImpuesto = $xml->createElement('precioTotalSinImpuesto', $det->price);  //Info facura--precioTotalSinImpuesto
                $precioTotalSinImpuesto = $Second_Node->appendChild($precioTotalSinImpuesto);
                //HIJOS DE DETALLE PADRE DE: codgio-codigo porcentaje -tarifa-base etc
                $impuestos = $xml->createElement('impuestos');  //Info facura--impuestos
                $impuestos = $Second_Node->appendChild($impuestos);
                //HIJOS DE IMPUESTOS
                //FOR EACH EN CASO DE APLICAR
                $impuesto = $xml->createElement('impuesto');  //Info facura--impuesto
                $impuesto = $impuestos->appendChild($impuesto);


                if ($det->iva == '0.00') {
                    $codigoporcentajeiva = 0;
                    $impuestoiva0 = true;
                    $codigo = $xml->createElement('codigo', 2);  //Info facura--codigos

                    $codigo = $impuesto->appendChild($codigo);
                    $codigoPorcentaje = $xml->createElement('codigoPorcentaje', $codigoporcentajeiva);  //Info facura--codigoPorcentajes
                    $codigoPorcentaje = $impuesto->appendChild($codigoPorcentaje);
                    $tarifa = $xml->createElement('tarifa', $det->iva);  //Info facura--tarifas
                    $tarifa = $impuesto->appendChild($tarifa);
                    $baseImponible = $xml->createElement('baseImponible', number_format($det->total, 2, '.', ' '));  //Info facura--baseImponibles
                    $baseImponible = $impuesto->appendChild($baseImponible);
                    $valoriva10 += $det->price;
                    $valor = $xml->createElement('valor', $det->price);  //Info facura--valor
                    $valor = $impuesto->appendChild($valor);
                } else {
                    $codigoporcentajeiva = 2;
                    $impuestoiva12 = true;

                    $codigo = $xml->createElement('codigo', 2);  //Info facura--codigos

                    $codigo = $impuesto->appendChild($codigo);
                    $codigoPorcentaje = $xml->createElement('codigoPorcentaje', $codigoporcentajeiva);  //Info facura--codigoPorcentajes
                    $codigoPorcentaje = $impuesto->appendChild($codigoPorcentaje);
                    $tarifa = $xml->createElement('tarifa', $det->iva);  //Info facura--tarifas
                    $tarifa = $impuesto->appendChild($tarifa);
                    $baseImponible = $xml->createElement('baseImponible', number_format($det->price, 2, '.', ' '));  //Info facura--baseImponibles
                    $baseImponible = $impuesto->appendChild($baseImponible);

                    $precioDetIva = $det->total - $det->price;
                    $valoriva12 += $precioDetIva;

                    $precioBase12 += $det->price;
                    //var_dump($precioBase12);
                    $valor = $xml->createElement('valor', $precioDetIva);  //Info facura--valor
                    $valor = $impuesto->appendChild($valor);
                }
            }

            //var_dump($precioBase12);die;

            $totalImpuesto = $xml->createElement('totalImpuesto');  //Info facura--totalImpuesto

            if ($impuestoiva0 == true) {
                $codigo = $xml->createElement('codigo', 2);  //Info facura--codigo tabla 16
                $codigo = $totalImpuesto->appendChild($codigo);
                $codigoPorcentaje = $xml->createElement('codigoPorcentaje', 0);  //Info facura--codigoPorcentaje tabla 17
                $codigoPorcentaje = $totalImpuesto->appendChild($codigoPorcentaje);
                //$descuentoAdicional = $xml->createElement('descuentoAdicional','05');  //Info facura--descuentoAdicional
                //$descuentoAdicional = $totalImpuesto->appendChild($descuentoAdicional);
                $baseImponible = $xml->createElement('baseImponible', $valoriva10);  //Info facura--baseImponible
                $baseImponible = $totalImpuesto->appendChild($baseImponible);
                $valor = $xml->createElement('valor', '0.00');  //Info facura--valor
                $valor = $totalImpuesto->appendChild($valor);
            }

            if ($impuestoiva12 == true) {
                $precioIva = $venta->total_pay - $venta->cost;
                $codigo = $xml->createElement('codigo', 2);  //Info facura--codigo tabla 16
                $codigo = $totalImpuesto->appendChild($codigo);
                $codigoPorcentaje = $xml->createElement('codigoPorcentaje', 2);  //Info facura--codigoPorcentaje tabla 17
                $codigoPorcentaje = $totalImpuesto->appendChild($codigoPorcentaje);
                //$descuentoAdicional = $xml->createElement('descuentoAdicional','05');  //Info facura--descuentoAdicional
                //$descuentoAdicional = $totalImpuesto->appendChild($descuentoAdicional);
                $baseImponible = $xml->createElement('baseImponible', number_format($precioBase12, 2, '.', ' '));  //Info facura--baseImponible
                $baseImponible = $totalImpuesto->appendChild($baseImponible);
                $valor = $xml->createElement('valor', $valoriva12);  //Info facura--valor
                $valor = $totalImpuesto->appendChild($valor);
            }

            $totalImpuesto = $totalConImpuestos->appendChild($totalImpuesto);
        }
        //RETENCIONES SI APLICA EJEMPLO:
        //$retenciones = $xml->createElement('retenciones');  //Info facura--retenciones
        //$retenciones = $factura->appendChild($retenciones);
        //PADRE INFO ADICIONAL
        $infoAdicional = $xml->createElement('infoAdicional');  //Info facura--infoAdicional
        $infoAdicional = $factura->appendChild($infoAdicional);

        //HIJOS INFO ADICIONAL
        $campoAdicional = $xml->createElement('campoAdicional', $cliente->email);  //Info facura--campoAdicional
        $nombre = $xml->createAttribute('nombre');
        $nombre->value = 'Email';
        $campoAdicional->appendChild($nombre);
        $campoAdicional = $infoAdicional->appendChild($campoAdicional);

        $campoAdicional = $xml->createElement('campoAdicional', $cliente->address);  //Info facura--campoAdicional
        $nombre = $xml->createAttribute('nombre');
        $nombre->value = 'Direccion';
        $campoAdicional->appendChild($nombre);
        $campoAdicional = $infoAdicional->appendChild($campoAdicional);



        //Se eliminan espacios en blanco
        $xml->preserveWhiteSpace = false;

        //Se ingresa formato de salida
        $xml->formatOutput = true;

        //Se instancia el objeto
        $xml_string = $xml->saveXML();

        //Y se guarda en el nombre del archivo 'achivo.xml', y el obejto nstanciado
        \Storage::disk('public')->put('factura.xml', $xml_string);


        // Llamar a la funcion Firmar Factura de la custom library
        //$ejecutar = new \App\lib_firma_sri\ejecutar.php;
    }
    function search_number($resolutionnumber,$numberstart){

        $number=DB::select('select MAX(number) AS number from invoices_dian where status_dian="accepted" AND resolutionnumber=?',[$resolutionnumber]);
        if (empty($number)) {
            $number=(floatval($numberstart)+1);
        }elseif($numberstart>$number[0]->number+1){
            $number=(floatval($numberstart)+1);
        }
        else{
            $number=floatval($number[0]->number)+1;
        }
        return $number;
    }
    //Consecutivo de los documentos electronico (fe,nc,nd)
    function updateConsecutive($year,$doc,$col_value,$zips)
    {
        if($year!=intval(date('Y'))){
            Dian_settings::where('id','=', 1)->update(['year' => date('Y'),'fes' => 0,'ncs' => 0,'nds' => 0,'zips' => 0]);
            $col_value=0;
            $zips=0;
        }
        Dian_settings::where('id','=', 1)->update([$doc => $col_value+1,'zips' => $zips+1]);
    }
    //Genera un XML para la facturación electronica de Colombia
    public function xmlcolombia($id_bill,$bill) {
        $respuesta='Rechazada';

        $detalles = BillCustomerItem::where('bill_customer_id', $id_bill)->get();
        $totaltax=0;
        foreach ($detalles as $d) {
            $totaltax=$totaltax+($d->quantity*$d->price);
        }

        $factel = Factel::all();
        $factel = $factel->first();

        $settings_dian = Dian_settings::all();
        $settings_dian = $settings_dian->first();

        $global_settings = GlobalSetting::all();
        $global_settings = $global_settings->first();

        //se ajusta el sistema horario a colombia
        $timezone = new \DateTimeZone('America/Bogota');
        $fechaEmision = new \DateTime(date('Y-m-d'));
        $fechaEmision->setTimezone($timezone);
        $horaEmision = new \DateTime(date('H:i:s'));
        $horaEmision->setTimezone($timezone);
        // se busca el consecutivo y se genera la factura electronica de colombia
        $number=$this->search_number($settings_dian->resolutionnumber,$settings_dian->numberstart);
        $id_factura=DB::table('invoices_dian')->insertGetId([
            'bill_customers_id'=>$bill->id,
            'resolutionnumber'=>$settings_dian->resolutionnumber,
            'client_id'=>$bill->client_id,
            'date'=>$fechaEmision,
            'hour'=>$horaEmision,
            'typeoperation_cod'=>$settings_dian->typeoperation_cod,
            'payment_cod'=>'10',
            'number'=>$number,
            'prefix'=>$settings_dian->prefix,
            'cufe'=>'',
            'qr'=>'',
            'filename'=>'',
            'email'=>$global_settings->email,
            'phone'=>'',
            'municipio_cod'=>$bill->municipio_cod,
            'address'=>$bill->address,
            'bill_number'=>$bill->num_bill,
            'nmoney'=>$global_settings->nmoney,
            'subtotal'=>round($totaltax,2),
            'totaltax'=>round($bill->total_pay-$totaltax,2),
            'total'=>$bill->total_pay,
            'payment_date'=>$horaEmision,
            'use_transactions'=>$bill->use_transactions,
            'status'=>$bill->status
        ]);
        $factura=DB::table('invoices_dian')->where('id',$id_factura)->get();
        $factura = $factura->first();
        //EMPIEZA DOCUMENTO SE CREA LA RAIZ FACTURA
        $this->xml_colombia=new \DOMDocument('1.0','UTF-8');//Se crea el docuemnto
        $this->xml_colombia->xmlStandalone = false;
        $this->xml_colombia->formatOutput=true;
        $Invoice=$this->xml_colombia->createElement("Invoice");
        $Invoice=$this->xml_colombia->appendChild($Invoice);
        $Invoice->setAttribute('xmlns','urn:oasis:names:specification:ubl:schema:xsd:Invoice-2');
        $Invoice->setAttribute('xmlns:cac','urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2');
        $Invoice->setAttribute('xmlns:cbc','urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2');
        $Invoice->setAttribute('xmlns:ds',"http://www.w3.org/2000/09/xmldsig#");
        $Invoice->setAttribute('xmlns:ext',"urn:oasis:names:specification:ubl:schema:xsd:CommonExtensionComponents-2");
        $Invoice->setAttribute('xmlns:sts',"http://www.dian.gov.co/contratos/facturaelectronica/v1/Structures");
        $Invoice->setAttribute('xmlns:xades',"http://uri.etsi.org/01903/v1.3.2#");
        $Invoice->setAttribute('xmlns:xades141',"http://uri.etsi.org/01903/v1.4.1#");
        $Invoice->setAttribute('xmlns:xsi',"http://www.w3.org/2001/XMLSchema-instance");
        $Invoice->setAttribute('xsi:schemaLocation',"urn:oasis:names:specification:ubl:schema:xsd:Invoice-2     http://docs.oasis-open.org/ubl/os-UBL-2.1/xsd/maindoc/UBL-Invoice-2.1.xsd");
        $this->fntCrearUBLExtensions($Invoice,$settings_dian,$factura->number,$factura->date,$factura->total,'',$bill->dni);
        $this->fntCrearElemento($Invoice,'cbc:UBLVersionID','UBL 2.1');
        $this->fntCrearElemento($Invoice,'cbc:CustomizationID','10');
        $this->fntCrearElemento($Invoice,'cbc:ProfileID','DIAN 2.1');
        $this->fntCrearElemento($Invoice,'cbc:ProfileExecutionID',$settings_dian->typeoperation_cod);
        $this->fntCrearElemento($Invoice,'cbc:ID',$settings_dian->prefix.$factura->number);
        $this->fntCrearElemento($Invoice,'cbc:UUID','',[
            ["schemeID","2"],
            ["schemeName","CUFE-SHA384"]
        ]);
        $this->fntCrearElemento($Invoice,'cbc:IssueDate',$factura->date);
        $this->fntCrearElemento($Invoice,'cbc:IssueTime',$factura->hour.'-05:00');
        $this->fntCrearElemento($Invoice,'cbc:InvoiceTypeCode','01');
        $this->fntCrearElemento($Invoice,'cbc:DocumentCurrencyCode',$global_settings->nmoney,[
            ['listAgencyID','6'],
            ['listAgencyName','United Nations Economic Commission for Europe'],
            ['listID','ISO 4217 Alpha']
        ]);
        $this->fntCrearElemento($Invoice,'cbc:LineCountNumeric',count($detalles));
        //EMPIEZA INFORMACION DEL EMISOR
        $AccountingSupplierParty=$this->xml_colombia->createElement("cac:AccountingSupplierParty");
        $AccountingSupplierParty=$Invoice->appendChild($AccountingSupplierParty);
        $this->fntCrearElemento($AccountingSupplierParty,"cbc:AdditionalAccountID",$settings_dian->typetaxpayer_cod);
        $this->fntParty($AccountingSupplierParty,$settings_dian->typedoc_cod,$settings_dian->identificacion,$settings_dian->economicactivity_cod,$settings_dian->businessname,$settings_dian->municipio_cod,$settings_dian->direction,$settings_dian->typeresponsibility_cod,$settings_dian->phone,$settings_dian->email,$settings_dian->prefix);
        //FINALIZA LA INFORMACIÓN DEL EMISOR
        //EMPIEZA LA INFORMACIÓN DEL CLIENTE
        $AccountingCustomerParty=$this->xml_colombia->createElement("cac:AccountingCustomerParty");
        $AccountingCustomerParty=$Invoice->appendChild($AccountingCustomerParty);
        $this->fntCrearElemento($AccountingCustomerParty,"cbc:AdditionalAccountID",$bill->typetaxpayer_cod);
        $this->fntParty($AccountingCustomerParty,$bill->typedoc_cod,$bill->dni,$bill->economicactivity_cod,$bill->name,$bill->municipio_cod,$bill->address,$bill->typeresponsibility_cod,$bill->phone,$bill->email);
        //EMPIEZA LA INFORMACIÓN DE PAGO
        $this->fntCrearPaymentMeans($Invoice,'1',$factura->id);
        //$this->fntCrearPrepaidPayment($Invoice,$factura->id,$factura->total,$factura->date,$factura->date,'Factura pagada',$global_settings->nmoney);
        $this->fntCrearTaxTotal_IVA($Invoice,$global_settings->nmoney,$detalles);
        $this->fntCrearLegalMonetaryTotal($Invoice,$global_settings->nmoney,$detalles,$factura->subtotal);
        $this->fntCrearInvoiceLines($Invoice,$detalles,$global_settings->nmoney);
        //Se instancia el objeto
        $xml_string = $this->xml_colombia->saveXML();
        $pathCertificate = $factel->certificado_digital;
        $password =  $factel->pass_certificado;
        $server = $_SERVER['SERVER_NAME'];

        $settings_dian = Dian_settings::all();
        $settings_dian = $settings_dian->first();
        $filename=$this->firmar_xml_colombia('fv',$pathCertificate,$password,$xml_string,$settings_dian->identificacion,$factura->date,$settings_dian->tecnicalkey,($settings_dian->fes+1));
        $nombrezip=$this->fntnombrearchivo('z',$settings_dian->identificacion,"000",$factura->date,($settings_dian->zips+1));
        //Crear zip
        $uuid = $this->xml_colombia->getElementsByTagName('cbc:UUID');

        $zip = new ZipArchive();
        $zip->open('js/lib_dian/comprobantes_colombia/'.$nombrezip.'.zip',ZipArchive::CREATE);
        // Añadimos un archivo en la raiz del zip.
        $zip->addFile('js/lib_dian/comprobantes_colombia/'.$filename.'.xml',$filename.'.xml');
        $zip->close();
        //Actualizamos el estado de la factura
        DB::table('invoices_dian')
            ->where('id',$id_factura)
            ->update([
                'cufe' => $this->cufe ,
                'qr' => $this->qr ,
                'filename' => $filename ,
                'status_dian' => 'rejected' ,
            ]);
        //Se envia la nota a la DIAN
        $respuesta="";
        $estado="NO AUTORIZADO";
        if($settings_dian->typeoperation_cod=='2'){
            $resp=$this->SendTestSetAsync($pathCertificate, $password,$settings_dian->testsetid,$nombrezip.'.zip',$nombrezip);
            if ($resp!='') {
                if ($resp->SendTestSetAsyncResponse->SendTestSetAsyncResult->ZipKey!='') {
                    $intentos=0;
                    $respuesta.=$resp->SendTestSetAsyncResponse->SendTestSetAsyncResult->ZipKey;
                    //esperamos 2 segundos para consultar el estado de la factura
                    sleep(2);
                    while ($intentos <= 5){
                        //consultamos si fue aceptado zip
                        $getStatusZip = new GetStatusZip($pathCertificate, $password);
                        $getStatusZip->trackId = $resp->SendTestSetAsyncResponse->SendTestSetAsyncResult->ZipKey;
                        $resp=$getStatusZip->signToSend()->getResponseToObject()->Envelope->Body->GetStatusZipResponse->GetStatusZipResult->DianResponse;
                        if (!empty($resp->StatusCode)){
                            if ($resp->IsValid=='true'){
                                $respuesta='Aceptada';
                                $this->updateConsecutive($settings_dian->year,'fes',$settings_dian->fes,$settings_dian->zips);
                                //print_r($resp);
                            }else{
                                unlink('js/lib_dian/comprobantes_colombia/'.$nombrezip.'.zip');//Eliminar el zip
                                $respuesta=" ErrorMessage: ";
                                foreach ($resp->ErrorMessage as $key => $value) {
                                    $respuesta.=implode('\n',$value);
                                }
                            }
                            $intentos=10;
                        }else{
                            sleep(2);
                        }
                        $intentos++;
                    }
                }else{
                    $respuesta='Error en el ZipKey';
                    if($resp!=''){
                        if(isset($resp->SendTestSetAsyncResponse->SendTestSetAsyncResult->ErrorMessage)){
                            $respuesta.=' , ErrorMessage: ';
                            foreach ($resp->SendTestSetAsyncResponse->SendTestSetAsyncResult->ErrorMessage as $key => $string) {
                                $respuesta.=$string[$key];
                            }
                        }
                    }else{

                    }
                    unlink('js/lib_dian/comprobantes_colombia/'.$nombrezip.'.zip');//Eliminar el zip
                }

            }else{
                $respuesta.=' Error en el envio del SendTestSetAsync';
            }
        }else{
            $resp=$this->SendBillSync($pathCertificate,$password,$filename,$nombrezip.'.zip');
            if ($resp!='') {
                if ($resp->SendBillSyncResponse->SendBillSyncResult->IsValid!='false') {
                    $intentos=0;
                    $respuesta.=$resp->SendBillSyncResponse->SendBillSyncResult->XmlDocumentKey;
                    $respuesta.='SendBillSync</br>';
                    $respuesta.="Creado: ".$resp->Header->Security->Timestamp->Created.'<br/>';
                    $respuesta.="Expira: ".$resp->Header->Security->Timestamp->Expires.'<br/>';
                    $resp=$resp->Body->SendBillSyncResponse->SendBillSyncResult;
                    if ($resp->IsValid=='false'&&isset($resp->ErrorMessage->string))
                    {
                        $conta=0;
                        $respuesta.='Mensaje de errores: <br/>';
                        if (count($resp->ErrorMessage->string)>1)
                        {
                            foreach ($resp->ErrorMessage->string as $error)
                            {
                                $conta++;
                                $respuesta.=$conta.'. '.$error.'<br/>';
                            }
                        }
                        else
                        {
                            $respuesta.=$resp->ErrorMessage->string.'<br/>';
                        }
                    }
                    if (is_string($resp->XmlDocumentKey)) {
                        $respuesta.='XmlDocumentKey: '.$resp->XmlDocumentKey.'<br/>';
                    }else{
                        $respuesta.='XmlDocumentKey: '.var_dump($resp->XmlDocumentKey).'<br/>';
                    }
                    if (is_string($resp->IsValid)) {
                        $respuesta.='IsValid: '.$resp->IsValid.'<br/>';
                    }else{
                        $respuesta.='IsValid: '.var_dump($resp->IsValid).'<br/>';
                    }
                    if (is_string($resp->StatusCode)) {
                        $respuesta.='StatusCode: '.$resp->StatusCode.'<br/>';
                    }else{
                        $respuesta.='StatusCode: '.var_dump($resp->StatusCode).'<br/>';
                    }
                    if (is_string($resp->StatusDescription)) {
                        $respuesta.='StatusDescription: '.$resp->StatusDescription.'<br/>';
                    }else{
                        $respuesta.='StatusDescription: '.var_dump($resp->StatusDescription).'<br/>';
                    }
                    if (is_string($resp->StatusMessage)) {
                        $respuesta.='StatusMessage: '.$resp->StatusMessage.'<br/>';
                    }else{
                        $respuesta.='StatusMessage: '.var_dump($resp->StatusMessage).'<br/>';
                    }
                    if ($resp->IsValid=='true'){
                        $respuesta='Aceptada';
                        $this->updateConsecutive($settings_dian->year,'fes',$settings_dian->fes,$settings_dian->zips);
                    }else{
                        unlink('js/lib_dian/comprobantes_colombia/'.$nombrezip.'.zip');//Eliminar el zip
                        $respuesta=" ErrorMessage: ";
                        foreach ($resp->ErrorMessage as $key => $string) {
                            $respuesta.=var_export($string,true);
                        }
                        if($resp->StatusCode=='89'){
                            $respuesta.=$resp->StatusDescription;
                        }
                    }
                }else{
                    $respuesta='Documento xml con errores en campos mandatorios';
                    if($resp!=''){
                        if(isset($resp->SendBillSyncResponse->SendBillSyncResult->ErrorMessage)){
                            $respuesta.=' , ErrorMessage: ';
                            foreach ($resp->SendBillSyncResponse->SendBillSyncResult->ErrorMessage as $key => $string) {
                                $respuesta.=var_export($string,true);
                            }
                        }
                    }else{
                        $respuesta.=' Error en el envio del fntSendBillSync '.$nombrezip.'.zip';
                    }
                    //unlink('js/lib_dian/comprobantes_colombia/'.$filename.'.xml');//Eliminar el xml
                    unlink('js/lib_dian/comprobantes_colombia/'.$nombrezip.'.zip');//Eliminar el zip
                }

            }else{
                $respuesta.=' Error en el envio del fntSendBillSync '.$nombrezip.'.zip';
            }
        }
        // Tipo 10 son las facturas, tipo 11 las notas crédito y tipo 12 las notas debitos
        //Se registra el estado del envio de la nota
        DB::table('sri')->insertGetId([
            'id_factura' => $id_bill,
            'id_error' => 0,
            'mensaje' => $respuesta,
            'informacionAdicional' => '',
            'tipo' => '10',
            'claveAcceso' => '',
            'estado' => $estado,
        ]);
        if($respuesta!='Aceptada'){
            return [$id_factura,$respuesta];
        }
        //Actualizamos el estado de la factura
        DB::table('invoices_dian')
            ->where('id',$id_factura)
            ->update([
                'status_dian' => "accepted",
            ]);
        return [$id_factura,$respuesta];
    }
    function SendBillSync($pathCertificate, $password,$filename,$nombrezip){
        $resp='';
        if(is_readable('js/lib_dian/comprobantes_colombia/'.$nombrezip)) {
            $fileContent = file_get_contents('js/lib_dian/comprobantes_colombia/'.$nombrezip);
            $zip = base64_encode($fileContent);
            $SendBillSync = new SendBillAsync($pathCertificate, $password);
            $SendBillSync->To = 'https://vpfe.dian.gov.co/WcfDianCustomerServices.svc?wsdl';
            $SendBillSync->fileName = $filename;
            $SendBillSync->contentFile = $zip;
            $resp=$SendBillSync->signToSend()->getResponseToObject()->Envelope->Body;
            throw new Exception('Class '.get_class($this). ' resp ' . json_encode($resp)); 
        }
        return $resp;
    }
    function SendTestSetAsync($pathCertificate, $password,$testSetId,$filename,$nombrezip){
        $resp='';
        if(is_readable('js/lib_dian/comprobantes_colombia/'.$filename)) {
            $fileContent = file_get_contents('js/lib_dian/comprobantes_colombia/'.$filename);
            $zip = base64_encode($fileContent);
            $sendTestSetAsync = new SendTestSetAsync($pathCertificate, $password);
            $sendTestSetAsync->fileName = $nombrezip;
            $sendTestSetAsync->contentFile = $zip;
            $sendTestSetAsync->testSetId = $testSetId;
            $resp=$sendTestSetAsync->signToSend()->getResponseToObject()->Envelope->Body;
            throw new Exception('Class '.get_class($this). ' resp ' . json_encode($resp)); 
        }
        return $resp;
    }

    function fntCrearUBLExtensions($padre,$settings_dian,$numerofv,$fechaemision,$totalapagar,$CUFE,$identificacion_client)
    {
        //Crear UBLExtensions
        $UBLExtensions=$this->xml_colombia->createElement("ext:UBLExtensions");
        $UBLExtensions=$padre->appendChild($UBLExtensions);
        $this->fntCrearUBLExtension1($UBLExtensions,$settings_dian,$numerofv,$fechaemision,$totalapagar,$CUFE,$identificacion_client);

        $UBLExtension=$this->xml_colombia->createElement("ext:UBLExtension");
        $UBLExtensions->appendChild($UBLExtension);
        $ExtensionContent=$this->fntCrearElemento($UBLExtension,"ext:ExtensionContent");
        $UBLExtension->appendChild($ExtensionContent);
    }
    function fntCrearUBLExtension1($padre,$settings_dian,$numerofv,$fechaemision,$totalapagar,$CUFE,$identificacion_client){
        //Crear UBLExtension
        $UBLExtension=$this->xml_colombia->createElement("ext:UBLExtension");
        $UBLExtension=$padre->appendChild($UBLExtension);
        $ExtensionContent=$this->fntCrearElemento($UBLExtension,"ext:ExtensionContent");
        $DianExtensions=$this->fntCrearElemento($ExtensionContent,"sts:DianExtensions");
        $InvoiceControl=$this->fntCrearElemento($DianExtensions,"sts:InvoiceControl");
        $this->fntCrearElemento($InvoiceControl,"sts:InvoiceAuthorization",$settings_dian->resolutionnumber);
        $AuthorizationPeriod=$this->fntCrearElemento($InvoiceControl,"sts:AuthorizationPeriod");
        $this->fntCrearElemento($AuthorizationPeriod,"cbc:StartDate",$settings_dian->resolutiondatestar);
        $this->fntCrearElemento($AuthorizationPeriod,"cbc:EndDate",$settings_dian->resolutiondateend);
        $AuthorizedInvoices=$this->fntCrearElemento($InvoiceControl,"sts:AuthorizedInvoices");
        $this->fntCrearElemento($AuthorizedInvoices,"sts:Prefix",$settings_dian->prefix);
        $this->fntCrearElemento($AuthorizedInvoices,"sts:From",$settings_dian->numberstart);
        $this->fntCrearElemento($AuthorizedInvoices,"sts:To",$settings_dian->numberend);

        $InvoiceSource=$this->fntCrearElemento($DianExtensions,"sts:InvoiceSource");
        $this->fntCrearElemento($InvoiceSource,"cbc:IdentificationCode","CO",[
            ["listAgencyID","6"],
            ["listAgencyName","United Nations Economic Commission for Europe"],
            ["listSchemeURI","urn:oasis:names:specification:ubl:codelist:gc:CountryIdentificationCode-2.1"]
        ]);

        $SoftwareProvider=$this->fntCrearElemento($DianExtensions,"sts:SoftwareProvider");
        $this->fntCrearElemento($SoftwareProvider,"sts:ProviderID","800197268",[
            ["schemeAgencyID","195"],
            ["schemeAgencyName","CO, DIAN (Dirección de Impuestos y Aduanas Nacionales)"],
            ["schemeID","4"],
            ["schemeName","31"]
        ]);
        $this->fntCrearElemento($SoftwareProvider,"sts:ProviderID",$settings_dian->softwareid,[
            ["schemeAgencyID","195"],
            ["schemeAgencyName","CO, DIAN (Dirección de Impuestos y Aduanas Nacionales)"]
        ]);
        $this->fntCrearElemento($SoftwareProvider,"sts:SoftwareID",$settings_dian->softwareid,[
            ["schemeAgencyID","195"],
            ["schemeAgencyName","CO, DIAN (Dirección de Impuestos y Aduanas Nacionales)"]
        ]);

        $this->fntCrearElemento($DianExtensions,"sts:SoftwareSecurityCode",$this->fntCalcularSoftwareSecurityCode($settings_dian,$numerofv),[
            ["schemeAgencyID","195"],
            ["schemeAgencyName","CO, DIAN (Dirección de Impuestos y Aduanas Nacionales)"]
        ]);

        $AuthorizationProvider=$this->fntCrearElemento($DianExtensions,"sts:AuthorizationProvider");
        $this->fntCrearElemento($AuthorizationProvider,"sts:AuthorizationProviderID","800197268",[
            ["schemeAgencyID","195"],
            ["schemeAgencyName","CO, DIAN (Dirección de Impuestos y Aduanas Nacionales)"],
            ["schemeID","4"],
            ["schemeName","31"]
        ]);

        $this->fntCrearElemento($DianExtensions,"sts:QRCode","1");
    }
    function fntCrearAddress($padre,$cod,$Direccion){
        $municipio = DB::table('municipio')
            ->select('municipio.Description AS Municipio', 'municipio.departamento_cod', 'departamento.Description AS Departamento', 'departamento.pais_cod', 'pais.Description AS Country')
            ->join('departamento', 'departamento.cod', '=', 'municipio.departamento_cod')
            ->join('pais', 'pais.cod', '=', 'departamento.pais_cod')
            ->where('municipio.cod', '=', $cod)
            ->first();
        if(!empty($municipio))
        {
            $this->fntCrearElemento($padre,"cbc:ID",$cod);
            $this->fntCrearElemento($padre,"cbc:CityName",$municipio->Municipio);
            $this->fntCrearElemento($padre,"cbc:CountrySubentity",$municipio->Departamento);
            $this->fntCrearElemento($padre,"cbc:CountrySubentityCode",$municipio->departamento_cod);
            $padreLine=$this->fntCrearElemento($padre,"cac:AddressLine");
            $this->fntCrearElemento($padreLine,"cbc:Line",$Direccion);
            $Country=$this->fntCrearElemento($padre,"cac:Country");
            $this->fntCrearElemento($Country,"cbc:IdentificationCode",$municipio->pais_cod);
            $this->fntCrearElemento($Country,"cbc:Name",$municipio->Country,[
                ['languageID','es']
            ]);
        }
    }
    function fntCrearElemento($padre,$elemento,$texto='',$atributos=[]){
        $elemento=$this->xml_colombia->createElement($elemento);
        $elemento=$padre->appendChild($elemento);
        if (!empty($atributos))
        {
            foreach ($atributos as $attr)
            {
                $elemento->setAttribute($attr[0],$attr[1]);
            }
        }
        if ($texto!='')
        {
            $textID=$this->xml_colombia->createTextNode($texto);
            $textID=$elemento->appendChild($textID);
        }
        return $elemento;
    }
    function fntCrearContact($padre,$Telefono,$Correo){
        if ($Telefono!=''&&$Correo!='') {
            $Contact=$this->fntCrearElemento($padre,"cac:Contact");
            if ($Telefono!='') {
                $this->fntCrearElemento($Contact,"cbc:Telephone",$Telefono);
            }
            if ($Correo!='') {
                $this->fntCrearElemento($Contact,"cbc:ElectronicMail",$Correo);
            }
        }
    }
    function fntParty($padre,$typedoc_cod,$identificacion,$economicactivity_cod,$businessname,$municipio_cod,$Direccion,$typeresponsibility_cod,$telefono,$email,$prefix='')
    {
        $Party=$this->fntCrearElemento($padre,"cac:Party");
        if($typedoc_cod=='31')
        {
            $PartyIdentification=$this->fntCrearElemento($Party,"cac:PartyIdentification");
            $this->fntCrearElemento($PartyIdentification,"cbc:ID",explode("-", $identificacion)[0]);
        }
        if($economicactivity_cod!='0')
        {
            $PartyIdentification=$this->fntCrearElemento($Party,"cbc:IndustryClassificationCode",$economicactivity_cod);
        }
        $PartyName=$this->fntCrearElemento($Party,"cac:PartyName");
        $this->fntCrearElemento($PartyName,"cbc:Name",$businessname);
        $PhysicalLocation=$this->fntCrearElemento($Party,"cac:PhysicalLocation");
        $Address=$this->fntCrearElemento($PhysicalLocation,"cac:Address");
        $this->fntCrearAddress($Address,$municipio_cod,$Direccion);

        $PartyTaxScheme=$this->xml_colombia->createElement("cac:PartyTaxScheme");
        $PartyTaxScheme=$Party->appendChild($PartyTaxScheme);
        $this->fntCrearElemento($PartyTaxScheme,"cbc:RegistrationName",$businessname);
        if($typedoc_cod=='31'  && count(explode("-",$identificacion))>1){
            $this->fntCrearElemento($PartyTaxScheme,"cbc:CompanyID",explode("-", $identificacion)[0],[
                ['schemeAgencyID','195'],
                ['schemeAgencyName','CO, DIAN (Dirección de Impuestos y Aduanas Nacionales)'],
                ['schemeID',explode("-", $identificacion)[1]],
                ['schemeName','31']
            ]);
        }else{
            $this->fntCrearElemento($PartyTaxScheme,"cbc:CompanyID", $identificacion,[
                ['schemeAgencyID','195'],
                ['schemeAgencyName','CO, DIAN (Dirección de Impuestos y Aduanas Nacionales)'],
                ['schemeName',$typedoc_cod]
            ]);
        }
        $this->fntCrearElemento($PartyTaxScheme,"cbc:TaxLevelCode",$typeresponsibility_cod,[
            ['listName','05']
        ]);
        $RegistrationAddress=$this->fntCrearElemento($PartyTaxScheme,"cac:RegistrationAddress");

        $this->fntCrearAddress($RegistrationAddress,$municipio_cod,$Direccion);
        $TaxScheme=$this->fntCrearElemento($PartyTaxScheme,"cac:TaxScheme");
        $this->fntCrearElemento($TaxScheme,"cbc:ID",'01');
        $this->fntCrearElemento($TaxScheme,"cbc:Name",'IVA');
        $PartyLegalEntity=$this->fntCrearElemento($Party,"cac:PartyLegalEntity");
        $this->fntCrearElemento($PartyLegalEntity,"cbc:RegistrationName",$businessname);
        if ($typedoc_cod=='31' && count(explode("-",$identificacion))>1)
        {
            $this->fntCrearElemento($PartyLegalEntity,"cbc:CompanyID",explode("-",$identificacion)[0],[
                ['schemeAgencyID','195'],
                ['schemeAgencyName','CO, DIAN (Dirección de Impuestos y Aduanas Nacionales)'],
                ['schemeID',explode("-",$identificacion)[1]],
                ['schemeName',$typedoc_cod]
            ]);
        }
        else
        {
            $this->fntCrearElemento($PartyLegalEntity,"cbc:CompanyID",$identificacion,[
                ['schemeAgencyID','195'],
                ['schemeAgencyName','CO, DIAN (Dirección de Impuestos y Aduanas Nacionales)'],
                ['schemeName',$typedoc_cod]
            ]);
        }
        if ($prefix!='')
        {
            $CorporateRegistrationScheme=$this->fntCrearElemento($PartyLegalEntity,"cac:CorporateRegistrationScheme");
            $this->fntCrearElemento($CorporateRegistrationScheme,"cbc:ID",$prefix);
        }
        $this->fntCrearContact($Party,$telefono,$email);
    }
    function fntnombrearchivo($prefijo,$nit,$dian,$fecha,$enviados){
        if(count(explode("-", $nit))>1)
        {
            $nit=explode("-", $nit);
            $nit=$nit[0];
        }
        $nit=$this->fntcompletarceros($nit,10);
        $enviados=$this->fntcompletarceros($enviados,8);
        $año=explode("-", $fecha)[0];
        $año=substr($año, -2);
        $this->nombrearchivo=$prefijo.$nit.$dian.$año.$enviados;
        return $this->nombrearchivo;
    }
    function fntcompletarceros($texto,$maxlongitud){
        $numcaracteres=$maxlongitud-strlen($texto);
        $auxtexto="";
        for ($i=0; $i < $numcaracteres; $i++)
        {
            $auxtexto.="0";
        }
        return $texto=$auxtexto.$texto;
    }
    function redondear_dos_decimal($valor) {
        $valor=round($valor * 100) / 100;
        $pos = strpos($valor,".");
        if ($pos!==false)
        {
            $array=explode('.', $valor);
            $valor.=(strlen($array[1])>1)?'':'0';
        }
        else
        {
            $valor.='.00';
        }
        return $valor;
    }
    function fntCalcularSoftwareSecurityCode($settings_dian,$numerofv){
        return hash('sha384',($settings_dian->softwareid.$settings_dian->softwarepin.$settings_dian->prefix.$numerofv));
    }
    //FORMA DE PAGO
    function fntCrearPaymentMeans($padre,$payment_cod,$id_invoces){
        $PaymentMeans=$this->xml_colombia->createElement("cac:PaymentMeans");
        $PaymentMeans=$padre->appendChild($PaymentMeans);
        $this->fntCrearElemento($PaymentMeans,"cbc:ID",$payment_cod);
        $this->fntCrearElemento($PaymentMeans,"cbc:PaymentMeansCode",'10');
        $this->fntCrearElemento($PaymentMeans,"cbc:PaymentID",$id_invoces);
    }
    //PAGOS REALIZADO
    function fntCrearPrepaidPayment($padre,$ID,$PaidAmount,$ReceivedDate,$PaidDate,$InstructionID,$money){
        if ($PaidAmount>0)
        {
            //Crear PrepaidPayment
            $PrepaidPayment=$this->xml_colombia->createElement("cac:PrepaidPayment");
            $PrepaidPayment=$padre->appendChild($PrepaidPayment);
            $this->fntCrearElemento($PrepaidPayment,"cbc:ID",$ID);
            $this->fntCrearElemento($PrepaidPayment,"cbc:PaidAmount",$this->redondear_dos_decimal($PaidAmount),[
                ['currencyID',$money]
            ]);
            if ($ReceivedDate!='')
            {
                $this->fntCrearElemento($PrepaidPayment,"cbc:ReceivedDate",$ReceivedDate);
                $this->fntCrearElemento($PrepaidPayment,"cbc:PaidDate",$PaidDate);
            }
            $this->fntCrearElemento($PrepaidPayment,"cbc:InstructionID",$InstructionID);
        }
    }
    //IVA
    function fntCrearTaxTotal_IVA($padre,$moneda,$detallefactura){
        $TaxTotal=$this->xml_colombia->createElement("cac:TaxTotal");
        $TaxTotal=$padre->appendChild($TaxTotal);
        $porcentajesiva=[];
        $totaliva=0;
        foreach ($detallefactura as $d){
            if($d->iva>0){
                if (isset($porcentajesiva[$d->iva])){
                    $porcentajesiva[$d->iva]+=($d->quantity*$d->price);
                }
                else{
                    $porcentajesiva[$d->iva]=($d->quantity*$d->price);
                }
                $totaliva+=($d->iva/100)*($d->quantity*$d->price);
            }
        }
        $this->fntCrearElemento($TaxTotal,"cbc:TaxAmount",$this->fntFormatonumero($totaliva),[
            ["currencyID",$moneda]
        ]);
        foreach ($porcentajesiva as $key => $Subtotal){
            $TaxSubtotal=$this->fntCrearElemento($TaxTotal,"cac:TaxSubtotal",'');
            $this->fntCrearElemento($TaxSubtotal,"cbc:TaxableAmount",$this->fntFormatonumero($Subtotal),[
                ["currencyID",$moneda]
            ]);
            $this->fntCrearElemento($TaxSubtotal,"cbc:TaxAmount",$this->fntFormatonumero($Subtotal*$key/100),[
                ["currencyID",$moneda]
            ]);
            $TaxCategory=$this->fntCrearElemento($TaxSubtotal,"cac:TaxCategory",'');
            $TaxCategory=$TaxSubtotal->appendChild($TaxCategory);
            $this->fntCrearElemento($TaxCategory,"cbc:Percent",$this->fntFormatonumero($key));
            $TaxScheme=$this->fntCrearElemento($TaxCategory,"cac:TaxScheme");
            $TaxScheme=$TaxCategory->appendChild($TaxScheme);
            $this->fntCrearElemento($TaxScheme,"cbc:ID",'01');
            $this->fntCrearElemento($TaxScheme,"cbc:Name",'IVA');
        }
    }
    //TOTALES
    function fntCrearLegalMonetaryTotal($padre,$moneda,$detallefactura,$subtotal){
        $LegalMonetaryTotal=$this->xml_colombia->createElement("cac:LegalMonetaryTotal");
        $LegalMonetaryTotal=$padre->appendChild($LegalMonetaryTotal);

        $this->fntCrearElemento($LegalMonetaryTotal,"cbc:LineExtensionAmount",$this->redondear_dos_decimal($subtotal),[
            ["currencyID",$moneda]
        ]);
        $baseIVA=0;
        $totalconiva=0;
        foreach ($detallefactura as $d) {
            if ($d->iva>0){
                $baseIVA+=($d->quantity*$d->price);
            }
            $totalconiva+=($d->quantity*$d->price)*(($d->iva/100)+1);
        }
        $this->fntCrearElemento($LegalMonetaryTotal,"cbc:TaxExclusiveAmount",$this->redondear_dos_decimal($baseIVA),[
            ["currencyID",$moneda]
        ]);
        $this->fntCrearElemento($LegalMonetaryTotal,"cbc:TaxInclusiveAmount",$this->redondear_dos_decimal($totalconiva),[
            ["currencyID",$moneda]
        ]);
        //$this->fntCrearElemento($LegalMonetaryTotal,"cbc:PrepaidAmount",$this->redondear_dos_decimal($totalconiva),[
        //	["currencyID",$moneda]
        //]);
        $this->fntCrearElemento($LegalMonetaryTotal,"cbc:PayableAmount",$this->redondear_dos_decimal($totalconiva),[
            ["currencyID",$moneda]
        ]);
    }
    function fntCrearInvoiceLines($padre,$detallefactura,$moneda){
        $conta=0;
        foreach ($detallefactura as $d)
        {
            $conta++;
            $plan_id=($d->plan_id=='')?$d->id:$d->plan_id;
            $this->fntCrearInvoiceLine($padre,$conta,$d->quantity,'ZZ',$d->price,$d->iva,$d->description,$plan_id,$moneda);
        }
    }
    function fntCrearInvoiceLine($padre,$ID,$Cantidad,$cod_unidadmedida,$Precio,$Porcentajeiva,$Nombreservicio,$Codigoservicio,$moneda){
        $Iva=($Cantidad*$Precio)*($Porcentajeiva/100);
        $InvoiceLine=$this->xml_colombia->createElement("cac:InvoiceLine");
        $InvoiceLine=$padre->appendChild($InvoiceLine);
        $this->fntCrearElemento($InvoiceLine,"cbc:ID",$ID);
        $this->fntCrearElemento($InvoiceLine,"cbc:InvoicedQuantity",$Cantidad.'.000000',[
            ['unitCode',$cod_unidadmedida]
        ]);
        $Total=($Cantidad*$Precio);
        $this->fntCrearElemento($InvoiceLine,"cbc:LineExtensionAmount",$this->fntFormatonumero($Total),[
            ['currencyID',$moneda]
        ]);
        $this->fntCrearElemento($InvoiceLine,"cbc:FreeOfChargeIndicator",($Total>0)?'false':'true');
        $TaxTotal=$this->fntCrearElemento($InvoiceLine,"cac:TaxTotal");
        $this->fntCrearElemento($TaxTotal,"cbc:TaxAmount",$this->fntFormatonumero($Iva),[
            ['currencyID',$moneda]
        ]);
        $TaxSubtotal=$this->fntCrearElemento($TaxTotal,"cac:TaxSubtotal");
        if ($Iva>0)
        {
            $this->fntCrearElemento($TaxSubtotal,"cbc:TaxableAmount",$this->redondear_dos_decimal($Precio),[
                ['currencyID',$moneda]
            ]);
        }
        else
        {
            $this->fntCrearElemento($TaxSubtotal,"cbc:TaxableAmount",'0.00',[
                ['currencyID',$moneda]
            ]);
        }
        $this->fntCrearElemento($TaxSubtotal,"cbc:TaxAmount",$this->fntFormatonumero($Iva),[
            ['currencyID',$moneda]
        ]);
        $TaxCategory=$this->fntCrearElemento($TaxSubtotal,"cac:TaxCategory");
        $this->fntCrearElemento($TaxCategory,"cbc:Percent",$this->fntFormatonumero($Porcentajeiva));
        $TaxScheme=$this->fntCrearElemento($TaxCategory,"cac:TaxScheme");
        $this->fntCrearElemento($TaxScheme,"cbc:ID",'01');
        $this->fntCrearElemento($TaxScheme,"cbc:Name",'IVA');
        $Item=$this->fntCrearElemento($InvoiceLine,"cac:Item");
        $this->fntCrearElemento($Item,"cbc:Description",$Nombreservicio);
        $StandardItemIdentification=$this->fntCrearElemento($Item,"cac:StandardItemIdentification");
        $this->fntCrearElemento($StandardItemIdentification,"cbc:ID",$Codigoservicio,[
            ['schemeID','999']
        ]);
        $AdditionalItemIdentification=$this->fntCrearElemento($Item,"cac:AdditionalItemIdentification");
        $this->fntCrearElemento($AdditionalItemIdentification,"cbc:ID",$Codigoservicio,[
            ['schemeID','999']
        ]);
        $Price= $this->fntCrearElemento($InvoiceLine,"cac:Price");
        $this->fntCrearElemento($Price,"cbc:PriceAmount",$this->redondear_dos_decimal($Precio),[
            ['currencyID',$moneda]
        ]);
        $this->fntCrearElemento($Price,"cbc:BaseQuantity",$Cantidad.'.000000',[
            ['unitCode',$cod_unidadmedida]
        ]);
    }
    public function fntFormatonumero($Numero,$decimal=2)
    {
        return number_format($Numero, $decimal, '.', '');
    }
    function firmar_xml_colombia($tipo,$pathCertificate,$passwors,$xmlString,$identificacion,$fecha,$technicalKey,$enviados)
    {
        $domDocument = new DOMDocument();
        $domDocument->loadXML($xmlString);
        $signInvoice = new SignInvoice($pathCertificate, $passwors, $xmlString,SignInvoice::ALGO_SHA256,$technicalKey);
        $nombrearchivo=$this->fntnombrearchivo($tipo,$identificacion,"000",$fecha,$enviados);
        //se obtiene el cufe
        // $this->cufe=$signInvoice->getCufe();
        // $this->qr=$signInvoice->getQR();
        //se guarda en el nombre del archivo y el obejto nstanciado
        file_put_contents('js/lib_dian/comprobantes_colombia/'.$nombrearchivo.'.xml', $signInvoice->xml);
        return $nombrearchivo;
    }
    public function invoicePaymentDelete($id) {
        $invoice = BillCustomer::find($id);

        if ($invoice) {
            $payments = PaymentNew::where('num_bill', $invoice->num_bill)->where('client_id', $invoice->client_id)->get();

            DB::beginTransaction();
            // delete transaction

            $cashPayments = 0;
            foreach ($payments as $payment) {
                $transaction = Transaction::select('id')
                    ->where([
                        'client_id' => $invoice->client_id,
                        'amount' => $payment->amount,
                        'date' => $payment->date,
                        'category' => 'payment'
                    ])
                    ->first();

                if($transaction) {
                    $transaction->delete();
                }

                $cashPayments += $payment->amount;

                $payment->delete();


            }

            // Maintain client account balance
            $invoice->client->balance = round($invoice->client->balance - $invoice->total_pay, 2);

            // generate transaction because amount is refund in client wallet
            $transaction = new Transaction();
            $transaction->client_id = $invoice->client_id;
            $transaction->amount = $invoice->total_pay;
            $transaction->account_balance = round($invoice->client->wallet_balance + $invoice->total_pay, 2);
            $transaction->category = 'payment';
            $transaction->quantity = 1;
            $transaction->date = Carbon::now()->format('Y-m-d');
            $transaction->description = 'Invoice payment refund and added in wallet balance';
            $transaction->save();

            if($payments->count() < 1) {
                $invoice->client->wallet_balance = round($invoice->client->wallet_balance + $invoice->total_pay, 2);

            } else {
                $invoice->client->wallet_balance = round($invoice->total_pay - $cashPayments, 2);
            }

            $invoice->client->save();

            $invoice->status = 3;
            $invoice->save();

            WalletPayment::where('client_id', $invoice->client->id)->where('num_bill', $invoice->num_bill)->delete();

            DB::commit();

            $client = Client::find($invoice->client->id);

            $cortadoDetails = CommonService::getServiceCortadoDate($client->id);
            $billingDueDate = CommonService::getCortadoDateWithTolerence($client->id, $client->billing_settings->billing_grace_period, $this->global->tolerance);
            foreach($client->service as $service) {
                if($service->status == 'de' && now()->startOfDay()->lessThanOrEqualTo($billingDueDate)) {
                    $clientServiceController = new ClientServiceController();
                    $request = new Request([
                        'id'   => $service->id,
                    ]);
                    $ok = $clientServiceController->postBanService($request, $service->id);

                    $service->status = 'ac';
                    $service->save();
                }

                if(!$cortadoDetails['paid'] && $cortadoDetails['cortado_date'] && $service->status == 'ac') {

                    if(now()->startOfDay()->greaterThan($billingDueDate)) {
                        $service->status = 'de';
                        $service->save();
                    }
                }
            }

            $nameClient = $client->name;
            CommonService::log("#$id Pago eliminado: $nameClient", $this->username, 'success' , $this->userId, $invoice->client_id);
            return Reply::success('Payments successfully deleted.');
        }

        return Reply::error('No payments found!');
    }

    public function invoicePaymentEdit($id) {
        $this->invoice = BillCustomer::find($id);
        $this->payment = PaymentNew::where('num_bill', $this->invoice->num_bill)->first();
        $this->clientId = $this->invoice->client_id;
        return view('billing.edit-payment', $this->data);
    }

    public function filterTotals(Request $request){
        $date = $request->extra_search;

        if($date) {
            $string = explode('|', $date);

            $date1 = $string[0];
            $date2 = $string[1];

            $date1 = str_replace('/', '-', $date1);
            $date2 = str_replace('/', '-', $date2);

            $from = date("Y-m-d", strtotime($date1));
            $to = date("Y-m-d", strtotime($date2));
        }



        if($date) {
            $invoices = BillCustomer::whereNotNull('client_id')->select('id', 'total_pay', 'status')->whereBetween('bill_customers.release_date', [$from, $to])->get();
        } else {
            $invoices = BillCustomer::whereNotNull('client_id')->select('id', 'total_pay', 'status')->get();
        }

        $unpaid_invoices = $invoices->where('status', 3);
        $paid_out_invoices = $invoices->where('status', 1);
        $paid_balance_invoices = $invoices->where('status', 2);
        $late_invoices = $invoices->where('status', 4);

        $data = [];

        $data['Unpaid'] = [
            'quantity' => $unpaid_invoices->count(),
            'total' => $unpaid_invoices->sum('total_pay')
        ];
        $data['Paid Out'] = [
            'quantity' => $paid_out_invoices->count(),
            'total' => $paid_out_invoices->sum('total_pay')
        ];
        $data['Paid (account balance)'] = [
            'quantity' => $paid_balance_invoices->count(),
            'total' => $paid_balance_invoices->sum('total_pay')
        ];
        $data['Late'] = [
            'quantity' => $late_invoices->count(),
            'total' => $late_invoices->sum('total_pay')
        ];
        $data['Total'] = [
            'quantity' => $invoices->count(),
            'total' => $invoices->sum('total_pay')
        ];

        $view = view('invoices/list', compact('data'))->render();

        return Reply::dataOnly(['view' => $view]);
    }


    public function getInvoiceMx(Request $request){
        $docType = $request->input('doc_type', '');
        $docId = $request->input('doc_id', '');
        if($docType === '' || $docId === '')
            return Reply::error('Error al obtener documentos');

        $invoiceConfig = InvoiceSettings::first();
        // dd($client);
        $apiKey = '';
        if( $invoiceConfig->is_active ){
            if($invoiceConfig->is_live == 1){
                $apiKey = $invoiceConfig->apikey;
            }else{
                $apiKey = $invoiceConfig->apikey_sandbox;
            }
        }else{
            return Reply::error('Facturas de México esta inactiva');
        }
        $signMx = new SignMx($apiKey, $invoiceConfig->is_live, $invoiceConfig->provider_name);
        $response = null;
        $fileName = '';
        if($docType === 'xml'){
            $response = $signMx->getInvoiceXml($docId);
            $fileName = $docId . '.xml';
        }elseif($docType === 'pdf'){
            $response = $signMx->getInvoicePdf($docId);
            $fileName = $docId . '.pdf';
        }else{
            return Reply::error('Formato no soportado');
        }

        $headers = [
            'Content-Disposition' => 'attachment; filename='. $fileName. ';',
            'Content-Type' => $docType === 'pdf' ? 'application/pdf' : 'application/xml'
        ];
        return response()->stream(function () use ($response)  {
            echo $response;
        }, 200, $headers);

        // return Reply::error('Error al obtener documentos');

    }

    public function signInvoiceMx(Request $request, $idInvoice){
        $bill = BillCustomer::find($idInvoice);
        $billItems = BillCustomerItem::where('bill_customer_id', $bill->id)->get();
        $invoiceConfig = InvoiceSettings::first();
        $client = Client::find($bill->client_id);
        // dd($client);
        $apiKey = '';
        if( $invoiceConfig->is_active ){
            if($invoiceConfig->is_live == 1){
                $apiKey = $invoiceConfig->apikey;
            }else{
                $apiKey = $invoiceConfig->apikey_sandbox;
            }
        }else{
            return Reply::error('Facturas de México esta inactiva');
        }
        $signMx = new SignMx($apiKey, $invoiceConfig->is_live, $invoiceConfig->provider_name);

        $items = [];
        foreach($billItems as $item){
            $line = [
                'product' => [
                    'description' => $item->description
                    , 'product_key' => $invoiceConfig->product_code
                    , 'price' => $item->total
                    , 'unit_key' => $invoiceConfig->unit_code
                ]
            ];
            $items[] = $line;
        }

        $data = [
            'customer' => [
                'legal_name' => $client->name,
                'email' => $client->email,
                'tax_id' => $client->dni
            ],
            'items' => $items,
            'bill_id' => $bill->id

        ];

        $response = $signMx->sendToSign($data);
        if( $response['code'] === 200)
            return Reply::success($response);
        return Reply::error('Error al enviar al SAT, verifica la configuración');
    }

}