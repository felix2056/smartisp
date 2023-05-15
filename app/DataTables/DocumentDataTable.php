<?php

namespace App\DataTables;

use App\Http\Controllers\PermissionsController;
use App\libraries\CheckUser;
use App\models\Client;
use App\models\Document;
use App\models\PaymentNew;
use App\models\Ticket;
use App\models\Transaction;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class DocumentDataTable extends DataTable
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
		
		        $html='';
		
		
		        if($row->type == "contract") {
			        $html.='<a href="'.route('portal.contracts.view', $row->id).'" target="_blank"  title="View"><span class="glyphicon glyphicon-print"></span></a>&nbsp;';
			        $html.=' <a href="'.route('portal.contracts.download', $row->id).'"  title="Remove"><span class="glyphicon glyphicon-download"></span></a>&nbsp;';
			
		        } else {
		        	
			        $html.='<a href="'.asset("/assets/documents/$row->client_id/$row->document_name").'" target="_blank"  title="View"><span class="glyphicon glyphicon-print"></span></a>&nbsp;';
			        $html.=' <a href="'.route('portal.documents.download', $row->id).'"  title="Remove"><span class="glyphicon glyphicon-download"></span></a>&nbsp;';
		        }
		        return $html;
	        })
	        ->addColumn('source', function ($row) {
		        return 'Uploaded';
	        })
	        ->editColumn('created_at', function ($row) {
		        return Carbon::parse($row->created_at)->format('m/d/Y');
	        })
	        ->rawColumns(['action']);
    }

    /**
     * Get query source of dataTable.
     *
     * @param \App\models\Ticket $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(Ticket $model): \Illuminate\Database\Eloquent\Builder
    {
	    $user = CheckUser::isLogin();
	    $client = Client::where('email', '=', $user)->orWhere('dni', '=', $user)->first();
	    return Document::join('clients', 'clients.id', '=', 'documents.client_id')
		    ->join('users', 'users.id', '=', 'documents.uploaded_by')
		    ->select('documents.id', 'users.name', 'documents.title', 'documents.type', 'documents.created_at', 'documents.description', 'documents.client_id', 'documents.document_name')
		    ->where('documents.client_id', $client->id)
		    ->where('documents.visible_to_client', 1);
    }

    /**
     * Optional method if you want to use html builder.
     *
     * @return \Yajra\DataTables\Html\Builder
     */
    public function html()
    {
        return $this->builder()
                    ->setTableId('document-table')
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
                   window.LaravelDataTables["document-table"].buttons().container()
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
            "#ID" => ['data' => 'id', 'name' => 'id'],
            'Added (Updated) By' => ['data' => 'name', 'name' => 'users.name'],
            'Source' => ['data' => 'source', 'name' => 'source'],
            'Title' => ['data' => 'title', 'name' => 'title'],
            'Date' => ['data' => 'created_at', 'name' => 'created_at'],
            'Description' => ['data' => 'description', 'name' => 'description'],
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
        return 'Documents_' . date('YmdHis');
    }
}
