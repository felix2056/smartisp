<?php

namespace App\DataTables;

use App\models\Sms;
use App\models\Product;
use App\models\Zone;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class VendorProductDataTable extends DataTable
{
    /**
     * Build DataTable class.
     *
     * @param mixed $query Results from query() method.
     * @return \Yajra\DataTables\DataTableAbstract
     */
    public function dataTable($query)
    {
        return datatables()
            ->eloquent($query)
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
            ->rawColumns(['action', 'photo', 'name', 'vendor.name']);
    }

    /**
     * Get query source of dataTable.
     *
     * @param \App\models\Sms $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(Product $model)
    {
        $vendorId  = request()->route('vendor');
        return $model->with('vendor', 'product_items')
            ->where('vendor_id', $vendorId)
            ->select('id', 'vendor_id', 'inv_products.name', 'sell_price', 'rent_price', 'photo');
    }

    /**
     * Optional method if you want to use html builder.
     *
     * @return \Yajra\DataTables\Html\Builder
     */
    public function html()
    {
        return $this->builder()
            ->setTableId('product-table')
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->dom("<'row'<'col-md-6'l><'col-md-6'Bf>><'row'<'col-sm-12'tr>><'row'<'col-sm-6'i><'col-sm-6'p>>")
            ->orderBy(0, "desc")
            ->destroy(true)
            ->responsive(true)
            ->serverSide(true)
            ->processing(true)
            ->language(__("app.datatable"))
            ->buttons(
                Button::make(['extend' => 'export', 'buttons' => ['excel', 'csv'], 'text' => '<i class="fa fa-download"></i> Export &nbsp;<span class="caret"></span>'])
            )
            ->parameters([
                'initComplete' => 'function () {
                   window.LaravelDataTables["product-table"].buttons().container()
                    .prependTo( ".widget-header .widget-toolbar")
                }',
            ]);
    }

    /**
     * Get columns.
     *
     * @return array
     */
    protected function getColumns()
    {
        return [
            __('app.name') => ['data' => 'name', 'name' => 'name'],
            __('app.sellPrice') => ['data' => 'sell_price', 'name' => 'sell_price'],
            __('app.rentPrice') => ['data' => 'rent_price', 'name' => 'rent_price'],
            __('app.photo') => ['data' => 'photo', 'name' => 'photo'],
            __('app.inStock') => ['data' => 'in_stock', 'name' => 'in_stock', 'searchable' => false, 'orderable' => false],
            __('app.internalUsages') => ['data' => 'internal_usages', 'name' => 'internal_usages', 'searchable' => false, 'orderable' => false],
            __('app.rented') => ['data' => 'rented', 'name' => 'rented', 'searchable' => false, 'orderable' => false],
            __('app.sold') => ['data' => 'sold', 'name' => 'sold', 'searchable' => false, 'orderable' => false],
            __('app.returned') => ['data' => 'returned', 'name' => 'returned', 'searchable' => false, 'orderable' => false],
            __('app.assigned') => ['data' => 'assigned', 'name' => 'assigned', 'searchable' => false, 'orderable' => false],
            Column::computed('action', __('app.operations'))
                ->exportable(false)
                ->printable(false)
                ->searchable(false)
                ->orderable(false)
                ->width(60)
                ->addClass('text-center'),

        ];
    }

    /**
     * Get filename for export.
     *
     * @return string
     */
    protected function filename()
    {
        return 'Product_' . date('YmdHis');
    }
}
