<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

use App\Http\Controllers\SecurityController;
use App\libraries\CheckInstall;
use App\libraries\Getip;
use App\libraries\Pencrypt;
use App\libraries\Slog;
use App\models\CashierDepositHistory;
use App\models\GlobalSetting;
use App\models\Plan;
use App\models\SmartBandwidth;
use App\models\UpdateHome;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\View;
use App\models\Client;
use App\models\Transaction;
use Carbon\Carbon;
use App\Classes\Reply;
use App\Http\Controllers\CrtokenController;
use App\models\Invoice;
use App\Http\Controllers\PermissionsController;

Route::get('/', 'LoginClientController@login')->name('login');
Route::get('/cashdesk/login', 'LoginCashdeskController@login')->name('login-form');
Route::post('/cashdesk/login', 'LoginCashdeskController@postLogin')->name('cashdesk.login');

Route::post('check-cert', 'InvoiceController@checkCert');


Route::get('admin', array('before' => 'installed', function () {
    if (CheckInstall::check()) {
        $ip = new Getip();
        return "Error: No se encuentra el archivo db_conf.php";
    }

    if (Auth::check())
        return Redirect::to('dashboard');

    $expira_En = gmdate('D, d M Y H:i:s', time() + 60) . ' GMT';
    $contents = View::make('auth.login');
    $response = Response::make($contents, 200);
    $response->header('Expires', $expira_En);
    $response->header('Cache-control', 'no-store, no-cache, must-revalidate, post-check=0, pre-check=0');
    $response->header('Pragma', 'no-cache');
    return $response;
}));


Route::get('logout', function () {
    if (isset(Auth::user()->level)) {


        if (Auth::user()->level == 'ad')
@setcookie("hcmd", 'kR2RsakY98pHL', time() + 0, "/", "", 0, true);

        //registramos en logs
        $log = new Slog();
        //save log
        $log->save("Ha cerrado sesión en el sistema", "info");

        Auth::logout();
        return Redirect::to('admin');
    } else {
        return Redirect::to('admin');
    }
});

Route::get('clientlogout', function () {

    $en = new Pencrypt();
@setcookie("Ruser", $en->encode(Session::get('Ruser')), time() - 60 * 60 * 24 * 6004, "/");
    Session::forget('Ruser');

    return Redirect::to('/');
});

Route::get('cashdesk/logout', function () {

    $en = new Pencrypt();
@setcookie("Cuser", $en->encode(Session::get('Cuser')), time() - 60 * 60 * 24 * 6004, "/");
    Session::forget('Cuser');

    Auth::guard('cashdesk')->logout();
    return Redirect::to('/cashdesk/login');
});

Route::post('auth', 'UserLogin@user')->name('auth');

Route::any('auth2', 'ClientLogin@user');



//cashdesk after login routes group
Route::prefix('cashdesk')->name('cashdesk.')->middleware(['auth:cashdesk'])->group(function () {

	Route::get('dashboard', array(function () {

		$level = \auth()->guard('cashdesk')->user()->level;
		$status = \auth()->guard('cashdesk')->user()->status;
		$company = GlobalSetting::first();

		$totalPayments = CashierDepositHistory::where('user_id', \auth()->guard('cashdesk')->user()->id)->get()->sum('amount');


		if($level == 'cs' && $status == true) {

			return \view('cashdesk/dashboard/index', ['company' => $company->company, 'payments' => $totalPayments]);

		} else {
			Auth::logout();
			$en = new Pencrypt();
			@setcookie("Cuser", $en->encode(Session::get('Cuser')), time() - 60 * 60 * 24 * 6004, "/");
			Session::forget('Cuser');		return Redirect::to('cashdesk/login')->with('block_user', true);
		}
	}))->name('dashboard');

	Route::get('history', 'Cashdesk\CashdeskDepositController@index')->name('history');
	Route::get('deposits/create', 'Cashdesk\CashdeskDepositController@create')->name('deposits.create');
	Route::get('search/client', 'Cashdesk\CashdeskDepositController@searchClient')->name('search.client');
	Route::get('search-by-invoice', 'Cashdesk\CashdeskDepositController@searchByInvoice')->name('search-by-invoice');
	Route::get('search-by-client-name', 'Cashdesk\CashdeskDepositController@searchByClientName')->name('search-by-client-name');
	Route::get('search-by-client-email', 'Cashdesk\CashdeskDepositController@searchByClientEmail')->name('search-by-client-email');
	Route::get('search-by-client-dni', 'Cashdesk\CashdeskDepositController@searchByClientDni')->name('search-by-client-dni');
	Route::get('search/client/{id}', 'Cashdesk\CashdeskDepositController@clientData')->name('search-by-client-data');
	Route::post('deposit/store', 'Cashdesk\CashdeskDepositController@addDeposit')->name('deposit.payments.store');

	Route::get('invoice/print/{id}', 'BillPrintController@printInvoicePDF')->name('invoice.prints');
	Route::get('invoice/show-pdf/{id}', 'BillPrintController@showInvoicePDF')->name('invoices.showPDF');
	Route::post('invoice/send-email/{id}', 'BillPrintController@SendInvoicePDF')->name('invoices.sendEmail');

});
Route::get('cashdesk/search-by-client-name', 'Cashdesk\CashdeskDepositController@searchByClientName')->name('cashdesk.search-by-client-name');



//region client profile routes
Route::get('cashdesk/myprofile', 'ProfileCashdeskController@getIndex')->middleware('auth:cashdesk');;
Route::post('cashdesk/myprofile/update', 'ProfileCashdeskController@postUpdate')->middleware('auth:cashdesk');;
//endregion

