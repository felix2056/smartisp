<?php

namespace App\DataTables;

use App\Http\Controllers\PermissionsController;
use App\models\PaymentNew;
use App\models\Ticket;
use App\models\Transaction;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class TicketAssignedToMeDataTable extends DataTable
{
    /**
     * Build DataTable class.
     *
     * @param mixed $query Results from query() method.
     * @return \Yajra\DataTables\DataTableAbstract
     */
    public function dataTable($query)
    {
    	$user = auth()->user();
    	
        return datatables()
            ->eloquent($query)
            ->editColumn('status', function ($row) {
	            $badges = [
		            'new' => 'badge-secondary',
		            'work_in_progress' => 'badge-success',
		            'resolved' => 'badge-primary',
		            'waiting_on_customer' => 'badge-warning',
		            'waiting_on_agent' => 'badge-info'
	            ];
	
	            return '<span class="badge '.$badges[$row->status].'">'.__('app.'.$row->status).'</span>';
            })
            ->editColumn('priority', function ($row) {
	            $badges = [
		            'low' => 'badge-secondary',
		            'medium' => 'badge-success',
		            'high' => 'badge-primary',
		            'urgent' => 'badge-warning',
	            ];
	
	            return '<span class="badge '.$badges[$row->priority].'">'.__('app.'.$row->priority).'</span>';
            })
            ->editColumn('type', function ($row) {
	            $badges = [
		            'question' => 'badge-secondary',
		            'incident' => 'badge-success',
		            'problem' => 'badge-primary',
		            'feature_request' => 'badge-warning',
		            'lead' => 'badge-info',
	            ];
	
	            return '<span class="badge '.$badges[$row->type].'">'.__('app.'.$row->type).'</span>';
            })
            ->rawColumns(['action','status', 'type', 'priority']);
    }

    /**
     * Get query source of dataTable.
     *
     * @param \App\models\Ticket $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(Ticket $model): \Illuminate\Database\Eloquent\Builder
    {
        $user = auth()->user();
        $tickets = Ticket::select('tickets.id','tickets.subject', 'tickets.section', 'tickets.status', 'tickets.type', 'tickets.priority')->where('tickets.user_id', $user->id);
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
                    ->setTableId('ticket-me-table')
            ->columns($this->getColumns())
            ->minifiedAjax(route('admin.tickets.assignedToMe'))
            ->dom('frtip')
            ->orderBy(0, "desc")
            ->destroy(true)
            ->responsive(true)
	        ->buttons(
		        Button::make(['extend' => 'export', 'buttons' => ['excel', 'csv'], 'text' => '<i class="fa fa-download"></i> Export &nbsp;<span class="caret"></span>'])
	        )
            ->serverSide(true)
            ->processing(true)
            ->language(__("app.datatable"));
    }

    /**
     * Get columns.
     *
     * @return array
     */
    protected function getColumns()
    {
        return [
            "#Ticket" => ['data' => 'id', 'name' => 'id'],
            __('app.state') => ['data' => 'status', 'name' => 'status', 'orderable' => false],
            __('app.priority') => ['data' => 'priority', 'name' => 'priority', 'orderable' => false],
            __('app.type') => ['data' => 'type', 'name' => 'type', 'orderable' => false]

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
