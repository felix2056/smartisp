<style type="text/css">
    #idrefre {
        top: 85px !important;
        cursor: pointer;
        z-index: 99999;
    }

    #idrefre:hover {
        background: #000;
    }

    #transaction-table_wrapper .dt-buttons {
        display: none;
    }
</style>
<div class="row">
    <div class="col-xs-12">
        <div class="row">
            <div class="col-xs-12 col-sm-12 widget-container-col">
                <div class="widget-box widget-color-blue2">
                    <div class="widget-body">
                        <div class="widget-main">
                            <!--Contenido widget-->
                            <div class="table-responsive">
                                @php
                                    use App\Http\Controllers\PermissionsController;
                                @endphp
                                @if(PermissionsController::hasAnyRole('pagos_nuevo'))
                                    <div class="row">
                                        <div class="col-md-12">
                                            <button type="button" class="btn btn-success" data-toggle="modal"
                                                    onclick="addPayment();return false;"><i class="icon-plus"></i> New
                                            </button>
                                        </div>
                                    </div>
                                @endif


                                <table id="transaction-table" class="table table-bordered table-hover">
                                    <div id="idrefre">
                                        <i class="ace-icon fa fa-refresh"></i>
                                    </div>
                                    <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>@lang('app.date')</th>
                                        <th>@lang('app.waytopay')</th>
                                        <th> @lang('app.amount')</th>
                                        <th>@lang('app.commentary')</th>
                                        <th>@lang('app.actions')</th>
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
<hr>
<div class="row">
    <div class="col-lg-6 col-md-9">
        <div class="panel panel-default">
            <div class="panel-heading">
                <strong><font style="vertical-align: inherit;"><font
                            style="vertical-align: inherit;">@lang('app.totals')</font></font></strong>
            </div>
            <div class="panel-body">
                <table class="display supertable table table-striped table-bordered">
                    <thead>
                    <tr>
                        <th><font style="vertical-align: inherit;"><font
                                    style="vertical-align: inherit;">@lang('app.type')</font></font></th>
                        <th><font style="vertical-align: inherit;"><font
                                    style="vertical-align: inherit;">@lang('app.quantity')</font></font></th>
                        <th><font style="vertical-align: inherit;"><font
                                    style="vertical-align: inherit;">@lang('app.total')</font></font></th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($data as $key => $total)
                        <tr>
                            <td><label class="label label-success"><font style="vertical-align: inherit;"><font
                                            style="vertical-align: inherit;"> {{ $key }}</font></font></label></td>
                            <td id="admin_customers_view_billing_transactions_totals_debit_amount">{{ $total['quantity'] }}</td>
                            <td id="admin_customers_view_billing_transactions_totals_debit_total">{{ $total['total'] }} {{ $global->nmoney }}</td>
                        </tr>
                    @endforeach

                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>


<script>
    $(document).ready(function () {
        $("#idrefre").click(function () {
            location.reload();
        });
    });
    $(function () {
        var table = '';
        renderDataTable();
    });

    function renderDataTable() {
        if ($.fn.DataTable.isDataTable('#transaction-table')) {
            $('#transaction-table').dataTable().fnClearTable();
            $('#transaction-table').dataTable().fnDestroy();
        }
        table = $('#transaction-table').DataTable({
            "oLanguage": {
                "sUrl": '{{ __('app.datatable') }}'
            },
            dom: "<'row'<'col-md-6'l><'col-md-6'Bf>><'row'<'col-sm-12'tr>><'row'<'col-sm-6'i><'col-sm-6'p>>",
            processing: true,
            serverSide: true,
            pageLength: '10',
            responsive: true,
            destroy: true,
            order: [
                '0', 'desc'
            ],
            buttons: [
                'excel', 'csv'
            ],
            ajax: {
                "url": "{{ route('payments.list', $clients->id) }}",
                "type": "POST",
                "cache": false,
            },
            columns: [
                {name: 'id', data: 'id'},
                {name: 'date', data: 'date', sortable: false},
                {name: 'way_to_pay', data: 'way_to_pay'},
                {name: 'amount', data: 'amount'},
                {name: 'commentary', data: 'commentary'},
                {name: 'action', data: 'action', sortable: false, searchable: false},
            ]
        });
    }

    function addPayment() {
        var url = '{{route('payments.create', $clients->id)}}';

        $.ajaxModal('#addEditModal', url);
    }

    function editPayment(id) {
        var url = '{{route('payments.edit', ':id')}}';
        url = url.replace(':id', id) + '?client_id=' + '{{$clients->id}}';

        $.ajaxModal('#addEditModal', url);
    }

    function deletePayment(id) {

        bootbox.confirm('{{ __('messages.Areyousureyouwanttodeletethepayment') }}', function (result) {
            if (result) {
                var url = '{{route('payments.delete', ':id')}}';
                url = url.replace(':id', id);

                $.easyAjax({
                    type: 'POST',
                    url: url,
                    container: "#transaction-table",
                    success: function (response) {
                        if (response.status == "success") {
                            table.draw();
                        }
                    }
                });
            }
        });

    }
</script>
