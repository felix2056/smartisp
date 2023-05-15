<?php

namespace App\DataTables;

use App\models\Notice;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class AdviceDataTable extends DataTable
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
                return '<div class="action-buttons">
                            <a class="red del" href="#" id="'.$row->id.'"><i class="ace-icon fa fa-trash-o bigger-130"></i></a>
                    </div>';
            })
            ->editColumn('type', function ($row) {
                if($row->name == 'screen')
                    return '<span class="label label-success arrowed">Aviso</span>';
                if($row->name == 'email')
                    return '<span class="label label-info arrowed">Email</span>';
            })
            ->editColumn('hits', function ($row) {
                return $row->hits . '/' .$row->total;
            });
    }

    /**
     * @param Notice $model
     * @return mixed
     */
    public function query(Notice $model)
    {
        return  Notice::join('templates', 'templates.id', '=', 'notices.template_id')
            ->join('routers', 'routers.id', '=', 'notices.router_id')
            ->select('notices.id', 'notices.name', 'notices.type', 'notices.hits',
                'notices.registered As noticesreg', 'templates.name As tempname', 'routers.name As routername', 'notices.total')
            ->orderBy('notices.name');
    }

    /**
     * Optional method if you want to use html builder.
     *
     * @return \Yajra\DataTables\Html\Builder
     */
    public function html()
    {
        return $this->builder()
            ->setTableId('advice-table')
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->dom("<'row'<'col-md-6'l><'col-md-6'Bf>><'row'<'col-sm-12'tr>><'row'<'col-sm-6'i><'col-sm-6'p>>")
            ->orderBy(1, "desc")
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
                   window.LaravelDataTables["advice-table"].buttons().container()
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
            __('app.type') => ['data' => 'type', 'name' => 'type'],
            __('app.templates') => ['data' => 'tempname', 'name' => 'templates.name'],
            __('app.router') => ['data' => 'routername', 'name' => 'routers.name'],
            __('app.scope') => ['data' => 'hits', 'name' => 'hits', 'orderable' => false, 'searchable' => false],
            __('app.shippingDate') => ['data' => 'noticesreg', 'name' => 'notices.registered'],
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
        return 'Advice_' . date('YmdHis');
    }
}
