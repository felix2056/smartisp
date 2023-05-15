<?php

namespace App\DataTables;

use App\Http\Controllers\PermissionsController;
use App\models\Box;
use App\models\PaymentNew;
use App\models\Transaction;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class ReportDataTable extends DataTable
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
                $styleb = '<div class="action-buttons">';
                return $styleb. '<a class="red del" href="javascript:void(0);" id="' .$row->id. '" data-type="' .$row->typepay. '"><i class="ace-icon fa fa-trash-o bigger-130"></i></a></div>';

            })
            ->editColumn('typepay', function ($row) {
                if ($row->typepay == 'ou')
                    return '<span class="label label-danger arrowed">' .__('app.expenses'). '</span>';
                else
                    return '<span class="label label-success arrowed">' .__('app.income'). '</span>';

            })
            ->rawColumns(['action']);
    }

    /**
     * Get query source of dataTable.
     *
     * @param \App\models\Transaction $model
     */
    public function query(PaymentNew $model)
    {
        $request = request();
        $date = $request->get('extra_search');
        $admin = $request->get('admin');


        if (empty($date)) {

            $reports = PaymentNew::select('id', 'client_id', 'way_to_pay', 'date', 'amount', 'received_by')->whereNull('deleted_at')->with([
                'client' => function($query) {
                    $query->select('id', 'router_id', 'plan_id', 'name')->with([
                        'plan:id,name',
                        'router:id,name'
                    ]);
                }, 'received'
            ]);

            $entries = Box::selectRaw('id,name,router_id,detail,type as way_to_pay,date_reg as date,amount')
                ->where('type', 'ou')
                ->with('router:id,name');
        } else {

            $string = explode('|', $date);

            $date1 = $string[0];
            $date2 = $string[1];

            $date1 = str_replace('/', '-', $date1);
            $date2 = str_replace('/', '-', $date2);

            $from = date("Y-m-d", strtotime($date1));
            $to = date("Y-m-d", strtotime($date2));


            $reports = PaymentNew::select('id', 'client_id', 'way_to_pay', 'date', 'amount', 'received_by')
                ->whereNull('deleted_at')
                ->whereBetween('date', array($from, $to))
                ->with([
                    'client' => function($query) {
                        $query->select('id', 'router_id', 'plan_id', 'name')->with([
                            'plan:id,name',
                            'router:id,name'
                        ]);
                    }, 'received'
                ]);

            if($admin == 'all') {
                $reports = $reports;
            } else {
                $reports = $reports->where('received_by', $admin);
            }

            $entries = Box::selectRaw('id,name,router_id,detail,type as way_to_pay,date_reg as date,amount,user_id')
                ->whereBetween('date_reg', array($from, $to))
                ->where('type', 'ou')
                ->with('router:id,name', 'user');

            if($admin == 'all') {
                $entries = $entries;
            } else {
                $entries = $entries->where('user_id', $admin);
            }
        }
        return $reports->union($entries);

//        $data = collect();
//        $data = $data->merge($reports)->merge($entries);
//        return $data;
    }

    /**
     * Optional method if you want to use html builder.
     *
     * @return \Yajra\DataTables\Html\Builder
     */
    public function html()
    {
        return $this->builder()
                    ->setTableId('report-table')
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
                   window.LaravelDataTables["report-table"].buttons().container()
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
            __('app.clientBusinessName') => ['data' => 'client', 'name' => 'client'],
            __('app.secretary') => ['data' => 'user', 'name' => 'user'],
            __('app.detail') => ['data' => 'detail', 'name' => 'detail'],
            __('app.type') => ['data' => 'typepay', 'name' => 'typepay'],
            __('app.date') => ['data' => 'date', 'name' => 'date'],
            __('app.amount') => ['data' => 'amount', 'name' => 'amount'],
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
        return 'Report_' . date('YmdHis');
    }
}
