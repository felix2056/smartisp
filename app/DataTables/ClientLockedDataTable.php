<?php

namespace App\DataTables;

use App\Http\Controllers\PermissionsController;
use App\models\campos_view_client;
use App\models\Client;
use App\models\GlobalSetting;
use App\Service\CommonService;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Html\Editor\Editor;
use Yajra\DataTables\Html\Editor\Fields;
use Yajra\DataTables\Services\DataTable;

class ClientLockedDataTable extends DataTable
{
    protected $global;
    protected $campos_v;

    public function __construct()
    {
        $this->global = GlobalSetting::first();
        $this->campos_v = campos_view_client::find(1);
    }
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
                $actions = '';

                $eliminar = '';
                $editar = '';

                $styleb = '<div class="action-buttons">';

                if (PermissionsController::hasAnyRole('access_clients_editar'))
                    $editar .= '<a class="green editar" href="#Edit" data-toggle="modal" data-target=".bs-edit-modal-lg" id="' . $row->id . '" title="' . __('app.edit') . '"><i class="ace-icon fa fa-pencil bigger-130"></i></a>';

                if (PermissionsController::hasAnyRole('access_clients_eliminar'))
                    $eliminar = '<a class="red del" href="#" id="' . $row->id . '" title="' . __('app.remove') . '"><i class="ace-icon fa fa-trash-o bigger-130"></i></a>';

