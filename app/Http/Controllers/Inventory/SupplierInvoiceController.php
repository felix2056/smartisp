<?php

namespace App\Http\Controllers\Inventory;

use App\Classes\Reply;
use App\DataTables\SupplierInvoiceDataTable;
use App\Http\Controllers\AdminBaseController;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Inventory\SupplierInvoice\StoreRequest;
use App\Http\Requests\Admin\Inventory\SupplierInvoice\UpdateRequest;
use App\models\ItemHistory;
use App\models\Product;
use App\models\ProductItem;
use App\models\Supplier;
use App\models\SupplierInvoice;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SupplierInvoiceController extends AdminBaseController
{
    public function __construct()
    {
        parent::__construct();
    }

    public function index(SupplierInvoiceDataTable $dataTable) {

        return $dataTable->render('inventory.supplier-invoices.index', $this->data);
    }

    public function create()
    {
        $this->suppliers = Supplier::all();
        $this->products = Product::all();
        return view('inventory.supplier-invoices.create', $this->data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     */
    public function store(StoreRequest $request)
    {
        $supplierInvoice = new SupplierInvoice();
        $supplierInvoice = $this->storeUpdate($supplierInvoice, $request);

        return Reply::success("Invoice successfully added.");
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
        $this->suppliers = Supplier::all();
        $this->invoice = SupplierInvoice::find($id);
        $this->products = Product::all();
        return view('inventory.supplier-invoices.edit', $this->data);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     */
    public function update(UpdateRequest $request, $id)
    {
        $supplierInvoice = SupplierInvoice::find($id);
        $supplierInvoice = $this->storeUpdate($supplierInvoice, $request);

        return Reply::success("SupplierInvoice successfully updated.");
    }

    /**
     * Remove the specified resource from storage
     * @param  int  $id
     */
    public function destroy($id)
    {
        $supplierInvoice = SupplierInvoice::find($id);

        if(!$supplierInvoice) {
            return Reply::error('Requested resource does not found.');
        }

        $itemIds = ProductItem::where('supplier_invoice_id', $supplierInvoice->id)->pluck('id')->toArray();

        // delete item history
        ItemHistory::whereIn('item_id', $itemIds)->delete();

        // delete items before delete invoice
        ProductItem::where('supplier_invoice_id', $supplierInvoice->id)->delete();

        $supplierInvoice->delete();

        return Reply::success('SupplierInvoice successfully removed.');
    }


    /**
     * Show the form for add bar code for the specified invoice.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function addBarcode($id)
    {
        $this->invoice = SupplierInvoice::with('product_items')->find($id);
        return view('inventory.supplier-invoices.add-bar-code', $this->data);
    }


    public function storeBarcode(Request $request, $id)
    {
        $invoice = SupplierInvoice::with('product_items')->find($id);
        $data = $request->all();

        foreach($invoice->product_items as $item) {
            $item->bar_code = $data['bar_code_'.$item->id];
            $item->save();
        }

        return Reply::success('Barcode successfully uploaded.');
    }

    /**
     * Show the form for add serial code for the specified invoice.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function addSerialNumber($id)
    {
        $this->invoice = SupplierInvoice::with('product_items')->find($id);
        return view('inventory.supplier-invoices.add-serial-number', $this->data);
    }


    public function storeSerialNumber(Request $request, $id)
    {
        $invoice = SupplierInvoice::with('product_items')->find($id);
        $data = $request->all();

        foreach($invoice->product_items as $item) {
            $item->serial_code = $data['serial_code_'.$item->id];
            $item->save();
        }

        return Reply::success('Serial number successfully uploaded.');
    }

    private function storeUpdate($supplierInvoice, $request)
    {
        DB::beginTransaction();
        $supplierInvoice->supplier_id = $request->supplier_id;
        $supplierInvoice->invoice_number = $request->invoice_number;
        $supplierInvoice->amount = array_sum($request->total);
        $supplierInvoice->invoice_date = Carbon::createFromFormat('d/m/Y', $request->invoice_date)->format('Y-m-d');
        $supplierInvoice->save();

        $product_item_data = [];

        // get id ids into a seprate array we we can remove that does not exists in this array
        $ids = [];
        foreach ($request->pos as $pos_key => $pos_value) {
            if(isset($request->id) && $request->id[$pos_key] != "") {
                $ids[] = (int)$request->id[$pos_key];
            }
        }

        $supplierInvoiceIds = $supplierInvoice->product_items->pluck('id')->toArray();

        $uniques = array_merge(array_diff($supplierInvoiceIds, $ids), array_diff($ids, $supplierInvoiceIds));

        // all the elements
        if(count($uniques) > 0) {
            foreach($uniques as $unique) {
                ProductItem::where('supplier_invoice_id', $supplierInvoice->id)->where('id', $unique)->delete();
            }
        }

        // create keys from pos
        foreach ($request->pos as $pos_key => $pos_value) {

            if($request->quantity[$pos_key] > 1) {
                for($i = 0; $i < $request->quantity[$pos_key]; $i++) {
                    $product_item_data['product_id'] = $request->products[$pos_key];
                    $product_item_data['supplier_invoice_id'] = $supplierInvoice->id;
                    $product_item_data['tax'] = $request->iva[$pos_key];
                    $product_item_data['quantity'] = 1;
                    $product_item_data['amount'] = $request->price[$pos_key];
                    $product_item_data['amount_with_tax'] = $request->total[$pos_key];

                    if(isset($request->id) && $request->id[$pos_key] != "" && $request->id[$pos_key] != "0") {
                        $productItem = ProductItem::find($request->id[$pos_key]);
                        $productItem->update($product_item_data);
                    } else {
                        ProductItem::create($product_item_data);
                    }
                }
            }


        }




        DB::commit();
        return $supplierInvoice;
    }
}
