<?php

namespace App\Http\Controllers;
use App\DataTables\Client\InvoiceDataTable;
use App\libraries\CheckUser;
use App\models\BillCustomer;
use App\models\Client;
use App\models\GlobalSetting;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\View;
use App\Traits\StripeSettings;

class BillClientController extends BaseController
{
    use StripeSettings;

    public function __construct()
    {
        $this->setStripeConfigs();
    }

    public function getIndex(InvoiceDataTable $dataTable)
    {
        $user = CheckUser::isLogin();

        if ($user == 1)
            return Redirect::to('/');

        $client = Client::where('email', '=', $user)->orWhere('dni', '=', $user)->get();
        $global = GlobalSetting::all()->first();
        $data = array(
            "user" => $user,
            "name" => $client[0]->name,
            "company" => $global->company,
            "photo" => $client[0]->photo,
            "global" => $global,
        );

        return $dataTable->render('billsClient.index', $data);
    }

    public function postList()
    {
        $user = CheckUser::isLogin();

        if ($user == 1)
            return Redirect::to('/');

        $client = Client::where('email', '=', $user)->orWhere('dni', '=', $user)->get();

        $bills = BillCustomer::with('client')->where('client_id', $client[0]->id)->orderBy('id', 'desc')->get();

        return Response::json($bills);
    }


}
