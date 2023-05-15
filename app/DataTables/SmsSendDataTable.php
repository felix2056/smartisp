<?php

namespace App\DataTables;

use App\models\Sms;
use App\models\Zone;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class SmsSendDataTable extends DataTable
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
                if ($row->clname == "Grupo") {
                    return $styleb. '<a class="blue infor" href="#" data-toggle="modal" data-target="#info" id="' .$row->id. '"><i class="ace-icon fa fa-info-circle bigger-130"></i></a><a class="green reenviar" href="#" id="' .$row->id. '"><i class="ace-icon fa fa-reply-all bigger-130"></i></a><a class="red del" href="#" id="' .$row->id. '"><i class="ace-icon fa fa-trash-o bigger-130"></i></a></div>';
                } else {
                    return $styleb. '<a class="grey" href="#"><i class="ace-icon fa fa-info-circle bigger-130"></i></a><a class="green reenviar" href="#" id="' .$row->id. '"><i class="ace-icon fa fa-reply bigger-130"></i></a><a class="red del" href="#" id="' .$row->id. '"><i class="ace-icon fa fa-trash-o bigger-130"></i></a></div>';
                }


            })
            ->editColumn('tcl', function ($row) {
                $per = ((int)($row->send_rate) / (int) $row->tcl) * 100;
                $per = round($per);
                if ($per == 100) {
                    return '<span class="label label-success">Enviado</span>';
                } else {
                    return '<div class="progress"><div class="progress-bar progress-bar-striped active" aria-valuemin="0" aria-valuemax="100" style="width: ' . $per . '%;">' . $per . '%</div></div>';
                }
            })
            ->rawColumns(['action', 'tcl']);
    }

    /**
     * Get query source of dataTable.
     *
     * @param \App\models\Sms $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(Sms $model)
    {
        return $model->join('routers', 'sms.router_id', '=', 'routers.id')
            ->select('sms.client As clname', 'sms.id',
                'routers.name As roname', 'sms.send_date', 'sms.phone',
                'sms.total_clients As tcl', 'sms.send_rate', 'sms.gateway', 'sms.message');
    }

    /**
     * Optional method if you want to use html builder.
     *
     * @return \Yajra\DataTables\Html\Builder
     */
    public function html()
    {
        return $this->builder()
            ->setTableId('sms-send-table')
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
                   window.LaravelDataTables["sms-send-table"].buttons().container()
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
            __('app.client') => ['data' => 'clname', 'name' => 'sms.client'],
            __('app.router') => ['data' => 'roname', 'name' => 'routers.name'],
            __('app.destinationNo') => ['data' => 'phone', 'name' => 'phone'],
            __('app.date') => ['data' => 'send_date', 'name' => 'send_date'],
            __('app.message') => ['data' => 'message', 'name' => 'message'],
            'Gateway' => ['data' => 'gateway', 'name' => 'gateway'],
            __('app.state') => ['data' => 'tcl', 'name' => 'tcl', 'orderable' => false, 'searchable' => false],
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
        return 'SMS_Send_' . date('YmdHis');
    }
}
