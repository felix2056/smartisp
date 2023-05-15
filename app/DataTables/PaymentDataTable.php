<?php

namespace App\DataTables;

use App\Http\Controllers\PermissionsController;
use App\models\PaymentNew;
use App\models\Transaction;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class PaymentDataTable extends DataTable
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
                if(PermissionsController::hasAnyRole('pagos_finanzas_editar')){
                    $html.='<a href="javascript:;" onclick="editPayment(\''.$row->id.'\')" title="Edit"><span class="glyphicon glyphicon-edit"></span></a>&nbsp;';
                }

                if(PermissionsController::hasAnyRole('pagos_finanzas_eliminar')){
                    $html.=' <a href="javascript:;" onclick="deletePayment(\''.$row->id.'\')" title="Remove"><span class="glyphicon glyphicon-trash"></span></a>';
                }

                if($html == '') {
                    $html='Sin permisos';
                }

                return $html;

            })
            ->filterColumn('name', function ($query, $keyword) {
                $sql = "clients.name  like ?";
                $query->whereRaw($sql, ["%{$keyword}%"]);
            })
            ->editColumn('date', function ($row) {
                return Carbon::parse($row->date)->format('m/d/Y');
            })
            ->rawColumns(['action', 'name']);
    }

    /**
     * Get query source of dataTable.
     *
     * @param \App\models\PaymentNew $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(PaymentNew $model): \Illuminate\Database\Eloquent\Builder
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

        $payments = $model->join('clients', 'clients.id', '=', 'payment_news.client_id')
            ->select('payment_news.id', 'clients.name', 'payment_news.way_to_pay', 'payment_news.date', 'payment_news.amount', 'payment_news.commentary', 'payment_news.id_pago');

        if($date) {
            $payments = $payments->whereBetween('payment_news.date', [$from, $to]);
        }

        return $payments;

    }

    /**
     * Optional method if you want to use html builder.
     *
     * @return \Yajra\DataTables\Html\Builder
     */
    public function html()
    {
        return $this->builder()
                    ->setTableId('payment-table')
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
                   window.LaravelDataTables["payment-table"].buttons().container()
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
            __('app.waytopay') => ['data' => 'way_to_pay', 'name' => 'way_to_pay'],
            __('app.clientName') => ['data' => 'name', 'name' => 'name'],
            __('app.date') => ['data' => 'date', 'name' => 'date', 'orderable' => false],
            "Suma" => ['data' => 'amount', 'name' => 'amount'],
            __('app.commentary') => ['data' => 'commentary', 'name' => 'commentary'],
            'Id Pago'  => ['data' => 'id_pago', 'name' => 'id_pago', 'orderable' => false, 'searchable' => true, 'visible' => false, 'exportable' => false],
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
        return 'Payment_' . date('YmdHis');
    }
}
