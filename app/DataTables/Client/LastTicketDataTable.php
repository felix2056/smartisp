<?php

namespace App\DataTables\Client;

use App\Http\Controllers\PermissionsController;
use App\libraries\CheckUser;
use App\models\Client;
use App\models\PaymentNew;
use App\models\Ticket;
use App\models\Transaction;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class LastTicketDataTable extends DataTable
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
            ->filterColumn('client', function ($query, $keyword) {
                $sql = "clients.name  like ?";
                $query->whereRaw($sql, ["%{$keyword}%"]);
            })
            ->addColumn('action', function ($row) {
                $styleb = '<div class="action-buttons">';
                return $styleb. '</a><a class="green editar" href="#Edit" data-toggle="modal" data-target=".bs-edit-modal-lg" id="' .$row->id. '"><i class="ace-icon fa fa-pencil-square-o bigger-130"></i></a></div>';
            })
            ->editColumn('status', function ($row) {
                if($row->status == 'work_in_progress'){
                    return '<span class="badge badge-success">Abierto</span>';
                }else{
                    return '<span class="badge badge-primary">Cerrado</span>';
                }
            })
            ->editColumn('created_at', function($row) {
                return $row->created_at->format('j F, Y');
            })
            ->editColumn('updated_at', function($row) {
                return $row->updated_at->format('j F, Y');
            })
            ->rawColumns(['action','status']);
    }

    /**
     * Get query source of dataTable
     * @param \App\models\Ticket $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(Ticket $model): \Illuminate\Database\Eloquent\Builder
    {
        $user = CheckUser::isLogin();

        $client = Client::where('email', '=', $user)->orWhere('dni', '=', $user)->first();

        $tickets = Ticket::where('client_id', $client->id);

        return $tickets;
    }

    /**
     * Optional method if you want to use html builder.
     *
     * @return \Yajra\DataTables\Html\Builder
     */
    public function html()
    {
        return $this->builder()
            ->setTableId('ticket-table')
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
                   window.LaravelDataTables["ticket-table"].buttons().container()
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
            __('app.section') => ['data' => 'id', 'name' => 'id'],
            __('app.affair') => ['data' => 'subject', 'name' => 'subject'],
            __('app.state') => ['data' => 'status', 'name' => 'status', 'orderable' => false],
            __('app.date') => ['data' => 'created_at', 'name' => 'created_at'],
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
        return 'Ticket_' . date('YmdHis');
    }
}
