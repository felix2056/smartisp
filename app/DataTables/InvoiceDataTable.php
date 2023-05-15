<?php

namespace App\DataTables;

use App\Http\Controllers\PermissionsController;
use App\models\BillCustomer;
use App\models\Factel;
use App\models\Sri;
use App\models\Transaction;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class InvoiceDataTable extends DataTable
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
                $actions = '';

                if (!in_array($row->status, [1, 2, 4])) {
                    if (PermissionsController::hasAnyRole('factura_finanzas_pagar')) {
                        $actions = '<a href="javascript:void(0)" onclick="payInvoice(' . $row->id . ')" title="Pay Invoice"><span class="glyphicon glyphicon-ok"></span></a>&nbsp;    ';
                    }
                }

                $actions = $actions . '
                    <a href="javascript:void(0)" onclick="showInvoice(' . $row->id . ')" title="View Invoice">
                        <span class="glyphicon glyphicon-info-sign"></span>
                    </a>
                    &nbsp;
                    <a href="' . route('invoice.showPDF', ['id' => $row->id]) . '" target="_blank" title="View Invoice">
                        <span class="glyphicon glyphicon-file"></span>
                    </a>

                    <a href="' . route('invoice.print', ['id' => $row->id]) . '" target="_blank" title="Print Invoice">
                        <span class="glyphicon glyphicon-print"></span>
                    </a>

                    <a href="' . route('invoice.showPDF', ['id' => $row->id, 'download' => 'true']) . '" title="Download">
                        <span class="glyphicon glyphicon-download"></span>
                    </a>&nbsp;  ';
                $actions .= '<a href="javascript:void(0)" onclick="sendEmail(' . $row->id . ')" title="'.__('app.sendinvoice').'"><span class="glyphicon glyphicon-send"></span></a>';

                if (!in_array($row->status, [1, 2, 4])) {

                    if (PermissionsController::hasAnyRole('factura_finanzas_editar')) {
                        if ($row->invoice_items->count() > 0) {
                            $actions .= '
                            <a href="javascript:void(0)" onclick="editInvoice(' . $row->id . ')" title="Edit Invoice">
                                <span class="glyphicon glyphicon-edit"></span>
                            </a>';
                        } else {
                            $actions .= '
                            <a href="javascript:void(0)" onclick="editCustomInvoice(' . $row->id . ')" title="Edit Invoice">
                                <span class="glyphicon glyphicon-edit"></span>
                            </a>';
                        }
                    }
                    if (PermissionsController::hasAnyRole('factura_finanzas_eliminar')) {
                        $actions .= '<a href="javascript:void(0)" onclick="deleteInvoice(' . $row->id . ')" title="Delete Invoice"><span class="glyphicon glyphicon-trash"></span></a>';
                    }
                } else {

                    if ($row->status == 2) {
                        if (PermissionsController::hasAnyRole('factura_finanzas_eliminar')) {
                            $actions .= '<a href="javascript:void(0)" onclick="deleteInvoice(' . $row->id . ')" title="Delete Invoice"><span class="glyphicon glyphicon-trash"></span></a>';
                        }
                    } else {

                        $actions = $actions . '
                        <a href="javascript:void(0)" onclick="editInvoicePayment(' . $row->id . ')" title="Edit Payment">
                            <span class="glyphicon glyphicon-pencil"></span>
                        </a>
                        <a href="javascript:void(0)" onclick="deleteInvoicePayment(' . $row->id . ')" title="Delete Payment"><span class="glyphicon glyphicon-remove"></span></a>';

                        $emisor = Factel::all();
                        $empresa = $emisor->first();
                        if($empresa->status== 2){
                            $invoice = Sri::where('id_factura', $row->id)->where('tipo','=','10')->where('estado','=','AUTORIZADO')->get();
                        }elseif($empresa->status== 3){
                            $invoice = Sri::where('id_factura', $row->id)->where('tipo','=','3')->where('estado', 'signed')->get();
                        }else{
                            $invoice = Sri::where('id_factura', $row->id)->where('estado','=','AUTORIZADO')->get();
                        }

                        if (!empty($empresa)) {
                            $status = $empresa->status;
                            if ($status != 1) {
                                if($status == 0){
                                    if(empty($invoice[0])){
                                        $actions .= '<a href="javascript:void(0)" onclick="send_sri(' . $row->id . ')" title="Send SRI"><span class="glyphicon glyphicon-envelope"></span></a>';
                                    }else{
                                        $actions .= '<a href="javascript:void(0)"  title="Send SRI"><span class="glyphicon glyphicon-envelope" style="color:gray"></span></a>';
                                    }
                                }else if($status == 2){
                                    if(empty($invoice[0])){
                                        $actions .= '<a href="javascript:void(0)" onclick="send_DIAN(' . $row->id . ')" title="Send DIAN"><span class="glyphicon glyphicon-envelope"></span></a>';
                                    }else{
                                        $actions .= '<a href="javascript:void(0)"  title="Send DIAN"><span class="glyphicon glyphicon-envelope" style="color:gray"></span></a>';
                                        $actions .= '<a href="javascript:void(0)" onclick="send_Note_DIAN(' . $row->id . ')" title="Send Note DIAN" style="margin-left: 5px;"><span class="glyphicon glyphicon-comment"></span></a>';
                                    }
                                }elseif($status == 3 ){
                                    if(empty($invoice[0])){
                                        $actions .= '<a href="javascript:void(0)" onclick="send_SAT(' . $row->id . ')" title="Send SAT"><span class="glyphicon glyphicon-envelope" style="margin:0 5px"></span></a>';
                                    }else{
                                        $actions .= '<a href="javascript:void(0)"  title="Send SAT"><span class="glyphicon glyphicon-envelope" style="color:gray;margin:0 5px"></span></a>';
                                        $actions .= '<a target="_blank" href="' . route('invoice_mx.payment.file') . '?doc_type=pdf&doc_id=' . $invoice[0]->informacionAdicional . '" title="Get SAT PDF"><span class="glyphicon glyphicon-print" style="margin:0 5px"></span></a>';
                                        $actions .= '<a target="_blank" href="' . route('invoice_mx.payment.file') . '?doc_type=xml&doc_id=' . $invoice[0]->informacionAdicional . '" title="Get SAT XML"><span class="glyphicon glyphicon-save-file" style="margin:0 5px"></span></a>';
                                    }
                                }
                            }
                        }
                    }
                }
                return $actions;
            })
            ->editColumn('name', function ($row) {
                return '<a href="' . route('billing', $row->client_id) . '#bill">' . $row->name . '</a>';
            })
            ->editColumn('release_date', function ($row) {
                return Carbon::parse($row->release_date)->format('d/m/Y');
            })
            ->editColumn('paid_on', function ($row) {
                if ($row->paid_on === null) {
                    return '--';
                }
                return Carbon::parse($row->paid_on)->format('d/m/Y');
            })
            ->editColumn('status', function ($row) {
                $badge = '';
                $label = [
                    'paid' => 'success',
                    'unpaid' => 'warning',
                    'late' => 'danger'
                ];
                switch ($row->status) {
                    case '1':
                        $badge = [
                            'status' => 'paid',
                            'label' => $label['paid']
                        ];
                        break;

                    case '2':
                        $badge = [
                            'status' => 'paid (account balance)',
                            'label' => $label['paid']
                        ];
                        break;

                    case '3':
                        $badge = [
                            'status' => 'Unpaid',
                            'label' => $label['unpaid']
                        ];
                        break;

                    case '4':
                        $badge = [
                            'status' => 'paid',
                            'label' => $label['paid']
                        ];
                        break;

                    default:
                        # code...
                        break;
                }

                $status = '<label class="label label-' . $badge['label'] . '">
                    <font style="vertical-align: inherit;">
                    <font style="vertical-align: inherit;">
                    ' . ucFirst($badge['status']) . '
                    </font>
                    </font>
                    </label>';

                return $status;
            })
            ->rawColumns(['action', 'name' ,'status']);
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

        $invoices = BillCustomer::select('bill_customers.id', 'clients.name', 'bill_customers.num_bill', 'bill_customers.release_date', 'bill_customers.total_pay', 'bill_customers.paid_on', 'bill_customers.status', 'bill_customers.created_at','clients.id as client_id')
            ->join('clients', 'clients.id', 'bill_customers.client_id');

        if($date) {
            $invoices = $invoices->whereBetween('bill_customers.release_date', array($from, $to));
        }

        return $invoices;
    }

    /**
     * Optional method if you want to use html builder.
     *
     * @return \Yajra\DataTables\Html\Builder
     */
    public function html()
    {
        return $this->builder()
                    ->setTableId('invoice-table')
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
                   window.LaravelDataTables["invoice-table"].buttons().container()
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
            __('app.name') => ['data' => 'name', 'name' => 'clients.name'],
            __('app.invoiceNumber') => ['data' => 'num_bill', 'name' => 'num_bill'],
            __('app.releaseDate') => ['data' => 'release_date', 'name' => 'release_date'],
            __('app.total') => ['data' => 'total_pay', 'name' => 'total_pay'],
            __('app.paymentDate') => ['data' => 'paid_on', 'name' => 'paid_on'],
            __('app.behavior') => ['data' => 'status', 'name' => 'status'],
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
        return 'Invoice_' . date('YmdHis');
    }
}
