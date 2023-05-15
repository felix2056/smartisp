<?php

namespace App\DataTables;

use App\models\Template;
use App\models\Zone;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class TemplateDataTable extends DataTable
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
            ->editColumn('name', function ($row) {
                return '<a href="tempview?id='.$row->id.'" target="_blank">'.$row->name.'</a>';
            })
            ->editColumn('type', function ($row) {
                if($row->type == 'screen')
                    return '<span class="label label-purple arrowed">'.__('app.notice').'</span>';

                if($row->type == 'email')
                    return '<span class="label label-success arrowed">'.__('app.email').'</span>';

                if($row->type == 'invoice')
                    return '<span class="label label-info arrowed">'.__('app.bill').'</span>';

                if($row->type == 'sms')
                    return '<span class="label label-warning arrowed">'.__('app.sms').'</span>';

                if($row->type == 'hotspo')
                    return '<span class="label label-danger arrowed">'.__('app.hotspot').'</span>';
            })

            ->rawColumns(['action', 'name', 'type']);
    }

    /**
     * Get query source of dataTable.
     *
     * @param \App\models\Template $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(Template $model)
    {
        return $model->select('name', 'registered', 'type', 'id');
    }

    /**
     * Optional method if you want to use html builder.
     *
     * @return \Yajra\DataTables\Html\Builder
     */
    public function html()
    {
        return $this->builder()
            ->setTableId('template-table')
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
                   window.LaravelDataTables["template-table"].buttons().container()
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
            __('app.Created') => ['data' => 'registered', 'name' => 'registered'],
            __('app.type') => ['data' => 'type', 'name' => 'type', 'orderable' => false, 'searchable' => false],
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
        return 'Template_' . date('YmdHis');
    }
}
