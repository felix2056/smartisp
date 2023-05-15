<div class="modal-header">
    <button type="button" class="close" data-dismiss="modal"><span
            aria-hidden="true">&times;</span><span class="sr-only">{{ __('app.close') }}</span>
    </button>
    <h4 class="modal-title" id="myModalLabel"><i
            class="fa fa-user-plus"></i>
        {{ __('app.add') }} {{ __('app.new') }} Internet Service</h4>
</div>
<div class="modal-body" id="winnew">
    <form class="form-horizontal" role="form" id="add-services">

        <div class="form-group">
            <label for="slcrouter"
                   class="col-sm-3 control-label">{{ __('app.router') }}</label>
            <div class="col-sm-9">
                <select class="form-control" name="router"
                        id="slcrouter">
                </select>
            </div>
        </div>

        <div class="form-group" id="typeauth">
            <label for="slauth" class="col-sm-3 control-label">{{ __('app.logIn') }} (hotspot)</label>
            <div class="col-sm-9">
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

        <div class="form-group" id="showuser">
            <label for="user" class="col-sm-3 control-label"
                   id="tuser"></label>
            <div class="col-sm-5">
                <input type="text" name="user_hot"
                       class="form-control" autocomplete="off"
                       id="user_hot" maxlength="40">
            </div>

            <div class="col-sm-2">

                <button type="button"
                        class="btn btn-sm btn-primary"
                        id="genuser" title="Generar"><i
                        class="fa fa-bolt"></i>
                </button>
            </div>

        </div>

        <div class="form-group" id="showpass">
            <label for="pass" class="col-sm-3 control-label"
                   id="tpass"></label>
            <div class="col-sm-5">
                <input type="password" name="pass_hot"
                       class="form-control" autocomplete="off"
                       id="pass_hot" maxlength="40">
            </div>

            <div class="col-sm-1">
                <button type="button"
                        class="btn btn-sm btn-success"
                        id="genpass" title="Generar"><i
                        class="fa fa-bolt"></i>
                </button>
            </div>
            <div class="col-sm-3">
                <div class="checkbox">
                    <label>
                        <input type="checkbox" id="showp2"
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
            <div class="col-sm-9">
                <input type="text" name="mac"
                       class="form-control" autocomplete="off"
                       id="macAddress" maxlength="17"
                       placeholder="00:00:00:00:00:00">
            </div>
        </div>

        <div class="form-group">
            <label for="edit_slcplan" class="col-sm-3 control-label">{{ __('app.typeOfBilling') }}</label>
            <div class="col-sm-9">
                <select class="form-control" name="billing_type" id="billing_type">
                    <option value="recurring">{{ __('app.recurringPayment') }}</option>
                </select>
            </div>
        </div>



        <div class="form-group" id="send_sri" style="display: none;">
            <label for="send_invoice_label" class="col-sm-3 control-label">Emitir Factura</label>
            
            
            <div class="col-sm-9">
                <select class="form-control" name="send_invoice" id="send_invoice">
                    <option value="1">Activado</option>
                    <option value="0" selected>Desactivado</option>
                </select>
            </div>
        </div>

        

        <div class="form-group" id="pl">
            <label for="slcplan" class="col-sm-3 control-label">{{__('app.internetPlan')}}</label>
            <div class="col-sm-9">
                <select class="form-control" name="plan"
                        id="slcplan">
                </select>
            </div>
        </div>

        <div class="form-group" id="showprofiles">
            <label for="slcprofile"
                   class="col-sm-3 control-label"
                   id="Textprofile"></label>
            <div class="col-sm-9">
                <select class="form-control" name="profile"
                        id="slcprofile">
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
            <div class="col-sm-9">
                <div class="input-group">
                    <input class="form-control date-picker"
                           maxlength="8"
                           value="{{date('d-m-Y')}}" type="text"
                           data-date-format="dd-mm-yyyy"
                           id="date_in" name="date_in" readonly
                           required/>
                    <span class="input-group-addon">
                <i class="fa fa-calendar bigger-110"></i>
            </span>
                </div>
            </div>
        </div>

{{--        <div class="form-group">--}}
{{--            <label for="id-date-picker-1"--}}
{{--                   class="col-sm-3 control-label">{{ __('app.nextPaymentDate') }}</label>--}}
{{--            <div class="col-sm-9">--}}
{{--                <div class="input-group">--}}
{{--                    <input class="form-control date-picker"--}}
{{--                           maxlength="8" value="{{ \Carbon\Carbon::now()->addMonths(1)->format('d-m-Y') }}"--}}
{{--                           type="text"--}}
{{--                           data-date-format="dd-mm-yyyy"--}}
{{--                           id="date_pay" name="date_pay"--}}
{{--                           readonly required/>--}}
{{--                    <span class="input-group-addon">--}}
{{--              <i class="fa fa-calendar bigger-110"></i>--}}
{{--          </span>--}}
{{--                </div>--}}
{{--            </div>--}}
{{--        </div>--}}

