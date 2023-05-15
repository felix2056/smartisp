<?php

namespace App\DataTables;

use App\models\CronJob;
use App\models\Notice;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class CronJobDataTable extends DataTable
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
                            <a class="btn btn-sm btn-primary" onclick="fireCron('.$row->id.')" href="#" id="'.$row->id.'"><i class="ace-icon fa fa-play bigger-130"></i> Execute</a>
                    </div>';
            })
            ->editColumn('command', function ($row) {
                return '<span class="badge badge-success">'.$row->command.'</span>';
            })
	        ->rawColumns(['action', 'command']);
    }

    /**
     * @param Notice $model
     * @return mixed
     */
    public function query(CronJob $model)
    {
        return  $model->select('*');
    }

    /**
     * Optional method if you want to use html builder.
     *
     * @return \Yajra\DataTables\Html\Builder
     */
    public function html()
    {
        return $this->builder()
            ->setTableId('cron-job-table')
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
                   window.LaravelDataTables["cron-job-table"].buttons().container()
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
            __('app.id') => ['data' => 'id', 'name' => 'id'],
            __('app.command') => ['data' => 'command', 'name' => 'command'],
            __('app.description') => ['data' => 'description', 'name' => 'description'],
            __('app.interval') => ['data' => 'interval', 'name' => 'interval'],
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
        return 'Cron_job_' . date('YmdHis');
    }
}
