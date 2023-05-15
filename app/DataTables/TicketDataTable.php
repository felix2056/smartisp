<?php

namespace App\DataTables;

use App\Http\Controllers\PermissionsController;
use App\models\PaymentNew;
use App\models\Ticket;
use App\models\TicketViewColumn;
use App\models\Transaction;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class TicketDataTable extends DataTable
{
	protected $view;
	
	public function __construct()
	{
		$this->view = TicketViewColumn::first();
//		dd($this->view);
	}
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
            ->filterColumn('client', function ($query, $keyword) {
                $sql = "clients.name  like ?";
                $query->whereRaw($sql, ["%{$keyword}%"]);
            })
            ->addColumn('action', function ($row) use($user) {
                $styleb = '<div class="action-buttons">';
                $styleb .= '<a class="blue chok" href="#" id="'.$row->id.'"><i class="ace-icon fa fa-check-square-o bigger-130"></i></a><a class="green editar" href="#Edit" data-toggle="modal" data-target=".bs-edit-modal-lg" id="'.$row->id.'"><i class="ace-icon fa fa-pencil-square-o bigger-130"></i></a><a class="red del" href="#" id="'.$row->id.'"><i class="ace-icon fa fa-trash-o bigger-130"></i></a>';
                
                if($user->level == "ad") {
                    $styleb .= '<a class="green" onclick="changeAssignee('.$row->id.')" title="Change Assignee" href="javascript:;" id="'.$row->id.'"><i class="ace-icon fa fa-first-order bigger-130"></i></a>';
                }
                
                $styleb .= '</div>';
                
                return $styleb;
                
            })
            ->editColumn('name', function ($row) {
                if($row->name){
                    return '<span class="badge badge-primary">'.$row->name.'</span>';
                }else{
                    return '---';
                }
            })
            ->editColumn('status', function ($row) {
	            return '<select class="chosen-select form-control" name="status" id="status'.$row->id.'" onchange="changeStatus('.$row->id.')">
                                <option value="new" '.($row->status == "new" ? "selected" : "").' >'.__('app.new').'</option>
                                <option value="work_in_progress" '.($row->status == "work_in_progress" ? "selected" : "").'>'.__('app.work_in_progress').'</option>
                                <option value="resolved" '.($row->status == "resolved" ? "selected" : "").'>'.__('app.resolved').'</option>
                                <option value="waiting_on_customer" '.($row->status == "waiting_on_customer" ? "selected" : "").'>'.__('app.waiting_on_customer').'</option>
                                <option value="waiting_on_agent" '.($row->status == "waiting_on_agent" ? "selected" : "").'>'.__('app.waiting_on_agent').'</option>
                            </select>';
            })
            ->editColumn('priority', function ($row) {
	            return '<select class="chosen-select form-control" name="priority" id="priority'.$row->id.'"  onchange="changePriority('.$row->id.')">
                            <option value="low" '.($row->priority == "low" ? "selected" : "").' >'.__('app.low').'</option>
                            <option value="medium" '.($row->priority == "medium" ? "selected" : "").'>'.__('app.medium').'</option>
                            <option value="high" '.($row->priority == "high" ? "selected" : "").'>'.__('app.high').'</option>
                            <option value="urgent" '.($row->priority == "urgent" ? "selected" : "").'>'.__('app.urgent').'</option>
                        </select>';
            })
            ->editColumn('type', function ($row) {
	            return '<select class="chosen-select form-control" name="type" id="type'.$row->id.'" onchange="changeType('.$row->id.')">
                            <option value="question" '.($row->type == "question" ? "selected" : "").' >'.__('app.question').'</option>
                            <option value="incident" '.($row->type == "incident" ? "selected" : "").'>'.__('app.incident').'</option>
                            <option value="problem" '.($row->type == "problem" ? "selected" : "").'>'.__('app.problem').'</option>
                            <option value="feature_request" '.($row->type == "feature_request" ? "selected" : "").'>'.__('app.feature_request').'</option>
                            <option value="lead" '.($row->type == "lead" ? "selected" : "").'>'.__('app.lead').'</option>
                        </select>';
            })
            ->editColumn('created_at', function($row){
                return $row->created_at->format('j F, Y');
            })
            ->rawColumns(['action','status','name', 'type', 'priority']);
    }

    /**
     * Get query source of dataTable.
     *
     * @param \App\models\Ticket $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(Ticket $model): \Illuminate\Database\Eloquent\Builder
    {
        $request = request();
        $user = auth()->user();
        
        $tickets = Ticket::join('clients', 'clients.id', '=', 'tickets.client_id')
	        ->leftJoin('users', 'users.id', '=', 'tickets.user_id')
            ->select('clients.name As client', 'tickets.id',
                'tickets.subject', 'tickets.section', 'tickets.status', 'tickets.type', 'tickets.priority', 'users.name', 'tickets.read_admin',
                'tickets.created_at');
        
        if($user->level != 'ad') {
	        $tickets = $tickets->where('tickets.user_id', $user->id);
        }
        

        if($request->status != 'all') {
            $tickets = $tickets->where('tickets.status', $request->status);
        }

        if($request->type != 'all') {
            $tickets = $tickets->where('tickets.type', $request->type);
        }

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
                    .prependTo( ".widget-header .widget-toolbar");
                }',
                'drawCallback' => 'function () {
                    $(".chosen-select", this).chosen("destroy").chosen();
    $("select", this).chosen("destroy").chosen()
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
            "#Ticket" => ['data' => 'id', 'name' => 'id'],
	        __('app.client') => ['data' => 'client', 'name' => 'clients.name', 'visible' => (bool)$this->view->client_id],
	        __('app.status') => ['data' => 'status', 'name' => 'status', 'orderable' => false, 'visible' => (bool)$this->view->status],
	        __('app.section') => ['data' => 'section', 'name' => 'section', 'visible' => (bool)$this->view->section],
	        __('app.affair') => ['data' => 'subject', 'name' => 'subject', 'visible' => (bool)$this->view->subject],
	        __('app.chooseAssignee') => ['data' => 'name', 'name' => 'users.name', 'visible' => (bool)$this->view->user_id],
	        __('app.priority') => ['data' => 'priority', 'name' => 'priority', 'orderable' => false, 'visible' => (bool)$this->view->priority],
	        __('app.type') => ['data' => 'type', 'name' => 'type', 'orderable' => false, 'visible' => (bool)$this->view->type],
	        __('app.creationDate') => ['data' => 'created_at', 'name' => 'created_at', 'visible' => (bool)$this->view->created_at_view],
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
