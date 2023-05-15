<?php

namespace App\DataTables;

use App\models\User;
use App\models\UserCredit;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Html\Editor\Editor;
use Yajra\DataTables\Html\Editor\Fields;
use Yajra\DataTables\Services\DataTable;

class UserCreditDataTable extends DataTable
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
                
                if($row->status == 1) {
                    return $styleb.'<a class="red del" href="#" id="'.$row->id.'"><i class="ace-icon fa fa-trash-o bigger-130"></i></a></div>';
                }
                else{
                    return $styleb.'<a class="red del" href="#" id="'.$row->id.'"><i class="ace-icon fa fa-trash-o bigger-130"></i></a></div>';
                }
            })
            ->rawColumns(['action', 'status', 'level']);
    }

    /**
     * @param User $model
     * @return mixed
     */
    public function query(UserCredit $model)
    {
    	$request = request();
    	$user = $request->route('id');

        return $model->join('users', 'users.id', '=', 'user_credits.user_id')
	        ->select('user_credits.id', 'users.name', 'user_credits.credit')
	        ->where('user_id', $user);

    }

    /**
     * Optional method if you want to use html builder.
     *
     * @return \Yajra\DataTables\Html\Builder
     */
    public function html()
    {
        return $this->builder()
            ->setTableId('user-credit-table')
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
                   window.LaravelDataTables["user-credit-table"].buttons().container()
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
            __('app.id') => ['data' => 'id', 'name' => 'id'],
            __('app.fullName') => ['data' => 'name', 'name' => 'users.name'],
            __('app.balance') => ['data' => 'credit', 'name' => 'credit'],
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
        return 'User_Credits' . date('YmdHis');
    }
}
