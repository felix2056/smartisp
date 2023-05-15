<?php

namespace App\Http\Controllers;

use App\Classes\Reply;
use App\libraries\Helpers;
use App\models\BillCustomer;
use App\models\BillingSettings;
use App\models\Client;
use App\models\GlobalSetting;
use App\models\OdbSplitter;
use App\models\OnuType;
use App\models\PaymentNew;
use App\models\SuspendClient;
use App\models\Transaction;
use App\models\Zone;
use App\models\Establecimientos;
use App\models\PuntoEmision;
use App\Service\CommonService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Session;
class BillingController extends BaseController
{
    public function index($clientId)
    {

        //Validar estados
        $info=PermissionsController::authorizeRoles('facturacion');
        if (isset($info['status'])) {
            return redirect('/dashboard')->with('status_rol',$info['message']);
        }

        $clients = Client::find($clientId);

        $id = Auth::user()->id;
        $level = Auth::user()->level;
        $global = GlobalSetting::all()->first();
        $GoogleMaps = Helpers::get_api_options('googlemaps');
        $perm = DB::table('permissions')->where('user_id', '=', $id)->get();

        if (count($GoogleMaps) > 0) {
            $key = $GoogleMaps['k'];
        } else {
            $key = 0;
        }

        $OdbSplitter = OdbSplitter::all();
        $OnuType = OnuType::all();
        $Zone = Zone::all();
        $punto_emision = PuntoEmision::all();

        $pfx = file_get_contents(public_path() . '/js/lib_dian/comprobantes_colombia/smartisp.animeinterface.xyz.pfx');
        $password = "smartisp123";

        $data = array(
            "clients" => $clients, 
            "plans" => $perm[0]->access_plans, 
            "routers" => $perm[0]->access_routers,
            "users" => $perm[0]->access_users, "system" => $perm[0]->access_system, "bill" => $perm[0]->access_pays,
            "template" => $perm[0]->access_templates, "ticket" => $perm[0]->access_tickets, "sms" => $perm[0]->access_sms,
            "reports" => $perm[0]->access_reports,
            "v" => $global->version, "st" => $global->status, "map" => $key,
            "lv" => $global->license, "company" => $global->company,
            'permissions' => $perm->first(),
            // menu options
            "punto_emision"=>$punto_emision, "OdbSplitter" => $OdbSplitter, "OnuType" => $OnuType, "Zone" => $Zone,
            "openssl"=> openssl_pkcs12_read($pfx, $certs, $password)
        );

        return view('billing.index', $data);
    }

