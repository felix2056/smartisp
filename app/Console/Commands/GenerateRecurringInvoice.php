<?php

namespace App\Console\Commands;

use App\models\Client;
use App\models\GlobalSetting;
use App\models\RecurringInvoice;
use App\models\User;
use App\Service\CommonService;
use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class GenerateRecurringInvoice extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'generate:recurring-invoice';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate automatic recurring invoices.';

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
        Log::debug('start Generate Recurring Invoices');
        $recurringInvoices = RecurringInvoice::with('items')
	        ->where(function ($query) {
                $query->whereNull('next_pay_date')->orWhereDate('next_pay_date', '=', Carbon::today());
            })
	        ->where('status', 'enable')->where('service_status', 'active')
	        ->chunkById(5, function($recurringInvoices) {
		        foreach($recurringInvoices as $invoice) {
			        if(is_null($invoice->end_date) || (!is_null($invoice->end_date) && $invoice->end_date->gt(Carbon::now())) ) {
				        if($invoice->start_date->day >= Carbon::now()->day) {
					        $invoice_num = CommonService::getBillNumber();
					        $client = Client::find($invoice->client_id);
					        $this->info($client->name);
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
					
					        $invoice_items_data = [];
					
					        $cortadoDate = null;
					
					        if($client->billing_settings) {
						        $cortadoDate = Carbon::now()->addDays($client->billing_settings->billing_due_date)->subDays(1)->format('Y-m-d');
					        }
					
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
						        'recurring_invoice' => 'yes',
						        'cortado_date' => $cortadoDate
					        ];
					
					        $invoice_data['total_pay'] = $invoice->price;
					
					        if ((float) $client->wallet_balance >= (float) $invoice_data['total_pay']) {
						        $invoice_data['status'] = 2;
						        $invoice_data['paid_on'] = Carbon::now()->format('Y-m-d');
						        $client->wallet_balance = round($client->wallet_balance - $invoice_data['total_pay'], 2);
					        } else {
						        $client->balance = round($client->balance - $invoice_data['total_pay'], 2);
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
					
					        if($invoice->status != 3) {
						        $user = User::where('level', 'ad')
							        ->where('email', '!=', "support@smartisp.us")
							        ->orderBy('id', 'asc')
							        ->first();
						        CommonService::addWalletPayment($client->id, $invoice_data['num_bill'], $invoice_data['total_pay'], $user->id);
					        }

				        }
				
			        }
		        }
	        });

        
    }
}
