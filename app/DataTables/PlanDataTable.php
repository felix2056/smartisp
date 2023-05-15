<?php

namespace App\DataTables;

use App\models\Plan;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class PlanDataTable extends DataTable
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
                    <a class="blue sb" href="#" id="'.$row->id.'">
                        <i class="ace-icon negro_c fa fa-tachometer bigger-130"></i>
                    </a>
                    <a class="green editar" href="#Edit" data-toggle="modal" data-target=".bs-edit-modal-lg" id="'.$row->id.'">
                        <i class="ace-icon fa fa-pencil bigger-130"></i>
                    </a>
                    <a class="red del" href="#" id="'.$row->id.'">
                        <i class="ace-icon fa fa-trash-o bigger-130"></i>
                    </a>
                    </div>';

            })
            ->editColumn('title', function ($row) {
                if($row->title == 'Importados')
                    return '<span class="text-danger">'.$row->title.'</span>';

                return $row->title;
            })
            ->editColumn('name', function ($row) {
                if($row->name == 'Importados')
                    return '<span class="text-danger">'.$row->name.'</span>';

                return $row->name;
            })
            ->editColumn('download', function ($row) {
                return $row->download.'Kbps';
            })
            ->editColumn('upload', function ($row) {
                return $row->upload.'Kbps';
            })
            ->editColumn('for_all', function ($row) {
                if ($row->for_all ==1) {
                    return '<div class="progress progress-striped"><div class="progress-bar progress-bar-info" aria-valuemin="0" aria-valuemax="100" style="width: '.$row->bandwidth.'%;">'.$row->bandwidth.'%</div></div>';
                }

                return '<div class="progress progress-striped"><div class="progress-bar progress-bar-success" aria-valuemin="0" aria-valuemax="100" style="width: '.$row->bandwidth.'%;">'.$row->bandwidth.'%</div></div>';
            })
            ->editColumn('num_clients', function ($row) {
                return '<a href="'.route('plan.clients.index', $row->id).'"><span class="badge badge-success">'.$row->num_clients.'</span></a>';
            })
            ->rawColumns(['action', 'name', 'for_all', 'tp', 'num_clients']);
    }

    /**
     * Get query source of dataTable.
     *
     * @param \App\models\Plan $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(Plan $model)
    {
        return Plan::join('smart_bandwidth', 'plans.id', '=', 'smart_bandwidth.plan_id')
            ->select(
                'plans.id',
                'plans.title',
                'plans.name',
                'plans.download',
                'plans.upload',
                'plans.cost',
                DB::raw('(select count(client_services.id) from client_services where client_services.plan_id = plans.id and client_services.status="ac")as num_clients'),
                'smart_bandwidth.bandwidth',
                'smart_bandwidth.for_all'
            );
    }

    /**
     * Optional method if you want to use html builder.
     *
     * @return \Yajra\DataTables\Html\Builder
     */
    public function html()
    {
        return $this->builder()
            ->setTableId('plan-table')
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
                   window.LaravelDataTables["plan-table"].buttons().container()
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
            __('app.title') => ['data' => 'title', 'name' => 'title'],
            __('app.serviceName') => ['data' => 'name', 'name' => 'name'],
            __('app.downloadSpeed') => ['data' => 'download', 'name' => 'download'],
            __('app.uploadSpeed') => ['data' => 'upload', 'name' => 'upload'],
            __('app.extraSpeed') => ['data' => 'for_all', 'name' => 'for_all', 'orderable' => false, 'searchable' => false],
            __('app.clients') => ['data' => 'num_clients', 'name' => 'num_clients'],
            __('app.cost') => ['data' => 'cost', 'name' => 'cost'],
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
        return 'Plan_' . date('YmdHis');
    }
}
