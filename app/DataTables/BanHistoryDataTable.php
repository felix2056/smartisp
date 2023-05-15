<?php

namespace App\DataTables;

use App\models\CortadoReason;
use App\models\Plan;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class BanHistoryDataTable extends DataTable
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
	        ->editColumn('ip', function ($row) {
		        return $row->service->ip;
	        })
	        ->editColumn('status', function ($row) {
		        if ($row->status == 'active')
			        return '<span class="label label-success arrowed">' . __('app.active') . '</span>';
		        if ($row->status == 'blocked')
			        return '<span class="label label-danger">' . __('app.blocked') . '</span>';
	        })
	        ->rawColumns(['status']);
    }

    /**
     * Get query source of dataTable.
     *
     * @param \App\models\Plan $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(CortadoReason $model)
    {
    	$request = request();
        return $model->select('*')->with('service')->where('service_id', $request->route('id'));
    }

    /**
     * Optional method if you want to use html builder.
     *
     * @return \Yajra\DataTables\Html\Builder
     */
    public function html()
    {
        return $this->builder()
            ->setTableId('ban-history-table')
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
//                'initComplete' => 'function () {
//                   window.LaravelDataTables["ban-history-table"].buttons().container()
//                    .prependTo( ".widget-header .widget-toolbar")
//                }',
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
            __('app.ip') => ['data' => 'ip', 'name' => 'service.ip'],
            __('app.status') => ['data' => 'status', 'name' => 'status'],
            __('app.reason') => ['data' => 'reason', 'name' => 'reason'],

        ];
    }

    /**
     * Get filename for export.
     *
     * @return string
     */
    protected function filename()
    {
        return 'Cortado_Historial_' . date('YmdHis');
    }
}
