<style type="text/css">
    #idrefre {
        top: 85px !important;
        cursor: pointer;
        z-index: 99999;
    }

    #idrefre:hover {
        background: #000;
    }

    #document-table_wrapper .dt-buttons {
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
                                {{--@php--}}
                                    {{--use App\Http\Controllers\PermissionsController;--}}
                                {{--@endphp--}}
                                {{--@if(PermissionsController::hasAnyRole('pagos_nuevo'))--}}
                                    <div class="row">
                                        <div class="col-md-12">
                                            <button type="button" class="btn btn-sm btn-success m-l-10" data-toggle="modal"
                                                    onclick="addDocument();return false;"><i class="icon-plus"></i> Add Document
                                            </button>
                                            <button type="button" class="btn btn-sm btn-success" data-toggle="modal"
                                                    onclick="addContract();return false;"><i class="icon-plus"></i> Add Contract
                                            </button>
                                        </div>
                                    </div>
                                {{--@endif--}}


                                <table id="document-table" class="table table-bordered table-hover">
                                    {{--<div id="idrefre">--}}
                                        {{--<i class="ace-icon fa fa-refresh"></i>--}}
                                    {{--</div>--}}
                                    <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>@lang('app.addedBy')</th>
                                        <th>@lang('app.source')</th>
                                        <th>@lang('app.title')</th>
                                        <th>@lang('app.date')</th>
                                        <th>@lang('app.description')</th>
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
        if ($.fn.DataTable.isDataTable('#document-table')) {
            $('#document-table').dataTable().fnClearTable();
            $('#document-table').dataTable().fnDestroy();
        }
        table = $('#document-table').DataTable({
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
                "url": "{{ route('documents.list', $clients->id) }}",
                "type": "POST",
                "cache": false,
            },
            columns: [
                {name: 'id', data: 'id'},
                {name: 'users.name', data: 'name'},
                {name: 'source', data: 'source'},
                {name: 'title', data: 'title'},
                {name: 'created_at', data: 'created_at'},
                {name: 'description', data: 'description'},
                {name: 'action', data: 'action', sortable: false, searchable: false},
            ]
        });
    }

    function addDocument() {
        var url = '{{route('documents.create', $clients->id)}}';

        $.ajaxModal('#addEditModal', url);
    }

    function addContract() {
        var url = '{{route('contracts.create', $clients->id)}}';

        $.ajaxModal('#addEditModal', url);
    }

    function editContract(id) {
        var url = '{{route('contracts.edit', ':id')}}';
        url = url.replace(':id', id) + '?client_id=' + '{{$clients->id}}';

        $.ajaxModal('#addEditModal', url);
    }

    function addUpdateDocument(id) {
        if(typeof id != "undefined") {
            var url = '{{ route('documents.update', ':id') }}';
            url = url.replace(':id', id);
        } else {
            var url = '{{ route('documents.store') }}';
        }

        $.easyAjax({
            type: 'POST',
            url: url,
            container: '#add_edit_document',
            file: true,
            success: function(res) {
                if(res.status == 'success') {
                    $('#addEditModal').modal('hide');
                    table.draw();
                }
            }
        });
    }

    function addUpdateContract(id) {
        if(typeof id != "undefined") {
            var url = '{{ route('contracts.update', ':id') }}';
            url = url.replace(':id', id);
        } else {
            var url = '{{ route('contracts.store') }}';
        }

        var contract_content = tinymce.activeEditor.getContent({format : 'html'});
        var title = $('#title').val();
        var description = $('#description').val();
        var templates = $('#templates').val();
        var client_id = $('#contractClientID').val();

        var visible_to_client = 0;

        if($('#visible_to_client').is(':checked')) {
            visible_to_client = $('#visible_to_client').val();

        }

        let data = {
            contract_content: contract_content,
            title: title,
            client_id: client_id,
            description: description,
            templates: templates,
            visible_to_client: visible_to_client,
        }

        $.easyAjax({
            type: 'POST',
            url: url,
            container: '#add_edit_document',
            data: data,
            success: function(res) {
                if(res.status == 'success') {
                    $('#addEditModal').modal('hide');
                    table.draw();
                }
            }
        });
    }

    function editDocument(id) {
        var url = '{{route('documents.edit', ':id')}}';
        url = url.replace(':id', id) + '?client_id=' + '{{$clients->id}}';

        $.ajaxModal('#addEditModal', url);
    }

    function deleteDocument(id) {

        bootbox.confirm('{{ __('messages.Areyousureyouwanttodeletethedocument') }}', function (result) {
            if (result) {
                var url = '{{route('documents.delete', ':id')}}';
                url = url.replace(':id', id);

                $.easyAjax({
                    type: 'POST',
                    url: url,
                    container: "#document-table",
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
