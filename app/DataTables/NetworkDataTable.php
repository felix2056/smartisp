<?php

namespace App\DataTables;

use App\models\AddressRouter;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class NetworkDataTable extends DataTable
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

                if($row->status == 'of' || $row->status == 'er' || $row->status == 'nc'){
                    return $styleb.'<a class="grey" href="#"><i class="ace-icon fa fa-info-circle bigger-130"></i></a><a class="green editar" href="#Edit" data-toggle="modal" data-target=".bs-edit-modal-lg" id="'.$row->id.'"><i class="ace-icon fa fa-pencil bigger-130"></i></a><a class="red del" href="#" id="'.$row->id.'"><i class="ace-icon fa fa-trash-o bigger-130"></i></a></div>';
                }
                else{
                    return $styleb.'<a class="blue infor" href="#" data-toggle="modal" data-target="#info-router" id="'.$row->id.'"><i class="ace-icon fa fa-info-circle bigger-130"></i></a><a class="green editar" href="#Edit" data-toggle="modal" data-target=".bs-edit-modal-lg" id="'.$row->id.'"><i class="ace-icon fa fa-pencil bigger-130"></i></a><a class="red del" href="#" id="'.$row->id.'"><i class="ace-icon fa fa-trash-o bigger-130"></i></a></div>';
                }

            })
            ->editColumn('used', function ($row) {
                return '<div class="progress progress-striped"><div class="progress-bar progress-bar-warning" aria-valuemin="0" aria-valuemax="100" style="width: '.$row->used.'%;">'.$row->used.'%</div></div>';
            })

            ->rawColumns(['action', 'used']);
    }

    /**
     * Get query source of dataTable.
     *
     * @param \App\models\AddressRouter $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(AddressRouter $model)
    {
        return $model->select('*');
    }

    /**
     * Optional method if you want to use html builder.
     *
     * @return \Yajra\DataTables\Html\Builder
     */
    public function html()
    {
        return $this->builder()
            ->setTableId('network-table')
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
                   window.LaravelDataTables["network-table"].buttons().container()
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
            __('app.net') => ['data' => 'network', 'name' => 'network'],
            __('app.total') => ['data' => 'hosts', 'name' => 'hosts'],
            __('app.used') => ['data' => 'used', 'name' => 'used'],
            __('app.name') => ['data' => 'name', 'name' => 'name'],
            "Routing" => ['data' => 'type', 'name' => 'type'],
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
        return 'Network_' . date('YmdHis');
    }
}
