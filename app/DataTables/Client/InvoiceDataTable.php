<?php

namespace App\DataTables\Client;

use App\libraries\CheckUser;
use App\models\BillCustomer;
use App\models\Client;
use App\models\GlobalSetting;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redirect;
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
        $global = GlobalSetting::first();
        return datatables()
            ->eloquent($query)
            ->addColumn('action', function ($row) use($global) {

                $actions = '';
                $styleb = '<div class="action-buttons">';
                if ($row->status == 3) {

                    $actions .= $styleb.'<a class="blue" href="../billprint/' .$row->id. '" target="_black"><i class="ace-icon fa fa-print bigger-130"></i></a>
                        <a class="blue" href="../bill-download/' .$row->id. '" target="_black">
                            <i class="ace-icon fa fa-download bigger-130"></i>
                        </a>
                        <div class="inline position-relative">
                            <button class="btn btn-minier dropdown-toggle" data-toggle="dropdown" data-position="auto">Pay <i class="ace-icon fa fa-caret-down icon-only"></i></button>
                            <ul class="dropdown-menu dropdown-only-icon dropdown-yellow dropdown-menu-right dropdown-caret dropdown-close">';

                    if($global->paypal_status == 1) {
                        $actions .= '<li>
                                    <a href="/paypal/' .$row->id. '"><i class="fa fa-paypal"></i> Pay via Paypal </a>
                                </li>';
                    }
                    if($global->stripe_status == 1) {
                        $actions .= '<li class="divider"></li>
                                <li>
                                    <a href="javascript:void(0);" id="stripePaymentButton" onclick="payWithStripe(\'' .$row->id. '\', \''  .$row->client->email. '\', \''  .$row->total_pay. '\', \'' .$row->client->email. '\')">
                                    <i class="fa fa-cc-stripe"></i> Pay via Stripe 
                                </a>
                                </li>';
                    }
                    if($global->payu_status == 1) {
                        $actions .= '
                                <li class="divider"></li>
                                <li>
                                    <a href="/paypayu/' .$row->id. '"><i class="fa fa-cc-visa"></i> Pay via PayU </a>
                                </li>';
                    }
                    if($global->directo_pago_status == 1) {
                        $actions .= '<li>
                                <a href="/directopago/' .$row->id. '"><i class="fa fa-money"></i> Pay via DLocal </a>
                                </li>';
                    }

                    if($global->pay_valida_status == 1) {
                        $actions .= '<li>
                                <a href="/payvalida/' .$row->id. '"><i class="fa fa-money"></i> Pay via Payvalida </a>
                                </li>';
                    }

                    $actions .= ' </ul></div></ul></div>';

                    return $actions;
                    }
                return $styleb . '</a><a class="blue" href="../billprint/' .$row->id. '" target="_black"><i class="ace-icon fa fa-print bigger-130"></i></a></div>';
            })
            ->editColumn('status', function ($row) {
                if ($row->status == 1) {
                    return '<span class="badge badge-success">'.__('messages.Paidout'). '</span>';
                } else if ($row->status == 2) {
                    return '<span class="badge badge-purple">' .__('messages.paidFromAccount'). '</span>';
                } else if ($row->status == 3) {
                    return '<span class="badge badge-danger">' .__('app.unpaid'). '</span>';
                } else if ($row->status == 4) {
                    return '<span class="badge badge-pink">Tarde</span>';
                } else if ($row->status == 5) {
                    return '<span class="badge badge-primary">' .__('app.remove'). '</span>';
                }
            })
            ->rawColumns(['action', 'status']);
        ;
    }

    /**
     * Get query source of dataTable.
     *
     * @param \App\models\BillCustomer $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(BillCustomer $model)
    {
        $user = CheckUser::isLogin();

        $client = Client::where('email', '=', $user)->orWhere('dni', '=', $user)->first();

        $bills = BillCustomer::with('client')
            ->where('client_id', $client->id);

        return $bills;
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
                'createdRow' => "function (row, data, index) {
                    //console.log(data['open']);
                    if (data['open'] == 0) {

                        $('td', row).addClass('rowblue');
                    }
                }",
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
            __('app.bill') => ['data' => 'num_bill', 'name' => 'num_bill'],
            __('app.period') => ['data' => 'period', 'name' => 'period'],
            __('app.DateOfIssue') => ['data' => 'release_date', 'name' => 'release_date'],
            __('app.duedate') => ['data' => 'expiration_date', 'name' => 'expiration_date'],
            __('app.AmountToPay') => ['data' => 'total_pay', 'name' => 'total_pay'],
            __('app.state') => ['data' => 'status', 'name' => 'status'],
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