{{--        <div class="form-group">--}}
{{--            <label for="regpay" class="col-sm-3 control-label">{{ __('app.registerPayment') }}</label>--}}
{{--            <div class="col-sm-9">--}}
{{--                <div class="checkbox">--}}
{{--                    <label>--}}
{{--                        <input name="regpay" value="1"--}}
{{--                               type="checkbox" class="ace"--}}
{{--                               id="regpay"/>--}}
{{--                        <span class="lbl"></span>--}}
{{--                    </label>--}}
{{--                </div>--}}
{{--            </div>--}}
{{--        </div>--}}

{{--        <div class="form-group">--}}
{{--            <label for="regpay" class="col-sm-3 control-label">--}}
{{--                <p class="text-success"><i--}}
{{--                        class="fa fa-files-o"></i> {{ __('app.copy') }}--}}
{{--                </p></label>--}}
{{--            <div class="col-sm-9">--}}
{{--                <div class="checkbox">--}}
{{--                    <label>--}}
{{--                        <input name="copy" type="checkbox"--}}
{{--                               class="ace" id="copy"/>--}}
{{--                        <span class="lbl"></span>--}}
{{--                    </label>--}}
{{--                </div>--}}
{{--            </div>--}}
{{--        </div>--}}
    </form>
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-default"
            data-dismiss="modal">{{ __('app.close') }}
    </button>
    <button onclick="addbtnClientService();return false;" type="button"
            class="btn btn-primary addbtnClientService"
            data-loading-text="@lang('app.saving')..."><i
            class="fa fa-floppy-o"></i>
        {{ __('app.save') }}
    </button>
