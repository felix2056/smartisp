<?php

namespace App\Http\Controllers\Inventory;

use App\Classes\Reply;
use App\DataTables\SupplierDataTable;
use App\DataTables\SupplierInvoiceDataTable;
use App\Http\Controllers\AdminBaseController;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\Inventory\Supplier\StoreRequest;
use App\Http\Requests\Admin\Inventory\Supplier\UpdateRequest;
use App\models\Product;
use App\models\Supplier;
use App\models\Vendor;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
class SupplierController extends AdminBaseController
{
    public function __construct()
    {
        parent::__construct();
    }

    public function index(SupplierDataTable $dataTable) {

        return $dataTable->render('inventory.supplier.index', $this->data);
    }

    public function create(){

        return view('inventory.supplier.create', $this->data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     */
    public function store(StoreRequest $request)
    {
        $supplier = new Supplier();
        $supplier = $this->storeUpdate($supplier, $request);

        return Reply::success("Supplier successfully added.");
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(SupplierInvoiceDataTable $dataTable, $id)
    {
        $this->supplier = Supplier::find($id);
        $this->supplierVendors = Vendor::join('inv_products', 'inv_products.vendor_id', '=', 'inv_vendors.id')
            ->join('inv_product_items', 'inv_product_items.product_id', '=', 'inv_products.id')
            ->join('inv_supplier_invoice', 'inv_supplier_invoice.id', '=', 'inv_product_items.supplier_invoice_id')
            ->join('inv_supplier', 'inv_supplier.id', '=', 'inv_supplier_invoice.supplier_id')
            ->where('inv_supplier_invoice.supplier_id', $id)
            ->select('inv_vendors.id', 'inv_vendors.name')
            ->groupBy('inv_vendors.id')
            ->get();

        return $dataTable->render('inventory.supplier.show', $this->data);
    }

    public function supplierProductData(Request $request, $id)
    {
        $products = Product::with('vendor', 'product_items')
            ->join('inv_product_items', 'inv_product_items.product_id', '=', 'inv_products.id')
            ->join('inv_supplier_invoice', 'inv_supplier_invoice.id', '=', 'inv_product_items.supplier_invoice_id')
            ->join('inv_supplier', 'inv_supplier.id', '=', 'inv_supplier_invoice.supplier_id')
            ->where('inv_supplier_invoice.supplier_id', $id)
            ->groupBy('inv_products.id')
            ->select('inv_products.id', 'inv_products.vendor_id', 'inv_products.name', 'inv_products.sell_price', 'inv_products.rent_price', 'inv_products.photo');


        return DataTables::of($products)
            ->addColumn('action', function ($row) {
                $styleb = '<div class="action-buttons">';

                return $styleb. '<a class="blue infor" onclick="editProduct('.$row->id.')" href="javascript:;"   id="' .$row->id. '">
<i class="ace-icon fa fa-edit bigger-130"></i></a>
<a class="red del" href="javascript:;" onclick="deleteProduct('.$row->id.')" id="' .$row->id. '"><i class="ace-icon fa fa-trash-o bigger-130"></i></a>
</div>';

            })
            ->editColumn('name', function ($row) {
                return '<a href="'.route('inventory.products.show', $row->id).'">'.$row->name.'</a>';
            })
            ->editColumn('vendor.name', function ($row) {
                return '<a href="'.route('inventory.vendors.show', $row->vendor->id).'">'.$row->vendor->name.'</a>';
            })
            ->editColumn('photo', function ($row) {
                if(strlen($row->photo) > 0) {
                    $photo = $row->photo;
                    $fileUrl = asset("assets/images/products/$photo");
                    return '<img src="'.$fileUrl.'" class="img-responsive" width="50px"/>';
                }
                return '';
            })
            ->editColumn('in_stock', function ($row) {
                return $row->product_items->where('status', 'In Stock')->count();
            })
            ->editColumn('assigned', function ($row) {
                return $row->product_items->where('status', 'Assigned')->count();
            })
            ->editColumn('returned', function ($row) {
                return $row->product_items->where('status', 'Returned')->count();
            })
            ->editColumn('sold', function ($row) {
                return $row->product_items->where('status', 'Sold')->count();
            })
            ->editColumn('rented', function ($row) {
                return $row->product_items->where('status', 'Rented')->count();
            })
            ->editColumn('internal_usages', function ($row) {
                return $row->product_items->where('status', 'Internal Usages')->count();
            })
            ->rawColumns(['action', 'photo', 'name', 'vendor.name'])
            ->make(true);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $this->supplier = Supplier::find($id);
        return view('inventory.supplier.edit', $this->data);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     */
    public function update(UpdateRequest $request, $id)
    {
        $supplier = Supplier::find($id);
        $supplier = $this->storeUpdate($supplier, $request);

        return Reply::success("Supplier successfully updated.");
    }

    /**
     * Remove the specified resource from storage
     * @param  int  $id
     */
    public function destroy($id)
    {
        $supplier = Supplier::find($id);

        if(!$supplier) {
            return Reply::error('Requested resource does not found.');
        }

        $supplier->delete();

        return Reply::success('Supplier successfully removed.');
    }

    private function storeUpdate($supplier, $request)
    {
        $supplier->name = $request->name;
        $supplier->contact_name = $request->contact_name;
        $supplier->email = $request->email;
        $supplier->address = $request->address;
        $supplier->phone = $request->phone;
        $supplier->tax_included = $request->tax_included ? $request->tax_included : 0;
        $supplier->save();

        return $supplier;
    }
}
