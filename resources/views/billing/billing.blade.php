<link type="text/css" rel="stylesheet" href="{{ asset('assets/js/plupload/js/themes/flick/jquery-ui.min.css') }}" media="screen" />
<link rel="stylesheet" href="{{ asset('assets/js/plupload/js/jquery.ui.plupload/css/jquery.ui.plupload.css') }}" />
<div class="row">
    <div class="col-xs-12">
        <div class="row alert alert-warning">
            <div class="col-lg-4 col-xs-12">
                @lang('app.walletBalance'):
            </div>

            <div id="client_balance" class="col-lg-2 col-xs-12">
                {{ round($clients->wallet_balance, 2) }} {{ $global->nmoney }}
            </div>

            <div id="updateButton" class="col-lg-2 col-xs-12">
                @if (\App\Http\Controllers\PermissionsController::hasAnyRole('edit_client_balance'))
                <button class="btn btn-primary btn-sm btn-smm" type="button" onclick="editBalance({{ $clients->id }});return false;">
                    <i class="fa fa-dollar" aria-hidden="true"></i> @lang('app.editBalance')
                </button>
                @endif
            </div>
        </div>


        <div class="row alert alert-warning">
            <div class="col-lg-4 col-xs-12">
                @lang('app.pendingInvoiceAmount'):
            </div>

            <div id="client_pending_invoice_balance" class="col-lg-2 col-xs-12">
                {{ round($clients->balance, 2) }} {{ $global->nmoney }}
            </div>

            <div id="updateButton" class="col-lg-2 col-xs-12">
                @if (\App\Http\Controllers\PermissionsController::hasAnyRole('edit_client_balance'))
                    <button class="btn btn-primary btn-sm btn-smm" type="button" onclick="editPendingInvoice({{ $clients->id }});return false;">
                        <i class="fa fa-dollar" aria-hidden="true"></i> @lang('app.editPendingInvoice')
                    </button>
                @endif
            </div>
        </div>
        <div class="row alert alert-danger">
            <div class="col-lg-4 col-xs-12">
                @lang('app.cutService'):
            </div>

            <div id="client_pending_invoice_balance" class="col-lg-2 col-xs-12">
                {{ $serviceCutDate }}
            </div>

            @if($serviceCutDate)
                <div id="updateButton" class="col-lg-2 col-xs-12">
                    @if (\App\Http\Controllers\PermissionsController::hasAnyRole('access_clients_editar'))
                        <button class="btn btn-primary btn-sm btn-smm" type="button" onclick="editCortadoDate();return false;">
                            <i class="fa fa-dollar" aria-hidden="true"></i> @lang('app.editCotadoDate')
                        </button>
                    @endif
                </div>
            @endif

        </div>

        <form id="admin_customers_billing_settings_form" class="admin_customers_billing_settings_form form-horizontal" role="form" action="{{ route('billing.settings', ['client' => $clients->id]) }}" method="POST">
            {{ csrf_field() }}
            <div class="row">
                <div class="col-lg-6 col-md-9">
                    <div class="panel panel-default">
                        <div class="panel-heading">@lang('app.billingSettings')</div>
                        <div class="panel-body">
                            <fieldset>
                                <div class="form-group">
                                    <label class="control-label col-lg-7 col-md-3">
                                        @lang('app.billingDay')
                                    </label>

                                    <div class="col-lg-5 col-md-9">
                                        <select type="select" id="customers_billing_day" @if(!$perm->billing_setting_update) disabled @endif style="width: 100%;" original-value="1" force-send="0" class="select2 select2-hidden-accessible" name="CustomerBilling[date]" tabindex="-1" aria-hidden="true">
                                            @for ($i = 1; $i < 32; $i++)
                                            <option value="{{ $i }}" {{ $settings->billing_date == $i ? 'selected' : '' }}>{{ $i }}</option>
                                            @endfor
                                        </select>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="control-label col-lg-7 col-md-3">
                                        @lang('app.billingDue')
                                    </label>

                                    <div class="col-lg-5 col-md-9">
                                        <select type="select" id="customers_billing_due" @if(!$perm->billing_setting_update) disabled @endif style="width: 100%;" original-value="15" force-send="0" class="select2 select2-hidden-accessible" name="CustomerBilling[due_date]" tabindex="-1" aria-hidden="true"><option value="0">@lang('app.disabled')</option>
                                            @for ($i = 1; $i < 100; $i++)
                                            <option value="{{ $i }}" {{ $settings->billing_due_date == $i ? 'selected' : '' }}>{{ $i }}</option>
                                            @endfor
                                        </select>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="control-label col-lg-7 col-md-3">
                                        Promesa de pago
                                    </label>

                                    <div class="col-lg-5 col-md-9">
                                        <select type="select" id="customers_billing_grace_period" @if(!$perm->billing_setting_update) disabled @endif style="width: 100%;" original-value="0" force-send="0" class="select2 select2-hidden-accessible" name="CustomerBilling[grace_period]" tabindex="-1" aria-hidden="true">
                                            <option value="0" {{ $settings->billing_grace_period == 0 ? 'selected' : '' }}>0</option>
                                            @for ($i = 1; $i < 100; $i++)
                                            <option value="{{ $i }}" {{ $settings->billing_grace_period == $i ? 'selected' : '' }}>{{ $i }}</option>
                                            @endfor
                                        </select>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="control-label col-lg-7 col-md-3">
                                        {{ __('app.invoicePayType') }}
                                    </label>

                                    <div class="col-lg-5 col-md-9">
                                        <select type="select" id="customers_invoice_pay_type" style="width: 100%;" class="select2 select2-hidden-accessible" name="CustomerBilling[invoice_pay_type]">
                                            <option value="prepay" @if($settings->billing_invoice_pay_type == 'prepay') selected @endif>{{ __('app.prepay') }}</option>
                                            <option value="postpay" @if($settings->billing_invoice_pay_type == 'postpay') selected @endif>{{ __('app.postpay') }}</option>
                                        </select>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="control-label col-lg-7 col-md-3">
                                        @lang('app.createInvoices') (@lang('app.afterToCharge'))
                                    </label>

                                    <div class="col-lg-5 col-md-9">
                                        <div class="checkbox">
                                            <label>
                                                <input name="CustomerBilling[create_invoice]" @if(!$perm->billing_setting_update) disabled @endif value="1" id="billing_create_invoice" type="checkbox" class="ace" {{ $settings->billing_create_invoice ? 'checked' : '' }} />
                                                <span class="lbl"></span>
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <div class="form-group">
                                    <label class="control-label col-lg-7 col-md-3">
                                        @lang('app.autopayinvoicesfromaccountbalance')
                                    </label>

                                    <div class="col-lg-5 col-md-9">
                                        <div class="checkbox">
                                            <label>
                                                <input name="CustomerBilling[auto_pay_invoice]" @if(!$perm->billing_setting_update) disabled @endif value="1" id="billing_auto_pay_invoice" type="checkbox" class="ace" {{ $settings->billing_auto_pay_invoice ? 'checked' : '' }} />
                                                <span class="lbl"></span>
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-lg-12">

                                        <div class="margin-bottom-sm pull-right">
                                            <button class="btn btn-primary" type="button" data-toggle="collapse" data-target="#collapseBillingCalendar" aria-expanded="false" aria-controls="collapseBillingCalendar">
                                                <i class="fa fa-calendar-o" aria-hidden="true"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>

                                <div class="collapse" id="collapseBillingCalendar">
                                    <div class="col-lg-7 col-xs-7">
                                        <div id="billing_calendar" class="datepicker">

                                        </div>
                                    </div>
                                    <div class="col-lg-5 col-xs-5">
                                        <div class="alert alert-info">
                                            @lang('app.billingDay')
                                        </div>

                                        <div class="alert alert-warning">
                                            @lang('app.billingDue') (@lang('app.customerWillBeBlocked'))
                                        </div>

                                        <div class="alert alert-danger">
                                            @lang('app.endOfGracePeriod') (@lang('app.customerWillBeInactive'))
                                        </div>

                                    </div>

                                </div>

                            </fieldset>

                        </div>
                    </div>
                    <div class="panel panel-default">
                        <div class="panel-heading">Fecha de creación</div>
                        <div class="panel-body">
                            <h5>{{ $clients->created_at }}</h5>
                        </div>
                    </div>
                </div>

                <div class="col-lg-6 col-md-9">
                    @if($perm->billing_setting_update)
                    <div class="panel panel-default">
                        <div class="panel-heading">@lang('app.actions')</div>
                        <div class="panel-body">
                            <input type="button" value="To charge" id="customers_billing_overview_to_bill" class="btn btn-success btn-sm" data-target="#charge" data-toggle="modal" onclick="toCharge();return false;">


                            <input type="button" value="Cancel last charge" id="customers_billing_overview_cancel_to_bill_button" class="btn btn-warning btn-sm" data-target="#charge" data-toggle="modal" onclick="cancelCharge();return false;">
                        </div>
                    </div>
                    @endif
                    <div class="panel panel-default">
                        <div class="widget-box widget-color-blue2 collapsed">
                            <div class="widget-header" style="padding-top: 0;
                            padding-bottom: 0;
                            background: whitesmoke;
                            font-size: 7px !important;
                            color: #333;
                            font-weight: unset !important;">
                                <p class="widget-title" style="font-size: 12px;
                                color: #333333b3 !important;">@lang('app.additionalDetail')</p>
                                <div class="widget-toolbar">
                                    <a data-action="collapse" href="#"><i class="ace-icon fa fa-chevron-down"></i></a>
                                </div>
                            </div>
                            <div class="widget-body" style="display: none;">
                                <div class="widget-main">
                                    {{-- <form id="teplatecontent" action="" method="get"> --}}
                                        <textarea  id="editor_general">  {{ $settings->reminder_additional }}</textarea>
                                    {{-- </form> --}}

                                    <input type="hidden" style="width: 100%;" original-value="" force-send="0" class="form-control input-sm" autocomplete="nope"  name="reminder_additional"  id="reminder_additional" value="{{ $settings->reminder_additional }}">
                                </div>
                                <div class="row">
                                    <div class="col-lg-12">
                                        <a href="javascript:;" style="margin: 10px 15px;" class="btn btn-primary pull-right saveNotes">Save</a>
                                    </div>
                                </div>

                            </div>
                        </div>
                    </div>

                    <div class="panel panel-default">
                        <div class="widget-box widget-color-blue2 collapsed">
                            <div class="widget-header" style="padding-top: 0;
                            padding-bottom: 0;
                            background: whitesmoke;
                            font-size: 7px !important;
                            color: #333;
                            font-weight: unset !important;">
                                <p class="widget-title" style="font-size: 12px;
                                color: #333333b3 !important;">@lang('app.hardware')</p>
                                <div class="widget-toolbar">
                                    <a data-action="collapse" href="#"><i class="ace-icon fa fa-chevron-down"></i></a>
                                </div>
                            </div>
                            <div class="widget-body" style="display: none;">
                                <div class="widget-main">
                                    <div class="table-responsive">

                                        <table id="hardware-table" class="table table-bordered table-hover">
                                            {{--<div id="idrefre">--}}
                                            {{--<i class="ace-icon fa fa-refresh"></i>--}}
                                            {{--</div>--}}
                                            <thead>
                                            <tr>
                                                <th>Item ID</th>
                                                <th>@lang('app.product')</th>
                                                <th>@lang('app.barCode')</th>
                                                <th>@lang('app.status')</th>
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
            <div class="row">
                <div class="col-lg-12">
                    <input type="submit" value="Save" class="btn btn-primary pull-right">
                </div>
            </div>

            <nobr><input type="submit" value="Submit" style="width:-10px; height:-10px; visibility: hidden;"></nobr>
        </form>
    </div>
    <!--Modal info client-->
    <div class="modal fade bs-example-modal-md" tabindex="-1" role="dialog"
    aria-labelledby="myLargeModalLabel" id="addEditBalanceModal">
    <div class="modal-dialog modal-md" role="document">
        <div class="modal-content">



        </div>
    </div>
