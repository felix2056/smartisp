<?php

namespace App\DataTables;

use App\models\Sms;
use App\models\ProductItem;
use App\models\Zone;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class ProductItemDataTable extends DataTable
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

                $styleb .= '<div class="btn-group btn-group-sm" role="group">
    <button id="btnGroupDrop1" type="button" class="btn btn-white btn-xs dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
      ...
    </button>
    <div class="dropdown-menu" aria-labelledby="btnGroupDrop1">';
                if($row->status == 'Internal Usages') {
                    $styleb .= '<button class="dropdown-item btn btn-xs" onclick="rentItem('.$row->id.')">Rent</button><br>
      <button class="dropdown-item btn btn-xs" onclick="sellItem('.$row->id.')">Sell</button><br>
      <button class="dropdown-item btn btn-xs" onclick="returnItem('.$row->id.')">Return</button>
    </div>
  </div>';

                } else if($row->status == 'In Stock') {
                    $styleb .= '<button class="dropdown-item btn btn-xs" onclick="internalUsagesItem('.$row->id.')">Internal Usage</button><br>
      <button class="dropdown-item btn btn-xs" onclick="rentItem('.$row->id.')">Rent</button><br>
      <button class="dropdown-item btn btn-xs" onclick="sellItem('.$row->id.')">Sell</button><br>
      <button class="dropdown-item btn btn-xs" onclick="itemAssignCustomer('.$row->id.')">Assign Customer</button><br>
    </div>
  </div>';

                }
                else if($row->status != 'In Stock' || $row->status != 'Internal Usages') {
                    $styleb .= '<button class="dropdown-item btn btn-xs" onclick="returnItem('.$row->id.')">Return</button>
    </div>
  </div>';
                }
                $history = route('inventory.items.history', $row->id);
                $styleb =  $styleb. '<a class="blue green" href="'.$history.'">
                                <i class="ace-icon fa fa-history bigger-130" aria-hidden="true"></i>
                            </a>
                            <a class="blue infor" onclick="editItem('.$row->id.')" href="javascript:;"   id="' .$row->id. '">
                                <i class="ace-icon fa fa-edit bigger-130"></i>
                            </a>';
                if($row->status == 'In Stock') {
                    $styleb .= '<a class="red del" href="javascript:;" onclick="deleteItem('.$row->id.')" id="' .$row->id. '"><i class="ace-icon fa fa-trash-o bigger-130"></i></a>';
                }



                return $styleb.'</div>';

            })
            ->editColumn('status', function ($row) {

                $classes = [
                    'In Stock' => 'primary',
                    'Assigned' => 'success',
                    'Returned' => 'info',
                    'Sold' => 'dark',
                    'Rented' => 'warning',
                    'Internal Usages' => 'secondary',
                ];
                return '<span class="badge badge-'.$classes[$row->status].'">'.$row->status.'</span';
            })
            ->rawColumns(['action', 'status', 'name']);
    }

    /**
     * Get query source of dataTable.
     *
     * @param \App\models\Sms $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(ProductItem $model)
    {
        $productId = request()->route('product');
        return $model->join('inv_products', 'inv_products.id', '=', 'inv_product_items.product_id')
            ->select(
                'inv_product_items.id',
                'inv_products.id as productId',
                'inv_products.name',
                'inv_product_items.supplier_invoice_id',
                'inv_product_items.bar_code',
                'inv_product_items.status',
                'inv_product_items.mark',
                'inv_product_items.photo',
                'inv_product_items.notes'
            )
            ->where('product_id', $productId);
    }

    /**
     * Optional method if you want to use html builder.
     *
     * @return \Yajra\DataTables\Html\Builder
     */
    public function html()
    {
        return $this->builder()
            ->setTableId('item-table')
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
                   window.LaravelDataTables["item-table"].buttons().container()
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
            __('app.ID') => ['data' => 'id', 'name' => 'id'],
            __('app.product') => ['data' => 'name', 'name' => 'name', 'searchable' => false, 'orderable' => false],
            __('app.supplierInvoiceId') => ['data' => 'supplier_invoice_id', 'name' => 'supplier_invoice_id'],
            __('app.barCode') => ['data' => 'bar_code', 'name' => 'bar_code'],
            __('app.status') => ['data' => 'status', 'name' => 'status'],
            __('app.mark') => ['data' => 'mark', 'name' => 'mark'],
            __('app.photo') => ['data' => 'photo', 'name' => 'photo'],
            __('app.notes') => ['data' => 'notes', 'name' => 'notes'],
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
        return 'Items_' . date('YmdHis');
    }
}