Route::get('dashboard', array('before' => 'auth', function () {


	$info_c = UpdateHome::where('id', 1)->first();
	if ($info_c->corrida == 0) {

		$plans = Plan::all();
		foreach ($plans as $plan) {
			$SMB = new SmartBandwidth();
			$SMB->plan_id = $plan->id;
			$SMB->start_time = '00:00:00';
			$SMB->end_time = '00:00:00';
			$SMB->mode = 'd';
			$SMB->days = 'all';
			$SMB->bandwidth = 0;
			$SMB->for_all = 0;
			$SMB->save();
		}
		$info_c->corrida = 1;
		$info_c->save();

	}

	$id = Auth::user()->id;
	$level = Auth::user()->level;

	if($level == 'cs') {
		Auth::logout();
		$en = new Pencrypt();
		@setcookie("Cuser", $en->encode(Session::get('Cuser')), time() - 60 * 60 * 24 * 6004, "/");
		Session::forget('Cuser');
		return Redirect::to('cashdesk/login');
	}

	$status = Auth::user()->status;
	$arraySta = array('status' => '2000', 'mensaje' => 'Todo bien');
	if ($status == true || $level == 'ad') {

		$estado_li = 'ok';
		Session::put('licencia', 'ok');
		$global = GlobalSetting::all()->first();
		if ($global->license_id != '0') {
			$detalle_licencia = SecurityController::status_licencia($global->license_id);
			if ($detalle_licencia['status'] == '200') {
				// $detalle_licencia['license']='expired';
				if ($detalle_licencia['license'] != 'expired') {
					$now = new \DateTime();
					$fecha_ex = $detalle_licencia['expires'];
					$date = new DateTime($fecha_ex);
					$fecha_actual = $now->format('Y-m-d');
					$fecha_expiracion = $date->format('Y-m-d');

					$fecha1 = new DateTime($fecha_actual);
					$fecha2 = new DateTime($fecha_expiracion);
					$resultado = $fecha1->diff($fecha2);
					$dias_restantes = $resultado->format('%a');

					if ($global->license == '17' or $global->license == '11111') {
						$message = trans('messages.demoLicenseExpireInDays', ['day' => $dias_restantes, 'buy' => "<a target='_blank' class='comprar_text' href='https://www.smartisp.us/precios/'>" . __('app.buy') . "</a>"]);
						$arraySta = array('status' => '200', 'mensaje' => $message);

					} else {

						if ($dias_restantes <= 10) {
							$message = trans('messages.demoLicenseExpireInDaysSubscription', ['day' => $dias_restantes, 'buy' => "<a target='_blank' class='comprar_text' href='https://www.smartisp.us/precios/'>" . __('app.buy') . "</a> "]);
							$arraySta = array('status' => '200', 'mensaje' => $message);
						}

					}
				} else {
					//Espirada
					$estado_li = 'expired';
					Session::put('licencia', $estado_li);
					$arraySta = array('status' => '403', 'mensaje' => __('app.licenseExpire') . "<a target='_blank' class='comprar_text' href='https://www.smartisp.us/precios/'>" . __('app.buy') . "</a>");
				}
			} else {
				$arraySta = array('status' => '403', 'mensaje' => __('messages.enterLicenseToUseSystem') . '<a class="licencia_text" href="/license">AQUI</a>.');
			}


		} else {
			$Licencia = GlobalSetting::find(1);
			$Licencia->status = 'in';
			$Licencia->save();
			//No ingresada
			$arraySta = array('status' => '403', 'mensaje' => __('messages.enterLicenseToUseSystem') . '<a class="licencia_text" href="/license">AQUI</a>.');
		}

		$perm = DB::table('permissions')->where('user_id', '=', $id)->get();


		$app_checks_incidents = DB::table('app_checks_incidents')->where('status', '=', '3')->count();
		$app_servers_incidents = DB::table('app_servers_incidents')->where('status', '=', '3')->count();


		$data = array(
			"clients" => $perm[0]->access_clients,
			"plans" => $perm[0]->access_plans,
			"routers" => $perm[0]->access_routers,
			"users" => $perm[0]->access_users,
			"system" => $perm[0]->access_system,
			"bill" => $perm[0]->access_pays,
			"template" => $perm[0]->access_templates,
			"ticket" => $perm[0]->access_tickets,
			"sms" => $perm[0]->access_sms,
			"reports" => $perm[0]->access_reports,
			"estado_financier" => $perm[0]->estado_financier,
			"st" => $global->status,
			"lv" => $global->license,
			"v" => $global->version,
			"status_licen" => $arraySta,
			"company" => $global->company,
			"estado_lic" => $estado_li,
			"ms" => $global->message,
			"permissions" => $perm->first(),
			"app_checks_incidents" => $app_checks_incidents,
			"app_servers_incidents" => $app_servers_incidents,
		);


		// Must be already set
		Session::put('username2', $id);
		$contents = View::make('dashboard.admin', $data);
		$response = Response::make($contents, 200);
		$response->header('Expires', 'Tue, 1 Jan 1980 00:00:00 GMT');
		$response->header('Cache-Control', 'no-store, no-cache, must-revalidate, post-check=0, pre-check=0');
		$response->header('Pragma', 'no-cache');
		return $response;
	} else {
		Auth::logout();
		return Redirect::to('admin')->with('block_user', true);
	}
}))->middleware('auth');


Route::get('aviso', 'GetAdviceController@adv');

// cron tab functions

Route::any('guardar_crn_consumo', 'GetClientController@TraficGuardar');

Route::any('crnkl32jd93t', 'CrtokenController@starcrn');

Route::any('crncl32jd94t', 'CrtokenController@starnotifcrn');

Route::any('crncl32jd92t', 'CrtokenController@waitingsms');

Route::any('crnc31hy55t', 'CrtokenController@checkonline');

Route::match(['get', 'post'], 'whatsapp-webhook', 'CrtokenController@whatsappWebhook');

Route::any('crncl3426b', 'AutomaticTasksController@startbackup');

Route::any('crnque432g', 'AutomaticTasksController@startqueues');

Route::any('crnsmb352', 'AutomaticTasksController@startsmartbandwidth');

Route::any('router-status', 'RoutersController@routerStatus')->name('admin.router-status');