</div>
</div>

<script>
    $(function () {
        var table = '';
        renderDataTable();
    });


    function renderDataTable() {
        if ($.fn.DataTable.isDataTable('#hardware-table')) {
            $('#hardware-table').dataTable().fnClearTable();
            $('#hardware-table').dataTable().fnDestroy();
        }
        table = $('#hardware-table').DataTable({
            "oLanguage": {
                "sUrl": '{{ __('app.datatable') }}'
            },
            dom: "<'row'<'col-md-6'l><'col-md-6'Bf>><'row'<'col-sm-12'tr>><'row'<'col-sm-6'i><'col-sm-6'p>>",
            processing: true,
            autoWidth: false,
            serverSide: true,
            pageLength: '10',
            responsive: true,
            destroy: true,
            order: [
                '0', 'desc'
            ],
            buttons: [

            ],
            ajax: {
                "url": "{{ route('hardware.list', $clients->id) }}",
                "type": "POST",
                "cache": false,
            },
            columns: [
                {name: 'id', data: 'id'},
                {name: 'name', data: 'name'},
                {name: 'bar_code', data: 'bar_code'},
                {name: 'status', data: 'status'},
            ]
        });
    }

    $('#invoice_request_auto_next').datepicker({
        dateFormat: 'yy-mm-dd',
        showOtherMonths: true,
        selectOtherMonths: true,
        minDate: moment().format('YYYY-MM-DD'),
        changeMonth: true,
        changeYear: true,
    })

    $('#invoice_request_auto_next_addon').click(function(e) {
        $('#invoice_request_auto_next').datepicker('setDate', '')
    });


    function toCharge() {
        $.ajaxModal('#addEditModal', "{{ route('transactionsToBillView', $clients->id) }}");
    }


    function editCortadoDate() {
        $.ajaxModal('#addEditModal', "{{ route('client.editCortadoDate', $clients->id) }}");
    }

    function cancelCharge() {
        $.ajaxModal('#addEditModal', "{{ route('cancelChargeView', $clients->id) }}");
    }


    var day = $('#customers_billing_day').val();
    var due = $('#customers_billing_due').val();
    var grace_period = $('#customers_billing_grace_period').val();

    var billing_day = moment().date(day).startOf('day')
    var billing_due = billing_day.clone().add(due, 'days')
    var payment_due = billing_due.clone().add(grace_period, 'days')

    var day_1 = $('#reminder_day_1').val()
    var day_2 = $('#reminder_day_2').val()
    var day_3 = $('#reminder_day_3').val()

    var reminder_day_1 = billing_day.clone().add(day_1, 'days')
    var reminder_day_2 = reminder_day_1.clone().add(day_2, 'days')
    var reminder_day_3 = reminder_day_2.clone().add(day_3, 'days')

    $('#billing_calendar').datepicker({
        dateFormat: 'yy-mm-dd',
        showOtherMonths: true,
        selectOtherMonths: true,
        minDate: new Date(1900, 1, 1),
        changeMonth: true,
        changeYear: true,
        beforeShowDay: function (date) {
            if (moment(date).isSame(billing_day)) {
                return [true, 'alert alert-info', 'Día de facturación'];
            } else if (moment(date).isSame(billing_due)) {
                return [true, 'alert-warning', 'Vencimiento de facturación'];
            } else if (moment(date).isSame(payment_due)) {
                return [true, 'alert-danger', 'Fin del periodo de gracia'];
            } else
            return [true, '', ''];
        }
    });

    $('#billing_reminder_calendar_customer').datepicker({
        dateFormat: 'yy-mm-dd',
        showOtherMonths: true,
        selectOtherMonths: true,
        minDate: new Date(1900, 1, 1),
        changeMonth: true,
        changeYear: true,
        beforeShowDay: function (date) {
            if (moment(date).isSame(billing_day)) {
                return [true, 'alert alert-success', 'Día de facturación'];
            } else if (moment(date).isSame(reminder_day_1)) {
                return [true, 'alert-info', 'Recordatorio #1'];
            } else if (moment(date).isSame(reminder_day_2)) {
                return [true, 'alert-warning', 'Recordatorio #2'];
            } else if (moment(date).isSame(reminder_day_3)) {
                return [true, 'alert-danger', 'Recordatorio #3'];
            } else
            return [true, '', ''];
        }
    })

    function update_periods() {
        day = $('#customers_billing_day').val();
        due = $('#customers_billing_due').val();
        grace_period = $('#customers_billing_grace_period').val();

        billing_day = moment().date(day).startOf('day');
        billing_due = billing_day.clone().add(due, 'days');
        payment_due = billing_due.clone().add(grace_period, 'days');

        $('#billing_calendar').datepicker('refresh');
        update_periods_reminder();
    }

    $('#customers_billing_day').change(update_periods);
    $('#customers_billing_due').change(update_periods);
    $('#customers_billing_grace_period').change(update_periods);

    function update_periods_reminder() {
        day_1 = $('#reminder_day_1').val()
        day_2 = $('#reminder_day_2').val()
        day_3 = $('#reminder_day_3').val()

        reminder_day_1 = billing_day.clone().add(day_1, 'days')
        reminder_day_2 = reminder_day_1.clone().add(day_2, 'days')
        reminder_day_3 = reminder_day_2.clone().add(day_3, 'days')

        $('#billing_reminder_calendar_customer').datepicker('refresh');
    }

    $('#reminder_day_1').change(update_periods_reminder);
    $('#reminder_day_2').change(update_periods_reminder);
    $('#reminder_day_3').change(update_periods_reminder);

    $('#admin_customers_billing_settings_form').on('submit', function(e) {
        e.preventDefault();

        var form = $(this);
        $.easyAjax({
            type: form.attr('method'),
            url: form.attr('action'),
            data: form.serialize()
        })
    });

    function editBalance(id) {
        var url = '{{ route('client.edit-balance', ':id') }}';
        url = url.replace(':id', id);
        $.ajaxModal('#addEditBalanceModal', url);
    }

    function editPendingInvoice(id) {
        var url = '{{ route('client.edit-pending-invoice', ':id') }}';
        url = url.replace(':id', id);
        $.ajaxModal('#addEditBalanceModal', url);
    }

