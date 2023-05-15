<?php

namespace App\Http\Controllers\Inventory;

use App\Classes\Reply;
use App\DataTables\VendorDataTable;
use App\DataTables\VendorProductDataTable;
use App\Http\Controllers\AdminBaseController;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Inventory\Vendor\StoreRequest;
use App\Http\Requests\Admin\Inventory\Vendor\UpdateRequest;
use App\models\Supplier;
use App\models\Vendor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class VendorController extends AdminBaseController
{
    public function __construct()
    {
        parent::__construct();
    }

    public function index(VendorDataTable $dataTable) {

        return $dataTable->render('inventory.vendor.index', $this->data);
    }

    public function create(){

        return view('inventory.vendor.create', $this->data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     */
    public function store(StoreRequest $request)
    {
        $vendor = new Vendor();
        $vendor = $this->storeUpdate($vendor, $request);

        return Reply::success("Vendor successfully added.");
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(VendorProductDataTable $dataTable, $id)
    {
        $this->vendor = Vendor::find($id);
        $this->vendorSuppliers = Vendor::join('inv_products', 'inv_products.vendor_id', '=', 'inv_vendors.id')
            ->join('inv_product_items', 'inv_product_items.product_id', '=', 'inv_products.id')
            ->join('inv_supplier_invoice', 'inv_supplier_invoice.id', '=', 'inv_product_items.supplier_invoice_id')
            ->join('inv_supplier', 'inv_supplier.id', '=', 'inv_supplier_invoice.supplier_id')
            ->where('inv_products.vendor_id', $id)
            ->select('inv_supplier.id', 'inv_supplier.name')
            ->groupBy('inv_supplier.id')
            ->get();

        return $dataTable->render('inventory.vendor.show', $this->data);

    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $this->vendor = Vendor::find($id);
        return view('inventory.vendor.edit', $this->data);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     */
    public function update(UpdateRequest $request, $id)
    {
        $vendor = Vendor::find($id);
        $vendor = $this->storeUpdate($vendor, $request);

        return Reply::success("Vendor successfully updated.");
    }

    /**
     * Remove the specified resource from storage
     * @param  int  $id
     */
    public function destroy($id)
    {
        $vendor = Vendor::find($id);

        if(!$vendor) {
            return Reply::error('Requested resource does not found.');
        }

        $vendor->delete();

        return Reply::success('Vendor successfully removed.');
    }

    private function storeUpdate($vendor, $request)
    {
        $vendor->name = $request->name;
        $vendor->save();

        return $vendor;
    }
}
