<?php

namespace App\DataTables;

use App\models\Sms;
use App\models\Supplier;
use App\models\SupplierInvoice;
use App\models\Zone;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class SupplierInvoiceDataTable extends DataTable
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

                return $styleb. '<a class="green infor" title="Add Bar Code" onclick="addBarCodeSupplierInvoice('.$row->id.')" href="javascript:;"   id="' .$row->id. '">
<i class="ace-icon fa fa-barcode bigger-130"></i></a>
<a class="red infor" onclick="addSerialCodeSupplierInvoice('.$row->id.')" href="javascript:;" title="Add serial number"  id="' .$row->id. '">
    <i class="ace-icon fa fa-barcode bigger-130"></i>
</a>
<a class="blue infor" onclick="editSupplierInvoice('.$row->id.')" href="javascript:;"   id="' .$row->id. '">
    <i class="ace-icon fa fa-edit bigger-130"></i>
</a>
<a class="red del" href="javascript:;" onclick="deleteSupplierInvoice('.$row->id.')" id="' .$row->id. '"><i class="ace-icon fa fa-trash-o bigger-130"></i></a>
</div>';

            })
            ->editColumn('invoice_date', function ($row) {
                if($row->invoice_date) {
                    return $row->invoice_date->format('Y-m-d H:i:s');
                }
            })
//            ->editColumn('serial_code', function ($row) {
//                if($row->product_items) {
//                    $serial = '';
//                    foreach ($row->product_items as $item) {
//                        $serial .= '<p>' . $item->serial_code . '</p>';
//                    }
//
//                    if ($serial == '') {
//                        $serial = '---';
//                    }
//                    return $serial;
//                }
//            })
            ->editColumn('supplier.name', function ($row) {
                return '<a href="'.route('inventory.suppliers.show', $row->supplier_id).'">'.$row->supplier->name.'</a>';
            })
            ->rawColumns(['action', 'tax_included', 'supplier.name']);
    }

    /**
     * Get query source of dataTable.
     *
     * @param \App\models\Sms $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(SupplierInvoice $model)
    {
        $supplierId = request()->route('supplier');

        $query = $model->with('supplier', 'product_items')->select('inv_supplier_invoice.id', 'supplier_id', 'invoice_number', 'file', 'invoice_date');

        if($supplierId) {
            $query  = $query->where('supplier_id', $supplierId);
        }

        return $query;
    }

    /**
     * Optional method if you want to use html builder.
     *
     * @return \Yajra\DataTables\Html\Builder
     */
    public function html()
    {
        return $this->builder()
            ->setTableId('supplier-invoices-table')
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
                   window.LaravelDataTables["supplier-invoices-table"].buttons().container()
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
        $supplierId = request()->route('supplier');
        return [
            __('app.supplier') => ['data' => 'supplier.name', 'name' => 'supplier.name', 'visible' => $supplierId ? false : true],
            __('app.supplierInvoiceNumber') => ['data' => 'invoice_number', 'name' => 'invoice_number'],
            __('app.invoiceFile') => ['data' => 'file', 'name' => 'file'],
            __('app.date') => ['data' => 'invoice_date', 'name' => 'invoice_date'],
//            'Serial number' => ['data' => 'serial_code', 'name' => 'serial_code', 'sortable' => false, 'searchable' => false],
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
        return 'Supplier_invoice' . date('YmdHis');
    }
}