//Session::get('licencia')
Route::group(['middleware' => ['auth']], function () {

    Route::group(['middleware' => ['license-expire']], function () {
	
	    Route::group(['prefix' => 'clients'], function () {
		    Route::get('update-balance/{id}', 'ClientsController@editBalance')->name('client.edit-balance');
		    Route::post('update-balance/{id}', 'ClientsController@updateBalance')->name('client.update-balance-submit');
		    Route::get('update-pending-invoice/{id}', 'ClientsController@editPendingInvoice')->name('client.edit-pending-invoice');
		    Route::post('update-pending-invoice/{id}', 'ClientsController@updatePendingInvoice')->name('client.update-pending-invoice-submit');
		    Route::get('billing/{user_id}', 'BillingController@index')->name('billing');
		    Route::post('billing/settings/{client}', 'BillingController@update')->name('billing.settings');
		    Route::post('billing/page/{client}', 'BillingController@page')->name('billing.pages');
		    Route::post('billings/settings/{id}', 'BillingController@saveNotes')->name('billing.saveNotes');
		
		    Route::get('services/create/{client}', ['as' => 'billing.services.create', 'uses' => 'ClientServiceController@create']);
		    Route::get('services/ban-history/{id}', ['as' => 'billing.services.ban-history', 'uses' => 'ClientServiceController@banHistory']);
		    Route::post('services/list', ['as' => 'billing.services.list', 'uses' => 'ClientServiceController@list']);
		    Route::post('services/{client}/store', ['as' => 'billing.services.store', 'uses' => 'ClientServiceController@store']);
		    Route::get('services/edit/{id}', ['as' => 'billing.services.edit', 'uses' => 'ClientServiceController@edit']);
		    Route::post('services/update/{id}', ['as' => 'billing.services.update', 'uses' => 'ClientServiceController@update']);
		    Route::post('services/ban/{id}', ['as' => 'billing.services.ban', 'uses' => 'ClientServiceController@postBanService']);
		    Route::post('services/destroy/{id}', ['as' => 'billing.service/delete', 'uses' => 'ClientServiceController@postDelete']);
                    Route::patch('services/{id}/geo-json', ['as' => 'client.service.geo-json', 'uses' => 'ClientServiceController@saveGeoJson']);
		
		    // documents routes
		    Route::get('contracts/create/{id}', 'DocumentController@contractCreate')->name('contracts.create');
		    Route::post('contracts/store', 'DocumentController@contractStore')->name('contracts.store');
		    Route::get('contracts/edit/{id}', 'DocumentController@contractEdit')->name('contracts.edit');
		    Route::post('contracts/update/{id}', 'DocumentController@contractUpdate')->name('contracts.update');
		    Route::post('contracts/update/{id}', 'DocumentController@contractUpdate')->name('contracts.update');
		    Route::get('contracts/view/{id}', 'DocumentController@viewContract')->name('contracts.view');
		    Route::get('contracts/download/{id}', 'DocumentController@downloadContract')->name('contracts.download');
		
		    Route::get('documents/create/{id}', 'DocumentController@create')->name('documents.create');
		    Route::post('documents/store', 'DocumentController@store')->name('documents.store');
		    Route::get('documents/edit/{id}', 'DocumentController@edit')->name('documents.edit');
		    Route::put('documents/update/{id}', 'DocumentController@update')->name('documents.update');
		    Route::post('documents/delete/{id}', 'DocumentController@delete')->name('documents.delete');
		    Route::post('documents/list/{clientId}', 'DocumentController@postList')->name('documents.list');
		    Route::get('documents/view/{id}', 'DocumentController@viewDocument')->name('documents.view');
		    Route::get('documents/download/{id}', 'DocumentController@downloadDocument')->name('documents.download');

            // hardware list of a client
            Route::post('hardware/list/{clientId}', 'HardwareController@postList')->name('hardware.list');


            //region clients routes
		    Route::get('', 'ClientsController@getIndex')->name('clients.index');
		    Route::get('locked', 'ClientsController@getIndex2')->name('clients.index2');
		    Route::get('day-free', 'ClientsController@getIndex3')->name('clients.index3');
		    Route::post('list', 'ClientsController@postList');
		    Route::post('list2', 'ClientsController@postList2');
		    Route::post('list3', 'ClientsController@postList3');
		    Route::post('create', 'ClientsController@postCreate');
		    Route::post('create-client', 'ClientsController@postCreateClient');
		    Route::post('delete', 'ClientsController@postDelete')->name('admin.client-destroy');
		    Route::post('update', 'ClientsController@postUpdate');
		    Route::post('update-client', 'ClientsController@postUpdateClient')->name('admin.client-update');
		    Route::post('ban', 'ClientsController@postBan');
		    Route::post('id_c', 'ClientsController@postIdClient');
		    Route::post('list/filter-totals', 'ClientsController@filterTotals')->name('client.filter-totals');
		    Route::get('export')->uses('ClientsController@exportToExcel')->name('client.excel.export');
                    Route::patch('/{id}/update-coordinates', 'ClientsController@updateCoordinates')->name('client.update-coordinates');
                    Route::patch('/{id}/update-map-marker-icon', 'ClientsController@updateMapMarkerIcon')->name('client.update-map-marker-icon');
                    Route::patch('/{id}/odb-geo-json', ['as' => 'client.odb-geo-json', 'uses' => 'ClientsController@saveOdbGeoJson']);

		    // cortado datre update manually
		    Route::get('change-cortado-date/{clientId}')->uses('ClientsController@editCortadoDate')->name('client.editCortadoDate');
            Route::post('update-cortado/{id}', 'ClientsController@updateCortado')->name('client.update-cortado-submit');
		    // plan clients route
		    Route::get('plan/{planId}', 'ClientsController@getPlanClients')->name('plan.clients.index');
		    Route::post('plan/list/filter-totals/{planId}', 'ClientsController@planClientfilterTotals')->name('plan.client.filter-totals');
		
		    // router clients route
		    Route::get('router/{routerId}', 'ClientsController@getRouterClients')->name('router.clients.index');
		    Route::post('router/list/filter-totals/{routerId}', 'ClientsController@routerClientfilterTotals')->name('router.client.filter-totals');
		
		
		    // region advice routes
		    Route::get('advice', 'AdviceController@getIndex');
		    Route::post('advice/clients', 'AdviceController@postClients')->name('advice.clients');
		    Route::post('advice/send', 'AdviceController@postSend')->name('advice.send');
		    Route::post('advice/delete', 'AdviceController@postDelete')->name('advice.delete');
		    //endregion
		    
		    
		    // region maps routes
		    Route::get('maps', 'MapsController@getIndex')->name('client.maps');
		    Route::get('maps/caja', 'MapsController@getIndex2')->name('client.maps.caja');
			//endregion
		
		
		    //zonas
		    Route::get('Ubicaciones', 'ProfilesController@getIndex')->name('profiles.index');
		    Route::post('profiles/getzone/data', 'ProfilesController@postData')->name('profiles.getzone.data');
		    Route::post('profiles/zone/create', 'ProfilesController@postCreate')->name('profiles.zone.create');
		    Route::post('profiles/zone/delete', 'ProfilesController@postDelete')->name('profiles.zone.delete');
		    Route::post('profiles/zone/update', 'ProfilesController@postUpdate')->name('profiles.zone.update');
		    //zonas
		
		    //zonas odb
		    Route::get('Ubicaciones/odb', 'OdbSplitterController@getIndex')->name('odb.index');
		    Route::post('profiles/odb/data', 'OdbSplitterController@postData')->name('profiles.odb.data');
		    Route::post('profiles/odb/create', 'OdbSplitterController@postCreate')->name('profiles.odb.create');
		    Route::post('profiles/odb/delete', 'OdbSplitterController@postDelete')->name('profiles.odb.delete');
		    Route::post('profiles/odb/update', 'OdbSplitterController@postUpdate')->name('profiles.odb.update');
		    Route::patch('odb/{id}/update-coordinates', 'OdbSplitterController@updateCoordinates')->name('odb.update-coordinates');
                    Route::patch('odb/{id}/update-map-marker-icon', 'OdbSplitterController@updateMapMarkerIcon')->name('odb.update-map-marker-icon');

                    //zonas odb
		
		    //zonas onu
		    Route::get('Ubicaciones/onuType', 'OnuTypeController@getIndex')->name('onuType.index');
		    Route::post('profiles/onu/data', 'OnuTypeController@postData')->name('profiles.onu.data');
		    Route::post('profiles/onu/create', 'OnuTypeController@postCreate')->name('profiles.onu.create');
		    Route::post('profiles/onu/delete', 'OnuTypeController@postDelete')->name('profiles.onu.delete');
		    Route::post('profiles/onu/update', 'OnuTypeController@postUpdate')->name('profiles.onu.update');
	    });
	
	    Route::group(['prefix' => 'finance'], function () {
		    Route::get('dashboard', 'FinanceDashboardController@getIndex')->name('finance.dashboard');
		    Route::post('finance-stats', 'FinanceDashboardController@financeDashboardStats')->name('finance.dashboard.stats');
		    Route::get('transactions', 'TransactionController@getIndex')->name('finance.transaction.index');
		    Route::post('transactions/filter-totals', 'TransactionController@filterTotals')->name('finance.transaction.filter-totals');
		    Route::get('invoices', 'InvoiceController@index')->name('finance.invoice.index');
		
		    Route::post('invoices/filter-totals', 'InvoiceController@filterTotals')->name('finance.invoices.filter-totals');
		
		
		    Route::get('payments', 'PaymentController@getIndex')->name('finance.payments.index');
		    Route::post('payments/filter-totals', 'PaymentController@filterTotals')->name('finance.payments.filter-totals');
		
		    //region sri routes
		    Route::get('sri/dashboard', 'SriController@getIndex')->name('sri.dashboard');
		    Route::post('sri/delete/{id}', 'SriController@delete')->name('sri.delete');
		    Route::post('sri/sri/lists', 'SriController@postLists')->name('sri.invoice.lists');
		    Route::get('sri/export-popup', 'SriController@exportInvoicePopup')->name('sri.export-popup');
		    Route::post('sri/export-invoices', 'SriController@exportInvoices')->name('sri.export-invoices');
		    Route::post('sri/check-invoices', 'SriController@checkInvoices')->name('sri.check');
			//endregion
		
		    //region secuenciales routes
		    Route::get('secuenciales/dashboard', 'SecuencialesController@getIndex')->name('secuenciales.dashboard');
		    Route::post('secuenciales/delete/{id}', 'SecuencialesController@delete')->name('secuenciales.update');
		    Route::get('secuenciales/show-invoice/{id}', 'SecuencialesController@showInvoice')->name('secuenciales.showInvoice');
		    Route::post('secuenciales/sri/lists', 'SecuencialesController@postLists')->name('secuenciales.invoice.lists');
		    Route::get('secuenciales/export-popup', 'SecuencialesController@exportInvoicePopup')->name('secuenciales.export-popup');
		    Route::post('secuenciales/export-invoices', 'SecuencialesController@exportInvoices')->name('secuenciales.export-invoices');
		    Route::post('secuenciales/check-invoices', 'SecuencialesController@checkInvoices')->name('secuenciales.check');
		    Route::get('secuenciales/save/{valor}', 'SecuencialesController@save')->name('secuenciales.save');
			//endregion
		
		
		    // region Establecimientos
		    Route::get('establecimientos', 'EstablecimientosController@getIndex')->name('establecimientos.index');;
		    Route::post('establecimientos/lists', 'EstablecimientosController@postLists')->name('establecimientos.invoice.lists');
		    Route::post('establecimientos/create', 'EstablecimientosController@postCreate')->name('establecimientos.create');
		    Route::get('establecimientos/show-establecimiento/{id}', 'EstablecimientosController@showInvoice')->name('establecimientos.showInvoice');
		    Route::get('establecimientos/delete-establecimiento/{id}', 'EstablecimientosController@deleteEstablecimiento')->name('establecimientos.delete');
		    Route::get('establecimientos/new-establecimiento', 'EstablecimientosController@newInvoice')->name('establecimientos.newInvoice');
		    //endregion
		
		    // region ptoEmision
		    Route::get('ptoEmision', 'PuntoController@getIndex')->name('ptoEmision.index');
		    Route::post('ptoEmision/lists', 'PuntoController@postLists')->name('ptoEmision.invoice.lists');
		    Route::post('ptoEmision/create', 'PuntoController@postCreate')->name('ptoEmision.create');
		    Route::get('ptoEmision/show-ptoEmision/{id}', 'PuntoController@showInvoice')->name('ptoEmision.showInvoice');
		    Route::get('ptoEmision/new-invoice', 'PuntoController@newInvoice')->name('ptoEmision.newInvoice');
		    Route::get('ptoEmision/delete-ptoEmision/{id}', 'PuntoController@deletePtoEmision')->name('ptoEmision.delete');
		    //endregion
		
		    // region note
		    Route::get('note/create/{id}', 'NoteController@create')->name('note.create');
		    Route::post('note/createnote/{id}', 'NoteController@createnote')->name('note.createnote');
		    Route::get('note/dashboard', 'NoteController@getIndex')->name('note.dashboard');
		    Route::post('table/note/lists/{id}', 'NoteController@postLists')->name('table.note.lists');
		    Route::get('note/additem/{id}/{idtr}', 'NoteController@getItemdian')->name('note.additem');
		    //endregion
	    });


	    // all inventory routes are in this inventory group
        Route::group(['prefix' => 'inventory', 'as' => 'inventory.'], function () {
            Route::get('dashboard', 'Inventory\DashboardController@index')->name('dashboard');

            Route::resource('vendors', 'Inventory\VendorController');

            Route::post('suppliers/products/{id}', 'Inventory\SupplierController@supplierProductData')->name('suppliers.products');
            Route::resource('suppliers', 'Inventory\SupplierController');
            // item internal usages routes
            Route::get('items/internal-usages-model/{itemId}', 'Inventory\ItemController@internalUsagesModel')->name('items.internal-usages-model');
            Route::post('items/internal-usages-model/{itemId}', 'Inventory\ItemController@internalUsagesSave')->name('items.internal-usages-save');

            // return item routes
            Route::get('items/return-item-modal/{itemId}', 'Inventory\ItemController@returnItemModel')->name('items.return-item-modal');
            Route::post('items/return-item-modal/{itemId}', 'Inventory\ItemController@returnItemSave')->name('items.return-item-save');

            // return item assign routes
            Route::get('items/item-assign-modal/{itemId}', 'Inventory\ItemController@itemAssignCustomerModel')->name('items.item-assign-modal');
            Route::post('items/item-assign-modal/{itemId}', 'Inventory\ItemController@itemAssignCustomerSave')->name('items.item-assign-save');

            // return item sell routes
            Route::get('items/item-sell-modal/{itemId}', 'Inventory\ItemController@itemSellCustomerModel')->name('items.item-sell-modal');
            Route::post('items/item-sell-modal/{itemId}', 'Inventory\ItemController@itemSellCustomerSave')->name('items.item-sell-save');

            // return item rent routes
            Route::get('items/rent-item-model/{itemId}', 'Inventory\ItemController@itemRentCustomerModel')->name('items.rent-item-model');
            Route::post('items/rent-item-model/{itemId}', 'Inventory\ItemController@itemRentCustomerSave')->name('items.rent-item-save');

            // return item routes
            Route::get('items/history/{itemId}', 'Inventory\ItemController@history')->name('items.history');
            Route::resource('items', 'Inventory\ItemController');

            // supplier invoice routes
            Route::get('supplier-invoices/{invoiceId}/add-bar-code', 'Inventory\SupplierInvoiceController@addBarcode')->name('supplier-invoices.add-bar-code');
            Route::post('supplier-invoices/{invoiceId}/store-bar-code', 'Inventory\SupplierInvoiceController@storeBarcode')->name('supplier-invoices.store-bar-code');
            Route::get('supplier-invoices/{invoiceId}/add-serial-code', 'Inventory\SupplierInvoiceController@addSerialNumber')->name('supplier-invoices.add-serial-code');
            Route::post('supplier-invoices/{invoiceId}/store-serial-code', 'Inventory\SupplierInvoiceController@storeSerialNumber')->name('supplier-invoices.store-serial-code');
            Route::resource('supplier-invoices', 'Inventory\SupplierInvoiceController');

            Route::resource('products', 'Inventory\ProductController');
        });

        //zonas onu

        Route::post('viewClient/create', 'ClientsController@postCreateCamposView')->name('viewClient.create');

//endregion

//region clients routes
        Route::post('client/getclient/plans', 'GetClientController@postPlans')->name('admin.client.plan');
        Route::post('client/getclient/factel', 'GetClientController@postFactel')->name('admin.client.factel');
        Route::post('client/getclient/routers', 'GetClientController@postRouters')->name('admin.client.router');
        Route::post('client/getclient/cajas', 'GetClientController@postRoutersCajas');
        Route::post('client/getclient/dhcp', 'GetClientController@postDhcp');
        Route::post('client/getclient/router', 'GetClientController@postRouter');
        Route::post('client/getclient/listprofilesppp', 'GetClientController@postListprofilesppp')->name('client.getclient.listprofilesppp');
        Route::post('client/getclient/trafic', 'GetClientController@postTrafic');
        Route::post('client/getclient/listprofileshotspot', 'GetClientController@postListprofileshotspot')->name('client.getclient.listprofileshotspot');
        Route::post('client/getclient/control', 'GetClientController@postControl')->name('admin.client.control');
        Route::post('client/getclient/data', 'GetClientController@postData')->name('admin.client.getclient');
        Route::post('client/getclient/gpsmap', 'GetClientController@postGpsmap');
        Route::post('client/getclient/tools', 'GetClientController@postTools');
        Route::post('client/getclient/info', 'GetClientController@postInfo');
        Route::post('client/getclient/client', 'GetClientController@postClient')->name('client.getclient.client');
        Route::post('client/getclient/clients', 'GetClientController@postClients');
        Route::post('client/getclient/gcl', 'GetClientController@postGcl')->name('client.getclient.gcl');
        Route::post('client/getclient/allclients', 'GetClientController@postAllclients');

        Route::post('client/getclient/caja', 'GetClientController@info_caja');


        Route::post('client/getservice/info', 'GetClientController@postServiceInfo')->name('client/getservice/info');
        Route::post('client/getservice/tools', 'GetClientController@postServiceTools')->name('client/getservice/tools');
        Route::post('client/getservice/trafic', 'GetClientController@postServiceTrafic')->name('client/getservice/trafic');
//endregion

//region users routes
        Route::post('user/getuser/data', 'GetUserController@postData');
//endregion

//region users routes
        Route::get('users', 'UsersController@getIndex');
        Route::post('users/create', 'UsersController@postCreate');
        Route::post('users/delete', 'UsersController@postDelete');
        Route::post('users/update', 'UsersController@postUpdate');
        Route::post('users/ban', 'UsersController@postBan');
//endregion

//region cashier credits routes
        Route::get('users/{id}/credits', 'UsersController@getCredits')->name('user-credits');
        Route::post('users/credits/create', 'UsersController@storeCredits')->name('user-credits-create');
        Route::post('users/credits/delete', 'UsersController@deleteCredits')->name('user-credit-delete');
//endregion


// region logs routes
        Route::get('logs', 'LogsController@getIndex');
        Route::post('logs/list', 'LogsController@postList');
//endregion


// region sms routes
        Route::get('sms/index', 'SmsController@getIndex');
        Route::post('sms/listsend', 'SmsController@postListsend');
        Route::post('sms/inbox', 'SmsController@postInbox');
        Route::post('sms/send', 'SmsController@postSend');
        Route::post('sms/delete', 'SmsController@postDelete');
        Route::post('sms/forward', 'SmsController@postForward');
        Route::post('sms/sendanswer', 'SmsController@postSendanswer');
        Route::post('sms/listgroup', 'SmsController@postListgroup');
        Route::post('sms/ph-to-id', 'SmsController@getphtoid');
        Route::post('sms/msg-status', 'SmsController@msgstatus');
//endregion


// region plans routes
        Route::get('plans', 'PlansController@getIndex');
//        Route::post('plans/list', 'PlansController@postList');
        Route::post('plans/create', 'PlansController@postCreate');
        Route::post('plans/delete', 'PlansController@postDelete');
        Route::post('plans/update', 'PlansController@postUpdate');
//endregion

// region plan routes
        Route::post('plan/getplan/data', 'GetPlanController@postData');
//endregion

// region smart bandwidth routes
        Route::get('sb/getinfo/days', 'GetSmartBandwidthController@getDays');
        Route::post('sb/getinfo/data', 'GetSmartBandwidthController@postData');
//endregion

// region config routes
        Route::post('config/getconfig/adv', 'GetConfigController@postAdv');
        Route::post('config/getconfig/apis', 'GetConfigController@postApis');
        Route::post('config/getconfig/deviceid', 'GetConfigController@postDeviceid');
        Route::post('config/getconfig/general', 'GetConfigController@postGeneral');
        Route::post('config/whatsappsms', 'ConfigController@postWhatsappsms');
	Route::post('config/weboxapp', 'ConfigController@postWebox');
        Route::post('config/whatsappcloudapi', 'ConfigController@postWhatsappCloudApi');
        Route::get('config/getconfig/defaultlocation', 'GetConfigController@getDefaultlocation');
        Route::get('config/getconfig/debug', 'GetConfigController@getDebug');
        Route::post('config/getconfig/email', 'GetConfigController@postEmail');
        Route::post('config/getconfig/sms', 'GetConfigController@postSms');
        Route::post('config/getconfig/ipserver', 'GetConfigController@postIpserver');
//endregion

// region routers routes
        Route::get('routers', 'RoutersController@getIndex');
        Route::get('routers/change-ip', 'RoutersController@getChangeIp')->name('router.get-change-ip');
        Route::post('routers/change-ip-submit/{id}', 'RoutersController@changeIp')->name('router.submit-change-ip');
        Route::post('routers/refresh/{id}', 'RoutersController@refresh')->name('router.refresh');
        Route::post('routers/create', 'RoutersController@postCreate');
        Route::post('routers/delete', 'RoutersController@postDelete');
        Route::post('routers/update', 'RoutersController@postUpdate');
        Route::post('routers/ips', 'RoutersController@postIps');
        Route::post('routers/networks', 'RoutersController@postNetworks');
        Route::post('routers/inte', 'RoutersController@postInte');
        Route::post('routers/interface', 'RoutersController@postInterface');
        Route::post('routers/routerinterface', 'RoutersController@postRouterinterface');
        Route::post('routers/list', 'RoutersController@postList');
        Route::get('routers/restart-freeradius', 'RoutersController@restartFreeradius');
        Route::patch('routers/{id}/update-coordinates', 'RoutersController@updateCoordinates')->name('router.update-coordinates');
        Route::patch('routers/{id}/update-map-marker-icon', 'RoutersController@updateMapMarkerIcon')->name('router.update-map-marker-icon');

//endregion

// region networks routes
        Route::get('networks', 'NetworkController@getIndex');
        Route::post('networks/list', 'NetworkController@postList');
        Route::post('networks/create', 'NetworkController@postCreate');
        Route::post('networks/update', 'NetworkController@postUpdate');
        Route::post('networks/delete', 'NetworkController@postDelete');
//endregion

// region networks routes
        Route::post('network/getinfo/data', 'GetNetworkInfoController@postData');
//endregion

// region networks routes
        Route::post('network/getnetwork/data', 'GetNetworkController@postData');
        Route::post('network/getnetwork/networks', 'GetNetworkController@postNetworks');
        Route::post('network/getnetwork/ip', 'GetNetworkController@postIp');
//endregion

// region router routes
        Route::post('router/getrouter/data', 'GetRouterController@postData');
        Route::post('router/getrouter/location', 'GetRouterController@postLocation');
        Route::post('router/getrouter/control', 'GetRouterController@postControl');
        Route::post('router/getrouter/configplan', 'GetRouterController@postConfigplan');
        Route::post('router/getrouter/ipnet', 'GetRouterController@postIpnet');
//endregion

// region bill routes
        // Route::get('bill', 'BillController@getIndex');
        Route::post('bill/list', 'BillController@postList');
        Route::post('bill/create', 'BillController@postCreate');
        Route::post('bill/delete', 'BillController@postDelete');
        Route::post('bill/print', 'BillController@postPrint');
        Route::post('bill/ipnet', 'BillController@postIpnet');
        Route::post('bill/sendmail', 'BillController@postSendmail');
//endregion

// region box routes
        Route::get('box', 'BoxController@getIndex');
        Route::post('box/listin', 'BoxController@postListin');
        Route::post('box/listout', 'BoxController@postListout');
        Route::get('box/totalcounters', 'BoxController@getTotalcounters');
        Route::post('box/create', 'BoxController@postCreate');
        Route::post('box/delete', 'BoxController@postDelete');
//endregion

// region reports routes
        Route::get('reports', 'ReportsController@getIndex');
        Route::post('reports/list', 'ReportsController@postList');
        Route::post('reports/amount', 'ReportsController@postAmount');
        Route::post('reports/delete', 'ReportsController@postDelete');

        Route::post('reports/filter-totals', 'ReportsController@filterTotals');
//endregion

// region stat routes
        Route::get('stat', 'StatsController@getIndex');
        Route::get('stat/internet', 'StatsController@getInternet');
        Route::get('stat/general', 'StatsController@getGeneral');
        Route::get('stat/peryears', 'StatsController@getPeryears');
        // Route::get('stat/peryears', 'StatsController@getPayed');
//endregion

// region templates routes
        Route::get('templates', 'TemplatesController@getIndex');
        Route::post('templates/list', 'TemplatesController@postList');
        Route::post('templates/listvisual', 'TemplatesController@postListvisual');
        Route::post('templates/listhtml', 'TemplatesController@postListhtml');
        Route::post('templates/listtem', 'TemplatesController@postListtem');
        Route::post('templates/delete', 'TemplatesController@postDelete');
        Route::post('templates/seteme', 'TemplatesController@postSeteme')->name('templates.seteme');
        Route::post('templates/setype', 'TemplatesController@postSetype')->name('templates.setype');
        Route::post('templates/create', 'TemplatesController@postCreate');
        Route::post('templates/html', 'TemplatesController@postHtml');
//endregion

// region visualeditor routes
        Route::get('visualeditor', 'VisualEditorController@getIndex');
//endregion

// region htmleditor routes
        Route::get('htmleditor', 'HtmlEditorController@getIndex');
//endregion

// region tickets routes
        Route::get('tickets', 'TicketsController@getIndex')->name('admin.tickets.list');
        Route::get('tickets/dashboard', 'TicketsController@getDashboard')->name('admin.tickets.dashboard');
        Route::post('tickets/change-fields/{id}', 'TicketsController@changeFields')->name('admin.tickets.change-fields');
        Route::post('tickets/list', 'TicketsController@postList');
        Route::post('tickets/close', 'TicketsController@postClose');
        Route::post('tickets/delete', 'TicketsController@postDelete');
        Route::post('tickets/reply', 'TicketsController@postReply');
        Route::post('tickets/create', 'TicketsController@postCreate');
        Route::get('tickets/change-assignee/{id}', 'TicketsController@getAssignee')->name('tickets.assignee');
        Route::post('tickets/change-assignee/{id}', 'TicketsController@changeAssignee')->name('tickets.assignee.update');

	    Route::get('tickets/column-visible', 'TicketsController@getColumnVisible')->name('tickets.column-visible');
	    Route::post('tickets/column-visible-update', 'TicketsController@updateColumnVisible')->name('tickets.column-visible-update');

	    Route::get('ticket/assigned-to-me', 'TicketsController@getAssignedToMe')->name('admin.tickets.assignedToMe');
	    Route::get('ticket/administrator', 'TicketsController@getAllAdminstrator')->name('admin.tickets.administrator');

//endregion

// region htmleditor routes
        Route::post('ticket/getticket/status', 'GetTicketController@postStatus');
        Route::post('ticket/getticket/show', 'GetTicketController@postShow');
//endregion

        Route::get('tempview', 'TemplatePreviewController@viewIndex');


//region sms routes
        Route::get('sms', 'SmsController@getIndex');
        Route::post('sms/listsend', 'SmsController@postListsend');
        Route::post('sms/inbox', 'SmsController@postInbox');
        Route::post('sms/send', 'SmsController@postSend');
        Route::post('sms/delete', 'SmsController@postDelete');
        Route::post('sms/forward', 'SmsController@postForward');
        Route::post('sms/sendanswer', 'SmsController@postSendanswer');
        Route::post('sms/listgroup', 'SmsController@postListgroup');
        Route::post('sms/client-whatsapp-chat', 'SmsController@postClientWhatsappChat');
//endregion

//region config routes
        Route::post('config/search-disable', 'ConfigController@postSearchDisable');
        Route::get('config', 'ConfigController@getIndex');
        Route::post('config/adv', 'ConfigController@postAdv');
        Route::post('config/general', 'ConfigController@postGeneral');
        Route::post('config/logo', 'ConfigController@postLogo');
        Route::post('config/logo_f', 'ConfigController@postLogo_factura');
        Route::post('config/smtp', 'ConfigController@postSmtp');
        Route::post('config/emailticket', 'ConfigController@postEmailticket');
        Route::post('config/email_f', 'ConfigController@postEmail_f');

        Route::post('config/factel', 'ConfigController@postFactel');
        Route::post('config/venezuala', 'ConfigController@postVenezuala')->name('config.venezuala');
        Route::post('config/emisor', 'ConfigController@postEmisor');
        Route::post('config/factelstatus', 'ConfigController@postFactelStatus');

        Route::post('config/defaultmap', 'ConfigController@postDefaultmap');
        Route::post('config/maptype', 'ConfigController@postMapType');
        Route::post('config/zone', 'ConfigController@postZone');
        Route::post('config/debug', 'ConfigController@postDebug');
        Route::post('config/cache', 'ConfigController@postCache');
        Route::post('config/ressys', 'ConfigController@postRessys');
        Route::post('config/apimikrotik', 'ConfigController@postApimikrotik');
        Route::post('config/generalsms', 'ConfigController@postGeneralsms');
        Route::post('config/modem', 'ConfigController@postModem');
        Route::post('config/smsgateway', 'ConfigController@postSmsgateway');
        Route::post('config/apimaps', 'ConfigController@postApimaps');
        Route::post('config/apistreet', 'ConfigController@postApistreet');
        Route::post('config/stripegateway', 'ConfigController@postStripeGateway');
        Route::post('config/paypalgateway', 'ConfigController@postPaypalGateway');
        Route::post('config/payugateway', 'ConfigController@postPayuGateway');
        Route::post('config/directopago', 'ConfigController@postDirectoPago');
        Route::post('config/payvalida', 'ConfigController@postPayValida');
        Route::post('config/savelocale', 'ConfigController@postSaveLocale');
        Route::post('config/language-setting', 'ConfigController@postLanguageSetting');
        Route::post('config/smartolt', 'ConfigController@postSmartoltApi');


        // cron-jobs
        Route::get('cron-jobs', 'CronJobsController@getIndex');
        Route::post('cron-jobs/{id}', 'CronJobsController@fireCron')->name('cron-fire');
//endregion

//region sms/getinfo routes
        Route::post('sms/getinfo/gateway', 'GetSmsController@postGateway');
        Route::post('sms/getinfo/usb', 'GetSmsController@postUsb');
        Route::post('sms/getinfo/answersms', 'GetSmsController@postAnswersms');
//endregion

//region tools routes
        Route::get('tools', 'ToolsController@getIndex');
        Route::post('tools/send', 'ToolsController@postSend');
        Route::post('tools/ping', 'ToolsController@postPing');
        Route::post('tools/torch', 'ToolsController@postTorch');
        Route::post('tools/sendsms', 'ToolsController@postSendsms');
//endregion

//region toolsImport routes
        Route::post('toolsImport/import', 'ImportToolController@postImport');
        Route::post('toolsImport/profile', 'ImportToolController@postProfile');
//endregion

//region backups routes
        Route::get('backups', 'BackupsController@getIndex');
        Route::post('backups/list', 'BackupsController@postList');
        Route::post('backups/create', 'BackupsController@postCreate');
        Route::post('backups/delete', 'BackupsController@postDelete');
        Route::post('backups/upload', 'BackupsController@postUpload');
        Route::post('backups/restore', 'BackupsController@postRestore');
//endregion


//region sms/getinfo routes
        Route::get('myprofile', 'ProfileController@getIndex');
        Route::post('myprofile/update', 'ProfileController@postUpdate');
//endregion

//region sms routes
        Route::get('sms', 'SmsController@getIndex');
        Route::post('sms/listsend', 'SmsController@postListsend');
        Route::post('sms/inbox', 'SmsController@postInbox');
        Route::post('sms/send', 'SmsController@postSend');
        Route::post('sms/delete', 'SmsController@postDelete');
        Route::post('sms/forward', 'SmsController@postForward');
        Route::post('sms/sendanswer', 'SmsController@postSendanswer');
        Route::post('sms/listgroup', 'SmsController@postListgroup');
//endregion

//region portal/get routes
        Route::post('router/getlogs/log', 'GetLogsController@postLog');
//endregion



//region router/getlogs routes
        Route::post('router/getinfo/data', 'GetRouterInfoController@postData');
        Route::post('router/getinfo/lan', 'GetRouterInfoController@postLan');
//endregion

//region map/get routes
        Route::post('map/get/gpsmap', 'GetMapController@postGpsmap');
        Route::post('map/get/gpsmap2', 'GetMapController@postGpsmap2');
//endregion

//region map/get routes
        Route::post('smartbandwidth/update', 'SmartBandwidthController@postUpdate');
//endregion

        Route::post('transactions/list/{clientId}', 'TransactionController@postList')->name('transaction.list');
        Route::get('transactions/{id}', 'TransactionController@edit')->name('transaction.edit');
        Route::get('transactions/{id}', 'TransactionController@edit')->name('transaction.edit');
        Route::post('transactions/{id}', 'TransactionController@delete')->name('transaction.delete');

        Route::get('invoice/create/{id}', 'InvoiceController@create')->name('invoice.create');
        Route::post('invoice/store', 'InvoiceController@store')->name('invoice.store');
        Route::get('invoice/add-one-time-invoice/{id}', 'InvoiceController@addOneTimeInvoiceView')->name('invoice.addOneTimeInvoiceView');

        //recurring invoice view
        Route::get('recurring-invoice/{id}', 'InvoiceController@recurringInvoice')->name('invoice.recurringInvoice');
        Route::post('recurring-invoice/item/{id}', 'InvoiceController@storeRecurringInvoice')->name('invoice.storeRecurringInvoice');
        Route::post('recurring-invoice/data/{id}', 'InvoiceController@recurringInvoiceList')->name('invoice.recurringInvoiceList');
        Route::post('recurring-invoice/generate/{id}', 'InvoiceController@recurringInvoiceGenerate')->name('invoice.recurringInvoiceGenerate');

        Route::post('invoice/item/{id}', 'InvoiceController@addOneTimeInvoiceCreate')->name('invoice.addOneTimeInvoiceCreate');
        Route::get('invoice/edit/{id}', 'InvoiceController@edit')->name('invoice.edit');
        Route::get('invoice/edit-recurring/{id}', 'InvoiceController@editRecurring')->name('invoice.editRecurring');
        Route::get('invoice/edit-recurring-invoices/{id}', 'InvoiceController@editRecurringInvoices')->name('invoice.editRecurringInvoice');
        Route::post('recurring-invoice/item/{id}/{invoice_id}', 'InvoiceController@updateRecurringInvoice')->name('invoice.updateRecurringInvoice');
        Route::post('invoice/update/{id}', 'InvoiceController@updateInvoice')->name('invoice.update');
        Route::post('invoice/update-recurring/{id}', 'InvoiceController@updateOneTimeInvoice')->name('invoice.updateRecurring');
        Route::post('invoice/delete/{id}', 'InvoiceController@delete')->name('invoice.delete');
        Route::post('recurring-invoice/delete/{id}', 'InvoiceController@recurringDelete')->name('recurring-invoice.delete');
        Route::post('recurring/ban/{id}', ['as' => 'recurring.services.ban', 'uses' => 'InvoiceController@recurringBan']);
        Route::post('invoice/payment/send/{id}', 'InvoiceController@invoicePaymentSend')->name('invoice.payment.send');

        Route::post('invoice/list/{clientId}', 'InvoiceController@postList')->name('invoice.list');
        Route::get('invoice/payment/edit/{id}', 'InvoiceController@invoicePaymentEdit')->name('invoice.payment.edit');
        Route::post('invoice/payment/delete/{id}', 'InvoiceController@invoicePaymentDelete')->name('invoice.payment.delete');

        Route::post('invoice/send-email/{id}', 'BillPrintController@SendInvoicePDF')->name('invoice.sendEmail');

        Route::get('invoice/show-pdf/{id}', 'BillPrintController@showInvoicePDF')->name('invoice.showPDF');
        Route::get('invoice/show-invoice/{id}', 'InvoiceController@showInvoice')->name('invoice.showInvoice');
        Route::get('invoice/pay-invoice-view/{id}', 'InvoiceController@payInvoiceView')->name('invoice.payInvoiceView');
        Route::post('invoice/pay-invoice', 'InvoiceController@payInvoice')->name('invoice.payInvoice');
        Route::get('invoice/print/{id}', 'BillPrintController@printInvoicePDF')->name('invoice.print');
        Route::get('invoice/download/{id}', 'BillPrintController@download')->name('invoice.download');
        Route::get('invoice/download-csv/{id}', 'BillPrintController@downloadCsv')->name('invoice.download.csv');
        Route::post('invoice/send-fiscal/{id}', 'BillPrintController@sendToFiscal')->name('invoice.fiscal.send');

        Route::get('invoice/generateInvoice/cron', 'AutomaticTasksController@startGenerateinvoice')->name('invoice.generate');

        Route::post('invoice/test', 'InvoicetestController@sendtestxml_dian')->name('invoice.sendtestxml_dian');

        Route::get('payments/create/{id}', 'PaymentController@create')->name('payments.create');
        Route::post('payments/store', 'PaymentController@store')->name('payments.store');
        Route::get('payments/edit/{id}', 'PaymentController@edit')->name('payments.edit');
        Route::post('payments/update/{id}', 'PaymentController@update')->name('payments.update');
        Route::post('payments/delete/{id}', 'PaymentController@delete')->name('payments.delete');
        Route::post('payments/list/{clientId}', 'PaymentController@postList')->name('payments.list');

        Route::get('transactions--to-bill/{id}', 'TransactionController@toBill')->name('transactionsToBill');
        Route::get('transactions--to-bill-view/{id}', 'TransactionController@toBillView')->name('transactionsToBillView');
        Route::get('transactions--to-bill-create', 'TransactionController@toBillCreate')->name('transactionsToBillCreate');

        Route::get('cancel-charge-view/{id}', 'TransactionController@cancelChargeView')->name('cancelChargeView');
        Route::get('cancel-charge/{id}', 'TransactionController@cancelCharge')->name('cancelCharge');

        Route::get('invoices/export-popup', 'InvoiceController@exportInvoicePopup')->name('invoices.export-popup');
        Route::post('invoices/check-invoices', 'InvoiceController@checkInvoices')->name('invoices.check');
        Route::post('invoices/export-invoices', 'InvoiceController@exportInvoices')->name('invoices.export-invoices');
        Route::post('export-history/list', 'ExportHistoryController@postList')->name('export-history.list');
        Route::get('export-history/download/{id}', 'ExportHistoryController@download')->name('export-history.download');
        Route::post('export-history/delete/{id}', 'ExportHistoryController@delete')->name('export-history.delete');


        // Routes Invoice MX
        Route::post('invoice_mexico/payment/send/{id}', 'InvoiceController@signInvoiceMx')->name('invoice_mx.payment.send');
        Route::get('invoice_mexico/payment/file', 'InvoiceController@getInvoiceMx')->name('invoice_mx.payment.file');
        Route::post('invoice_mexico/payment/sendemail', 'InvoiceController@sendEmailMx')->name('invoice_mx.payment.email');

    });

//region license routes
    Route::get('license', 'SecurityController@getIndex');
    Route::post('license/activate', 'SecurityController@postActivate');
    Route::post('license/details', 'SecurityController@postDetails');
//endregion

//region verify updates
    Route::post('verifyUpdates', 'SecurityController@Updatesoft');
    Route::post('verify_updates_ok', 'SecurityController@ejecutarUpdate');
    Route::post('verify_reinicio_ok', 'SecurityController@reiniciar_vps');
//endregion

// region stats routes
    Route::post('stats/logs', 'GetStatsController@postLogs');
    Route::post('stats/data', 'GetStatsController@postData');
//endregion

// region stat routes
    Route::post('stat/payed', 'StatsController@getPayed');
    Route::get('stat/internet', 'StatsController@getInternet');
	Route::post('ticket/stat', 'StatsController@getTicketStats')->name('ticket.chart-data');

//endregion

// region notification routes
    Route::post('user/notifications/data', 'GetNotificationUserController@postData')->name('user.notifications.data');
    Route::post('user/notifications/ntclose', 'GetNotificationUserController@postNtclose');
//endregion

    Route::post('stats/data', 'GetStatsController@postData');
    Route::post('stats/logs', 'GetStatsController@postLogs');

// region RADIUS
    Route::get('radius', 'RadiusController@index');
//endregion


    // region SMARTOLT
    Route::get('smartolt', 'SmartOLTController@index')->name('listSmartOLT');
    Route::get('smartolt/{olt_id}', 'SmartOLTController@getDetailOLT');
    Route::post('smartolt/vlan', 'SmartOLTController@newVlan');
    Route::post('smartolt/zona', 'SmartOLTController@newZona');
    Route::get('smartolt/check_information/{id}', ['as' => 'smartolt.check_information', 'uses' => 'SmartOLTController@check_information']);
    Route::get('smartolt/authorize/{id}/{sn}/{board}/{port}/{olt_id}/{type_id}', ['as' => 'smartolt.authorize', 'uses' => 'SmartOLTController@authorize_data']);
    Route::post('smartolt/authorize_nuevo', 'SmartOLTController@authorize_nuevo');
    Route::post('smartolt/delete', 'SmartOLTController@delete');
    Route::post('smartolt/asociar', 'SmartOLTController@asociar');


//endregion



});