                if ($row->service->count() > 0) {
                    foreach ($row->service as $service) {
                        if ($service->status == 'ac') {
                            $actions .= $styleb . '<a class="blue ban-service" href="#" id="' . $service->id . '" title="' . __('app.serviceCut') . '" xmlns="http://www.w3.org/1999/html"><i class="ace-icon fa fa-adjust bigger-130"></i></a>' . $editar . $eliminar . '<a class="grey tool" title="' . __('app.tools') . '" href="#" id="' . $service->id . '"><i class="ace-icon fa fa-wrench bigger-130"></i></a><a class="blue infos" title="' . __('app.information') . '" href="#" id="' . $service->id . '"><i class="ace-icon fa fa-info-circle bigger-130"></i></a></div>';
                        }
                        if ($service->status == 'de') {
                            $actions .= $styleb . '<a class="blue ban-service" href="#" id="' . $service->id . '" title="' . __('app.activate') . ' ' . __('app.service') . '"><i class="ace-icon fa fa-adjust bigger-130"></i></a>' . $eliminar . '<a class="blue infos" title="' . __('app.information') . '" href="#" id="' . $service->id . '"><i class="ace-icon fa fa-info-circle bigger-130"></i></a></div>';
                        }
                    }
                } else {
                    $actions .= $styleb . $editar . $eliminar . '</div>';
                }
                return $actions;

            })
            ->editColumn('name', function ($row) {
                return '<a href="' . route('billing', $row->id) . '">' . $row->name . '</a>';
            })
            ->editColumn('online', function ($row) {

                $tp = '';
                foreach ($row->service as $service) {
                    if ($service->online == 'on') {
                        $tp .= '<p><span class="label label-success">' . __('app.Online') . '</span></p>';
                    }
                    if ($service->online == 'off') {
                        $tp .= '<p><span class="label label-danger">' . __('app.disconnected') . '</span></p>';
                    }
                    if ($service->online == 'ver') {
                        $tp .= '<p><span class="label label-warning">' . __('app.verifying') . '</span></p>';
                    }
                }

                if ($tp == '') {
                    $tp = '---';
                }
                return $tp;
            })
            ->editColumn('tp', function ($row) {
                $tp = '';
                foreach ($row->service as $service) {
                    if ($service->router->control_router->type_control == 'ho') {
                        $tp .= '<p><span class="label label-purple">Hotspot</span></p>';
                    }

                    if ($service->router->control_router->type_control == 'ha') {
                        $tp .= '<p><span class="label label-purple">Hotspot - PCQ</span></p>';
                    }

                    if ($service->router->control_router->type_control == 'sq') {
                        $tp .= '<p><span class="label label-success">Simple Queues</span></p>';
                    }
                    if ($service->router->control_router->type_control == 'pp') {
                        $tp .= '<p><span class="label label-yellow">PPPoE</span></p>';
                    }
                    if ($service->router->control_router->type_control == 'pa') {
                        $tp .= '<p><span class="label label-yellow">PPPoE - PCQ</span></p>';
                    }
                    if ($service->router->control_router->type_control == 'nc') {
                        $tp .= '<p><span class="label label-grey">Sin conexión</span></p>';
                    }
                    if ($service->router->control_router->type_control == 'pc') {
                        $tp .= '<p><span class="label label-warning">PCQ</span></p>';
                    }
                    if ($service->router->control_router->type_control == 'st') {
                        $tp .= '<p><span class="label label-success">Simple Queues (with Tree)</span></p>';
                    }
                    if ($service->router->control_router->type_control == 'pt') {
                        $tp .= '<p><span class="label label-yellow">PPPoE - Simple Queues (with Tree)</span></p>';
                    }
                    if ($service->router->control_router->type_control == 'dl') {
                        $tp .= '<p><span class="label label-default">DHCP Leases</span></p>';
                    }
                    if ($service->router->control_router->type_control == 'ps') {
                        $tp .= '<p><span class="label label-yellow">PPPoE - Simple Queues</span></p>';
                    }
                    if ($service->router->control_router->type_control == 'no') {
                        $tp .= '<p><span class="label label-default">' . __('app.none') . '</span></p>';
                    }
                }

                if ($tp == '') {
                    $tp = '---';
                }
                return $tp;

            })
            ->editColumn('plan_name', function ($row) {
                $planName = '';
                foreach ($row->service as $service) {
                    if ($service->plan->name == 'Importados')
                        $planName .= '<p><span class="text-danger">' . $service->plan->name . '</span></p>';
                    else
                        $planName .= '<p>' . $service->plan->name . '</p>';
                }

                if ($planName == '') {
                    $planName = '---';
                }
                return $planName;
            })
            ->editColumn('status', function ($row) {
                $status = '';
                foreach ($row->service as $service) {
                    if ($service->status == 'ac')
                        $status .= '<p><span class="label label-success arrowed">' . __('app.active') . '</span></p>';
                    else
                        $status .= '<p><span class="label label-danger">' . __('app.blocked') . '</span></p>';
                }

                if ($status == '') {
                    $status = '---';
                }

                return $status;
            })
            ->editColumn('expiration', function ($row) {
//                $expiration = '';
//                foreach ($row->service as $service) {
//                    $expiration .= '<p>' . $service->suspend_client->expiration->format("Y-m-d") . '</p>';
//                }
//
//
//                if ($expiration == '') {
//                    $expiration = '---';
//                }
//
//                return $expiration;
	            $cortadoDetails = CommonService::getServiceCortadoDate($row->id);
	            if($cortadoDetails['cortado_date']) {
		            return $cortadoDetails['cortado_date'];
	            }
	
	            return '---';
            })
            ->editColumn('ip', function ($row) {
                $ip = '';
                foreach ($row->service as $service) {
                    $ip .= '<p>' . $service->ip . '</p>';
                }

                if ($ip == '') {
                    $ip = '---';
                }
                return $ip;
            })
            ->editColumn('mac', function ($row) {
                $ip = '';
                foreach ($row->service as $service) {
                    $ip .= '<p>' . $service->mac . '</p>';
                }

                if ($ip == '') {
                    $ip = '---';
                }
                return $ip;
            })
            ->editColumn('router', function ($row) {
                $router = '';
                foreach ($row->service as $service) {
                    $router .= '<p>' . $service->router->name . '</p>';
                }

                if ($router == '') {
                    $router = '---';
                }

                return $router;
            })
            ->addColumn('cut', function ($row) {
//                $expiration = '';
//                foreach ($row->service as $service) {
//                    if (($row->billing_grace_period != 0) || ($this->global->tolerance != 0)) {
//                        $t_diass = $row->billing_grace_period + $this->global->tolerance;
//                        $expiration .= '<p>' . Carbon::parse($service->suspend_client->expiration)->addDays($t_diass)->format('d/m/Y H:i:s') . '</p>';
//                    } else {
//                        $expiration .= '<p>' . Carbon::parse($service->suspend_client->expiration)->format('d/m/Y H:i:s') . '</p>';
//                    }
//                }
//
//                if ($expiration == '') {
//                    $expiration = '---';
//                }
//
//                return $expiration;
	            $cutDate = CommonService::getCortadoDateWithTolerence($row->id, $row->billing_grace_period, $this->global->tolerance);
	            
	            if($cutDate) {
		            return $cutDate;
	            }
	
	            return '---';
            })
            ->rawColumns(['action', 'name', 'online', 'tp', 'plan_name', 'status', 'zone', 'odb_id', 'onu_id', 'onusn', 'cut', 'expiration', 'router', 'ip', 'mac']);
    }

    /**
     * Get query source of dataTable.
     *
     * @param \App\models\Client $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(Client $model)
    {
        $request = request();
         $users = Client::join('client_services', 'client_services.client_id', '=', 'clients.id')
             ->leftJoin('odb_splitter', 'odb_splitter.id', '=', 'clients.odb_id')
             ->leftJoin('zone', 'zone.id', '=', 'clients.zona_id')
             ->leftJoin('onu_type', 'onu_type.id', '=', 'clients.onu_id')
             ->join('billing_settings', 'billing_settings.client_id', '=', 'clients.id')
             ->whereHas('service', function (Builder $query) {
                 $query->where('status', 'de');
             })->with(['service', 'service.router', 'service.router.control_router', 'service.plan']);

        if ($request->control != 'all') {
            $users = $users->join('routers', 'routers.id', '=', 'clients.router_id')
                ->join('control_routers', 'routers.id', '=', 'control_routers.router_id')
                ->where('control_routers.type_control', $request->control);
        }

        if ($request->online != 'all') {
            $users = $users->where('client_services.online', $request->online);
        }

        $users = $users->select('clients.id', 'clients.name',
            'client_services.online', 'clients.email', 'clients.dni',
            'clients.balance', 'billing_settings.billing_grace_period', 'billing_settings.billing_date', 'billing_settings.billing_due_date', 'zone.name as zone', 'odb_splitter.name as odb_id', 'onu_type.onutype as onu_id', 'clients.onusn')
            ->groupBy('clients.id');

        return $users;
    }

    /**
     * Optional method if you want to use html builder.
     *
     * @return \Yajra\DataTables\Html\Builder
     */
    public function html()
    {
        $domElement = "<'row'<'col-md-6'l><'col-md-6'Bf>><'row'<'col-sm-12'tr>><'row'<'col-sm-6'i><'col-sm-6'p>>";

        if($this->global->search_show != 1) {
            $domElement = "<'row'<'col-md-6'l> <'col-md-6'B>><'row'<'col-sm-12'tr>><'row'<'col-sm-6'i><'col-sm-6'p>>";
        }

        return $this->builder()
            ->setTableId('client-locked-table')
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->dom($domElement)
            ->orderBy(0)
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
                   window.LaravelDataTables["client-locked-table"].buttons().container()
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
            "Name" => ['data' => 'name', 'name' => 'name', 'orderable' => false, 'searchable' => true, 'visible' => (bool)$this->campos_v->name],
            'Email'  => ['data' => 'email', 'name' => 'email', 'orderable' => false, 'searchable' => false, 'visible' => false, 'exportable' => true],
            'DNI'  => ['data' => 'dni', 'name' => 'dni', 'orderable' => false, 'searchable' => false, 'visible' => false, 'exportable' => true],
            'Billing Date'  => ['data' => 'billing_date', 'name' => 'billing_date', 'orderable' => false, 'searchable' => false, 'visible' => false, 'exportable' => true],
            'Billing Due Date'  => ['data' => 'billing_due_date', 'name' => 'billing_due_date', 'orderable' => false, 'searchable' => false, 'visible' => false, 'exportable' => true],
            'IP'  => ['data' => 'ip', 'name' => 'service.ip', 'orderable' => false, 'searchable' => true, 'visible' => (bool)$this->campos_v->ip, 'exportable' => false],
            __('app.balance') => ['data' => 'balance', 'name' => 'balance', 'orderable' => false, 'searchable' => false, 'visible' => (bool)$this->campos_v->balance, 'exportable' => true],
            __('app.state') => ['data' => 'online', 'name' => 'online', 'orderable' => false, 'searchable' => false, 'visible' => (bool)$this->campos_v->estado, 'exportable' => false],
            __('app.control') => ['data' => 'tp', 'name' => 'tp', 'orderable' => false, 'searchable' => false,'visible' => (bool)$this->campos_v->control, 'exportable' => false],
            __('app.payday') => ['data' => 'expiration', 'name' => 'expiration', 'orderable' => false, 'searchable' => false, 'visible' => (bool)$this->campos_v->day_payment, 'exportable' => false],
            __('app.serviceCut') => ['data' => 'cut', 'name' => 'cut', 'orderable' => false, 'searchable' => false, 'visible' => (bool)$this->campos_v->cut, 'exportable' => false],
            __('app.plan') => ['data' => 'plan_name', 'name' => 'plan_name', 'orderable' => false, 'searchable' => false, 'visible' => (bool)$this->campos_v->plan, 'exportable' => false],
            __('app.service') => ['data' => 'status', 'name' => 'status', 'orderable' => false, 'searchable' => false, 'visible' => (bool)$this->campos_v->servicio, 'exportable' => false],
            'Mac' => ['data' => 'mac', 'name' => 'mac', 'orderable' => false, 'searchable' => false, 'visible' => (bool)$this->campos_v->mac, 'exportable' => false],
            __('app.router') => ['data' => 'router', 'name' => 'router', 'orderable' => false, 'searchable' => false, 'visible' => (bool)$this->campos_v->router, 'exportable' => false],
            'Zona' => ['data' => 'zone', 'name' => 'zone', 'orderable' => false, 'searchable' => false, 'visible' => (bool)$this->campos_v->zone, 'exportable' => false],
            'Caja' => ['data' => 'odb_id', 'name' => 'odb_id', 'visible' => (bool)$this->campos_v->odb_id, 'exportable' => false],
            'ONUs/CPE' => ['data' => 'onu_id', 'name' => 'onu_id', 'visible' => (bool)$this->campos_v->onu_id, 'exportable' => false],
            'ONUs SN' => ['data' => 'onusn', 'name' => 'onusn', 'visible' => (bool)$this->campos_v->onusn, 'exportable' => false],
            Column::computed('action', __('app.operations'))
                ->exportable(false)
                ->printable(false)
                ->orderable(false)
                ->searchable(false)
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
        return 'Client_Locked' . date('YmdHis');
    }

    public function pdf()
    {
        set_time_limit(0);
        if ('snappy' == config('datatables-buttons.pdf_generator', 'snappy')) {
            return $this->snappyPdf();
        }

        $pdf = app('dompdf.wrapper');
        $pdf->loadView('datatables::print', ['data' => $this->getDataForPrint()]);

        return $pdf->download($this->getFilename() . '.pdf');
    }

}
