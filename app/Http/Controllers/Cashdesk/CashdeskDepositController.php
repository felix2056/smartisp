<?php

namespace App\Http\Controllers\Cashdesk;

use App\Classes\Reply;
use App\DataTables\CashdeskHistoryDataTable;
use App\DataTables\CashdeskInvoiceDataTable;
use App\Http\Controllers\BaseController;
use App\Http\Controllers\ClientServiceController;
use App\Http\Requests\Payment\StoreRequest;
use App\libraries\Burst;
use App\libraries\Chkerr;
use App\libraries\CountClient;
use App\libraries\GetPlan;
use App\libraries\Helpers;
use App\libraries\Mikrotik;
use App\libraries\Mkerror;
use App\libraries\RocketCore;
use App\libraries\RouterConnect;
use App\libraries\Slog;
use App\models\BillCustomer;
use App\models\CashierDepositHistory;
use App\models\Client;
use App\models\ClientService;
use App\models\ControlRouter;
use App\models\GlobalSetting;
use App\models\PaymentNew;
use App\models\radius\Radgroupcheck;
use App\models\radius\Radusergroup;
use App\models\Router;
use App\models\Transaction;
use App\Service\CommonService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CashdeskDepositController extends BaseController
{
	public function index(CashdeskHistoryDataTable $dataTable)
	{
		$payments = CashierDepositHistory::where('user_id', $this->user->id)->get();
		
		$cash = $payments->where('way_to_pay', 'Cash');
		$bankTransfer = $payments->where('way_to_pay', 'Bank Transfer');
		$payPal = $payments->where('way_to_pay', 'PayPal');
		$stripe = $payments->where('way_to_pay', 'Stripe');
		$other = $payments->where('way_to_pay', 'Other');
		$datas = [];
		
		$datas['Cash'] = [
			'quantity' => $cash->count(),
			'total' => $cash->sum('amount'),
		];
		
		$datas['Bank Transfer'] = [
			'quantity' => $bankTransfer->count(),
			'total' => $bankTransfer->sum('amount'),
		];
		
		$datas['PayPal'] = [
			'quantity' => $payPal->count(),
			'total' => $payPal->sum('amount'),
		];
		
		$datas['Stripe'] = [
			'quantity' => $stripe->count(),
			'total' => $stripe->sum('amount'),
		];
		
		$datas['Other'] = [
			'quantity' => $other->count(),
			'total' => $other->sum('amount'),
		];
		
		$datas['total'] = [
			'quantity' => $cash->count() + $bankTransfer->count() + $payPal->count() + $stripe->count() + $other->count(),
			'total' => $cash->sum('amount') + $bankTransfer->sum('amount') + $payPal->sum('amount') + $stripe->sum('amount') + $other->sum('amount'),
		];
		
		$this->datas = $datas;
		return $dataTable->render('cashdesk.history.index', $this->data);
	}
	
	public function searchClient()
	{
		$this->clients = Client::select('id', 'name')->get();
		return view('cashdesk.search.index', $this->data);
	}
	
	public function searchByInvoice(Request $request)
	{
		return BillCustomer::where('num_bill', 'like', "%$request->q%")->select('client_id', 'num_bill')->get();
	}
	
	public function searchByClientName(Request $request)
	{
		return Client::where('name', 'like', "%$request->q%")->select('id', 'name')->get();
	}
	
	public function searchByClientEmail(Request $request)
	{
		return Client::where('email', 'like', "%$request->q%")->select('id', 'email')->get();
	}
	
	public function searchByClientDni(Request $request)
	{
		return Client::where('dni', 'like', "%$request->q%")->select('id', 'dni')->get();
	}
	
	public function clientData(CashdeskInvoiceDataTable $dataTable, $id)
	{
		$this->client = Client::find($id);
		
		if(!$this->client) {
			abort(404);
		}
		
		return $dataTable->render('cashdesk.client.index', $this->data);
	}
	
	/**
	 * @param StoreRequest $request
	 * @return array|string[]
	 * @throws ValidationException
	 */
	public function addDeposit(StoreRequest $request)
	{
		if($request->id_pago != '') {
			$payment = PaymentNew::where('id_pago', $request->id_pago)->first();
			
			if($payment) {
				$validator = \Validator::make([], []);
				$validator->getMessageBag()->add('id_pago', 'The id pago has already been taken.');
				throw new ValidationException($validator);
			}
			
		}

        //Find invoice for client
        $invoices = BillCustomer::where('client_id', $request->client_id)
            // ->whereMonth('start_date', Carbon::now()->month)
            ->where('status', 3)
            ->orderBy('id', 'asc')
            ->get();

		$invoiceAmount = $invoices->pluck('total_pay')->toArray();

		$isLessThenInvoice = false;

        $amount  = round($request->amount, 2);

        if(count($invoiceAmount) > 0 ) {
            if($amount < round(min($invoiceAmount), 2)) {
                $isLessThenInvoice = true;
            }
        }

        if($isLessThenInvoice) {
            $validator = \Validator::make([], []);
            $validator->getMessageBag()->add('amount', 'The amount is less then invoice amount.');
            throw new ValidationException($validator);
        }

		DB::beginTransaction();
		
		$cashdeskUser = auth()->guard('cashdesk')->user();
		
		if($cashdeskUser->balance < $request->amount) {
			return Reply::error('You not have enough credits. Please add credits to your account first. You have only '.$cashdeskUser->balance. " credits in your account.");
		}
		
		// Maintain cashdesk user deposit history
		$cashdeskDepositHistory = new CashierDepositHistory();
		$cashdeskDepositHistory->client_id = $request->client_id;
		$cashdeskDepositHistory->user_id = auth()->guard('cashdesk')->user()->id;
		$cashdeskDepositHistory->amount = round($request->amount, 2);
		$cashdeskDepositHistory->comment = $request->commentary;
		$cashdeskDepositHistory->save();
		
		// Subtract from cashdesk user balance
		
		$cashdeskUser->balance = round($cashdeskUser->balance - $request->amount, 2);
		$cashdeskUser->save();

		
		// Get client details
		$client = Client::with('billing_settings')
			->find($request->client_id);
		
		
		// Maintain client account balance
		$client->wallet_balance = round($client->wallet_balance + $request->amount, 2);
		$client->save();
		
		
		// This is required for maintaining the client balance in transactions
		$clientBalance = round($client->wallet_balance, 2);
		
		// When user have balance in his/her account and adding payment then payment will added as advance payment
		// Add new payment
		$payment = new  PaymentNew();
		$payment->way_to_pay = $request->way_to_pay;
		$payment->date = Carbon::parse($request->date)->format('Y-m-d');
		$payment->amount = round($request->amount, 2);// Save if there any advance payment
		$payment->memo = $request->memo;
		
		if($request->has('id_pago')) {
			$payment->id_pago = $request->id_pago;
		}
		
		$payment->commentary = $request->commentary;
		$payment->note = $request->note;
		$payment->client_id = $request->client_id;
		$payment->received_by = \auth()->user()->id;
		$payment->save();
		
		// Add transactions
		$transaction = new Transaction();
		$transaction->client_id = $request->client_id;
		$transaction->amount = round($request->amount, 2);// Save if there any advance payment
		$transaction->account_balance = $clientBalance;
		$transaction->category = 'payment';
		$transaction->quantity = 1;
		$transaction->description = $request->commentary;
		$transaction->date = Carbon::parse($request->date)->format('Y-m-d');
		$transaction->save();
		
		/*$suspend = $client->suspend_client;
		
		// If client balance is less then 0 then deactivate all the services
		if((float)$client->balance < 0) {
			if(now()->startOfDay()->greaterThan($suspend->expiration)) {
				foreach ($client->service as $service) {
					$service->status = 'de';
					$service->save();
				}
			}
		}*/
		
		foreach($invoices as $invoice) {
			if($invoice->total_pay <= round($client->wallet_balance, 2)) {
				$invoice->paid_on = Carbon::now()->format('Y-m-d');
				$invoice->status = 1;
				$invoice->save();
				
				// maintain wallet balance of client
				$client->wallet_balance = round($client->wallet_balance - $invoice->total_pay, 2);
				
				// maintain total pending of client
				$client->balance = round($client->balance + $invoice->total_pay, 2);
				
				$client->save();
				
				
				// Add transactions of invoice payment
				$transaction = new Transaction();
				$transaction->client_id = $request->client_id;
				$transaction->amount = round($invoice->total_pay, 2);// Save if there any advance payment
				$transaction->account_balance = $client->wallet_balance;
				$transaction->category = 'payment';
				$transaction->quantity = 1;
				$transaction->description = $request->commentary;
				$transaction->date = Carbon::parse($request->date)->format('Y-m-d');
				$transaction->save();
				
				if($invoice->status != 3) {
					CommonService::addWalletPayment($client->id, $invoice->num_bill, $invoice->total_pay, auth()->guard('cashdesk')->user()->id);
				}
			}
		}

		$global = GlobalSetting::first();
        // get details for cortado service
        $cortadoDetails = CommonService::getServiceCortadoDate($client->id);
        $billingDueDate = CommonService::getCortadoDateWithTolerence($client->id, $client->billing_settings->billing_grace_period, $global->tolerance);
        foreach($client->service as $service) {
            if($service->status == 'de' && (now()->startOfDay()->lessThanOrEqualTo($billingDueDate) || $cortadoDetails['paid'])) {
//                $clientServiceController = new ClientServiceController();

                $request = new Request([
                    'id'   => $service->id,
                ]);

                $ok = $this->banService($client->id, $service->id);

                $service->status = 'ac';
                $service->save();
            }

            /*if(!$cortadoDetails['paid'] && $cortadoDetails['cortado_date'] && $service->status == 'ac') {

                if(now()->startOfDay()->greaterThan($billingDueDate)) {
                    $service->status = 'de';
                    $service->save();
                }
            }*/
        }
		
		DB::commit();
		
		return Reply::success('Payment Successfully added.');
	}


	public function banService($client_id, $id){
        $process = new Chkerr();

        $service = ClientService::find($id);
        $client = Client::find($service->client_id);

        //obtenemos la ip del cliente
        $nameClient = $client->name;
        $target = $service->ip;
        $mac = $service->mac;
        $statusClient = $service->status;
        $router_id = $service->router_id;
        $userClient = $service->user_hot;


        $pl = new GetPlan();
        $plan = $pl->get($service->plan_id);
        $burst = Burst::get_all_burst($plan['upload'],$plan['download'],$plan['burst_limit'],$plan['burst_threshold'],$plan['limitat']);

        $namePlan = $plan['name'];
        $maxlimit = $plan['maxlimit'];

        $config = ControlRouter::where('router_id','=',$router_id)->get();

        $typeconf = $config[0]->type_control;

        $arp = $config[0]->arpmac;
        $advs = $config[0]->adv;
        $dhcp = $config[0]->dhcp;

        if ($advs==1) {
            $drop=0;
        }else{
            $drop=1;
        }


        $router = new RouterConnect();
        $con = $router->get_connect($router_id);
        $log = new Slog();

        $data = array(
            'name' => $nameClient.'_'.$service->id,
            'user' => $userClient,
            'ip' => $service->ip,
            'status' => $statusClient,
            'arp' => $arp,
            'adv' => $advs,
            'drop' => $drop,
            'planName' => $namePlan,
            'namePlan' => $plan['name'],
            'mac' => $mac,
            'lan' => $con['lan'],
            //for simple queue with tree
            'plan_id' => $service->plan_id,
            'router_id' => $service->router_id,
            'download' => $plan['download'],
            'upload' => $plan['upload'],
            'maxlimit' => $plan['maxlimit'],
            'aggregation' => $plan['aggregation'],
            'limitat' => $plan['limitat'],
            'bl' => $burst['blu'].'/'.$burst['bld'],
            'bth' => $burst['btu'].'/'.$burst['btd'],
            'bt' => $plan['burst_time'].'/'.$plan['burst_time'],
            'burst_limit' => $plan['burst_limit'],
            'burst_threshold' => $plan['burst_threshold'],
            'burst_time' => $plan['burst_time'],
            'priority' => $plan['priority'].'/'.$plan['priority'],
            'comment' => 'SmartISP - '.$plan['name'],
            'tree_priority' => $service->tree_priority,
            'no_rules' => $plan['no_rules'],

        );

        $service->save();

        $counter = new CountClient();

        if($typeconf=='nc'){

            $STATUS = ClientService::find($client_id);
            $ip = $service->ip;
            if($STATUS->status == 'ac') {
                $st='de';
                $online = 'off';
                $m = "Se ha cortado el servicio de atención al cliente para ip $ip ";
                //descontamos el numero de clientes del plan
                $counter->step_down_plan($client->plan_id);
            }
            else {
                $st='ac';
                $online = 'on';

                $m = "Se ha activado el servicio de atención al cliente para ip $ip";
                //incrementamos el numero de clientes en el plan
                $counter->step_up_plan($client->plan_id);
            }

            // Save history for client ban or active
//            $this->manageCortadoHistory($service, $request);

            $service->status = $st;
            $service->online = $online;



            $service->save();
//            CommonService::log($m, $this->username, 'change' , $this->userId, $service->client_id);


//            if($STATUS=='ac')
//                return $process->show('banned');
//            else
//                return $process->show('unbanned');
        }

        $global = GlobalSetting::all()->first();

        $debug = $global->debug;

        if ($typeconf=='no') {

            //GET all data for API
            $conf = Helpers::get_api_options('mikrotik');
            $API = new Mikrotik($con['port'],$conf['a'],$conf['t'],$conf['s']);
            $API->debug = $conf['d'];

            //Bloqueamos simple
            if ($API->connect($con['ip'], $con['login'], $con['password'])) {


                $rocket = new RocketCore();
                $error = new Mkerror();

                if ($data['status']=='ac') { //esta activo bloqueamos

                    $STATUS = $rocket->set_basic_config($API,$error,$data,$target,null,'block',$debug);

                    if ($debug==1) {
                        if ($STATUS!=false) {
                            return $STATUS;
                        }
                    }

                    $STATUS='true';

                } else {//esta bloqueado activamos

                    $STATUS = $rocket->set_basic_config($API,$error,$data,$target,null,'unblock',$debug);

                    if ($debug==1) {
                        if ($STATUS!=false) {
                            return $STATUS;
                        }
                    }

                    $STATUS='false';
                }

                $API->disconnect();

                $ip = $service->ip;

                if($STATUS=='true' || $STATUS=='ac') {
                    $st='de';
                    $online = 'off';
                    $m = "Se ha cortado el servicio de atención al cliente para ip $ip";
                    //descontamos el numero de clientes del plan
                    $counter->step_down_plan($client->plan_id);
                }
                else {
                    $st='ac';
                    $online = 'on';
                    $m = "Se ha activado el servicio de atención al cliente para ip $ip";
                    //incrementamos el numero de clientes en el plan
                    $counter->step_up_plan($client->plan_id);
                }

                // Save history for client ban or active
//                $this->manageCortadoHistory($service, $request);

                //guardamos en la base de datos
                $service->status = $st;
                $service->online = $online;
                $service->save();

//                $log->save($m,"change",$nameClient);
//                CommonService::log($m, $this->username, 'change' , $this->userId, $service->client_id);

//                if($STATUS=='true' || $STATUS=='ac')
//                    return $process->show('banned');
//                else
//                    return $process->show('unbanned');
            }
//            else
//                return $process->show('errorConnect');

        }

        if($typeconf=='sq'){

            //GET all data for API
            $conf = Helpers::get_api_options('mikrotik');
            $API = new Mikrotik($con['port'],$conf['a'],$conf['t'],$conf['s']);
            $API->debug = $conf['d'];

            //Bloqueamos simple
            if ($API->connect($con['ip'], $con['login'], $con['password'])) {

                $rocket = new RocketCore();

                $STATUS = $rocket->block_simple_queues($API,$data,$target,$debug);

                if ($debug==1) {
                    if($process->check($STATUS)){
                        $API->disconnect();
                        return $process->check($STATUS);
                    }
                }

                $API->disconnect();

                $ip = $service->ip;

                if($STATUS=='true' || $STATUS=='ac'){
                    $st='de';
                    $online = 'off';
                    $m = "Se ha cortado el servicio de atención al cliente para ip $ip ";
                    //descontamos el numero de clientes del plan
                    $counter->step_down_plan($client->plan_id);
                } else {
                    $st='ac';
                    $online = 'on';
                    $m = "Se ha activado el servicio de atención al cliente para ip $ip ";
                    //incrementamos el numero de clientes en el plan
                    $counter->step_up_plan($client->plan_id);
                }

                // Save history for client ban or active
//                $this->manageCortadoHistory($service, $request);

                //guardamos en la base de datos
                $service->status = $st;
                $service->online = $online;
                $service->save();
//                CommonService::log($m, $this->username, 'change' , $this->userId, $service->client_id);

//                if($STATUS=='true' || $STATUS=='ac')
//                    return $process->show('banned');
//                else
//                    return $process->show('unbanned');
            }
//            else
//                return $process->show('errorConnect');
        }

        if($typeconf=='st'){

            //GET all data for API
            $conf = Helpers::get_api_options('mikrotik');
            $API = new Mikrotik($con['port'],$conf['a'],$conf['t'],$conf['s']);
            $API->debug = $conf['d'];

            //Bloqueamos simple
            if ($API->connect($con['ip'], $con['login'], $con['password'])) {
                $ip = $service->ip;
                if($statusClient=='ac'){
                    $st='de';
                    $online = 'off';
                    $m = "Se ha cortado el servicio de atención al cliente para ip $ip";
                    //descontamos el numero de clientes del plan
                    $counter->step_down_plan($client->plan_id);
                }
                else{
                    $st='ac';
                    $online = 'on';
                    $m = "Se ha activado el servicio de atención al cliente para ip $ip";
                    //incrementamos el numero de clientes en el plan
                    $counter->step_up_plan($client->plan_id);
                }

                // Save history for client ban or active
//                $this->manageCortadoHistory($service, $request);

                //guardamos en la base de datos
                $service->status = $st;
                $service->online = $online;
                $service->save();
//                CommonService::log($m, $this->username, 'change' , $this->userId, $service->client_id);

                $rocket = new RocketCore();

                $STATUS = $rocket->block_simple_queue_with_tree($API,$data,$target,$debug);

                if ($debug==1) {
                    if($process->check($STATUS)){
                        $API->disconnect();
                        return $process->check($STATUS);
                    }
                }

                $API->disconnect();

//                if($STATUS=='true' || $STATUS=='ac')
//                    return $process->show('banned');
//                else
//                    return $process->show('unbanned');
            }
//            else
//                return $process->show('errorConnect');
        }

        if ($typeconf=='dl') {
            //bloqueamos hotspot

            //GET all data for API
            $conf = Helpers::get_api_options('mikrotik');
            $API = new Mikrotik($con['port'],$conf['a'],$conf['t'],$conf['s']);
            $API->debug = $conf['d'];

            if ($API->connect($con['ip'], $con['login'], $con['password'])) {

                $rocket = new RocketCore();

                $STATUS = $rocket->block_dhcp_lease($API,$data,$target,$debug);

                if ($debug==1) {
                    if($process->check($STATUS)){
                        $API->disconnect();
                        return $process->check($STATUS);
                    }
                }

                $API->disconnect();

                $ip = $service->ip;

                if($STATUS=='true' || $STATUS=='ac'){
                    $st='de';
                    $online='off';
                    $m = "Se ha cortado el servicio de atención al cliente para ip $ip";
                    //descontamos el numero de clientes del plan
                    $counter->step_down_plan($client->plan_id);
                } else {
                    $st='ac';
                    $online='on';
                    $m = "Se ha activado el servicio de atención al cliente para ip $ip";
                    //incrementamos el numero de clientes en el plan
                    $counter->step_up_plan($client->plan_id);
                }

                // Save history for client service ban or active
//                $this->manageCortadoHistory($service, $request);


                //guardamos en la base de datos
                $service->status = $st;
                $service->online = $online;
                $service->save();
//                CommonService::log($m, $this->username, 'change' , $this->userId, $service->client_id);

//                if($STATUS=='true' || $STATUS=='ac')
//                    return $process->show('banned');
//                else
//                    return $process->show('unbanned');

            }
//            else
//                return $process->show('errorConnect');
        }

        if ($typeconf=='pt') {
            //bloqueamos pppoe simple queue with tree

            //GET all data for API
            $conf = Helpers::get_api_options('mikrotik');
            $API = new Mikrotik($con['port'],$conf['a'],$conf['t'],$conf['s']);
            $API->debug = $conf['d'];

            if ($API->connect($con['ip'], $con['login'], $con['password'])) {


                $rocket = new RocketCore();

                $STATUS = $rocket->block_ppp($API,$data,$target,$debug);

                if ($debug==1) {
                    if($process->check($STATUS)){
                        $API->disconnect();
                        return $process->check($STATUS);
                    }
                }
                $ip = $service->ip;
                if($STATUS=='true' || $STATUS=='ac'){
                    $st='de';
                    $online='off';
                    $m = "Se ha cortado el servicio de atención al cliente para ip $ip";
                    //descontamos el numero de clientes del plan
                    $counter->step_down_plan($client->plan_id);
                }
                else{
                    $st='ac';
                    $online='on';
                    $m = "Se ha activado el servicio de atención al cliente para ip $ip";
                    //incrementamos el numero de clientes en el plan
                    $counter->step_up_plan($client->plan_id);
                }

                // Save history for client service ban or active
//                $this->manageCortadoHistory($service, $request);

                //guardamos en la base de datos
                $service->status = $st;
                $service->online = $online;
                $service->save();

//                CommonService::log($m, $this->username, 'change' , $this->userId, $service->client_id);


                $STATUS = $rocket->block_simple_queue_with_tree($API,$data,$target,$debug);

                if ($debug==1) {
                    if($process->check($STATUS)){
                        $API->disconnect();
                        return $process->check($STATUS);
                    }
                }

                $API->disconnect();


//                if($STATUS=='true' || $STATUS=='ac')
//                    return $process->show('banned');
//                else
//                    return $process->show('unbanned');

            }
//            else
//                return $process->show('errorConnect');
        }

        if ($typeconf=='pp' || $typeconf=='ps') {
            //bloqueamos pppoe

            //GET all data for API
            $conf = Helpers::get_api_options('mikrotik');
            $API = new Mikrotik($con['port'],$conf['a'],$conf['t'],$conf['s']);
            $API->debug = $conf['d'];

            if ($API->connect($con['ip'], $con['login'], $con['password'])) {


                $rocket = new RocketCore();

                $STATUS = $rocket->block_ppp($API,$data,$target,$debug);

                if ($debug==1) {
                    if($process->check($STATUS)){
                        $API->disconnect();
                        return $process->check($STATUS);
                    }
                }

                $API->disconnect();
                $ip = $service->ip;
                if($STATUS=='true' || $STATUS=='ac'){
                    $st='de';
                    $online='off';
                    $m = "Se ha cortado el servicio de atención al cliente para ip $ip";
                    //descontamos el numero de clientes del plan
                    $counter->step_down_plan($client->plan_id);
                }
                else{
                    $st='ac';
                    $online='on';
                    $m = "Se ha activado el servicio de atención al cliente para ip $ip";
                    //incrementamos el numero de clientes en el plan
                    $counter->step_up_plan($client->plan_id);
                }

                // Save history for client service ban or active
//                $this->manageCortadoHistory($service, $request);

                //guardamos en la base de datos
                $service->status = $st;
                $service->online = $online;
                $service->save();
//                CommonService::log($m, $this->username, 'change' , $this->userId, $service->client_id);
//
//                if($STATUS=='true' || $STATUS=='ac')
//                    return $process->show('banned');
//                else
//                    return $process->show('unbanned');

            }
//            else
//                return $process->show('errorConnect');
        }

        if ($typeconf=='pa') {
            //bloqueo PPP-PCQ
            //GET all data for API
            $conf = Helpers::get_api_options('mikrotik');
            $API = new Mikrotik($con['port'],$conf['a'],$conf['t'],$conf['s']);
            $API->debug = $conf['d'];

            if ($API->connect($con['ip'], $con['login'], $con['password'])) {

                $rocket = new RocketCore();

                //$num_cli = Helpers::getnumcl($router_id,$typeconf,$client->plan_id);
                $num_cli = Helpers::getnumcl($router_id,$typeconf,$service->plan_id); /**fix 19/06**/
                //opcion avanzada burst del plan
                $burst = Burst::get_all_burst($plan['upload'],$plan['download'],$plan['burst_limit'],$plan['burst_threshold'],$plan['limitat']);

                $advanced_data = array(
                    'name' => $nameClient.'_'.$service->id,
                    'user' => $userClient,
                    'status' => $statusClient,
                    'arp' => $arp,
                    'adv' => $advs,
                    'dhcp' => $dhcp,
                    'drop' => $drop,
                    'mac' => $mac,
                    'lan' => $con['lan'],
                    'namePlan' => $namePlan,
                    'num_cl' => $num_cli,
                    'speed_down' => $plan['download'],
                    'speed_up' => $plan['upload'],
                    //advanced for pcq
                    'priority_a' => $plan['priority'],
                    'rate_down' => $plan['download'].'k',
                    'rate_up' => $plan['upload'].'k',
                    'burst_rate_down' => $burst['bld'],
                    'burst_rate_up' => $burst['blu'],
                    'burst_threshold_down' => $burst['btd'],
                    'burst_threshold_up' => $burst['btu'],
                    'limit_at_down' => $burst['lim_at_down'],
                    'limit_at_up' => $burst['lim_at_up'],
                    'burst_time' => $plan['burst_time'],
                    'no_rules' => $plan['no_rules'],
                );


                $STATUS = $rocket->block_ppp_secrets_pcq($API,$advanced_data,$target,$debug);

                if ($debug==1) {
                    if($process->check($STATUS)){
                        $API->disconnect();
                        return $process->check($STATUS);
                    }
                }


                $API->disconnect();

                $ip = $service->ip;
                if($STATUS=='true' || $STATUS=='ac'){
                    $st='de';
                    $online='off';
                    $m = "Se ha cortado el servicio de atención al cliente para ip $ip";
                    //descontamos el numero de clientes del plan
                    $counter->step_down_plan($client->plan_id);
                }
                else{
                    $st='ac';
                    $online ='on';
                    $m = "Se ha activado el servicio de atención al cliente para ip $ip";
                    //incrementamos el numero de clientes en el plan
                    $counter->step_up_plan($client->plan_id);
                }

                // Save history for client service ban or active
//                $this->manageCortadoHistory($service, $request);

                //guardamos en la base de datos
                $service->status = $st;
                $service->online = $online;
                $service->save();
//                CommonService::log($m, $this->username, 'change' , $this->userId, $service->client_id);
//
//                if($STATUS=='true' || $STATUS=='ac')
//                    return $process->show('banned');
//                else
//                    return $process->show('unbanned');

            }
//            else
//                return $process->show('errorConnect');


        }

        if ($typeconf=='pc') {
            //bloqueamos PCQ

            //GET all data for API
            $conf = Helpers::get_api_options('mikrotik');
            $API = new Mikrotik($con['port'],$conf['a'],$conf['t'],$conf['s']);
            $API->debug = $conf['d'];
            if ($API->connect($con['ip'], $con['login'], $con['password'])) {

                $rocket = new RocketCore();

                $num_cli = Helpers::getnumcl($router_id,$typeconf,$service->plan_id); /**fix 19/06**/

                //opcion avanzada burst del plan
                $burst = Burst::get_all_burst($plan['upload'],$plan['download'],$plan['burst_limit'],$plan['burst_threshold'],$plan['limitat']);

                $advanced_data = array(

                    'name' => $nameClient.'_'.$service->id,
                    'status' => $statusClient,
                    'arp' => $arp,
                    'adv' => $advs,
                    'dhcp' => $dhcp,
                    'drop' => $drop,
                    'mac' => $mac,
                    'lan' => $con['lan'],
                    'namePlan' => $namePlan,
                    'num_cl' => $num_cli,
                    'speed_down' => $plan['download'],
                    'speed_up' => $plan['upload'],
                    //advanced for pcq
                    'priority_a' => $plan['priority'],
                    'rate_down' => $plan['download'].'k',
                    'rate_up' => $plan['upload'].'k',
                    'burst_rate_down' => $burst['bld'],
                    'burst_rate_up' => $burst['blu'],
                    'burst_threshold_down' => $burst['btd'],
                    'burst_threshold_up' => $burst['btu'],
                    'limit_at_down' => $burst['lim_at_down'],
                    'limit_at_up' => $burst['lim_at_up'],
                    'burst_time' => $plan['burst_time'],
                    'no_rules' => $plan['no_rules'],

                );

                $STATUS = $rocket->block_pcq($API,$advanced_data,$target,$debug);

                if ($debug==1) {
                    if($process->check($STATUS)) {
                        $API->disconnect();
                        return $process->check($STATUS);
                    }
                }

                $API->disconnect();
                $ip = $service->ip;
                if($STATUS=='true' || $STATUS=='ac') {
                    $st='de';
                    $online='off';
                    $m = "Se ha cortado el servicio de atención al cliente para ip $ip";
                    //descontamos el numero de clientes del plan
                    $counter->step_down_plan($client->plan_id);
                }
                else {
                    $st='ac';
                    $online='on';
                    $m = "Se ha activado el servicio de atención al cliente para ip $ip";
                    //incrementamos el numero de clientes en el plan
                    $counter->step_up_plan($client->plan_id);
                }

                // Save history for client service ban or active
//                $this->manageCortadoHistory($service, $request);

                //guardamos en la base de datos
                $service->status = $st;
                $service->online = $online;
                $service->save();
//                CommonService::log($m, $this->username, 'change' , $this->userId, $service->client_id);

//                if($STATUS=='true' || $STATUS=='ac')
//                    return $process->show('banned');
//                else
//                    return $process->show('unbanned');

            }
//            else
//                return $process->show('errorConnect');


        }

        if($typeconf=='ra' || $typeconf == 'rp' || $typeconf == 'rr'){

            /**TODO: cuando este testeado y funcionando correctamente, lo que vamos hacer es refactorizar y poner en typeconf un || para los dos casos, asi no replicamos todo esto**/
            /**bloqueamos en Radius y ademas bloqueamos segun el tipo que es, replicando el pa,pt,ps**/
            $existe = Radgroupcheck::where('groupname','locked')->where('attribute','Auth-Type')->where('value','Reject')->first();
            if(!$existe){
                Radgroupcheck::create([
                    'groupname' => 'locked',
                    'attribute' => 'Auth-Type',
                    'op' => ':=',
                    'value' => 'Reject',

                ]);
            }

            if($typeconf=='ra'){ /**aplicamos el mismo caso que pt**/
                //bloqueamos pppoe simple queue with tree

                //GET all data for API
                $conf = Helpers::get_api_options('mikrotik');
                $API = new Mikrotik($con['port'],$conf['a'],$conf['t'],$conf['s']);
                $API->debug = $conf['d'];

                if ($API->connect($con['ip'], $con['login'], $con['password'])) {


                    $rocket = new RocketCore();

                    $STATUS = $rocket->block_ppp($API,$data,$target,$debug);

                    if ($debug==1) {
                        if($process->check($STATUS)){
                            $API->disconnect();
                            return $process->check($STATUS);
                        }
                    }
                    $ip = $service->ip;
                    if($STATUS=='true' || $STATUS=='ac'){
                        $st='de';
                        $online='off';
                        $m = "Se ha cortado el servicio de atención al cliente para ip $ip";
                        //descontamos el numero de clientes del plan
                        $counter->step_down_plan($client->plan_id);

                        Radusergroup::create([
                            'username' => $service->user_hot,
                            'groupname' => 'locked',
                            'priority' => 1
                        ]);

                    }
                    else{
                        $st='ac';
                        $online='on';
                        $m = "Se ha activado el servicio de atención al cliente para ip $ip";
                        //incrementamos el numero de clientes en el plan
                        $counter->step_up_plan($client->plan_id);

                        Radusergroup::where('username',$service->user_hot)->delete();

                    }

                    // Save history for client service ban or active
//                    $this->manageCortadoHistory($service, $request);

                    //guardamos en la base de datos
                    $service->status = $st;
                    $service->online = $online;
                    $service->save();

//                    CommonService::log($m, $this->username, 'change' , $this->userId, $service->client_id);


                    $STATUS = $rocket->block_simple_queue_with_tree($API,$data,$target,$debug);

                    if ($debug==1) {
                        if($process->check($STATUS)){
                            $API->disconnect();
                            return $process->check($STATUS);
                        }
                    }

                    $API->disconnect();


//                    if($STATUS=='true' || $STATUS=='ac')
//                        return $process->show('banned');
//                    else
//                        return $process->show('unbanned');

                }
//                else
//                    return $process->show('errorConnect');
            }
            if($typeconf=='rp'){ /**aplicamos el mismo caso que pa**/
                //bloqueo PPP-PCQ
                //GET all data for API
                $conf = Helpers::get_api_options('mikrotik');
                $API = new Mikrotik($con['port'],$conf['a'],$conf['t'],$conf['s']);
                $API->debug = $conf['d'];

                if ($API->connect($con['ip'], $con['login'], $con['password'])) {

                    $rocket = new RocketCore();

                    //$num_cli = Helpers::getnumcl($router_id,$typeconf,$client->plan_id);
                    $num_cli = Helpers::getnumcl($router_id,$typeconf,$service->plan_id); /**fix 19/06**/
                    //opcion avanzada burst del plan
                    $burst = Burst::get_all_burst($plan['upload'],$plan['download'],$plan['burst_limit'],$plan['burst_threshold'],$plan['limitat']);

                    $advanced_data = array(
                        'name' => $nameClient.'_'.$service->id,
                        'user' => $userClient,
                        'status' => $statusClient,
                        'arp' => $arp,
                        'adv' => $advs,
                        'dhcp' => $dhcp,
                        'drop' => $drop,
                        'mac' => $mac,
                        'lan' => $con['lan'],
                        'namePlan' => $namePlan,
                        'num_cl' => $num_cli,
                        'speed_down' => $plan['download'],
                        'speed_up' => $plan['upload'],
                        //advanced for pcq
                        'priority_a' => $plan['priority'],
                        'rate_down' => $plan['download'].'k',
                        'rate_up' => $plan['upload'].'k',
                        'burst_rate_down' => $burst['bld'],
                        'burst_rate_up' => $burst['blu'],
                        'burst_threshold_down' => $burst['btd'],
                        'burst_threshold_up' => $burst['btu'],
                        'limit_at_down' => $burst['lim_at_down'],
                        'limit_at_up' => $burst['lim_at_up'],
                        'burst_time' => $plan['burst_time'],
                        'no_rules' => $plan['no_rules'],
                    );


                    $STATUS = $rocket->block_ppp_secrets_pcq($API,$advanced_data,$target,$debug);

                    if ($debug==1) {
                        if($process->check($STATUS)){
                            $API->disconnect();
                            return $process->check($STATUS);
                        }
                    }


                    $API->disconnect();
                    $ip = $service->ip;

                    if($STATUS=='true' || $STATUS=='ac'){
                        $st='de';
                        $online='off';
                        $m = "Se ha cortado el servicio de atención al cliente para ip $ip";
                        //descontamos el numero de clientes del plan
                        $counter->step_down_plan($client->plan_id);
                        Radusergroup::create([
                            'username' => $service->user_hot,
                            'groupname' => 'locked',
                            'priority' => 1
                        ]);

                    }
                    else{
                        $st='ac';
                        $online ='on';
                        $m = "Se ha activado el servicio de atención al cliente para ip $ip";
                        //incrementamos el numero de clientes en el plan
                        $counter->step_up_plan($client->plan_id);

                        Radusergroup::where('username',$service->user_hot)->delete();
                    }

                    // Save history for client service ban or active
//                    $this->manageCortadoHistory($service, $request);

                    //guardamos en la base de datos
                    $service->status = $st;
                    $service->online = $online;
                    $service->save();
//                    CommonService::log($m, $this->username, 'change' , $this->userId, $service->client_id);

//                    if($STATUS=='true' || $STATUS=='ac')
//                        return $process->show('banned');
//                    else
//                        return $process->show('unbanned');

                }
//                else
//                    return $process->show('errorConnect');

            }
            if($typeconf=='rr'){ /**aplicamos el mismo caso que ps**/

                //bloqueamos pppoe

                //GET all data for API
                $conf = Helpers::get_api_options('mikrotik');
                $API = new Mikrotik($con['port'],$conf['a'],$conf['t'],$conf['s']);
                $API->debug = $conf['d'];

                if ($API->connect($con['ip'], $con['login'], $con['password'])) {

                    $rocket = new RocketCore();

                    /**si es radius no tiene secret en el mkt**/
                    // $STATUS = $rocket->block_ppp($API,$data,$target,$debug);

                    if ($debug==1) {
                        if($process->check($data['status'])){
                            $API->disconnect();
                            return $process->check($data['status']);
                        }
                    }

                    $API->disconnect();
                    $ip = $service->ip;
                    if($data['status']=='true' || $data['status']=='ac'){

                        // con el comando coa mandamos a desconectar al cliente
                        $secret = Router::find($router_id)->radius->secret;
                        $ip_ro = Router::find($router_id)->ip;

                        Radusergroup::create([
                            'username' => $service->user_hot,
                            'groupname' => 'locked',
                            'priority' => 1
                        ]);

                        $st='de';
                        $online='off';
                        $m = "Se ha cortado el servicio de atención al cliente para ip $ip";
                        //descontamos el numero de clientes del plan
                        $counter->step_down_plan($client->plan_id);

                        $ejecucion = shell_exec('echo User-Name="'.$service->user_hot.'" | /usr/bin/radclient -c 1 -n 3 -r 3 -t 3 -x '.$ip_ro.':3799 disconnect '.$secret.' 2>&1');

                    }
                    else{
                        $st='ac';
                        $online='on';
                        $m = "Se ha activado el servicio de atención al cliente para ip $ip";
                        //incrementamos el numero de clientes en el plan
                        $counter->step_up_plan($client->plan_id);

                        Radusergroup::where('username',$service->user_hot)->delete();
                    }

                    // Save history for client service ban or active
//                    $this->manageCortadoHistory($service, $request);

                    //guardamos en la base de datos
                    $service->status = $st;
                    $service->online = $online;
                    $service->save();
//                    CommonService::log($m, $this->username, 'change' , $this->userId, $service->client_id);

//                    if($data['status']=='true' || $data['status']=='ac')
//                        return $process->show('banned');
//                    else
//                        return $process->show('unbanned');

                }
//                else
//                    return $process->show('errorConnect');


            }


        }
    }
}
