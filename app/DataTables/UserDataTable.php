<?php

namespace App\DataTables;

use App\models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Html\Editor\Editor;
use Yajra\DataTables\Html\Editor\Fields;
use Yajra\DataTables\Services\DataTable;

class UserDataTable extends DataTable
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
                    return $styleb.'<a class="blue ban-client" href="#" id="'.$row->id.'"><i class="ace-icon fa fa-adjust bigger-130"></i></a><a class="green editar" href="#Edit" data-toggle="modal" data-target=".bs-edit-modal-lg" id="'.$row->id.'"><i class="ace-icon fa fa-pencil bigger-130"></i></a><a class="red del" href="#" id="'.$row->id.'"><i class="ace-icon fa fa-trash-o bigger-130"></i></a></div>';
                }
                else{
                    return $styleb.'<a class="blue ban-client" href="#" id="'.$row->id.'"><i class="ace-icon fa fa-adjust bigger-130"></i></a><a class="red del" href="#" id="'.$row->id.'"><i class="ace-icon fa fa-trash-o bigger-130"></i></a></div>';
                }

            })
            ->editColumn('status', function ($row) {
                if($row->status == 1)
                    return '<span class="label label-success arrowed">'.__('app.active').'</span>';
                if($row->status == 0)
                    return '<span class="label label-danger">'.__('app.locked').'</span>';
            })
            ->editColumn('name', function ($row) {
            	if($row->level == 'cs') {
		            return '<a href="'.route('user-credits', $row->id).'">'.$row->name.'</a>';
	            }
            	
            	return $row->name;
            })
            ->editColumn('created_at', function ($row) {
            	return $row->created_at->format('Y-m-d H:i:s');
            })
            ->editColumn('level', function ($row) {
                if($row->level == 'us')
                    return '<span class="label label-success">Admin</span>';
                if($row->level == 'cs')
                    return '<span class="label label-primary">Cashier</span>';
            })
            ->rawColumns(['action', 'status', 'level', 'name']);
    }

    /**
     * @param User $model
     * @return mixed
     */
    public function query(User $model)
    {
        $my_id = Auth::user()->id;

        return $model->where('email', '!=', 'support@smartisp.us')->where('id', '<>', $my_id);

    }

    /**
     * Optional method if you want to use html builder.
     *
     * @return \Yajra\DataTables\Html\Builder
     */
    public function html()
    {
        return $this->builder()
            ->setTableId('user-table')
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
                   window.LaravelDataTables["user-table"].buttons().container()
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
            __('app.fullName') => ['data' => 'name', 'name' => 'name'],
            __('app.email') => ['data' => 'email', 'name' => 'email'],
            __('app.telephone') => ['data' => 'phone', 'name' => 'phone'],
            __('app.username') => ['data' => 'username', 'name' => 'username'],
            __('app.registered') => ['data' => 'created_at', 'name' => 'created_at'],
            __('app.state') => ['data' => 'status', 'name' => 'status'],
            __('app.type') => ['data' => 'level', 'name' => 'level'],
            __('app.balance') => ['data' => 'balance', 'name' => 'balance'],
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
        return 'User_' . date('YmdHis');
    }
}
