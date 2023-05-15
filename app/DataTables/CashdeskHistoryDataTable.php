<?php

namespace App\DataTables;

use App\Http\Controllers\PermissionsController;
use App\models\campos_view_client;
use App\models\CashierDepositHistory;
use App\models\Client;
use App\models\GlobalSetting;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class CashdeskHistoryDataTable extends DataTable
{
    protected $global;
    protected $campos_v;

    public function __construct()
    {
//        $this->global = GlobalSetting::first();
//        $this->campos_v = campos_view_client::find(1);
    }
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
            ->editColumn('created_at',
                function ($row) {
                    
                    return $row->created_at->format('Y-m-d H:i:s');

                })
            ->rawColumns(['action']);
    }

    /**
     * Get query source of dataTable.
     *
     * @param \App\models\Client $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(CashierDepositHistory $model)
    {
		$user = auth()->guard('cashdesk')->user();
		return $model->select('*')->with('client')->where('user_id', $user->id);
    }

    /**
     * Optional method if you want to use html builder.
     *
     * @return \Yajra\DataTables\Html\Builder
     */
    public function html()
    {
        $domElement = "<'row'<'col-md-6'l><'col-md-6'Bf>><'row'<'col-sm-12'tr>><'row'<'col-sm-6'i><'col-sm-6'p>>";

        return $this->builder()
            ->setTableId('cashdesk-deposit-history-table')
            ->columns($this->getColumns())
            ->minifiedAjax()
            ->dom($domElement)
            ->orderBy(0)
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
                   window.LaravelDataTables["cashdesk-deposit-history-table"].buttons().container()
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
            "Client" => ['data' => 'client.name', 'name' => 'client.name'],
            "Amount" => ['data' => 'amount', 'name' => 'amount'],
            "Comment" => ['data' => 'comment', 'name' => 'comment'],
            "Created At" => ['data' => 'created_at', 'name' => 'created_at']
        ];
    }

    /**
     * Get filename for export.
     *
     * @return string
     */
    protected function filename()
    {
        return 'Cashdesk_History_' . date('YmdHis');
    }

//    protected function buildExcelFile()
//    {
////        dd($this->query(new Client())->get());
//        ini_set('max_execution_time', 300);
//        $excel = app('excel');
//        $extraColumns=count($this->getDataForExport()['0'])-6; // $this->getDataForExport() is where the data is at!
//        return \Maatwebsite\Excel\Facades\Excel::create('Laravel Excel', function($excel) use ($extraColumns) {
//            $excel->sheet('Excel sheet', function($sheet) use ($extraColumns) {
//                // lots of formatting code here
//            })->export('xls');
//        });
//    }

    public function pdf()
    {
        set_time_limit(0);
        if ('snappy' == config('datatables-buttons.pdf_generator', 'snappy')) {
            return $this->snappyPdf();
        }

        $pdf = app('dompdf.wrapper');
        $pdf->loadView('datatables::print', ['data' => $this->getDataForPrint()]);

        return $pdf->download($this->getFilename() . '.pdf');
    }

}