//region portal routes
Route::get('portal', 'PortalController@getIndex')->name('portal.index');
Route::post('portal/stats/data', 'StatsClientController@postData');
Route::post('portal/stats/lasttickets', 'StatsClientController@postLasttickets');
//endregion

//region portal routes
Route::get('portal/bills', 'BillClientController@getIndex')->name('portal.bill');
Route::post('portal/bills/list', 'BillClientController@postList');
//endregion

Route::get('reprint/{id}', 'BillPrintController@printbill');

Route::get('billprint/{id}', 'BillClPrintController@printbill');
Route::get('bill-download/{id}', 'BillClPrintController@download');

//region portal/tickets routes
Route::get('portal/tickets', 'TicketClientController@getIndex')->name('portal.tickets.index');
Route::post('portal/tickets/list', 'TicketClientController@postList');
Route::post('portal/tickets/create', 'TicketClientController@postCreate');
Route::post('portal/tickets/reply', 'TicketClientController@postReply');
//endregion


//region portal/get routes
Route::post('portal/get/show', 'GetTicketClientController@postShow');
Route::post('portal/get/status', 'GetTicketClientController@postStatus');
//endregion

//region portal/documents routes
Route::get('portal/documents', 'DocumentClientController@index')->name('portal.documents.index');
Route::get('portal/documents/view/{id}', 'DocumentController@viewDocument')->name('portal.documents.view');
Route::get('portal/documents/download/{id}', 'DocumentController@downloadDocument')->name('portal.documents.download');
Route::get('portal/contracts/view/{id}', 'DocumentClientController@viewContract')->name('portal.contracts.view');
Route::get('portal/contracts/download/{id}', 'DocumentClientController@downloadContract')->name('portal.contracts.download');
//endregion

