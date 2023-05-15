<?php

namespace App\DataTables;

use App\models\Sms;
use App\models\Supplier;
use App\models\Zone;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class SupplierDataTable extends DataTable
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

                return $styleb. '<a class="blue infor" onclick="editSupplier('.$row->id.')" href="javascript:;"   id="' .$row->id. '">
<i class="ace-icon fa fa-edit bigger-130"></i></a>
<a class="red del" href="javascript:;" onclick="deleteSupplier('.$row->id.')" id="' .$row->id. '"><i class="ace-icon fa fa-trash-o bigger-130"></i></a>
</div>';

            })
            ->editColumn('tax_included', function ($row) {
                if($row->tax_included) {
                    return "<span class=\"badge badge-success\">Yes</span>";

                } else {
                    return "<span class=\"badge badge-danger\">No</span>";
                }
            })
            ->editColumn('name', function ($row) {
                return '<a href="'.route('inventory.suppliers.show', $row->id).'">'.$row->name.'</a>';
            })
            ->rawColumns(['action', 'tax_included', 'name']);
    }

    /**
     * Get query source of dataTable.
     *
     * @param \App\models\Sms $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(Supplier $model)
    {
        return $model->select('id', 'name', 'address', 'contact_name', 'email', 'phone', 'tax_included');
    }

    /**
     * Optional method if you want to use html builder.
     *
     * @return \Yajra\DataTables\Html\Builder
     */
    public function html()
    {
        return $this->builder()
            ->setTableId('supplier-table')
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
                   window.LaravelDataTables["supplier-table"].buttons().container()
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
            __('app.address') => ['data' => 'address', 'name' => 'address'],
            __('app.contactName') => ['data' => 'contact_name', 'name' => 'contact_name'],
            __('app.email') => ['data' => 'email', 'name' => 'email'],
            __('app.phone') => ['data' => 'phone', 'name' => 'phone'],
            __('app.taxIncluded') => ['data' => 'tax_included', 'name' => 'tax_included'],
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
        return 'Supplier_' . date('YmdHis');
    }
}