</div>
<script>
    ///// General Messages for system ///////
    //Mesages for confirmatios success
    function msg(msg, type) {
        if (type == 'success') {
            var clase = 'gritter-success';
            var tit = Lang.app.registered;
            var img = 'assets/img/ok.png';
            var stincky = false;
        }
        if (type == 'error') {
            var clase = 'gritter-error';
            var tit = Lang.app.error;
            var img = 'assets/img/error.png';
            var stincky = false;
        }
        if (type == 'debug') {
            var clase = 'gritter-error gritter-center';
            var tit = Lang.app.errorInternoDebugMode;
            var img = '';
            var stincky = false;
        }
        if (type == 'info') {
            var clase = 'gritter-info';
            var tit = Lang.app.information;
            var img = 'assets/img/info.png';
            var stincky = false;
        }
        if (type == 'mkerror') {
            var clase = 'gritter-error';
            var tit = 'Error desde Mikrotik';
            var img = '';
            var stincky = false;
        }

        if (type == 'system') {
            var clase = 'gritter-light gritter-center';
            var tit = 'Información del sistema';
            var img = '';
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
    //funcion para obtener las IP/Redes
    function getNet(sl, sel) {
        $.ajax({
            "url": baseUrl+"/router/getrouter/ipnet",
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
                $(sel).show('fast');
            }
        });
    }
    $(function () {
        $('#snet,#showuser,#showpass,#typeauth,#showprofiles').hide();
        $('#ro a[href="#dclient"]').tab('show');
        //fin de verificar
        $('#winnew').waiting({ fixed: true });
        $('#slcplan,#slcrouter').empty();
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
                $('#slcrouter').append($('<option>').text('Seleccione Router').attr('value', '').prop('selected', true));
                $.each(data, function(i, val) {
                    $('#slcrouter').append($('<option>').text(val['name']).attr('value', val.id));
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

        $('#slcplan').empty();
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
                $.each(data, function(i, val) {
                    $('#slcplan').append($('<option>').text(val['name']).attr('value', val.id));
                });

                //verificamos la variable global
                if (window.typecon == 'pp' || window.typecon == 'ho') {

                    var myText = data[0].name;
                    var re = $("#slcprofile").children().filter(function() {
                        return $(this).val() == myText;
                    }).prop('selected', true);

                    if (re.length == 0) {
                        $("#slcprofile").children().filter(function() {
                            return $(this).val() == '*0';
                        }).prop('selected', true);
                    }
                }
            }
        });

        $(document).on("change", "#slcrouter", function() {

            var idr = $('#slcrouter').val();

            if (idr != 'none') {

                $.ajax({
                    "url": "{{ route('admin.client.control') }}",
                    "type": "POST",
                    "data": { "id": idr },
                    "dataType": "json",
                    'error': function(xhr, ajaxOptions, thrownError) {
                        debug(xhr, thrownError);
                    }
                }).done(function(data) {

                    window.typecon = data.type;

                    if (data.type == 'ho') {

                        //listamos todos los user profiles de este router y selecionamos el perfil si hay cohincidencia
                        $('#slcprofile').empty();

                        $.ajax({
                            "url": "{{route('client.getclient.listprofileshotspot')}}",
                            "type": "POST",
                            "data": { "id": idr },
                            "dataType": "json",
                            'error': function(xhr, ajaxOptions, thrownError) {
                                debug(xhr, thrownError);
                            }
                        }).done(function(data) {
                            if (data.success == true) {

                                $('#slcprofile').append($('<option>').text('Crear perfil').attr('value', '*0'));

                                $pr = $.each(data.profiles, function(i, val) {
                                    $('#slcprofile').append($('<option>').text(val['name']).attr('value', val.name));
                                });

                                $.when($pr).done(function() {

                                    var myText = $('#slcplan').find("option:first-child").text();
                                    $("#slcprofile").children().filter(function() {
                                        return $(this).val() == myText;
                                    }).prop('selected', true);

                                });

                                $('#typeauth').show('fast');
                                $('#showuser,#showpass').show('fast');

                                $('#tuser').text('Usuario (hotspot)');
                                $('#tpass').text('Contraseña (hotspot)');
                                $('#Textprofile').text('Perfil Hotspot');
                                $('#showprofiles').show('fast');
                            } else {
                                $('#add').modal('toggle');
                                msg('No se puede conectar al router.', 'system');
                            }

                        }); //end ajax

                    }

                    if (data.type == 'sq' || data.type == 'st' || data.type == 'dl' || data.type == 'pc') {
                        $('#showuser,#showpass,#lsprofiles,#showprofiles,#typeauth').hide('fast');
                    }

                    if (data.type == 'nc') {
                        $('#showuser,#showpass,#lsprofiles,#showprofiles,#typeauth').hide('fast');
                    }

                    if (data.type == 'pp') {
                        //listamos todos los perfiles de este router y selecionamos el perfil si hay cohincidencia
                        $('#slcprofile').empty();

                        $.ajax({
                            "url": "{{ route('client.getclient.listprofilesppp') }}",
                            "type": "POST",
                            "data": { "id": idr },
                            "dataType": "json",
                            'error': function(xhr, ajaxOptions, thrownError) {
                                debug(xhr, thrownError);
                            }
                        }).done(function(data) {
                            if (data.success == true) {
                                $('#slcprofile').empty();
                                $('#slcprofile').append($('<option>').text('Crear perfil').attr('value', '*0'));

                                $pr = $.each(data.profiles, function(i, val) {
                                    $('#slcprofile').append($('<option>').text(val['name']).attr('value', val.name));
                                });


                                $.when($pr).done(function() {

                                    var myText = $('#slcplan').find("option:first-child").text();
                                    $("#slcprofile").children().filter(function() {
                                        return $(this).val() == myText;
                                    }).prop('selected', true);

                                });

                                $('#showuser,#showpass').show('fast');
                                $('#tuser').text('Usuario (ppp-secrets)');
                                $('#tpass').text('Contraseña (ppp-secrets)');
                                $('#Textprofile').text('Perfil PPPoE');
                                $('#showprofiles').show('fast');
                                //ocultamos
                                $('#typeauth').hide('fast');
                            } else {
                                $('#add').modal('toggle');
                                msg('No se puede conectar al router.', 'system');
                            }

                        }); //end ajax


                    } //end if pp

                    if (data.type == 'pa' || data.type == 'ps' || data.type == 'pt' || data.type == 'ra' || data.type == 'rp' || data.type == 'rr') {
                        $('#showuser,#showpass').show('fast');
                        $('#tuser').text('Usuario (ppp-secrets)');
                        $('#tpass').text('Contraseña (ppp-secrets)');
                        $('#lsprofiles,#showprofiles').hide('fast');
                        $('#typeauth').hide('fast');
                    }

                    if (data.type == 'ha') {
                        $('#typeauth').show('fast');
                        $('#showuser,#showpass').show('fast');
                        $('#tuser').text('Usuario (hotspot)');
                        $('#tpass').text('Contraseña (hotspot)');
                        $('#lsprofiles,#showprofiles').hide('fast');

                    }

                    if (data.type == 'no') {
                        $('#showuser,#showpass,#lsprofiles,#showprofiles,#typeauth').hide('fast');
                    }


                    if (data.type == 'un') {
                        $('#showuser,#showpass,#lsprofiles,#showprofiles,#typeauth,#snet').hide('fast');
                        msg('No termino de configurar el router, ingrese a <b>routers</b> opción editar "icono del lápiz".', 'system');
                    } else {
                        getNet(idr, '#snet');
                    }


                });
            } else {
                $('#showuser,#showpass,#lsprofiles,#showprofiles,#typeauth,#snet').hide('fast');
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
                        rid: $('#slcrouter').val()
                    }
                }
            },

            scrollBar: true,
            showAutocompleteOnFocus: true
        });
    })
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
    //funcion para buscar perfiles si hay un nombre de plan que sea igual a un perfil
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
    $(document).on("change", "#slcplan", function() {
        findprofile('#slcplan', '#slcprofile');
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


    function addbtnClientService() {
        $.easyAjax({
            type: 'POST',
            url: "{{ route('billing.services.store', $client) }}",
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

    //Here how to call above plugin from everywhere in your application document body
    $.toggleShowPassword({
        field: '#pass_hot',
        control: '#showp2'
    });
</script>
