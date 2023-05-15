<?php

namespace App\DataTables;

use App\models\Client;
use App\models\OdbSplitter;
use App\models\Zone;
use Illuminate\Support\Facades\DB;
use Yajra\DataTables\Html\Button;
use Yajra\DataTables\Html\Column;
use Yajra\DataTables\Services\DataTable;

class OdbDataTable extends DataTable
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

                if($this->validatePortUsados($row->id,$row->port)){

                    return $styleb.'<a class="green editar" href="#Edit" data-toggle="modal" data-target=".bs-edit-modal-lg" id="'.$row->id.'">
                        <i class="ace-icon fa fa-pencil bigger-130"></i>
                    </a>
                    <a class="red del" href="#" id="'.$row->id.'">
                        <i class="ace-icon fa fa-trash-o bigger-130"></i>
                    </a>
                    </div>';

                } else {

                    return $styleb.'<a class="green editar" href="#Edit" data-toggle="modal" data-target=".bs-edit-modal-lg" id="'.$row->id.'">
                        <i class="ace-icon fa fa-pencil bigger-130"></i>
                    </a>

                    </div>';
                }
            })
            ->editColumn('port_dis', function ($row) {
                $Client = Client::select('port')->where('odb_id',$row->id)->get();


                $ports="";
                $conteo=0;
                for ($i = 1; $i <= $row->port; $i++) {
                    $sw=true;
                    foreach ($Client as $key){
                        if($i==$key->port){
                            $sw=false;
                        }
                    }
                    if($sw){
                        $ports .= '<span class="ports">' . $i . '</span>';
                        $conteo++;
                    }
                }


                return $ports;
            })
            ->rawColumns(['action','port_dis']);
    }

    /**
     * Get query source of dataTable.
     *
     * @param \App\models\OdbSplitter $model
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function query(OdbSplitter $model)
    {
        return $model->join('zone', 'zone.id', '=', 'odb_splitter.zone_id')
            ->select('odb_splitter.id','odb_splitter.name','odb_splitter.port','zone.name as zone','odb_splitter.coordinates');
    }

    /**
     * Optional method if you want to use html builder.
     *
     * @return \Yajra\DataTables\Html\Builder
     */
    public function html()
    {
        return $this->builder()
            ->setTableId('odb-table')
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
                   window.LaravelDataTables["odb-table"].buttons().container()
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
            __('app.name') => ['data' => 'name', 'name' => 'name'],
            'Num Port' => ['data' => 'port', 'name' => 'port'],
            'Zone' => ['data' => 'zone', 'name' => 'zone.name'],
            'Coordinates' => ['data' => 'coordinates', 'name' => 'coordinates'],
            'Port Disponible' => ['data' => 'port_dis', 'name' => 'port_dis', 'orderable' => false, 'searchable' => false],
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
        return 'Odb_' . date('YmdHis');
    }

    /**
     * @param $id
     * @param $port
     * @return bool
     */
    public function validatePortUsados($id, $port){

        $Client = Client::select('port')->where('odb_id',$id)->get();
        $ports="";
        $conteo=0;
        $cant_tot = $port;
        $cant_red = $port;
        for ($i = 1; $i <= $port; $i++) {
            $sw=true;
            foreach ($Client as $key){
                if($i==$key->port){

                    $cant_red--;
                    $sw=false;
                }
            }
            if($sw){
                if($conteo==0){
                    $ports = $i;
                }else{
                    $ports = $ports.",".$i;
                }
                $conteo++;
            }
        }
        $status=false;
        if($cant_tot==$cant_red){
            $status=true;
        }
        return $status;
    }
}
