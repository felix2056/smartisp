<?php

namespace App\Http\Controllers\Inventory;

use App\Classes\Reply;
use App\DataTables\ItemDataTable;
use App\DataTables\ItemHistoryDataTable;
use App\Http\Controllers\AdminBaseController;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Inventory\Item\AssignCustomerRequest;
use App\Http\Requests\Admin\Inventory\Item\InternalUsagesRequest;
use App\Http\Requests\Admin\Inventory\Item\RentItemCustomerRequest;
use App\Http\Requests\Admin\Inventory\Item\ReturnItemRequest;
use App\Http\Requests\Admin\Inventory\Item\SellItemCustomerRequest;
use App\Http\Requests\Admin\Inventory\Item\StoreRequest;
use App\Http\Requests\Admin\Inventory\Item\UpdateRequest;
use App\models\BillCustomer;
use App\models\Client;
use App\models\GlobalSetting;
use App\models\ItemHistory;
use App\models\Product;
use App\models\ProductItem;
use App\models\RecurringInvoice;
use App\models\Router;
use App\models\User;
use App\Service\CommonService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class ItemController extends AdminBaseController
{
    public function __construct()
    {
        parent::__construct();
    }

    public function index(ItemDataTable $dataTable) {

        return $dataTable->render('inventory.item.index', $this->data);
    }

    public function create()
    {
        $this->products = Product::all();
        return view('inventory.item.create', $this->data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     */
    public function store(StoreRequest $request)
    {
        $item = new ProductItem();
        $item = $this->storeUpdate($item, $request);

        return Reply::success("Item successfully added.");
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $this->item = ProductItem::find($id);
        $this->products = Product::all();

        return view('inventory.item.edit', $this->data);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     */
    public function update(UpdateRequest $request, $id)
    {
        $item = ProductItem::find($id);
        $item = $this->storeUpdate($item, $request);

        return Reply::success("Item successfully updated.");
    }

    /**
     * Remove the specified resource from storage
     * @param  int  $id
     */
    public function destroy($id)
    {
        $item = ProductItem::find($id);

        if(!$item) {
            return Reply::error('Requested resource does not found.');
        }

        $item->delete();

        return Reply::success('Item successfully removed.');
    }

    private function storeUpdate($item, $request)
    {
        $item->product_id = $request->product_id;
        $item->bar_code = $request->bar_code;
        $item->serial_code = $request->serial_number;
        $item->amount_with_tax = $request->amount_with_tax;
        $item->notes = $request->notes;

        if($request->has('file')) {
            File::ensureDirectoryExists(public_path() . '/assets/images/products');
            $destinationPath = public_path() . '/assets/images/products/';
            $ext = pathinfo($request->file->getClientOriginalName(), PATHINFO_EXTENSION);

            $filename = md5($request->file->getClientOriginalName()).'.'.$ext;

            $upload_success = $request->file->move($destinationPath, $filename);

            if($upload_success) {
                $item->photo = $filename;
            }
        }


        $item->save();

        return $item;
    }

    /**
     * Show the form item history.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function internalUsagesModel($id)
    {
        $this->item = ProductItem::find($id);
        $this->users = User::where('email', '!=', 'support@smartisp.us')
            ->where('level' , 'us')
            ->orderBy('id', 'asc')
            ->get();

        return view('inventory.item.internal-usages-model', $this->data);
    }

    /**
     * Save item history
     * @param InternalUsagesRequest $request
     * @param  int $id
     * @return array
     */
    public function internalUsagesSave(InternalUsagesRequest $request, $id)
    {
        $item = ProductItem::find($id);

        if(!$item) {
            return Reply::error('Item not found.s');
        }

        // change item status
        $item->status = 'Internal Usages';
        $item->save();

        $user = User::find($request->user_id);
        $itemHistory = new ItemHistory();
        $itemHistory->status = 'Internal usage Administrator:'.$user->name;
        $itemHistory->item_id = $id;
        $itemHistory->notes = $request->notes;
        $itemHistory->date_time = Carbon::now()->format('Y-m-d H:i:s');
        $itemHistory->current_status = 'in_use';
        $itemHistory->save();

        return Reply::success("Item saved.");
    }

    public function history(ItemHistoryDataTable $dataTable, $id)
    {
        $this->productItem = ProductItem::find($id);

        if(!$this->productItem) {
            abort(404);
        }

        $this->itemId = $id;

        return $dataTable->render('inventory.item-history.index', $this->data);

    }

    public function returnItemModel($id)
    {
        $this->item = ProductItem::find($id);
        return view('inventory.item.return-item-model', $this->data);
    }

    /**
     * Save item history
     * @param InternalUsagesRequest $request
     * @param  int $id
     * @return array
     */
    public function returnItemSave(ReturnItemRequest $request, $id)
    {
        $item = ProductItem::find($id);

        if(!$item) {
            return Reply::error('Item not found.s');
        }

        DB::beginTransaction();

        if($item->status == 'Rented') {
            RecurringInvoice::where('item_id', $id)->delete();
        }

        ItemHistory::where('item_id', $id)->update(['current_status' => 'not_in_use']);

        // change item status
        $item->status = $request->status;
        $item->mark = $request->mark;
        $item->save();

        $itemHistory = new ItemHistory();
        $itemHistory->item_id = $id;
        $itemHistory->status = 'In stock';
        $itemHistory->notes = $request->notes;
        $itemHistory->date_time = Carbon::now()->format('Y-m-d H:i:s');
        $itemHistory->current_status = 'in_use';
        $itemHistory->save();

        DB::commit();

        return Reply::success("Item saved.");
    }

    public function itemAssignCustomerModel($id)
    {
        $this->item = ProductItem::find($id);
        return view('inventory.item.assign-customer-model', $this->data);
    }

    /**
     * Save item history
     * @param InternalUsagesRequest $request
     * @param  int $id
     * @return array
     */
    public function itemAssignCustomerSave(AssignCustomerRequest $request, $id)
    {
        $item = ProductItem::find($id);

        if(!$item) {
            return Reply::error('Item not found.s');
        }

        // change item status
        $item->status = 'Assigned';
        $item->save();

        $client = Client::find($request->client_id);
        $itemHistory = new ItemHistory();
        $itemHistory->status = 'Assigned: <a href="'. route('billing', $client->id) .'">'.$client->name.'</a>';
        $itemHistory->item_id = $id;
        $itemHistory->client_id = $client->id;
        $itemHistory->notes = $request->notes;
        $itemHistory->date_time = Carbon::now()->format('Y-m-d H:i:s');
        $itemHistory->current_status = 'in_use';
        $itemHistory->save();

        return Reply::success("Item saved.");
    }


    // rent item model
    public function itemRentCustomerModel($id)
    {
        $this->item = ProductItem::find($id);
        $this->routers = Router::select('id', 'name')->get();
        return view('inventory.item.rent-item-customer-model', $this->data);
    }

    /**
     * Save item  rent history
     * @param RentItemCustomerRequest $request
     * @param  int $id
     * @return array
     */
    public function itemRentCustomerSave(RentItemCustomerRequest $request, $id)
    {
        $item = ProductItem::find($id);

        if(!$item) {
            return Reply::error('Item not found.');
        }

        // change item status
        $item->status = 'Rented';
        $item->save();

        $client = Client::find($request->client_id);
        $itemHistory = new ItemHistory();
        $itemHistory->status = 'Rent: <a href="'. route('billing', $client->id) .'">'.$client->name.'</a>';
        $itemHistory->item_id = $id;
        $itemHistory->client_id = $client->id;
        $itemHistory->notes = $request->notes ? $request->notes : '';
        $itemHistory->date_time = Carbon::now()->format('Y-m-d H:i:s');
        $itemHistory->current_status = 'in_use';
        $itemHistory->save();

        // create recurring invoice
        $invoice_items_data = [];

        $invoice_data = [
            'start_date' => Carbon::now(),
            'client_id' => $request->client_id,
            'router_id' => $request->router_id,
            'item_id' => $id,
            'memorandum' => $request->memo,
            'frequency' => 'month',
            'price' => array_sum($request->total),
            'note' => $request->notes ? $request->notes : '',
        ];


        DB::beginTransaction();

        // create invoice
        $recurringInvoice = $client->recurring_invoices()->create($invoice_data);

        // create keys from pos
        foreach ($request->pos as $pos_key => $pos_value) {
            $invoice_item_data = [];
            // loop through request variables except keys
            foreach ($request->all() as $input_field => $input_value) {
                if (gettype($input_value) === 'array' && !in_array($input_field, ['pos', 'id'])) {
                    $invoice_item_data[$input_field] = $input_value[$pos_key];
                }
            }
            $invoice_item_data['recurring_invoice_id'] = $recurringInvoice->id;
            $invoice_item_data['created_at'] = Carbon::now()->format('Y-m-d H:i:s');
            $invoice_item_data['updated_at'] = Carbon::now()->format('Y-m-d H:i:s');
            array_push($invoice_items_data, $invoice_item_data);
        }

        // create invoice_items
        $recurringInvoice->items()->insert($invoice_items_data);

        // generate invoice for this rent product

        $invoice_num = CommonService::getBillNumber();
        $period = Carbon::now()->addMonths(1)->format('Y-m-d');

        $invoice_items_data = [];

        $invoice_data = [
            'num_bill' => $invoice_num,
            'start_date' => Carbon::now()->format('Y-m-d'),
            'billing_type' => 'recurring',
            'period' => $period,
            'release_date' => Carbon::now()->format('Y-m-d'),
            'expiration_date' => $period,
            'client_id' => $client->id,
            'open' => 0,
            'note' => $request->notes ? $request->notes : 'Product rent',
            'memo' => $request->meno,
            'use_transactions' => 0,
            'recurring_invoice' => 'yes',
        ];

        $invoice_data['total_pay'] = array_sum($request->total);

        if ((float) $client->wallet_balance >= (float) $invoice_data['total_pay']) {
            $invoice_data['status'] =  2;
            $invoice_data['paid_on'] = Carbon::now()->format('Y-m-d');
            $client->wallet_balance -= array_sum($request->total);
        }
        else {
            $invoice_data['status'] = 3;
            $client->balance = round($client->balance - array_sum($request->total), 2);
        }

        // create invoice
        $invoice = $client->invoices()->create($invoice_data);

        // create keys from pos
        foreach ($request->pos as $pos_key => $pos_value) {
            $invoice_item_data = [];
            // loop through request variables except keys
            foreach ($request->all() as $input_field => $input_value) {
                if (gettype($input_value) === 'array' && !in_array($input_field, ['pos', 'id'])) {
                    $invoice_item_data[$input_field] = $input_value[$pos_key];
                }
            }
            $invoice_item_data['bill_customer_id'] = $invoice->id;
            $invoice_item_data['created_at'] = Carbon::now()->format('Y-m-d H:i:s');
            $invoice_item_data['updated_at'] = Carbon::now()->format('Y-m-d H:i:s');
            array_push($invoice_items_data, $invoice_item_data);
        }

        $client->save();

        $transaction_data = [
            'client_id' => $client->id,
            'amount' => $invoice_data['total_pay'],
            'category' => 'recurring',
            'date' => Carbon::now()->format('Y-m-d'),
            'quantity' => '1',
            'account_balance' => $client->wallet_balance,
            'description' => 'Service charges'
        ];

        // create transaction
        $transaction = $client->transactions()->create($transaction_data);

        // create invoice_items
        $invoice->invoice_items()->insert($invoice_items_data);

        $recurringInvoice->next_pay_date = Carbon::parse($period)->addDay();
        $recurringInvoice->expiration_date = $period;
        $recurringInvoice->save();

        DB::commit();

        return Reply::success("Item saved.");
    }

    // rent item model
    public function itemSellCustomerModel($id)
    {
        $this->item = ProductItem::find($id);
        return view('inventory.item.sell-item-customer-model', $this->data);
    }

    /**
     * Save item  rent history
     * @param RentItemCustomerRequest $request
     * @param  int $id
     * @return array
     */
    public function itemSellCustomerSave(SellItemCustomerRequest $request, $id)
    {
        $item = ProductItem::find($id);

        if(!$item) {
            return Reply::error('Item not found.s');
        }

        \DB::beginTransaction();

        // change item status
        $item->status = 'Sold';
        $item->save();

        $client = Client::find($request->client_id);
        $itemHistory = new ItemHistory();
        $itemHistory->status = 'Sold: <a href="'. route('billing', $client->id) .'">'.$client->name.'</a>';
        $itemHistory->item_id = $id;
        $itemHistory->client_id = $client->id;
        $itemHistory->date_time = Carbon::now()->format('Y-m-d H:i:s');
        $itemHistory->current_status = 'in_use';
        $itemHistory->save();


        // set invoice cortado date for block service for this client
        $cortadoDate = null;

        if($client->billing_settings) {
            $cortadoDate = Carbon::createFromFormat('d', $client->billing_settings->billing_due_date)->format('Y-m-d');
        }


        $invoice_items_data = [];

        $invoice_data = [
            'num_bill' => CommonService::getBillNumber(),
            'billing_type' => 'none',
            'period' => $cortadoDate,
            'release_date' => Carbon::now()->format('Y-m-d'),
            'expiration_date' => $cortadoDate,
            'client_id' => $client->id,
            'open' => 0,
            'cortado_date' => $cortadoDate
        ];

        $invoice_data['total_pay'] = array_sum($request->total);

        $invoice_data['start_date'] = Carbon::now();

        if ((float) $client->wallet_balance >= (float) $invoice_data['total_pay']) {
            $invoice_data['status'] =  2;
            $invoice_data['paid_on'] = Carbon::now()->format('Y-m-d');
            $client->wallet_balance -= array_sum($request->total);
        }
        else {
            $invoice_data['status'] = 3;

            $client->balance = round($client->balance - array_sum($request->total), 2);
        }

        // create invoice
        $invoice = $client->invoices()->create($invoice_data);

        // create keys from pos
        foreach ($request->pos as $pos_key => $pos_value) {
            $invoice_item_data = [];
            // loop through request variables except keys
            foreach ($request->all() as $input_field => $input_value) {
                if (gettype($input_value) === 'array' && !in_array($input_field, ['pos', 'id'])) {
                    $invoice_item_data[$input_field] = $input_value[$pos_key];
                }
            }
            $invoice_item_data['bill_customer_id'] = $invoice->id;
            $invoice_item_data['created_at'] = Carbon::now()->format('Y-m-d H:i:s');
            $invoice_item_data['quantity'] = 1;
            $invoice_item_data['unit'] = 1;
            $invoice_item_data['updated_at'] = Carbon::now()->format('Y-m-d H:i:s');
            array_push($invoice_items_data, $invoice_item_data);
        }

        $client->save();

        $transaction_data = [
            'client_id' => $client->id,
            'amount' => $invoice_data['total_pay'],
            'category' => 'service',
            'date' => Carbon::now()->format('Y-m-d'),
            'quantity' => '1',
            'account_balance' => $client->wallet_balance,
            'description' => 'Service charges'
        ];

        // create transaction
        $transaction = $client->transactions()->create($transaction_data);

        // create invoice_items
        $invoice->invoice_items()->insert($invoice_items_data);

        \DB::commit();

        return Reply::success("Item saved.");
    }

}
