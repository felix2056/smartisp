<?php

namespace App\Console\Commands;

use App\models\BillCustomer;
use App\models\Client;
use App\models\GlobalSetting;
use App\models\SuspendClient;
use App\models\User;
use App\Service\CommonService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\models\Transaction;
use Illuminate\Support\Facades\Log;

class GenerateInvoice extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'generate-invoice';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate client\'s invoices';

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
     * @return mixed
     */
    public function handle()
    {
        set_time_limit(0);
        //Unlimited momory limit
        ini_set('memory_limit', '-1');

        $todayDay = Carbon::now()->day;

        $callback = function($query) {
            $query->select('id', 'client_id', 'date_in', 'plan_id')->where('billing_type', 'recurring');
        };

        $planCallback = function($query) {
            $query->select('id', 'name', 'cost', 'iva');
        };

        $billingSettingCallback = function($query) {
            $query->select('id', 'client_id', 'billing_due_date', 'billing_invoice_pay_type', 'billing_date', 'billing_grace_period');
        };

        $old = 0;
        Client::whereHas('service', $callback)
            ->join('billing_settings', 'billing_settings.client_id', '=', 'clients.id')
            ->with([
                'billing_settings' => $billingSettingCallback,
                'service' => $callback,
                'service.plan' => $planCallback
            ])
            ->where('billing_settings.billing_date', $todayDay)
            ->select('clients.id', 'clients.name', 'clients.wallet_balance', 'clients.balance')
            ->chunkById(5, function($clients)
            {
                foreach ($clients as $clientDetail) {

                    $lastPaidInvoice = BillCustomer::where('client_id', $clientDetail->id)
                        ->wherein('status', [1, 2, 4])
                        ->where('recurring_invoice', '!=', 'yes')
                        ->orderBy('id', 'desc')
                        ->first();

                    // check if last paid invoice is more then 2 month that means we dont need to generate invoice
                    if($lastPaidInvoice) {
                        $releaseDate = Carbon::parse($lastPaidInvoice->release_date);
                        if(Carbon::now()->diffInMonths($releaseDate) > 2)  {
                            continue;
                        }
                    }

                    $fecha_v=Carbon::now()->format('Y-m-d');

                    $cant_validate = Transaction::where('category','service')
                        ->where('client_id',$clientDetail->id)
                        ->where('description','Auto generated')
                        ->where('date',$fecha_v)
                        ->where('quantity',1)
                        ->count();
                    try {
                        if($cant_validate==0) {

                            $invoice = BillCustomer::select('id', 'start_date', 'release_date', 'expiration_date', 'created_at')->where('client_id', $clientDetail->id)->where('recurring_invoice', 'no')->latest()->first();
                            if($invoice) {
                                $secondLastInvoice = BillCustomer::where('client_id', $clientDetail->id)
                                    ->where('created_at', '<', $invoice->created_at)
                                    ->where('recurring_invoice', 'no')
                                    ->orderBy('created_at', 'desc')->first();

                                $clientGracePeriod = $clientDetail->billing_settings->billing_due_date;

                                $date = Carbon::parse($invoice->created_at)->addDays($clientGracePeriod);

                                if($invoice->status === 3 && $date->lessThan(Carbon::now()) ) {

                                    continue;
                                }

                                if($secondLastInvoice) {
                                    $date = Carbon::parse($secondLastInvoice->created_at)->addDays($clientGracePeriod);

                                    if($secondLastInvoice->status === 3 && $date->lessThan(Carbon::now())) {
                                        continue;
                                    }
                                }
                            }

                            $toBillMonth = Carbon::parse(Carbon::now())->month;
                            $toBill = Carbon::parse(Carbon::now());

                            if ($invoice !== null) {
                                //$invoice_start_month = Carbon::parse($invoice->start_date)->month;
                                //$invoice_expire_month = Carbon::parse($invoice->expiration_date)->month;

                                // Here we are checking the invoice expiration date is greater then today or not if greater then today
                                // then not generate invoice because this is the case of prepay like a user paid for 3 months already.
                                $now = Carbon::now();
                                $expirationDate = Carbon::parse($invoice->expiration_date);

                                // check if expiration date is greater then today
                                if($now->lessThan($expirationDate)) {
                                    continue;
                                }
                                // create array of months
//                                $invoice_month_arr = [];
//                                for ($i = $invoice_start_month; $i < $invoice_expire_month; $i++) {
//                                    array_push($invoice_month_arr, $i);
//                                }
//
//                                if (in_array($toBillMonth, $invoice_month_arr)) {
//                                    continue;
//                                }
                            }

                            $total = 0;
                            $totalPlanCost = 0;
                            $totalPlanVat = 0;

                            $endDate = $toBill->clone()->endOfMonth();

                            if($clientDetail->billing_settings) {
                                $billing_date = $clientDetail->billing_settings->billing_date;
                                $planDetails = [];

                                foreach($clientDetail->service as $client) {
                                    $planCost = 0;
                                    if ($invoice !== null) {
                                        $startDate = Carbon::parse($invoice->expiration_date)->addDay();
                                    } else {
                                        $startDate = $client->date_in;
                                    }
                                    if ($toBill->greaterThanOrEqualTo($client->date_in)) {
                                        if ($billing_date > 1) {
                                            if (Carbon::createFromFormat('d', $billing_date)->lessThanOrEqualTo(Carbon::now()) || $startDate->day == $billing_date) {
                                                $endDate = $toBill->clone()->add(1, 'month')->day($billing_date);
                                            } else {
                                                $endDate = $toBill->clone()->day($billing_date)->subDay();
                                            }
                                        }

                                        if($clientDetail->billing_settings->billing_invoice_pay_type == 'postpay') {
                                            if ($invoice !== null) {
                                                $endDate = $startDate->clone()->addMonth()->subDay();
                                            } else {
                                                $endDate = $startDate->clone()->endOfMonth();
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

                                        if($endDate->diffInDays($startDate) >= 30) {
                                            $planCost = round($client->plan->cost, 2);
                                        } else {
                                            $planCost = round($planCost, 2);
                                        }

                                        $planVAT = $client->plan->iva;

                                        $total += round($planCost + $planCost * $planVAT / 100, 2);

                                        $serviceTotal = $planCost + (($planCost * $planVAT) / 100);


                                        $totalPlanCost += $planCost;
                                        $totalPlanVat += $planVAT;

                                        $planDetails[] = [
                                            'start_date' => $startDate->format('Y-m-d'),
                                            'end_date' => $endDate->format('Y-m-d'),
                                            'plan_id' => $client->plan->id,
                                            'plan' => $client->plan,
                                            'plan_cost' => $planCost,
                                            'plan_vat' => $planVAT,
                                            'total' => $serviceTotal,
                                        ];
                                    }

//                                    $clientSuspend = SuspendClient::where('client_id', $clientDetail->id)->where('service_id', $client->id)->first();
//
//
//                                    /**se agrega el codigo dentro del if el 02/10/2021. Esto se hace ya que siempre actualizaba la fecha de corte, sin tener en cuenta si ya habia sucedido el corte de este mes o no***/
//                                    $global = GlobalSetting::first();
//                                    $dia_de_gracia = $clientDetail->billing_settings->billing_grace_period;
//                                    $tot_dias=$dia_de_gracia+$global->tolerance;
//
//
//                                    //if($clientSuspend->expiration->lte(Carbon::now()->startOfDay())){
//                                    if(isset($clientSuspend->expiration) && $clientSuspend->expiration->startOfDay()->add('days', $tot_dias)->format('Y-m-d') <= Carbon::now()->startOfDay()){
//                                        $clientSuspend->expiration = Carbon::createFromFormat('d', $clientDetail->billing_settings->billing_due_date)->format('Y-m-d');
//                                        $clientSuspend->save();
//                                    }




                                }

                                // set invoice cortado date for block service for this client
                                $cortadoDate = null;
                                if($clientDetail->billing_settings) {
                                    $cortadoDate = Carbon::now()->addDays($clientDetail->billing_settings->billing_due_date)->subDays(1)->format('Y-m-d');
                                }

                                // create invoice, transaction and update client's account balance
                                $invoice_data = [
                                    'client_id' => $clientDetail->id,
                                    'total_pay' => $total,
                                    'num_bill' => CommonService::getBillNumber(),
                                    'release_date' => Carbon::now()->format('Y-m-d'),
                                    'period' => $endDate->format('Y-m-d'),
                                    'start_date' => isset($startDate) ? $startDate->format('Y-m-d') : Carbon::now()->format('Y-m-d'),
                                    'expiration_date' => $endDate->format('Y-m-d'),
                                    'iva' => $totalPlanVat,
                                    'cost' => $totalPlanCost,
                                    'cortado_date' => $cortadoDate
                                ];

                                $invoice_data['status'] = 3;

                                if ((float) $clientDetail->wallet_balance >= (float) $total) {
                                    $invoice_data['status'] = 2;
                                    $invoice_data['paid_on'] = Carbon::now()->format('Y-m-d');
                                    $clientDetail->wallet_balance = round($clientDetail->wallet_balance - $total, 2);
                                } else {
                                    $clientDetail->balance = round($clientDetail->balance - $total, 2);
                                }

                                $clientDetail->save();

                                $transaction_data = [
                                    'client_id' => $clientDetail->id,
                                    'amount' => $total,
                                    'category' => 'service',
                                    'date' => Carbon::now()->format('Y-m-d'),
                                    'quantity' => 1,
                                    'account_balance' => $clientDetail->wallet_balance,
                                    'description' => 'Auto generated',
                                ];

                                $clientDetail->transactions()->create($transaction_data);

                                $invoice = $clientDetail->invoices()->create($invoice_data);

                                $invoice_items_data = [];
                                foreach($planDetails as $key => $item) {
                                    $invoice_items_data[$key] = [
                                        'bill_customer_id' => $invoice->id,
                                        'plan_id' => $item['plan_id'],
                                        'period_from' => $item['start_date'],
                                        'period_to' => $item['end_date'],
                                        'quantity' => 1,
                                        'unit' => 1,
                                        'price' => $item['plan_cost'],
                                        'iva' => $item['plan_vat'],
                                        'total' => $item['total'],
                                        'description' => $item['plan']['name'],
                                    ];
                                }

                                // create invoice_items
                                $invoice->invoice_items()->insert($invoice_items_data);

                                if($invoice->status != 3) {
                                    $user = User::where('level', 'ad')
                                        ->where('email', '!=', "support@smartisp.us")
                                        ->orderBy('id', 'asc')
                                        ->first();
                                    CommonService::addWalletPayment($client->id, $invoice_data['num_bill'], $invoice_data['total_pay'], $user->id);
                                }

                                $invoiceId = $invoice->num_bill;
                                CommonService::log("#$invoiceId Factura generada
: ", 'Automatic', 'success');
                                echo "Invoices generated";

                            }
                        }
                    } catch(\Exception $exception) {
                        Log::debug("$clientDetail->name : ". $exception->getMessage());
                        continue;
                    }


                }
            }, 'clients.id', 'id');

    }
}