</script>

<script src="{{asset('assets/js/jquery.gritter.min.js')}}"></script>
<script src="{{asset('assets/js/tinymce/tinymce.min.js')}}"></script>
{{--<script src="{{asset('assets/js/plupload/js/jquery-ui.min.js')}}"></script>--}}
<script src="{{asset('assets/js/plupload/js/plupload.full.min.js')}}"></script>
<script src="{{asset('assets/js/plupload/js/jquery.ui.plupload/jquery.ui.plupload.min.js')}}"></script>
<script src="{{asset('assets/js/plupload/js/i18n/es.js')}}"></script>
<script src="{{asset('assets/js/rocket/visualEditor-core.js')}}"></script>

<script type="text/javascript">
$(document).ready(function(e) {
    tinymce.init({
        selector: "#editor_general",
        init_instance_callback: function (editor) {
            editor.on('PostProcess', function (e) {

              var contenido=e.content;
              $('#reminder_additional').val(e.content);
              if(contenido.length>0){

                // $("#admin_customers_billing_settings_form").submit();

            }
        });
        },
        inline_styles : true,
        imagetools_toolbar: 'rotateleft rotateright | flipv fliph | editimage imageoptions',
        plugins:"colorpicker bootstrap advlist lists link image anchor searchreplace fullscreen insertdatetime code media table paste textcolor imagetools",
        bootstrapConfig: {
                'imagesPath': '<?php echo URL::to("assets/imgeditor/imagenes"); ?>' // replace with your images folder path
            },
            toolbar1: "bootstrap",
            toolbar2: "insertfile undo redo | styleselect | bold italic | fontsizeselect | alignleft aligncenter alignright alignjustify | bullist numlist | forecolor backcolor | link image | hr | imagetools | inserttable | searchreplace | fullscreen",
            schema: "html5",
            valid_children : "+a[div]",
            language: "es"
        });

    $('.saveNotes').on('click', function(e) {
        e.preventDefault();
        var reminder_additional = tinymce.activeEditor.getContent();

        $.easyAjax({
            type: 'POST',
            url: '{{route('billing.saveNotes', $settings->id)}}',
            data: {reminder_additional:reminder_additional},
            container:'.widget-color-blue2'
        })

    });

});
</script>

