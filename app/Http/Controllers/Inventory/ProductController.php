<?php

namespace App\Http\Controllers\Inventory;

use App\Classes\Reply;
use App\DataTables\ProductDataTable;
use App\DataTables\ProductItemDataTable;
use App\Http\Controllers\AdminBaseController;
use App\Http\Requests\Admin\Inventory\Product\StoreRequest;
use App\Http\Requests\Admin\Inventory\Product\UpdateRequest;
use App\models\Product;
use App\models\Vendor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class ProductController extends AdminBaseController
{
    public function __construct()
    {
        parent::__construct();
    }

    public function index(ProductDataTable $dataTable) {

        return $dataTable->render('inventory.product.index', $this->data);
    }

    public function create()
    {
        $this->vendors = Vendor::all();
        return view('inventory.product.create', $this->data);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     */
    public function store(StoreRequest $request)
    {
        $product = new Product();
        $product = $this->storeUpdate($product, $request);

        return Reply::success("Product successfully added.");
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(ProductItemDataTable $dataTable, $id)
    {
        $this->product = Product::find($id);

        if(!$this->product) {
            abort(404);
        }

        return $dataTable->render('inventory.product.show', $this->data);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $this->product = Product::find($id);
        $this->vendors = Vendor::all();

        return view('inventory.product.edit', $this->data);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     */
    public function update(UpdateRequest $request, $id)
    {
        $product = Product::find($id);
        $product = $this->storeUpdate($product, $request);

        return Reply::success("Product successfully updated.");
    }

    /**
     * Remove the specified resource from storage
     * @param  int  $id
     */
    public function destroy($id)
    {
        $product = Product::find($id);

        if(!$product) {
            return Reply::error('Requested resource does not found.');
        }

        $product->delete();

        return Reply::success('Product successfully removed.');
    }

    private function storeUpdate($product, $request)
    {
        $product->name = $request->name;
        $product->sell_price = $request->sell_price;
        $product->rent_price = $request->rent_price;
        $product->vendor_id = $request->vendor_id;

        if($request->has('file')) {
            File::ensureDirectoryExists(public_path() . '/assets/images/products');
            $destinationPath = public_path() . '/assets/images/products/';
            $ext = pathinfo($request->file->getClientOriginalName(), PATHINFO_EXTENSION);

            $filename = md5($request->file->getClientOriginalName()).'.'.$ext;

            $upload_success = $request->file->move($destinationPath, $filename);

            if($upload_success) {
                $product->photo = $filename;
            }
        }

        $product->save();

        return $product;
    }
}