    public function update(Client $client)
    {

        $clientBillingSettingOldData = [];

        if($client->billing_settings) {
            $clientBillingSettingOldData = $client->billing_settings->toArray();
        }

        $checkboxes = [
            'CustomerBilling' => [
                'status', 'create_invoice', 'auto_pay_invoice', 'send_notification',
            ],
            'CustomerInvoice' => ['status'],
            'CustomerReminder' => ['status', 'payment_status'],
        ];
        $global = GlobalSetting::all()->first();
        foreach ($checkboxes as $key => $value) {
            $reqObj = request()->request->get($key);
            foreach ($value as $field) {
                $reqObj[$field] = $reqObj[$field] ?? '0';
            }
            request()->request->add([
                $key => $reqObj,
            ]);
        }

        $data = [];

        foreach (request()->CustomerBilling as $key => $value) {
            $data['billing_' . $key] = $value;
        }

        foreach (request()->CustomerInvoice as $key => $value) {
            $data['invoice_' . $key] = $value;
        }

        foreach (request()->CustomerReminder as $key => $value) {
            $data['reminder_' . $key] = $value;
        }

        $perm = DB::table('permissions')->where('user_id', '=', \auth()->user()->id)->first();

        if(!($perm->billing_setting_update)) {
            unset($data['billing_date']);
            unset($data['billing_due_date']);
            unset($data['billing_grace_period']);
        }


        $client->billing_settings->update($data);

        $cortadoDetails = CommonService::getServiceCortadoDate($client->id);

        if($perm->billing_setting_update) {
	        $client = Client::with('service')->find($client->id);
//           $expiration_date = Carbon::now()->lessThanOrEqualTo(Carbon::createFromFormat('d', $data['billing_due_date'])) ? Carbon::now()->day($data['billing_due_date']) : Carbon::now()->day($data['billing_due_date']);
            //si es menor o igual

            // according to old billing setting before update
            $totalTollerenceDaysByNewSetting = $global->tolerance  + $data['billing_grace_period'];

            if(isset($cortadoDetails['cortado_date'])) {
                $cortadoDate = Carbon::parse($cortadoDetails['cortado_date']);
                $fecha_de_corte = $cortadoDate->addDays($totalTollerenceDaysByNewSetting);
            } else {
                $fecha_de_corte = Carbon::createFromFormat('d', $data['billing_date']);
            }



            $clientServiceController = new ClientServiceController();
            if(Carbon::now() <= $fecha_de_corte){
                //-------------------------
                //activamos
                //-------------------------
                //consultamos al cliente

                foreach($client->service as $service) {
                    if ($service->status == 'ac') {
                    }else{
                        $request = new Request([
                            'id'   => $service->id,
                        ]);
                        //si el cliente esta desactivado lo mandamos activar
                        $clientServiceController->postBanService($request, $service->id);

                    }
                }
            } else {
                //-------------------------
                //Cortamos
                //-------------------------
                foreach($client->service as $service) {
                    if ($service->status == 'ac') {
                        if($client->balance<0){
                            $request = new Request([
                                'id'   => $service->id,
                            ]);
                            $clientServiceController->postBanService($request, $service->id);
                        }

                        $client->billing_settings->billing_grace_period=0;
                        $client->billing_settings->save();
                    }
                }

            }

            if($cortadoDetails['invoiceId'] && isset($cortadoDetails['cortado_date'])) {
                $cortadoDate = Carbon::parse($cortadoDetails['cortado_date'])->subDays($clientBillingSettingOldData['billing_due_date']);
            	$invoice = BillCustomer::find($cortadoDetails['invoiceId']);
            	$invoice->cortado_date = $cortadoDate->addDays($data['billing_due_date']);
            	$invoice->save();
            }
            

//            SuspendClient::where('client_id', $client->id)->update([
//                'expiration' => $expiration_date->format('Y-m-d')
//            ]);
        }


        return Reply::success('Settings updated successfully.');
    }

