<style type="text/css">
    #idrefre {
        top: 17px !important;
        cursor: pointer;
        z-index: 1;
        padding: 1px 8px !important;
    }

    #idrefre:hover {
        background: #000;
    }

    #transaction-table_wrapper .dt-buttons {
        display: none;
    }
</style>
<div class="row">

    <div class="load_t"></div>
    <div class="col-xs-12">
        <div class="row">
            <div class="col-xs-12 col-sm-12 widget-container-col">
                <div class="widget-box widget-color-blue2">

                    <div id="idrefre">
                        <i class="ace-icon fa fa-refresh"></i>
                    </div>

                    <div class="widget-body">
                        <div class="widget-main">
                            <!--Contenido widget-->
                            <div class="table-responsive">
                                <table id="transaction-table" class="table table-bordered table-hover">
                                    <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>@lang('app.date')</th>
                                        <th>@lang('app.debit')</th>
                                        <th>@lang('app.credit')</th>
                                        <th>@lang('app.accountBalance')</th>
                                        <th>@lang('app.description')</th>
                                        <th>@lang('app.category')</th>
                                        <th>@lang('app.quantity')</th>
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
                        @php
                            $classes = ['credit' => 'warning', 'debit' => 'success', 'total' => 'primary'];
                        @endphp
                        @foreach($data as $key => $total)
                            <tr>
                                <td><label class="label label-{{ $classes[$key] }}"><font
                                            style="vertical-align: inherit;"><font
                                                style="vertical-align: inherit;">@if($key == 'debit')
                                                    + @endif {{ ucFirst($key) }}</font></font></label></td>
                                <td id="admin_customers_view_billing_transactions_totals_debit_amount">{{ $total['quantity'] }}</td>
                                <td id="admin_customers_view_billing_transactions_totals_debit_total">{{ $total['total'] }}</td>
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
                "url": "{{ route('transaction.list', $clients->id) }}",
                "type": "POST",
                "cache": false,
            },
            columns: [
                {name: 'id', data: 'id'},
                {name: 'date', data: 'date'},
                {name: 'debit', data: 'debit', sortable: false, searchable: false},
                {name: 'credit', data: 'credit', sortable: false, searchable: false},
                {name: 'account_balance', data: 'account_balance'},
                {name: 'description', data: 'description'},
                {name: 'category', data: 'category'},
                {name: 'quantity', data: 'quantity'},
                {name: 'action', data: 'action', sortable: false, searchable: false, responsivePriority: 7, targets: 8},
            ],
            "createdRow": function (row, data, dataIndex) {
                $(row).addClass('details-control');
                $(row).attr('data-id', data.id);
            }
        });
    }

    function editTransaction(id) {
        var url = '{{route('transaction.edit', ':id')}}';
        url = url.replace(':id', id);

        $.ajaxModal('#addEditModal', url);
    }

    function deleteTransaction(id) {

        bootbox.confirm("{{ __('messages.deleteTransaction') }}", function (result) {
            if (result) {
                var url = '{{route('transaction.delete', ':id')}}';
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
