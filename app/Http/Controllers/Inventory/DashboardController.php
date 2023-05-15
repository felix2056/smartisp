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
use App\models\ProductItem;
use App\models\Supplier;
use App\models\SupplierInvoice;
use App\models\Vendor;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;
class DashboardController extends AdminBaseController
{
    public function __construct()
    {
        parent::__construct();
    }

    public function index()
    {

        $this->itemCount = ProductItem::count();
        $this->productsCount = Product::count();
        $this->supplierCount = Supplier::count();
        $this->invoicesCount = SupplierInvoice::count();

        $productItems = ProductItem::all();

        $data = [
            ['In Stock', $productItems->where('status', 'In Stock')->count()],
            ['Assigned', $productItems->where('status', 'Assigned')->count()],
            ['Returned', $productItems->where('status', 'Returned')->count()],
            ['Sold', $productItems->where('status', 'Sold')->count()],
            ['Rented', $productItems->where('status', 'Rented')->count()],
            ['Internal Usages', $productItems->where('status', 'Internal Usages')->count()],
        ];
        $this->chart = $data;

        return view('inventory.dashboard.index', $this->data);
    }
}
