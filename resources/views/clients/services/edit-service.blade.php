<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal"><span
                aria-hidden="true">&times;</span><span class="sr-only">{{ __('app.close') }}</span>
    </button>
    <h4 class="modal-title" id="myModalLabel"><i
                class="fa fa-user-plus"></i>
        {{ __('app.edit') }} Internet Service</h4>
</div>
<div class="modal-body" id="winnew">
    <form class="form-horizontal" role="form" id="add-services">

        <div class="form-group">
            <label for="edit_slcrouter"
                   class="col-sm-3 control-label">{{ __('app.router') }}</label>
            <div class="col-sm-6">
                <select class="form-control" name="router"
                        id="edit_slcrouter">
                </select>
            </div>
        </div>

        <div class="form-group" id="typeauth">
            <label for="slauth" class="col-sm-3 control-label">{{ __('app.logIn') }} (hotspot)</label>
            <div class="col-sm-6">
                <select class="form-control" name="auth"
                        id="slauth">
                    <option value="userpass" selected>{{ __('app.userPassword') }}
                    </option>
                    <option value="mac">MAC - {{__('app.automaticLogin')}}
                    </option>
                    <option value="binding">IP {{__('app.bindingBypassed')}}
                    </option>
                </select>
            </div>
        </div>
        <div class="form-group" id="edtypeauth">
            <label for="slauth" class="col-sm-3 control-label">{{ __('app.logIn') }} (hotspot)</label>
            <div class="col-sm-6">
                <select class="form-control" name="edit_auth"
                        id="edit_slauth">
                    <option value="userpass" selected>{{ __('app.userPassword') }}
                    </option>
                    <option value="mac">MAC - {{ __('app.automaticLogin') }}
                    </option>
                    <option value="binding">IP {{ __('app.bindingBypassed') }}
                    </option>
                </select>
            </div>
        </div>
        <div class="form-group" id="showus">
            <label for="edit_user"
                   class="col-sm-3 control-label"
                   id="edtuser"></label>
            <div class="col-sm-5">
                <input type="text" name="edit_user"
                       class="form-control" autocomplete="off"
                       id="edit_user" maxlength="40" value="{{ $service->user_hot }}">
            </div>
            <div class="col-sm-2">

                <button type="button"
                        class="btn btn-sm btn-primary"
                        id="edgenuser" title="Generar"><i
                            class="fa fa-bolt"></i>
                </button>
            </div>
        </div>

        <div class="form-group" id="showpa">
            <label for="edit_pass"
                   class="col-sm-3 control-label"
                   id="edtpass"></label>
            <div class="col-sm-4">
                <input type="password" name="edit_pass"
                       class="form-control" autocomplete="off"
                       id="edit_pass" maxlength="40">
            </div>

            <div class="col-sm-1">
                <button type="button"
                        class="btn btn-sm btn-success"
                        id="edgenpass" title="Generar"><i
                            class="fa fa-bolt"></i>
                </button>
            </div>
            <div class="col-sm-1">
                @php
                    $encrypt = new \App\libraries\Pencrypt();
                    $password = $service->pass_hot;
                    $password = $encrypt->decode($password);
                @endphp
                <button type="button" onclick="togglePassword('{{ $password }}')" class="btn btn-sm btn-success">Show</button>
            </div>
            <div class="col-sm-3">
                <div class="checkbox">
                    <label>
                        <input type="checkbox" id="edshowp2"
                               class="ace">
                        <span class="lbl"> {{ __('app.seePassword') }}</span>
                    </label>
                </div>
            </div>

        </div>

        <div class="form-group" id="snet">
            <label for="ip" class="col-sm-3 control-label">IP
                {{ __('app.client') }}</label>
            <div class="col-sm-5">
                <div class="input-group">
                     <span class="input-group-addon"><i class="fa fa-search" aria-hidden="true"></i>
                     </span>
                    <input type="text" class="form-control"
                           autocomplete="off" name="ip" id="ip"
                           maxlength="17">
                </div>
            </div>
        </div>

        <div class="form-group">
            <label for="mac" class="col-sm-3 control-label">{{__('app.direction')}}
                MAC</label>
            <div class="col-sm-6">
                <input type="text" name="mac"
                       class="form-control" autocomplete="off"
                       id="macAddress" maxlength="17"
                       placeholder="00:00:00:00:00:00" value="{{ $service->mac }}">
            </div>
        </div>

        <div class="form-group">
            <label for="slcplan" class="col-sm-3 control-label">{{ __('app.typeOfBilling') }}</label>
            <div class="col-sm-6">
                <select class="form-control" name="billing_type" id="billing_type">
                    <option value="recurring">{{ __('app.recurringPayment') }}</option>
                </select>
            </div>
        </div>

        @if($service->send_invoice == 1)
            <div class="form-group" id="send_sri" style="display: none;">
                <label for="send_invoice_label" class="col-sm-3 control-label">Emitir Factura </label>
                
                
                <div class="col-sm-6">
                    <select class="form-control" name="send_invoice" id="send_invoice">
                        <option value="1" selected>Activado</option>
                        <option value="0">Desactivado</option>
                    </select>
                </div>
            </div>
        @endif
        @if($service->send_invoice  == 0)
            <div class="form-group" id="send_sri" style="display: none;">
                    <label for="send_invoice_label" class="col-sm-3 control-label">Emitir Factura </label>
                    
                    
                    <div class="col-sm-6">
                        <select class="form-control" name="send_invoice" id="send_invoice">
                            <option value="1">Activado</option>
                            <option value="0" selected>Desactivado</option>
                        </select>
                    </div>
                </div>
        @endif


        <div class="form-group" id="pl">
            <label for="edit_slcplan" class="col-sm-3 control-label">{{__('app.internetPlan')}}</label>
            <div class="col-sm-6">
                <select class="form-control" name="plan"
                        id="edit_slcplan">
                </select>
            </div>
        </div>

        <div class="form-group" id="edshowedprofiles">
            <label for="edit_slcprofile"
                   class="col-sm-3 control-label"
                   id="EditTextprofile"></label>
            <div class="col-sm-6">
                <select class="form-control" name="editprofile"
                        id="edit_slcprofile">
                </select>
            </div>
        </div>

        <div class="form-group">
            <label for="id-date-picker-1"
                   class="col-sm-3 control-label">{{ __('app.dateOfAddmission') }}</label>
            <?php

            $exp = strtotime('+1 month', strtotime(date('Y-m-d')));
            $exp = date('d-m-Y', $exp);
            ?>
            <div class="col-sm-6">
                <div class="input-group">
                    <input class="form-control date-picker"
                           maxlength="8"
                           value="{{ $service->date_in->format('d-m-Y') }}" type="text"
                           data-date-format="dd-mm-yyyy"
                           id="date_in" name="date_in" readonly
                           required/>
                    <span class="input-group-addon">
                <i class="fa fa-calendar bigger-110"></i>
            </span>
                </div>
            </div>
        </div>
        <div class="form-group">
            <label for="id-date-picker-1"
                   class="col-sm-3 control-label">{{ __('app.nextPaymentDate') }}</label>
            <div class="col-sm-6">
                <div class="input-group">
                    <input class="form-control date-picker"
                           maxlength="8" value="{{ $cortadoDate }}"
                           type="text"
                           data-date-format="dd-mm-yyyy"
                           id="edit_date_pay" name="edit_date_pay"
                           readonly required/>
                    <span class="input-group-addon">
              <i class="fa fa-calendar bigger-110"></i>
          </span>
                </div>
            </div>
        </div>
    </form>
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-default"
            data-dismiss="modal">{{ __('app.close') }}
    </button>
    <button onclick="updateClientService();return false;" type="button"
            class="btn btn-primary addbtnClientService"
            data-loading-text="@lang('app.saving')..."><i
                class="fa fa-floppy-o"></i>
        {{ __('app.save') }}
    </button>
