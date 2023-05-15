<?php
namespace App\libraries;
use App\models\BillCustomer;
use App\models\ClientService;
use App\Service\CommonService;
use Illuminate\Support\Facades\DB;
use App\models\BillingSettings;
use App\models\GlobalSetting;
use App\models\SuspendClient;
use Carbon\Carbon;
use http\Client;
use Session;
/**
* Client add DB
*/
class AddClient
{
	//metodo para añadir cliente a la BD
	function add($data){

                $en = new Pencrypt();

                $id = DB::table('clients')->insertGetId(
                        array('name' => $data['name'], 'ip' => $data['address'],'mac' => $data['mac'], 'date_in' => $data['date_in'], 'plan_id' => $data['plan_id'],
                                'router_id' => $data['router_id'], 'status' => 'ac', 'online' => 'ver', 'onmikrotik' => 1, 'email' => $data['email'],
                                'phone' => $data['phone'], 'address' => $data['dir'], 'coordinates' => $data['loc'], 'dni' => $data['dni'],
                                'password' => $en->encode($data['passportal']),'user_hot' => $data['user'],'pass_hot' => $en->encode($data['pass']), 'typeauth' => $data['typeauth'],
                                'created_at' => date('Y-m-d h:i:s'),
                                'updated_at' => date('Y-m-d h:i:s'),
                                'odb_id' => $data['odb_id'],
                                'onu_id' => $data['onu_id'],
                                'port' => $data['port'],
                                'onusn' => $data['onusn'],
                                'zona_id' => $data['zona_id'],
                                'billing_type' => $data['billing_type'])
                );
                $this->generateInvoice($id);
                return $id;

        }

    public function generateInvoice($client_id)
    {
        $client = \App\models\Client::find($client_id);
        $pay_date = request()->date_pay;

        $pay_date = date("Y-m-d", strtotime($pay_date));

        $suspend = new SuspendClient();
        $suspend->client_id = $client_id;
        $suspend->router_id = $client->router_id;
        $suspend->expiration = $pay_date;
        $suspend->save();

        $planCost = 0;
        $billing_date = 1;
        if(Session::get('billing_date')!=""){
            $billing_date = Session::get('billing_date');
        }
        (new BillingSettings())->create([
            'client_id' => $client_id,
            'billing_due_date' => Carbon::parse($suspend->expiration)->day,
            'billing_date' => $billing_date,
        ]);

        $billing_date = $client->billing_settings->billing_date;
        $toBill = Carbon::now();
        $startDate = $client->date_in;

        $endDate = $toBill->clone()->endOfMonth();
        if ($billing_date > 1) {
            if (Carbon::createFromFormat('d', $billing_date)->lessThanOrEqualTo(Carbon::now()) || $startDate->day == $billing_date) {
                $endDate = $toBill->clone()->add(1, 'month')->day($billing_date);
            }
            else {
                $endDate = $toBill->clone()->day($billing_date)->subDay();
            }
        }

        $startMonth = $startDate->month;
        $endMonth = $endDate->month;

        $currentDate = $startDate->clone();

        while ($currentDate->lessThanOrEqualTo($endDate)) {
            if ($currentDate->month === $startMonth || $currentDate->month === $endMonth) {
                if ($startMonth === $endMonth) {
                    $no_of_days = $endDate->day - $startDate->day + 1;
                    $planCost += $client->plan->cost * ($no_of_days / $startDate->daysInMonth);
                } else {
                    if ($currentDate->month === $startMonth) {
                        $no_of_days = $startDate->daysInMonth - $startDate->day + 1;
                        $days_in_month = $startDate->daysInMonth;
                    }
                    if ($currentDate->month === $endMonth) {
                        $no_of_days = $endDate->day - $endDate->clone()->startOfMonth()->day + 1;
                        $days_in_month = $endDate->daysInMonth;
                    }

                    if($endDate->diffInDays($startDate) >=30) {
                        $planCost += $client->plan->cost;
                    } else {
                        $planCost += $client->plan->cost * ($no_of_days / $days_in_month);
                    }

                }
            } else {
                $planCost += $client->plan->cost;
            }

            if($endDate->diffInDays($startDate) >=30) {
                $currentDate->endOfMonth()->add(1, 'month');
            } else {
                $currentDate->startOfMonth()->add(1, 'month');
            }
        }

        $planCost = round($planCost, 2);

        $planVAT = $client->plan->iva;
        $total = $planCost + ($planCost * $planVAT) / 100;

        $gracePeriod = $client->billing_settings->billing_grace_period;

        // create invoice, transaction and update client's account balance
        $invoice_data = [
            'client_id' => $client->id,
            'total_pay' => $total,
            'num_bill' => CommonService::getBillNumber(),
            'release_date' => Carbon::now()->format('Y-m-d'),
            'period' => $endDate->add('days', $gracePeriod)->format('Y-m-d'),
            'start_date' => $startDate->format('Y-m-d'),
            'expiration_date' => $endDate->format('Y-m-d'),
            'iva' => $client->plan->iva,
            'cost' => $planCost,
            'billing_type' => 'recurring',
            'status' => 3,
        ];

        $client->balance = round($client->balance - $total, 2);
        $client->save();

        $clientSuspend = SuspendClient::where('client_id', $client->id)->first();

        $clientSuspend->expiration = $pay_date = date("Y-m-d", strtotime($pay_date));;
        $clientSuspend->save();

        $transaction_data = [
            'client_id' => $client->id,
            'amount' => $total,
            'category' => 'service',
            'date' => Carbon::now()->format('Y-m-d'),
            'quantity' => 1,
            'account_balance' => $client->balance,
            'description' => $client->plan->name,
        ];

        if($total > 0) {
            $client->transactions()->create($transaction_data);

            $client->invoices()->create($invoice_data);
        }
    }