    public function page(Request $request, $clientId)
    {
        $global = GlobalSetting::first();
        $clients = Client::with('service', 'billing_settings')->find($clientId);
        if ($clients->billing_settings === null) {
            $expiring = SuspendClient::where('client_id', '=', $clients->id)->first();
            $billing_date = 1;

            if(Session::get('billing_date')!=""){
                $billing_date = Session::get('billing_date');
            }
            (new BillingSettings())->create([
                'client_id' => $clients->id,
                'billing_date' => $billing_date,
            ]);
            $billing_settings = $clients->refresh()->billing_settings;

        } else {
            $billing_settings = $clients->billing_settings;
        }

        switch ($request->page) {
            case 'billing':
                $perm = DB::table('permissions')->where('user_id', '=', \auth()->user()->id)->first();
	            $serviceCutDate = \App\Service\CommonService::getCortadoDateWithTolerence($clients->id, $billing_settings->billing_grace_period, $global->tolerance);
	            
	            if($serviceCutDate) {
	            	$serviceCutDate = Carbon::parse($serviceCutDate)->format('j F, Y');
	            }
	            
	            $view = view('billing.billing', ['settings' => $billing_settings, 'clients' => $clients, 'global' => $global, 'perm' => $perm, 'serviceCutDate' => $serviceCutDate])->render();
            
            return Reply::success(['view' => $view]);
            break;
            case 'services':
            $view = view('billing.services', ['settings' => $billing_settings, 'clients' => $clients, 'global' => $global])->render();
            return Reply::success(['view' => $view]);
            break;

            case 'transactions':
            $transactions = Transaction::where('client_id', $clientId)->get();

            $credit = $transactions->where('category', 'payment');
            $debit = $transactions->where('category', 'service');
            $data = [];

            $data['debit'] = [
                'quantity' => $debit->count(),
                'total' => $debit->sum('amount'),
            ];

            $data['credit'] = [
                'quantity' => $credit->count(),
                'total' => $credit->sum('amount'),
            ];

            $data['total'] = [
                'quantity' => $debit->count() + $credit->count(),
                'total' => $debit->sum('amount') - $credit->sum('amount'),
            ];

            $view = view('billing.transactions', ['clients' => $clients, 'data' => $data])->render();
            return Reply::success(['view' => $view]);
            break;

            case 'invoices':
            $invoices = BillCustomer::select('id', 'total_pay', 'status')->where('client_id', $clientId)->get();

            $unpaid_invoices = $invoices->where('status', 3);
            $paid_out_invoices = $invoices->where('status', 1);
            $paid_balance_invoices = $invoices->where('status', 2);
            $late_invoices = $invoices->where('status', 4);

            $data = [];

            $data['Unpaid'] = [
                'quantity' => $unpaid_invoices->count(),
                'total' => $unpaid_invoices->sum('total_pay'),
            ];
            $data['Paid Out'] = [
                'quantity' => $paid_out_invoices->count(),
                'total' => $paid_out_invoices->sum('total_pay'),
            ];
            $data['Paid (account balance)'] = [
                'quantity' => $paid_balance_invoices->count(),
                'total' => $paid_balance_invoices->sum('total_pay'),
            ];
            $data['Late'] = [
                'quantity' => $late_invoices->count(),
                'total' => $late_invoices->sum('total_pay'),
            ];
            $data['Total'] = [
                'quantity' => $invoices->count(),
                'total' => $invoices->sum('total_pay'),
            ];

            $view = view('billing.invoices', ['clients' => $clients, 'data' => $data])->render();
            return Reply::success(['view' => $view]);
            break;

            case 'payments':

            $payments = PaymentNew::select('way_to_pay', 'amount')->where('client_id', $clientId)->get();

            $data = [];

            $data['Cash'] = [
                'quantity' => $payments->where('way_to_pay', 'Cash')->count(),
                'total' => $payments->where('way_to_pay', 'Cash')->sum('amount'),
            ];
            $data['Bank Transfer'] = [
                'quantity' => $payments->where('way_to_pay', 'Bank Transfer')->count(),
                'total' => $payments->where('way_to_pay', 'Bank Transfer')->sum('amount'),
            ];
            $data['PayPal'] = [
                'quantity' => $payments->where('way_to_pay', 'PayPal')->count(),
                'total' => $payments->where('way_to_pay', 'PayPal')->sum('amount'),
            ];
            $data['Stripe'] = [
                'quantity' => $payments->where('way_to_pay', 'Stripe')->count(),
                'total' => $payments->where('way_to_pay', 'Stripe')->sum('amount'),
            ];
            $data['Others'] = [
                'quantity' => $payments->where('way_to_pay', 'Others')->count(),
                'total' => $payments->where('way_to_pay', 'Others')->sum('amount'),
            ];
            $data['Total'] = [
                'quantity' => $payments->count(),
                'total' => $payments->sum('amount'),
            ];

            $view = view('billing.payments', ['clients' => $clients, 'data' => $data, 'global' => $global])->render();
            return Reply::success(['view' => $view]);
            break;

            case 'statistics':

                $view = view('billing.statistic', ['settings' => $billing_settings, 'clients' => $clients, 'global' => $global])->render();
                return Reply::success(['view' => $view]);
                break;

            case 'documents':

                $view = view('billing.documents', ['settings' => $billing_settings, 'clients' => $clients, 'global' => $global])->render();
                return Reply::success(['view' => $view]);
                break;
            default:
            return 'Not found';
        }
    }

    public function saveNotes(Request $request,$id) {
        $setting = BillingSettings::find($id);
        $setting->reminder_additional = $request->reminder_additional;
        $setting->save();
        return Reply::success('Notes successfully saved.');
    }

}
