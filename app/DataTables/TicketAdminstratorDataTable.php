<?php

namespace App\DataTables;

use App\Http\Controllers\PermissionsController;
use App\models\PaymentNew;
use App\models\Ticket;
use App\models\Transaction;
use App\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class TicketAdminstratorDataTable extends DataTable
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
            ->eloquent($query);
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
        $tickets = User::join('tickets', 'users.id', '=', 'tickets.user_id')
	        ->select('users.name',DB::raw('COUNT(tickets.id) as count'))->where('tickets.user_id', '<>',$user->id);
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
                    ->setTableId('ticket-admin-table')
            ->columns($this->getColumns())
	        ->minifiedAjax( route('admin.tickets.administrator') )
	        ->dom('frtip')
            ->orderBy(0, "desc")
	        ->buttons(
		        Button::make(['extend' => 'export', 'buttons' => ['excel', 'csv'], 'text' => '<i class="fa fa-download"></i> Export &nbsp;<span class="caret"></span>'])
	        )
            ->destroy(true)
            ->responsive(true)
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
            __('app.name') => ['data' => 'name', 'name' => 'name'],
            __('app.count') => ['data' => 'count', 'name' => 'count', 'orderable' => false, 'searchable' => false],
//            __('app.priority') => ['data' => 'priority', 'name' => 'priority', 'orderable' => false],
//            __('app.type') => ['data' => 'type', 'name' => 'type', 'orderable' => false]

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
