<?php

namespace App\DataTables;

use App\Http\Controllers\PermissionsController;
use App\models\Transaction;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class TransactionDataTable extends DataTable
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
                if(PermissionsController::hasAnyRole('tran_finanzas_editar')){
                    $html.='<a href="javascript:;" onclick="editTransaction(\'' . $row->id . '\')" title="Edit"><span class="glyphicon glyphicon-edit"></span></a>&nbsp;';
                }

                if(PermissionsController::hasAnyRole('tran_finanzas_eliminar')){
                    $html.='<a class="red" href="javascript:;" onclick="deleteTransaction(\'' . $row->id . '\')" title="Remove"><span class="glyphicon glyphicon-trash"></span></a>';
                }

                if($html==''){
                    $html='Sin permisos';
                }

                return $html;


            })
            ->editColumn('date', function ($row) {
                return Carbon::parse($row->date)->format('Y-m-d');
            })
            ->rawColumns(['action']);
    }

    /**
     * Get query source of dataTable.
     *
     * @param \App\models\Transaction $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(Transaction $model)
    {
        $request = request();
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

        $transactions = $model->join('clients', 'clients.id', '=', 'transactions.client_id')
            ->select(
                'transactions.id',
                'transactions.created_at',
                DB::raw("(CASE WHEN category='service' THEN amount ELSE '-' END) as debit"),
                DB::raw("(CASE WHEN category='payment' THEN amount WHEN category='refund' THEN amount ELSE '-'  END) as credit"),
                'clients.name',
                'transactions.description',
                'transactions.category',
                'transactions.quantity',
                'transactions.date'
            );

        if($date) {
            $transactions = $transactions->whereBetween('transactions.date', [$from, $to]);
        }

        return $transactions;
    }

    /**
     * Optional method if you want to use html builder.
     *
     * @return \Yajra\DataTables\Html\Builder
     */
    public function html()
    {
        return $this->builder()
                    ->setTableId('transaction-table')
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
                   window.LaravelDataTables["transaction-table"].buttons().container()
                    .prependTo( ".widget-header .widget-toolbar")
                }',
                'fnDrawCallback' => 'function () {
                   filterTotals();
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
            __('app.id') => ['data' => 'id', 'name' => 'id'],
            __('app.date') => ['data' => 'date', 'name' => 'date'],
            __('app.debit') => ['data' => 'debit', 'name' => 'debit', 'orderable' => false, 'searchable' => false],
            __('app.credit') => ['data' => 'credit', 'name' => 'credit', 'orderable' => false, 'searchable' => false],
            __('app.clientName') => ['data' => 'name', 'name' => 'clients.name'],
            __('app.description') => ['data' => 'description', 'name' => 'description'],
            __('app.category') => ['data' => 'category', 'name' => 'category'],
            __('app.quantity') => ['data' => 'quantity', 'name' => 'quantity'],
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
        return 'Transaction_' . date('YmdHis');
    }
}