    public function addService($data)
    {
        $en = new Pencrypt();
        $service = new ClientService();
        $service->client_id = $data['client_id'];
        $service->ip = $data['address'];
        $service->mac = $data['mac'];
        $service->date_in = $data['date_in'];
        $service->plan_id = $data['plan_id'];
        $service->router_id = $data['router_id'];
        $service->send_invoice = $data['send_invoice'];
//        $service->automatic = $data['automatic'];
        $service->status = 'ac';
        $service->online = 'ver';
        if($data['user'] != '') {
            $service->user_hot = $data['user'];
        }
        $service->pass_hot = $en->encode($data['pass']);
        $service->typeauth = $data['typeauth'];
        $service->onmikrotik = 1;

        $service->save();

        $suspend = new SuspendClient();
        $suspend->client_id = $data['client_id'];
        $suspend->router_id = $data['router_id'];
        $suspend->service_id = $service->id;
        $suspend->save();
        
        if($service->client->billing_settings && $service->client->billing_settings->billing_invoice_pay_type == 'prepay') {
	        $this->generateInvoiceMultipleService($service, $data['client_id']);
        }

        return $service->id;
    }

    public function generateInvoiceMultipleService($service, $client_id)
    {
        $client = \App\models\Client::find($client_id);
//        $pay_date = request()->date_pay;
        $planCost = 0;

        $billing_date = $client->billing_settings->billing_date;
        $toBill = Carbon::now();
        $startDate = $service->date_in;

        $endDate = $toBill->clone()->endOfMonth();
        if ($billing_date > 1) {
            if (Carbon::createFromFormat('d', $billing_date)->lessThanOrEqualTo(Carbon::now()) || $startDate->day == $billing_date) {
                $endDate = $toBill->clone()->add(1, 'month')->day($billing_date);
            }
            else {
            	
            	
            	
                $endDate = $toBill->clone()->day($billing_date)->subDay();
            }
        }

        $startMonth = $startDate->month;
        $endMonth = $endDate->month;

        $currentDate = $startDate->clone();

        while ($currentDate->lessThanOrEqualTo($endDate)) {
            if ($currentDate->month === $startMonth || $currentDate->month === $endMonth) {
                if ($startMonth === $endMonth) {
                    $no_of_days = $endDate->day - $startDate->day + 1;
                    $planCost += $service->plan->cost * ($no_of_days / $startDate->daysInMonth);
                } else {
                    if ($currentDate->month === $startMonth) {
                        $no_of_days = $startDate->daysInMonth - $startDate->day + 1;
                        $days_in_month = $startDate->daysInMonth;
                    }
                    if ($currentDate->month === $endMonth) {
                        $no_of_days = $endDate->day - $endDate->clone()->startOfMonth()->day + 1;
                        $days_in_month = $endDate->daysInMonth;
                    }

                    if($endDate->diffInDays($startDate) >=30) {
                        $planCost += $service->plan->cost;
                    } else {
                        $planCost += $service->plan->cost * ($no_of_days / $days_in_month);
                    }

                }
            } else {
                $planCost += $service->plan->cost;
            }

            if($endDate->diffInDays($startDate) >=30) {
                $currentDate->endOfMonth()->add(1, 'month');
            } else {
                $currentDate->startOfMonth()->add(1, 'month');
            }
        }



        $planVAT = $service->plan->iva;

        $total = round($planCost + $planCost * $planVAT / 100, 2);

        $serviceTotal = $planCost + ($planCost * $planVAT) / 100;
//        $totalPlanCost = $planCost;
//        $totalPlanVat = $planVAT;

        $gracePeriod = $client->billing_settings->billing_grace_period;
	
	    // set invoice cortado date for block service for this client
	    $cortadoDate = null;
	
	    if($client->billing_settings) {
		    $cortadoDate = Carbon::createFromFormat('d', $client->billing_settings->billing_due_date)->format('Y-m-d');
	    }
	
	    $invoice = BillCustomer::where('client_id', $client->id)->where('billing_type', 'recurring')->first();
	
	    if(!$invoice) {
		    $cortadoDate = $endDate->clone()->addDays($client->billing_settings->billing_due_date)->format('Y-m-d');
	    }
        

        // create invoice, transaction and update client's account balance
        $invoice_data = [
            'client_id' => $client->id,
            'total_pay' => $total,
            'num_bill' => CommonService::getBillNumber(),
            'release_date' => Carbon::now()->format('Y-m-d'),
            'period' => $endDate->add('days', $gracePeriod)->format('Y-m-d'),
            'start_date' => $startDate->format('Y-m-d'),
            'expiration_date' => $endDate->format('Y-m-d'),
            'iva' => $service->plan->iva,
            'cost' => $planCost,
            'billing_type' => 'recurring',
            'service_id' => $service->id,
            'status' => 3,
	        'cortado_date' => $cortadoDate
        ];

        $client->balance = round($client->balance - $total, 2);
        $client->save();

        /*$clientSuspend = SuspendClient::where('client_id', $client->id)->where('service_id', $service->id)->first();

        $clientSuspend->expiration =  Carbon::createFromFormat('d', $client->billing_settings->billing_due_date)->addmonth()->format('Y-m-d');
        $clientSuspend->save();*/

        $transaction_data = [
            'client_id' => $client->id,
            'amount' => $total,
            'category' => 'service',
            'date' => Carbon::now()->format('Y-m-d'),
            'quantity' => 1,
            'account_balance' => $client->balance,
            'description' => $service->plan->name,
        ];

        if($total > 0) {

            $client->transactions()->create($transaction_data);

            $invoice = $client->invoices()->create($invoice_data);

            $invoice_items_data = [
                'bill_customer_id' => $invoice->id,
                'plan_id' => $service->plan->id,
                'period_from' => $startDate->format('Y-m-d'),
                'period_to' => $endDate->format('Y-m-d'),
                'quantity' => 1,
                'unit' => 1,
                'price' => $planCost,
                'iva' => $planVAT,
                'total' => $serviceTotal,
                'description' => $service->plan->name,
            ];

            $invoice->invoice_items()->insert($invoice_items_data);
        }
    }
}