</div>
<script>
    $(function () {
        $('#snet,#showus,#showpa,#typeauth,#showprofiles').hide();
        $('#ro a[href="#dclient"]').tab('show');
        //fin de verificar
        $('#winnew').waiting({ fixed: true });
        $('#edit_slcplan,#edit_slcrouter').empty();
        //get routers

        $.ajax({
            "url": "{{ route('admin.client.router') }}",
            "type": "POST",
            "data": {},
            "dataType": "json",
            'error': function(xhr, ajaxOptions, thrownError) {
                debug(xhr, thrownError);
            }
        }).done(function(data) {
            if (data.msg == 'norouters') {
                $('#add').modal('toggle');
                msg('No se encontraron <b>routers</b>, debe agregar al menos un router.', 'system');
            } else {
                $('#edit_slcrouter').append($('<option>').text('Seleccione Router').attr('value', '').prop('selected', true));

                $ro = $.each(data, function(i, val) {
                    $('#edit_slcrouter').append($('<option>').text(val['name']).attr('value', val.id));
                });

                $.when($ro).done(function() {
                    var myText = '{{ $service->router_id }}';
                    $("#edit_slcrouter").children().filter(function() {
                        return $(this).val() == myText;
                    }).prop('selected', true);
                    //obtenemos las ip redes del router
                    getNet(myText, '#snet');

                });

                $('#winnew').waiting('done');
            }

        });


        $.ajax({
            "url": "{{ route('admin.client.factel') }}",
            "type": "POST",
            "data": {},
            "dataType": "json",
            'error': function(xhr, ajaxOptions, thrownError) {
                debug(xhr, thrownError);
            }
        }).done(function(data) {
            console.log("llega factel")
            console.log(data.factelStatus)
            if(data.factelStatus == 0){
                $('#send_sri').show();
            }
        });

        $('.date-picker').datepicker({
            language: 'es',
            autoclose: true,
            todayHighlight: true
            //startView: 'year',
        });

        $('#edit_slcplan').empty();


        switch ('{{ $type->type_control }}') {
            case 'sq':
                $('#showus,#showpa,#edshowedprofiles,#edtypeauth').hide('fast');
                break;
            case 'st':
                $('#showus,#showpa,#edshowedprofiles,#edtypeauth').hide('fast');
                break;
            case 'no':
                $('#showus,#showpa,#edtypeauth').hide();
                break;
            case 'dl':
                $('#showus,#showpa,#edtypeauth').hide();
                break;
            case 'pc':
                $('#showus,#showpa,#edtypeauth').hide();
                break;
            case 'ho':

                $('#edit_slcprofile').empty();

                $.ajax({
                    "url": baseUrl+"/client/getclient/listprofileshotspot",
                    "type": "POST",
                    "data": { "id": '{{ $service->router_id }}' },
                    "dataType": "json",
                    'error': function(xhr, ajaxOptions, thrownError) {
                        debug(xhr, thrownError);
                    }
                }).done(function(data) {

                    if (data.success == true) {


                        $('#edit_slcprofile').append($('<option>').text('Crear perfil').attr('value', '*0'));

                        $pr = $.each(data.profiles, function(i, val) {
                            $('#edit_slcprofile').append($('<option>').text(val['name']).attr('value', val.name));
                        });
                        //mostramos los perfiles del router y selecionamos el perfil


                        $.when($pr).done(function() {

                            var prof = '{{ $service->plan->name }}'; //$('#edit_edit_slcplan').find("option:selected").text();

                            var er = $("#edit_slcprofile").children().filter(function() {
                                return $(this).val() == prof;
                            }).prop('selected', true);

                            if (er.length == 0) { // no encontro el perfil selecionamos el por default

                                $("#edit_slcprofile").children().filter(function() {
                                    return $(this).val() == '*0';
                                }).prop('selected', true);
                            }

                        });


                        //
                        // $("#edit_slauth").children().filter(function() {
                        //     return $(this).val() == type_auth;
                        // }).prop('selected', true);

                        $('#edtypeauth').show('fast');
                        $('#showus,#showpa').show('fast');

                        $('#EditTextprofile').text('Perfil Hotspot');
                        $('#edtuser').text('Usuario (hotspot)');
                        $('#edtpass').text('Contraseña (hotspot)');
                        $('#edshowedprofiles').show('fast');


                    } else {
                        $('#edit').modal('toggle');
                        msg('No se puede conectar al router.', 'system');
                    }

                }); //end ajax

                break;
            case 'ha':

                $('#edshowedprofiles').hide();

                $("#edit_slauth").children().filter(function() {
                    return $(this).val() == '{{ $service->typeauth }}';
                }).prop('selected', true);

                $('#edtuser').text('Usuario (hotspot)');
                $('#edtpass').text('Contraseña (hotspot)');

                if ('{{ $service->typeauth }}' == 'userpass') {

                    $('#showus,#showpa,#edtypeauth').show('fast');

                } else if ('{{ $service->typeauth }}' == 'mac') {

                    $('#edtypeauth').show('fast');
                    $('#showus,#showpa').hide();

                } else {

                    $('#showus,#edtypeauth').show('fast');
                    $('#showpa').hide('fast');
                }


                break;
            case 'pp':

                $('#edit_slcprofile').empty();
                $.ajax({
                    "url": baseUrl+"/client/getclient/listprofilesppp",
                    "type": "POST",
                    "data": { "id": '{{$service->router_id}}' },
                    "dataType": "json",
                    'error': function(xhr, ajaxOptions, thrownError) {
                        debug(xhr, thrownError);
                    }
                }).done(function(data) {
                    if (data.success == true) {


                        $('#edit_slcprofile').append($('<option>').text('Crear perfil').attr('value', '*0'));

                        $pr = $.each(data.profiles, function(i, val) {
                            $('#edit_slcprofile').append($('<option>').text(val['name']).attr('value', val.name));
                        });
                        //mostramos los perfiles del router y selecionamos el perfil

                        $.when($pr).done(function() {

                            var prof = '{{ $service->plan->name }}'; //$('#edit_edit_slcplan').find("option:selected").text();

                            var er = $("#edit_slcprofile").children().filter(function() {
                                return $(this).val() == prof;
                            }).prop('selected', true);

                            if (er.length == 0) { // no encontro el perfil selecionamos el por default

                                $("#edit_slcprofile").children().filter(function() {
                                    return $(this).val() == '*0';
                                }).prop('selected', true);
                            }

                        });

                        $('#EditTextprofile').text('Perfil PPPoE');
                        $('#edtuser').text('Usuario (ppp-secrets)');
                        $('#edtpass').text('Contraseña (ppp-secrets)');
                        $('#showus,#showpa,#edshowedprofiles').show('fast');
                        $('#edtypeauth').hide();

                    } else {
                        $('#edit').modal('toggle');
                        msg('No se puede conectar al router.', 'system');
                    }

                }); //end ajax

                break;
            case 'pa':

                $('#edtuser').text('Usuario (ppp-secrets)');
                $('#edtpass').text('Contraseña (ppp-secrets)');
                $('#showus,#showpa').show('fast');
                $('#edtypeauth,#edshowedprofiles').hide();


                break;

            case 'ps':
            case 'pt':

                $('#edtuser').text('Usuario (ppp-secrets)');
                $('#edtpass').text('Contraseña (ppp-secrets)');
                $('#showus,#showpa').show('fast');
                $('#edtypeauth,#edshowedprofiles').hide();


                break;

            case 'ra':

                $('#edtuser').text('Usuario (ppp-secrets)');
                $('#edtpass').text('Contraseña (ppp-secrets)');
                $('#showus,#showpa').show('fast');
                $('#edtypeauth,#edshowedprofiles').hide();

            case 'rp':

                $('#edtuser').text('Usuario (ppp-secrets)');
                $('#edtpass').text('Contraseña (ppp-secrets)');
                $('#showus,#showpa').show('fast');
                $('#edtypeauth,#edshowedprofiles').hide();

            case 'rr':

                $('#edtuser').text('Usuario (ppp-secrets)');
                $('#edtpass').text('Contraseña (ppp-secrets)');
                $('#showus,#showpa').show('fast');
                $('#edtypeauth,#edshowedprofiles').hide();


                break;

        }//end switch

        //get plan
        $.ajax({
            "url": baseUrl+"/client/getclient/plans",
            "type": "POST",
            "data": {},
            "dataType": "json",
            'error': function(xhr, ajaxOptions, thrownError) {
                debug(xhr, thrownError);
            }
        }).done(function(data) {
            if (data.msg == 'noplans')
                msg('No se encontraron <b>planes</b>, debe agregar al menos un plan.', 'system');
            else {
                var $sp = $.each(data, function(i, val) {
                    $('#edit_slcplan').append($('<option>').text(val['name']).attr('value', val.id));
                });
                $.when($sp).done(function() {
                    var myText = '{{ $service->plan_id }}';
                    $("#edit_slcplan").children().filter(function() {
                        return $(this).val() == myText;
                    }).prop('selected', true);
                });
            }
        });

        $('#ip').typeahead({
            onSelect: function(item) {
                var id = item.value;
            },
            ajax: {
                url: baseUrl+"/network/getnetwork/ip",
                method: "POST",
                preDispatch: function(query) {
                    return {
                        search: query,
                        rid: $('#edit_slcrouter').val()
                    }
                }
            },

            scrollBar: true,
            showAutocompleteOnFocus: true
        });
    })

    //funcion para obtener las IP/Redes
    function getNet(sl, sel) {
        $.ajax({
            "url": "{{ url('router/getrouter/ipnet') }}",
            "type": "POST",
            "data": { "id": sl },
            "dataType": "json",
            'error': function(xhr, ajaxOptions, thrownError) {
                debug(xhr, thrownError);
            }
        }).done(function(data) {

            if (data.net == 'notfound') {
                msg('No se encontraron ip/redes para este router, debe agregar al menos una IP/Red, ingrese a <b>routers</b> opción editar "icono del lápiz" posterior a la pestaña ip/redes.', 'error');
                $(sel).hide('fast');
            } else if (data.net == 'full') {
                msg('No se encontraron ip disponibles para este router, debe agregar una nueva IP/Red, ingrese a <b>Gestión de red - IP redes</b>', 'error');
                $(sel).hide('fast');
            } else {
                $('#ip').val('{{ $service->ip }}');
                $(sel).show('fast');
            }
        });
    }
    ///// funcion de depuracion
    function debug(xhr, thrownError) {
        $.ajax({
            "url": baseUrl+"/config/getconfig/debug",
            "type": "GET",
            "data": {},
            "dataType": "json"
        }).done(function(deb) {

            if (deb.debug == '1')
                msg('Error ' + xhr.status + ' ' + thrownError + ' ' + xhr.responseText, 'debug');
            else
                alert(Lang.messages.aninternalerrorhasoccurredformoredetailtalktothedebugmode);
        });
    }

    function findprofile(selector, selector2) {

        var myText = $(selector + ' option:selected').text();
        var r = $(selector2).children().filter(function() {
            return $(this).val() == myText;
        }).prop('selected', true);

        if (r.length == 0) {
            $(selector2).children().filter(function() {
                return $(this).val() == '*0';
            }).prop('selected', true);
        }
    }

    $(document).on("change", "#edit_slcplan", function() {
        findprofile('#edit_slcplan', '#edit_slcprofile');
    });

    //funcion para mostrar campos user name y pass en editar cliente
    $(document).on("change", "#edit_slcrouter", function() {
        var idr = $('#edit_slcrouter').val();
        if (idr != 'none') {
            $.ajax({
                "url": baseUrl+"/client/getclient/control",
                "type": "POST",
                "data": { "id": idr },
                "dataType": "json",
                'error': function(xhr, ajaxOptions, thrownError) {
                    debug(xhr, thrownError);
                }
            }).done(function(data) {
                if (data.type == 'ho' || data.type == 'ha') {
                    $('#EditTextprofile').text('Perfil Hotspot');
                    $('#edtuser').text('Usuario (hotspot)');
                    $('#edtpass').text('Contraseña (hotspot)');
                    //reselecionamos el select de autenticacion

                    $("#edit_slauth").children().filter(function() {
                        return $(this).val() == "userpass";
                    }).prop('selected', true);

                    $('#showus,#showpa,#edtypeauth').show('fast');
                } else if (data.type == 'no') {
                    $('#showus,#showpa,#edtypeauth').hide('fast');
                } else if (data.type == 'pc') {
                    $('#showus,#showpa,#edtypeauth').hide('fast');
                } else if (data.type == 'pp' || data.type == 'pa'  || data.type=='ps' || data.type=='pt') {

                    $('#EditTextprofile').text('Perfil PPPoE');
                    $('#edtuser').text('Usuario (ppp-secrets)');
                    $('#edtpass').text('Contraseña (ppp-secrets)');

                    $('#edtypeauth').hide();
                    $('#showus,#showpa').show('fast');

                } else
                    $('#showus,#showpa,#edtypeauth').hide('fast');
            });
        }
    });



    //control de mac address
    var macAddress = document.getElementById("macAddress");
    var macEditAddress = document.getElementById("macedAddress");

    function formatMAC(e) {
        var r = /([a-f0-9]{2})([a-f0-9]{2})/i,
            str = e.target.value.replace(/[^a-f0-9]/ig, "");
        while (r.test(str)) {
            str = str.replace(r, '$1' + ':' + '$2');
        }
        e.target.value = str.slice(0, 17);
    };

    if (macAddress !== null) macAddress.addEventListener("keyup", formatMAC, false);
    if (macEditAddress !== null) macEditAddress.addEventListener("keyup", formatMAC, false);

    //generar password para portal cliente
    $('#genporpass').pGenerator({

        'bind': 'click', // Bind an event to #myLink which generate a new password when raised;
        'passwordElement': '#pass', // Selector for the form input which will contain the new generated password;
        //'displayElement': '#my-display-element', // Selector which will display the new generated password;
        'passwordLength': 6, // Length of the generated password.
        'uppercase': false, // Password will contain uppercase letters;
        'lowercase': true, // Password will contain lowercase letters;
        'numbers': true, // Password will contain numerical characters;
        'specialChars': false, // Password will contain numerical characters;
    });

    //generar password para cliente hotspot
    $('#genpass').pGenerator({

        'bind': 'click',
        'passwordElement': '#pass_hot',
        'passwordLength': 6,
        'uppercase': true,
        'lowercase': true,
        'numbers': true,
        'specialChars': false,
    });

    //generar usuario para cliente hotspot
    $('#genuser').pGenerator({

        'bind': 'click',
        'passwordElement': '#user_hot',
        'passwordLength': 6,
        'uppercase': true,
        'lowercase': true,
        'numbers': false,
        'specialChars': false,
    });

    //edit functions //
    //generar password para portal cliente
    $('#edgenporpass').pGenerator({

        'bind': 'click',
        'passwordElement': '#edit_pass2',
        'passwordLength': 6,
        'uppercase': false,
        'lowercase': true,
        'numbers': true,
        'specialChars': false,
    });

    //generar password para cliente hotspot
    $('#edgenpass').pGenerator({

        'bind': 'click',
        'passwordElement': '#edit_pass',
        'passwordLength': 6,
        'uppercase': false,
        'lowercase': true,
        'numbers': true,
        'specialChars': false,
    });

    //generar usuario para cliente hotspot
    $('#edgenuser').pGenerator({

        'bind': 'click',
        'passwordElement': '#edit_user',
        'passwordLength': 6,
        'uppercase': true,
        'lowercase': true,
        'numbers': false,
        'specialChars': false,
    });

    function updateClientService() {
        $.easyAjax({
            type: 'POST',
            url: "{{ route('billing.services.update', [$service->id]) }}",
            container: "#add-services",
            data: $('#add-services').serialize(),
            success: function(response) {
                if(response[0].msg == 'success') {
                    $('#addEditModal').modal('hide');
                    serviceTable.ajax.reload();
                }
            }
        });
    }

    (function($) {
        $.toggleShowPassword = function(options) {
            var settings = $.extend({
                field: "#password",
                control: "#toggle_show_password",
            }, options);

            var control = $(settings.control);
            var field = $(settings.field)

            control.bind('click', function() {
                if (control.is(':checked')) {
                    field.attr('type', 'text');
                } else {
                    field.attr('type', 'password');
                }
            })
        };
    }(jQuery));

    var showPassword = false;
    function togglePassword(password) {
        showPassword = !showPassword;
        if(showPassword) {
            $('#edit_pass').val(password);
        } else {
            $('#edit_pass').val('');
        }
    }
    //Here how to call above plugin from everywhere in your application document body
    $.toggleShowPassword({
        field: '#edit_pass',
        control: '#edshowp2'
    });
</script>
