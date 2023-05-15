<?php

namespace App\DataTables;

use App\Http\Controllers\PermissionsController;
use App\models\Logg;
use App\models\PaymentNew;
use App\models\Ticket;
use App\models\Transaction;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class LogDataTable extends DataTable
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
            ->editColumn('type', function ($row) {
	            if($row->type == 'info')
		            return '<span class="label label-info arrowed">Info</span>';
	            if($row->type == 'danger')
		            return '<span class="label label-danger">Importante</span>';
	            if($row->type == 'success')
		            return '<span class="label label-success">Nuevo</span>';
	            if($row->type == 'change')
		            return '<span class="label label-warning">Cambio</span>';
            })
            ->editColumn('created_at', function ($row) {
		            return $row->created_at->format('Y-m-d H:i:s');
            })
            ->rawColumns(['type']);
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
        $logs = Logg::select('*');
	
	
	    $date = $request->get('extra_search');
	
	    if($date) {
		    $string = explode('|', $date);
		
		    $date1 = $string[0];
		    $date2 = $string[1];
		
		    $date1 = str_replace('/', '-', $date1);
		    $date2 = str_replace('/', '-', $date2);
		
		    $from = date("Y-m-d", strtotime($date1));
		    $to = date("Y-m-d", strtotime($date2));
	    }
        
        if($request->has('user') && $request->get('user') != 'all') {
        	$logs = $logs->where('user', $request->get('user'));
        }
	
	    if($date) {
		    $logs = $logs->whereDate('created_at', '>=', $from)->whereDate('created_at', '<=', $to);
	    }

        return $logs;
    }

    /**
     * Optional method if you want to use html builder.
     *
     * @return \Yajra\DataTables\Html\Builder
     */
    public function html()
    {
        return $this->builder()
                    ->setTableId('log-table')
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
                   window.LaravelDataTables["log-table"].buttons().container()
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
            __('app.detail') => ['data' => 'detail', 'name' => 'detail'],
            __('app.username') => ['data' => 'user', 'name' => 'user'],
            __('app.registered') => ['data' => 'created_at', 'name' => 'created_at'],
            __('app.type') => ['data' => 'type', 'name' => 'type'],
        ];
    }

    /**
     * Get filename for export.
     *
     * @return string
     */
    protected function filename()
    {
        return 'Log_' . date('YmdHis');
    }
}
