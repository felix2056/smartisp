<?php

namespace App\DataTables;

use App\Http\Controllers\PermissionsController;
use App\models\ClientService;
use App\models\Zone;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class ServiceDataTable extends DataTable
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
                $eliminar = '';
                $editar = '';

                $styleb = '<div class="action-buttons">';
                if (PermissionsController::hasAnyRole('servicio_edit'))
                    $editar .= '<a class="green edit" href="javascript:;" onclick="edit('. $row->id .')" id="' . $row->id . '" title="' . __('app.edit') . '"><i class="ace-icon fa fa-pencil bigger-130"></i></a>';

                if (PermissionsController::hasAnyRole('servicio_delete'))
                    $eliminar = '<a class="red deletes" href="#" id="' . $row->id . '" title="' . __('app.remove') . '"><i class="ace-icon fa fa-trash bigger-130"></i></a>';


                if ($row->tp != 'nc') {
                    $info='';
                    $info='<a class="blue info" title="' . __('app.information') . '" href="#" id="' . $row->id . '"><i class="ace-icon fa fa-info-circle bigger-130"></i></a>';


                    if ($row->status == 'ac') {
                        $activo_s='';
                        $llave='';
                        $llave='<a class="grey tool" title="' . __('app.tools') . '" href="#" id="' . $row->id . '"><i class="ace-icon fa fa-wrench bigger-130"></i></a>';
                        if (PermissionsController::hasAnyRole('servicio_activate_desactivar')){
                            $activo_s='<a class="blue ban-service" href="#" id="' . $row->id . '" title="' . __('app.serviceCut') . '"><i class="ace-icon fa fa-adjust bigger-130"></i></a>';
                        }
                        return $styleb . $activo_s . $editar.$llave.$info.$eliminar.'</div>';

                    }
                    if ($row->status == 'de') {
                        $desactivo_s='';
                        if (PermissionsController::hasAnyRole('servicio_activate_desactivar'))
                            $desactivo_s='<a class="blue ban-service" href="#" id="' . $row->id . '" title="' . __('app.activate') . ' ' . __('app.service') . '"><i class="ace-icon fa fa-adjust bigger-130"></i></a>';

                        return $styleb . $desactivo_s.$info.$eliminar.'</div>';

                    }
                }

            })
            ->editColumn('plan.name', function ($row) {
                if ($row->plan->plan_name == 'Importados')
                    return '<span class="text-danger">' . $row->name . '</span>';

                return $row->plan->name;
            })
            ->editColumn('cost', function ($row) {
                return $row->plan->cost;
            })
            ->editColumn('name', function ($row) {
                return $row->router->name;
            })
            ->editColumn('date_in', function ($row) {
                return $row->date_in->format('Y-m-d');
            })
            ->editColumn('status', function ($row) {
                if ($row->status == 'ac')
                    return '<span class="label label-success arrowed">' . __('app.active') . '</span>';
                if ($row->status == 'de')
                    return '<span class="label label-danger">' . __('app.blocked') . '</span>';
            })
            ->rawColumns(['action', 'plan_name', 'status']);
    }

    /**
     * Get query source of dataTable.
     *
     * @param \App\models\ClientService $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(ClientService $model)
    {
        $request = request();
        return $model->with('plan', 'client', 'router')->where('client_id', $request->route('client'));

    }

    /**
     * Optional method if you want to use html builder.
     *
     * @return \Yajra\DataTables\Html\Builder
     */
    public function html()
    {
        return $this->builder()
            ->setTableId('service-table')
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
                   window.LaravelDataTables["service-table"].buttons().container()
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
            __('app.service') => ['data' => 'name', 'name' => 'name'],
            __('app.plan') => ['data' => 'name', 'name' => 'name'],
            __('app.cost') => ['data' => 'name', 'name' => 'name'],
            __('app.ip') => ['data' => 'name', 'name' => 'name'],
            __('app.router') => ['data' => 'name', 'name' => 'name'],
            __('app.dateOfAddmission') => ['data' => 'name', 'name' => 'name'],
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
        return 'Services_' . date('YmdHis');
    }
}
