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

class CashdeskInvoiceDataTable extends DataTable
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
		
		        $actions = $actions . '
                    <a href="' . route('cashdesk.invoice.prints', ['id' => $row->id]) . '" target="_blank" title="Print Invoice">
                        <span class="glyphicon glyphicon-print"></span>
                    </a>

                    <a href="' . route('cashdesk.invoices.showPDF', ['id' => $row->id, 'download' => 'true']) . '" title="Download">
                        <span class="glyphicon glyphicon-download"></span>
                    </a>&nbsp;  ';
		        $actions .= '<a href="javascript:void(0)" onclick="sendEmail(' . $row->id . ')" title="'.__('app.sendinvoice').'"><span class="glyphicon glyphicon-send"></span></a>';
		
		        return $actions;
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
            ->rawColumns(['action' ,'status']);
    }

    /**
     * Get query source of dataTable.
     *
     * @param \App\models\Transaction $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(Transaction $model)
    {
        $clientId = request()->route('id');

        $invoices = BillCustomer::select('bill_customers.id', 'bill_customers.num_bill', 'bill_customers.release_date', 'bill_customers.total_pay', 'bill_customers.paid_on', 'bill_customers.status', 'bill_customers.created_at')
            ->where('client_id', $clientId)->orderBy('id', 'DESC')->take(5);
        
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
            ->setTableId('cashdesk-invoice-table')
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
                   window.LaravelDataTables["cashdesk-invoice-table"].buttons().container()
                    .prependTo( ".widget-header .widget-toolbar")
                }',
                'fnDrawCallback' => 'function () {
                
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
        return 'Invoice_Cashdesk_' . date('YmdHis');
    }
}
