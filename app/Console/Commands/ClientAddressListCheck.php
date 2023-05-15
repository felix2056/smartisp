<?php

namespace App\Console\Commands;

use App\libraries\Chkerr;
use App\libraries\Helpers;
use App\libraries\Mikrotik;
use App\libraries\Mkerror;
use App\libraries\PermitidosList;
use App\libraries\RouterConnect;
use App\models\Client;
use App\models\ControlRouter;
use App\models\GlobalSetting;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class ClientAddressListCheck extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'set-client-address-list';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check client is into address list or not';

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
	    $global = GlobalSetting::first();
	    Client::with('service')
		    ->chunkById(5, function($clients) use($global) {
			    foreach($clients as $client) {
				    $this->info($client->name);
				    foreach($client->service as $service) {
					    $conf = Helpers::get_api_options('mikrotik');
					    $router = new RouterConnect();
					    $con = $router->get_connect($service->router_id);
					    $API = new Mikrotik($con['port'], $conf['a'], $conf['t'], $conf['s']);
					    $API->debug = $conf['d'];
					    $debug = $global->debug;
					
					    $type = ControlRouter::where('router_id', '=', $service->router_id)->first();
					    $error = new Mkerror();
					    if ($API->connect($con['ip'], $con['login'], $con['password'])) {
						    if($type->address_list == 1) {
							    $data = [
								    'name' => $client->name.'_'.$service->id,
								    'address' => $service->ip
							    ];
							    PermitidosList::AddAddressList($API,$data, $debug, $error);
						    }
					    }
				    }
			    }
	        });

    }
}