//region portal routes
Route::post('portal/notifications/data', 'GetNotificationClientController@postData');
//endregion

//region client profile routes
Route::get('portal/myprofile', 'ProfileClientController@getIndex');
Route::post('portal/myprofile/update', 'ProfileClientController@postUpdate');
//endregion

// route for view/blade file
Route::get('paywithpaypal', array('as' => 'paywithpaypal', 'uses' => 'PaypalController@payWithPaypal',));
// route for post request
Route::get('paypal/{invoiceId}', array('as' => 'paypal', 'uses' => 'PaypalController@paymentWithpaypal',));
// route for check status responce
Route::get('paypal', array('as' => 'paypal.status', 'uses' => 'PaypalController@getPaymentStatus',));
Route::post('stripe/{invoiceId}', array('as' => 'stripe', 'uses' => 'StripeController@paymentWithStripe',));
Route::get('directopago/{invoiceId}', array('as' => 'directopago', 'uses' => 'DirectoPagoController@paymentWithDirectoPago',));
Route::post('directopago/webhook/{invoiceId}', array('as' => 'directopagoresponse', 'uses' => 'DirectoPagoController@paymentNotification'));

Route::get('payvalida/{invoiceId}', array('as' => 'payvalida', 'uses' => 'PayValidaController@paymentWithPayValida',));
Route::post('payvalida/webhook', array('as' => 'payvalidaresponse', 'uses' => 'PayValidaController@paymentNotification'));

// PayU route
Route::get('paypayu/{invoiceId}', array('as' => 'paypayu', 'uses' => 'PayuController@paymentWithPayu'));
Route::get('payuresponse', array('as' => 'payuresponse', 'uses' => 'PayuController@payuresponse'));
Route::get('payuconfirmation', array('as' => 'payuconfirmation', 'uses' => 'PayuController@payuconfirmation'));

Route::post('invoice_colombia/payment/send/{id}', 'InvoiceController@invoice_colombiaPaymentSend')->name('invoice_colombia.payment.send');

Route::get('/js/{locale}/lang.js', function ($lang) {

    $minutes = 10;

    $strings = \Cache::remember($lang . '.lang.js', $minutes, function () use ($lang) {

        $files = glob(resource_path('lang/' . $lang . '/*.php'));
        $strings = [];

        foreach ($files as $file) {
            $name = basename($file, '.php');
            $strings[$name] = require $file;
        }

        return $strings;
    });

    return response('window.Lang = ' . json_encode($strings) . ';')
        ->header('Content-Type', 'text/javascript');
})->name('assets.lang');


Route::any('{catchall}', function () {
    return Response::view('errors.missing', array(), 404);
})->where('catchall', '.*');
