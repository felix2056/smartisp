@extends('layouts.cashdesk.master')

@section('title',$company)

@section('styles')

    <link rel="stylesheet" href="{{asset('assets/css/jquery.gritter.min.css')}}" />
    <link rel="stylesheet" href="{{asset('assets/plugins/select2/select2.css')}}" />
    <style>
        .card {
            border: 1px solid #c1c1c1;
        }
        .col-sm-12 {
            min-width: 100%;
        }
        .control-label {
            text-align: left !important;
        }
    </style>
@endsection

@section('content')
    <div class="main-content">
        <div class="main-content-inner">
            <div class="breadcrumbs" id="breadcrumbs">
                <ul class="breadcrumb">
                    <li>
                        <i class="ace-icon fa fa-home home-icon"></i>
                        <a href="#">@lang('app.desk')</a>
                    </li>
                    <li class="active">@lang('app.searchClient')</li>
                </ul>
            </div>
            <div class="page-content">
                <div class="row">
                    <div class="col-sm-12 col-md-12">
                        <div class="card pull-up left-border info_c">
                            <div class="card-content">
                                <div class="card-body">
                                    <div class="row">
                                        <div class="col-md-12">
                                            <h3>Search Customer</h3>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-sm-12">
                                            <div class="card ">
                                                <div class="card-content">
                                                    <div class="card-body">
                                                        <div class="row">
                                                            <div class="col-md-6 col-sm-12">
                                                                <form id="deposit" class="form-horizontal" role="form" method="POST">
                                                                    @csrf
                                                                    <fieldset>

                                                                        <div class="form-group">
                                                                            <label for="client" class="control-label col-sm-12 col-md-5">
                                                                                Client Name
                                                                            </label>

                                                                            <div class="col-md-7 col-sm-12">
                                                                                <select class="name-select2 form-control" id="name" name="name">
                                                                                    {{--<option></option>--}}
                                                                                </select>
                                                                            </div>
                                                                        </div>
                                                                        <div class="form-group">
                                                                            <label for="client" class="control-label col-sm-12 col-md-5">
                                                                                Invoice Number
                                                                            </label>

                                                                            <div class="col-md-7 col-sm-12">
                                                                                <select class="invoice-select2 form-control" name="invoice_number" id="invoice_number">
                                                                                    {{--<option></option>--}}
                                                                                </select>
                                                                            </div>
                                                                        </div>
                                                                        <div class="form-group">
                                                                            <label for="client" class="control-label col-sm-12 col-md-5">
                                                                                {{ __('app.email') }}
                                                                            </label>

                                                                            <div class="col-md-7 col-sm-12">
                                                                                <select class="email-select2 form-control" name="email" id="email">
                                                                                    {{--<option></option>--}}
                                                                                </select>
                                                                            </div>
                                                                        </div>
                                                                        <div class="form-group">
                                                                            <label for="client" class="control-label col-sm-12 col-md-5">
                                                                                 {{ __('app.number') }} DNI/CI
                                                                            </label>

                                                                            <div class="col-md-7 col-sm-12">
                                                                                <select class="dni-select2 form-control" name="dni" id="dni">
                                                                                    {{--<option></option>--}}
                                                                                </select>
                                                                            </div>
                                                                        </div>

                                                                        <div class="row">
                                                                            <div class="col-md-12">
                                                                                <button class="btn btn-sm btn-primary" onclick="search();return false;" style="float: right;">Search</button>
                                                                            </div>
                                                                        </div>

                                                                    </fieldset>
                                                                </form>
                                                            </div>
                                                            <div class="col-md-6 col-sm-12"></div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    <script src="{{asset('assets/js/jquery.gritter.min.js')}}"></script>

    <script src="{{asset('assets/plugins/select2/select2.min.js')}}"></script>
    <script>

        function msg(msg,type)
        {
            if(type=='success'){
                var clase = 'gritter-success';
                var tit = Lang.app.registered;
                var img = '{{ asset('assets/img/ok.png') }}';
                var stincky = false;

            }
            if(type=='error'){
                var clase = 'gritter-error';
                var tit = Lang.app.error;
                var img = '{{ asset('assets/img/error.png') }}';
                var stincky = false;

            }
            if(type=='debug'){
                var clase = 'gritter-error gritter-center';
                var tit = Lang.app.errorInternoDebugMode;
                var img = '{{ asset('assets/img/error.png') }}';
                var stincky = false;

            }
            if(type=='info'){
                var clase = 'gritter-info';
                var tit = Lang.app.information;
                var img = '{{ asset('assets/img/info.png') }}';
                var stincky = false;

            }

            $.gritter.add({
                // (string | mandatory) the heading of the notification
                title: tit,
                // (string | mandatory) the text inside the notification
                text: msg,
                image: img, //in Ace demo dist will be replaced by correct assets path
                sticky: stincky,
                class_name: clase
            });
        }
        $(document).ready(function() {
            $(".invoice-select2").select2({
                ajax: {
                    url: '{{ route('cashdesk.search-by-invoice') }}',
                    dataType: 'json',
                    delay: 250,
                    data: function(params) {
                        return {
                            q: params.term // search term
                        };
                    },
                    processResults: function(data, params) {
                        // parse the results into the format expected by Select2
                        // since we are using custom formatting functions we do not need to
                        // alter the remote JSON data, except to indicate that infinite
                        // scrolling can be used
                        var resData = [];
                        data.forEach(function(value) {
                            if (value.num_bill.indexOf(params.term) != -1)
                                resData.push(value)
                        })
                        return {
                            results: $.map(resData, function(item) {
                                return {
                                    text: item.num_bill,
                                    id: item.client_id
                                }
                            })
                        };
                    },
                    cache: false
                },
                minimumInputLength: 1
            })

            $(".name-select2").select2({
                ajax: {
                    url: "{{ route('cashdesk.search-by-client-name') }}",
                    dataType: 'json',
                    delay: 250,
                    data: function(params) {
                        return {
                            q: params.term // search term
                        };
                    },
                    processResults: function(data, params) {
                        // parse the results into the format expected by Select2
                        // since we are using custom formatting functions we do not need to
                        // alter the remote JSON data, except to indicate that infinite
                        // scrolling can be used
                        var resData = [];
                        data.forEach(function(value) {
                            if (value.name.indexOf(params.term) != -1)
                                resData.push(value)
                        })
                        return {
                            results: $.map(resData, function(item) {
                                return {
                                    text: item.name,
                                    id: item.id
                                }
                            })
                        };
                    },
                    cache: false
                },
                minimumInputLength: 1
            })

            $(".email-select2").select2({
                ajax: {
                    url: "{{ route('cashdesk.search-by-client-email') }}",
                    dataType: 'json',
                    delay: 250,
                    data: function(params) {
                        return {
                            q: params.term // search term
                        };
                    },
                    processResults: function(data, params) {
                        // parse the results into the format expected by Select2
                        // since we are using custom formatting functions we do not need to
                        // alter the remote JSON data, except to indicate that infinite
                        // scrolling can be used
                        var resData = [];
                        data.forEach(function(value) {
                            if (value.email.indexOf(params.term) != -1)
                                resData.push(value)
                        })
                        return {
                            results: $.map(resData, function(item) {
                                return {
                                    text: item.email,
                                    id: item.id
                                }
                            })
                        };
                    },
                    cache: false
                },
                minimumInputLength: 1
            })

            $(".dni-select2").select2({
                ajax: {
                    url: "{{ route('cashdesk.search-by-client-dni') }}",
                    dataType: 'json',
                    delay: 250,
                    data: function(params) {
                        return {
                            q: params.term // search term
                        };
                    },
                    processResults: function(data, params) {
                        // parse the results into the format expected by Select2
                        // since we are using custom formatting functions we do not need to
                        // alter the remote JSON data, except to indicate that infinite
                        // scrolling can be used
                        var resData = [];
                        data.forEach(function(value) {
                            if (value.dni.indexOf(params.term) != -1)
                                resData.push(value)
                        })
                        return {
                            results: $.map(resData, function(item) {
                                return {
                                    text: item.dni,
                                    id: item.id
                                }
                            })
                        };
                    },
                    cache: false
                },
                minimumInputLength: 1
            })
        });
        // function search() {
        //
        // }
        const search = () => {
            var client = $('#name').val();
            var invoice = $('#invoice_number').val();
            var email = $('#email').val();
            var dni = $('#dni').val();

            if(!client && !invoice && !email && !dni) {
                msg('Please choose client first by client name or invoice number or email or DNI/CI', 'error')
                return false
            }

            var url = '{{ route('cashdesk.search-by-client-data', ':id') }}';

            if(client) {
                url = url.replace(':id', client);
            }

            if(invoice) {
                url = url.replace(':id', invoice);
            }

            if(email) {
                url = url.replace(':id', email);
            }

            if(dni) {
                url = url.replace(':id', dni);
            }


            window.location.href = url;
        }
    </script>
@endsection
