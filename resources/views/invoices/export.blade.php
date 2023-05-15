<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span><span class="sr-only">@lang('app.close')</span></button>
    <h4 class="modal-title" id="myModalLabel"><i class="fa fa-money"></i>
        @lang('app.export') Invoices</h4>
</div>
<div class="tabbable">
    <ul class="nav nav-tabs" id="ro">
        <li class="active"><a data-toggle="tab" href="#filters"><i
                        class="fa fa-info-circle"></i> @lang('app.export')</a>
        </li>
        <li id="redes"><a href="#history" role="tab"
                          data-toggle="tab" onclick="history();return false"><i
                        class="fa fa-sitemap"></i> @lang('app.export') @lang('app.record')</a></li>
    </ul>
    <div class="tab-content" id="myTab">
        <div id="filters" class="tab-pane fade in active">
            <form class="form-horizontal" method="post" action="{{route('invoices.export-invoices')}}" id="formaddpay" autocomplete="off">
                {{ csrf_field() }}
                <div class="modal-body">
                    <div class="form-group">
                        <label for="name" class="col-sm-3 control-label">@lang('app.period')</label>
                        <div class="col-sm-8">
                            <div class="input-group">
                                <input class="form-control" onchange="checkInvoices();return false;" type="text" name="date-range" id="date-range-picker" readonly />
                                <span class="input-group-addon">
                        <i class="fa fa-calendar bigger-110"></i>
                    </span>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label for="name" class="col-sm-3 control-label">@lang('app.partner')</label>
                    <div class="col-sm-8">
                        <select class="form-control" id="routers" name="routers" onchange="checkInvoices();return false;">
                            <option value="any" selected="">@lang('app.any')</option>
                            @foreach($routers as $router)
                                <option value="{{ $router->id }}">{{ $router->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label for="name" class="col-sm-3 control-label">@lang('app.location')</label>
                    <div class="col-sm-8">
                        <select class="form-control" id="location" name="location" onchange="checkInvoices();return false;">
                            <option value="any" selected="">@lang('app.any')</option>
                            @foreach($routers as $router)
                                <option value="{{ $router->location }}">{{ $router->location }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label for="name" class="col-sm-3 control-label">@lang('app.customerTypeofpayment')</label>
                    <div class="col-sm-8">
                        <select class="form-control" id="way_to_pay" name="way_to_pay" onchange="checkInvoices();return false;">
                            <option value="any" selected="">@lang('app.any')</option>
                            <option value="Cash">@lang('app.cash')</option>
                            <option value="Bank Transfer">@lang('app.bankTransfer')</option>
                            <option value="PayPal">PayPal</option>
                            <option value="Stripe">Stripe</option>
                            <option value="Other">@lang('app.other')</option>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label for="name" class="col-sm-3 control-label">@lang('app.state')</label>
                    <div class="col-sm-8">
                        <select class="form-control" id="status" name="status" onchange="checkInvoices();return false;">
                            <option value="any" selected="">@lang('app.any')</option>
                            <option value="1">@lang('app.paid')</option>
                            <option value="2">@lang('app.paid')(@lang('app.accountBalance'))</option>
                            <option value="3">@lang('app.unpaid')</option>
                            <option value="5">@lang('app.deleted')</option>

                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label for="name" class="col-sm-3 control-label">@lang('app.exportType')</label>
                    <div class="col-sm-8">
                        <select class="form-control" id="export_type" name="export_type">
                            <option value="type_csv">CSV</option>
                            <option value="type_pdf_archive">PDF files in archive</option>
                            <option value="type_pdf">One PDF file</option>

                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label for="name" class="col-sm-3 control-label">@lang('app.bills')</label>
                    <div class="col-sm-8">
                        <label for="name" class=" control-label" id="totalInvoices">250</label>
                    </div>
                </div>
                <div class="modal-footer" id="swfoot">
                    <button type="button" class="btn btn-default" data-dismiss="modal">@lang('app.close')</button>
                    <button type="submit" class="btn btn-primary"><i class="fa fa-floppy-o"></i>
                        @lang('app.export')</button>
                </div>
            </form>

        </div>
        <div id="history" class="tab-pane fade in">
            <div class="row">
                <div class="col-xs-12 col-sm-12 widget-container-col">
                    <div class="widget-box widget-color-blue2">
                        <div class="widget-body">
                            <div class="widget-main">

                                <div class="table-responsive">
                                    <table id="export-history-table" class="table table-bordered table-hover" width="100%">
                                        <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Exploit states</th>
                                            <th>Created in</th>
                                            <th>@lang('app.type')</th>
                                            <th>@lang('app.operation')</th>
                                        </tr>
                                        </thead>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<script>
    $(function () {
        $('input[name=date-range]').daterangepicker({
            'applyClass' : 'btn-sm btn-success',
            'cancelClass' : 'btn-sm btn-default',
            'separator':'|',
            locale: {
                applyLabel: 'Aplicar',
                cancelLabel: 'Cancelar',
                fromLabel: 'Del',
                toLabel:'Hasta',
                separator:'|',
                format: "DD-MM-YYYY",
                customRangeLabel: "Personalizado",
                daysOfWeek: [
                    "Do",
                    "Lu",
                    "Ma",
                    "Mi",
                    "Ju",
                    "Vi",
                    "Sa"
                ],
                monthNames: [
                    "Enero",
                    "Febrero",
                    "Marzo",
                    "Abril",
                    "Mayo",
                    "Junio",
                    "Julio",
                    "Agosto",
                    "Septiembre",
                    "Octubre",
                    "Noviembre",
                    "Diciembre"
                ],
            },
            ranges: {
                'Hoy': [moment(), moment()],
                'Ayer': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
                'Últimos 7 Días': [moment().subtract(6, 'days'), moment()],
                'Últimos 30 Días': [moment().subtract(29, 'days'), moment()],
                'Este Mes': [moment().startOf('month'), moment().endOf('month')],
                'El Mes Pasado': [moment().subtract(1, 'month').startOf('month'), moment().subtract(1, 'month').endOf('month')]
            }
        });
    });

    function checkInvoices() {
        $.easyAjax({
            type: 'POST',
            url: '{{ route('invoices.check') }}',
            data: $('#formaddpay').serialize(),
            container: "#formaddpay",
            success: function(res) {
                if(res.status == 'success') {
                    $('#totalInvoices').html(res.total)
                }
            }
        });
    }
    function exportInvoices() {
        var url = '{{ route('invoices.export-invoices') }}';

        $.easyAjax({
            type: 'POST',
            url: url,
            data: $('#formaddpay').serialize(),
            container: "#formaddpay",
            success: function(res) {
                if(res.status == 'success') {
                    $('#addEditModal').modal('hide');
                    table.draw();
                }
            }
        });
    }

    function history() {
        console.log('hii from history');
        renderDataTable();
    }

    function renderDataTable () {
        table = $('#export-history-table').DataTable({
            "oLanguage": {
                "sUrl": "{{ asset('assets/js/dataTables/dataTables.spanish.txt') }}"
            },
            dom: "<'row'<'col-md-6'l><'col-md-6'Bf>><'row'<'col-sm-12'tr>><'row'<'col-sm-6'i><'col-sm-6'p>>",
            processing: true,
            serverSide: true,
            pageLength: '10',
            responsive: true,
            destroy:true,
            buttons:[],
            order: [
                '0', 'desc'
            ],
            ajax: {
                "url": "{{ route('export-history.list') }}",
                "type": "POST",
                "cache": false,
            },
            columns: [
                {name:'id', data: 'id'},
                {name:'state', data: 'state' },
                {name:'created_at', data: 'created_at'},
                {name:'type', data: 'type'},
                {name:'action', data: 'action', sortable: false, searchable: false},
            ],
            "createdRow": function( row, data, dataIndex ) {
                $(row).addClass( 'details-control' );
                $(row).attr( 'data-id', data.id );
            }
        });
    }

    function removeHistory(id) {
        var url = '{{ route('export-history.delete', ':id') }}';
        url = url.replace(':id', id)

        $.easyAjax({
            type: 'POST',
            url: url,
            container: "#export-history-table",
            success: function(response) {
                if (response.status == "success") {
                    table.draw();
                }
            }
        });
    }
</script>
