<?php

namespace App\DataTables;

use App\models\Zone;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class ZonaDataTable extends DataTable
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
                return $styleb.'<a class="green editar" href="#Edit" data-toggle="modal" data-target=".bs-edit-modal-lg" id="'.$row->id.'">
                            <i class="ace-icon fa fa-pencil bigger-130"></i>
                        </a>
                        <a class="red del" href="#" id="'.$row->id.'">
                            <i class="ace-icon fa fa-trash-o bigger-130"></i>
                        </a>
                        </div>';

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
                return '<span class="badge badge-success">'.$row->num_clients.'</span>';
            })
            ->rawColumns(['action', 'name', 'for_all', 'tp', 'num_clients']);
    }

    /**
     * Get query source of dataTable.
     *
     * @param \App\models\Zone $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(Zone $model)
    {
        return $model->select('id','name','created_at');
    }

    /**
     * Optional method if you want to use html builder.
     *
     * @return \Yajra\DataTables\Html\Builder
     */
    public function html()
    {
        return $this->builder()
            ->setTableId('zone-table')
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
                   window.LaravelDataTables["zone-table"].buttons().container()
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
        return 'Zone_' . date('YmdHis');
    }
}
