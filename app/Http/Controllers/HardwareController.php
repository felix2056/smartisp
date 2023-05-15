<?php

namespace App\Http\Controllers;

use App\Classes\Reply;
use App\Http\Requests\Admin\Documents\ContractStoreRequest;
use App\Http\Requests\Admin\Documents\ContractUpdateRequest;
use App\Http\Requests\Admin\Documents\StoreRequest;
use App\Http\Requests\Admin\Documents\UpdateRequest;
use App\models\Document;
use App\models\ItemHistory;
use App\models\Template;
use App\Service\CommonService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;
use Yajra\DataTables\Facades\DataTables;
use Barryvdh\DomPDF\Facade as PDF;

class HardwareController extends BaseController
{
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
     * @param Request $request
     * @param $clientId
     * @return mixed
     * @throws \Exception
     */
    public function postList(Request $request, $clientId)
	{
		$documents = ItemHistory::join('inv_product_items', 'inv_product_items.id', '=', 'inv_item_history.item_id')
			->join('inv_products', 'inv_products.id', '=', 'inv_product_items.product_id')
			->select('inv_product_items.id', 'inv_products.name', 'inv_product_items.bar_code', 'inv_product_items.status')
			->where('inv_item_history.client_id', $clientId)
			->wherein('inv_product_items.status', ['Assigned', 'Sold', 'Rented'])->groupBy('inv_product_items.id');

		return Datatables::of($documents)
//		->editColumn('created_at', function ($row) {
//			return Carbon::parse($row->created_at)->format('m/d/Y');
//		})
//		->rawColumns(['action'])
		->make(true);
	}
	
}
