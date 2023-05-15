<?php

namespace App\DataTables;

use App\models\Box;
use App\models\Zone;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class BoxDataTable extends DataTable
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
                return $styleb.'<a class="red del" href="#" id="'.$row->id.'"><i class="ace-icon fa fa-trash-o bigger-130"></i></a></div>';

            })
            ->rawColumns(['action']);
    }

    /**
     * Get query source of dataTable.
     *
     * @param \App\models\Box $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(Box $model)
    {
        return $model->join('users', 'boxes.user_id', '=', 'users.id')
            ->select('users.username As usname', 'boxes.id',
                'boxes.date_reg', 'boxes.amount', 'boxes.num_receipt',
                'boxes.detail', 'boxes.type', 'boxes.name As social')
            ->where('boxes.type', '=', 'ou');
    }

    /**
     * Optional method if you want to use html builder.
     *
     * @return \Yajra\DataTables\Html\Builder
     */
    public function html()
    {
        return $this->builder()
            ->setTableId('box-table')
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
                   window.LaravelDataTables["box-table"].buttons().container()
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
            __('app.businessName') => ['data' => 'social', 'name' => 'boxes.name'],
            __('app.detail') => ['data' => 'detail', 'name' => 'detail'],
            __('app.amount') => ['data' => 'amount', 'name' => 'amount'],
            __('app.voucher') => ['data' => 'num_receipt', 'name' => 'num_receipt'],
            __('app.date') => ['data' => 'date_reg', 'name' => 'date_reg'],
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
        return 'Box_' . date('YmdHis');
    }
}
