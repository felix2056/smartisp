<?php

namespace App\DataTables;

use App\models\ItemHistory;
use App\models\Sms;
use App\models\ProductItem;
use App\models\Zone;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class ItemHistoryDataTable extends DataTable
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
            ->editColumn('current_status', function ($row) {
                if($row->current_status == 'in_use') {
                    return '<i class="ace-icon fa fa-check bigger-130"></i>';
                } else {
                    return '<i class="ace-icon fa fa-dash bigger-130"></i>';
                }

            })
            ->rawColumns(['status', 'current_status']);
    }

    /**
     * Get query source of dataTable.
     *
     * @param \App\models\Sms $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(ItemHistory $model)
    {

        $request = request();
        $itemId = $request->route('itemId');

        return $model->select(
                'status',
                'notes',
                'date_time',
                'current_status'
            )->where('item_id', $itemId);
    }

    /**
     * Optional method if you want to use html builder.
     *
     * @return \Yajra\DataTables\Html\Builder
     */
    public function html()
    {
        return $this->builder()
            ->setTableId('item-history-table')
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
                   window.LaravelDataTables["item-history-table"].buttons().container()
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
            __('app.status') => ['data' => 'status', 'name' => 'status'],
            __('app.notes') => ['data' => 'notes', 'name' => 'notes'],
            __('app.dateTime') => ['data' => 'date_time', 'name' => 'date_time'],
            __('app.currentStatus') => ['data' => 'current_status', 'name' => 'current_status'],
        ];
    }

    /**
     * Get filename for export.
     *
     * @return string
     */
    protected function filename()
    {
        return 'Item_History' . date('YmdHis');
    }
}
