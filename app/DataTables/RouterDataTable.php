<?php

namespace App\DataTables;

use App\models\ClientService;
use App\models\Router;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class RouterDataTable extends DataTable
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
                    return $styleb.'<a class="grey" href="#"><i class="ace-icon fa fa-info-circle bigger-130"></i></a>
                            <a class="default" title="Update Router Basic Details" href="javascript:;" onclick="getChangeIp('.$row->id.')" id="'.$row->id.'"><i class="ace-icon fa fa-gear bigger-130"></i></a>
                            <a class="green editar" title="Update Control (It will sync mikrotik api When you update)" href="#Edit" id="'.$row->id.'"><i class="ace-icon fa fa-pencil bigger-130"></i></a>
                            <a class="red del" href="#" id="'.$row->id.'"><i class="ace-icon fa fa-trash-o bigger-130"></i></a>
                            <a class="default refresh" title="Check Status" href="#" id="'.$row->id.'"><i class="ace-icon fa fa-refresh bigger-130"></i></a>
                            </div>';
                }
                else {

                    $service_freeradius = "";
                    if($row->control_router->type_control == "rr")
                        $service_freeradius = '<a title="Restart Freeradius" onclick="restart_freeradius()"  href="#" id="'.$row->id.'"><i class="ace-icon fa fa-desktop bigger-130"></i></a>';

                    return $styleb.'<a class="blue infor" href="#" data-toggle="modal" data-target="#info-router" id="'.$row->id.'">
                            <i class="ace-icon fa fa-info-circle bigger-130"></i></a>

                            <a class="default" title="Update Router Basic Details" href="javascript:;" onclick="getChangeIp('.$row->id.')" id="'.$row->id.'"><i class="ace-icon fa fa-gear bigger-130"></i></a>
                            <a class="green editar" title="Update Control (It will sync mikrotik api When you update)" href="#" id="'.$row->id.'"><i class="ace-icon fa fa-pencil bigger-130"></i></a>
                            <a class="red del" href="#" id="'.$row->id.'"><i class="ace-icon fa fa-trash-o bigger-130"></i></a>
                            <a class="default refresh" title="Check Status" href="#" id="'.$row->id.'"><i class="ace-icon fa fa-refresh bigger-130"></i></a>'.$service_freeradius.'
                            </div>';
                }

            })
            ->editColumn('model', function ($row) {
                if($row->model =='none') {
                    return $row->model;
                }
                else {
                    $str = $row->model;
                    return '<a href="http://routerboard.com/'.$str.'" target="_blank">'.$str.'</a>';
                }
            })
            ->editColumn('status', function ($row) {
                if($row->status == 'on')
                    return '<span class="label label-success arrowed">En línea</span>';
                if($row->status == 'of')
                    return '<span class="label label-danger">Apagado</span>';
                if($row->status == 'nc')
                    return '<span class="label label-grey">Sin conexión</span>';

            })
            ->editColumn('clients', function ($row) {
                $clients = ClientService::join('clients', 'clients.id', '=', 'client_services.client_id')
                    ->where('client_services.router_id', $row->id)
                    ->groupBy('clients.id')
                    ->get()
                    ->count();
                return '<a href="'.route('router.clients.index', $row->id).'"><span class="badge badge-success">'.$clients.'</span></a>';

            })
            ->rawColumns(['action', 'model', 'status', 'clients']);
    }

    /**
     * Get query source of dataTable.
     *
     * @param \App\models\Router $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(Router $model)
    {
        return $model->select('id','name', 'ip', 'model', 'status', 'clients', 'password', 'login', 'port', 'connection');
    }

    /**
     * Optional method if you want to use html builder.
     *
     * @return \Yajra\DataTables\Html\Builder
     */
    public function html()
    {
        return $this->builder()
            ->setTableId('router-table')
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
                   window.LaravelDataTables["router-table"].buttons().container()
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
            __('app.model') => ['data' => 'model', 'name' => 'model'],
            "IP (API)" => ['data' => 'ip', 'name' => 'ip'],
            __('app.state') => ['data' => 'status', 'name' => 'status', 'orderable' => false, 'searchable' => false],
            __('app.clients') => ['data' => 'clients', 'name' => 'clients'],
            Column::computed('action', __('app.operations'))
                ->exportable(false)
                ->printable(false)
                ->searchable(false)
                ->orderable(false)
                ->width(150),

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
