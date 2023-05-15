<?php

namespace App\Service;


use App\models\BillCustomer;
use App\models\Client;
use App\models\GlobalSetting;
use App\models\Logg;
use App\models\WalletPayment;
use Carbon\Carbon;

class CommonService
{
	//get the service expiration date of this client
	public static function getServiceCortadoDate($clientId)
	{
		$client = Client::with('service')->find($clientId);
		
		if($client->service->count() < 1) {
			// if no service found
			return [
				'paid' => false,
				'cortado_date' => null,
				'invoiceId' => null,
				'amount' => null
			];
		}
		// get last unpaid invoice
		$invoice = BillCustomer::where('client_id', $clientId)
			->where('status', 3)
			->where('recurring_invoice', '!=', 'yes')
			->where('total_pay', '>', 0)
			->orderBy('id', 'asc')
			->first();
		
		if($invoice) {
			return [
				'paid' => false,
				'cortado_date' => $invoice->cortado_date,
				'invoiceId' => $invoice->id,
				'amount' => $invoice->total_pay
			];
		}
		
		// if last unpaid invoice not found then find last paid invoice and return the cortado date
		$invoice = BillCustomer::where('client_id', $clientId)
			->wherein('status', [1, 2, 4])
			->where('recurring_invoice', '!=', 'yes')
			->orderBy('id', 'desc')
			->first();
		
		if($invoice) {
			return [
				'paid' => true,
				'cortado_date' => $invoice->cortado_date,
				'invoiceId' => $invoice->id,
				'amount' => $invoice->total_pay
			];
		}
		
		// if no invoice found
		return [
			'paid' => false,
			'cortado_date' => null,
			'invoiceId' => null,
			'amount' => null
		];
	}
	
	//get the service expiration date of this client with tolerence and grace period
	public static function getCortadoDateWithTolerence($clientId, $billingGracePeriod, $tolerance)
	{
		// get details for cortado service
		$cortadoDetails = CommonService::getServiceCortadoDate($clientId);
		if($cortadoDetails['cortado_date']) {
			$tot_dias = $billingGracePeriod + $tolerance;
			return Carbon::parse($cortadoDetails['cortado_date'])->startOfDay()->add('days', $tot_dias)->format('Y-m-d');
		}
		
		return null;
	}
	
	public static function addWalletPayment($clientId, $num_bill, $amount, $receivedBy)
	{
		$payment = new WalletPayment();
		$payment->client_id = $clientId;
		$payment->amount = $amount;
		$payment->num_bill = $num_bill;
		$payment->user_id = $receivedBy;
		$payment->save();
	}
	
	public static function log($message, $username, $type, $userId = null, $clientId = null)
	{
		$log = new Logg();
		$log->detail = $message;
		$log->user = ($username && $username != '') ? $username : "System";
		$log->type = $type;
		$log->user_id = $userId;
		$log->client_id = $clientId;
		$log->save();
	}

    /**
     * @return int
     * Generate invoice num and save in global settings for use in next invoice
     */
    public static function getBillNumber()
    {
        $globalSettings = GlobalSetting::first();

        if ($globalSettings->num_bill > 1) {
            $globalSettings->num_bill = $globalSettings->num_bill + 1;
            $globalSettings->save();

            return $globalSettings->num_bill;
        }

        $globalSettings->num_bill = $globalSettings->num_bill + 1;
        $globalSettings->save();

        return $globalSettings->num_bill;
    }
}
