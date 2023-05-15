// Clients Core - Funciones principales JQuery para clientes
//autocomplete
$(document).ready(function(e) {
    var treload;
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
    ////// end messages for this page //////

    ///// star loading modal //////////////

    function startloading(selector, text) {

        $(selector).loadingModal({
            position: 'auto',
            text: text,
            color: '#fff',
            opacity: '0.7',
            backgroundColor: 'rgb(0,0,0)',
            animation: 'spinner'
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
    //// fin de la funcion de depuracion
    //aditional config
    bootbox.setDefaults("locale", locale) //traslate bootbox
        //adition plugins
        //datepicker plugin

    //end aditional plugin






    //funcion para verificar clientes en linea
    function check_is_online() {

        //obtenemos el tipo de control
        $.ajax({
            "url": baseUrl+"/crnc31hy55t",
            "type": "GET",
            "data": {},
            "dataType": "json",
            'error': function(xhr, ajaxOptions, thrownError) {
                debug(xhr, thrownError);
            }
        }).done(function(data) {

            if (data.result) {
                treload.ajax.reload();
            }

        });
    }

    //ocultamos campos
    $('#showuser,#showpass,#showus,#showpa,#snet,#snet2,#showprofiles,#edshowedprofiles,#typeauth,#edtypeauth').hide();


    //funcion para obtener info del cliente
    $(document).on('click', '.info', function(event) {
        event.preventDefault();
        /* Act on the event */

        var id = $(this).attr("id");

        //obtenemos el tipo de control
        $.ajax({
            "url": baseUrl+"/client/getclient/info",
            "type": "POST",
            "data": { 'id': id },
            "dataType": "json",
            'error': function(xhr, ajaxOptions, thrownError) {
                debug(xhr, thrownError);
            }
        }).done(function(data) {

            $('#infotitle').text('Información - ' + data.name);
            $('#infopaydate').text(data.paydate);
            $('#infoplan').text(data.plan);

            //reset class
            $('#infoemail').removeClass('label-danger');
            $('#infoemail').removeClass('label-success');
            $('#infosms').removeClass('label-danger');
            $('#infosms').removeClass('label-success');
            $('#infoservice').removeClass('label-success');
            $('#infoservice').removeClass('label-danger');

            if (data.email == 'Desactivado') {
                $('#infoemail').addClass('label-danger').text(data.email);
            } else {
                $('#infoemail').addClass('label-success').text(data.email);
            }

            if (data.sms == 'Desactivado') {
                $('#infosms').addClass('label-danger').text(data.sms);
            } else {
                $('#infosms').addClass('label-success').text(data.sms);
            }

            $('#infocut').text(data.cut);

            if (data.status == 'ac') {
                $('#infoservice').text('Activo').addClass('label-success');
            } else {
                $('#infoservice').text('Cortado').addClass('label-danger');
            }

            $('#inforouter').text(data.router);
            $('#infoip').text(data.ip);
            $('#infomac').text(data.mac);

            switch (data.control) {
                case 'sq':
                $('#infocontrol').text('Simple Queues');
                break;
                case 'st':
                    $('#infocontrol').text('Simple Queues (with Tree)');
                    break;
                case 'ho':
                $('#infocontrol').text('Hotspot - User Profiles');
                break;
                case 'ha':
                $('#infocontrol').text('Hotspot - PCQ Address List');
                break;
                case 'dl':
                $('#infocontrol').text('DHCP Leases');
                break;
                case 'pp':
                $('#infocontrol').text('PPPoE - Secrets');
                break;
                case 'ps':
                    $('#infocontrol').text('PPPoE - Simple Queue');
                    break;
                case 'pt':
                    $('#infocontrol').text('PPPoE - Secrets Simple Queues (with Tree)');
                    break;
                case 'pa':
                $('#infocontrol').text('PPPoE - Secrets - PCQ Address List');
                break;
                case 'pc':
                $('#infocontrol').text('PCQ Address List');
                break;
                default:
                $('#infocontrol').text('Ninguno');
            }

            if (data.portal == '1') {
                $('#infoportal').text('Si');
            } else {
                $('#infoportal').text('No');
            }


            $('#modalinfo').modal('show');

        });


    });


    //inicio obtener planes y routers
    $('.newcl').click(function() {
        // verificamos is esta copiando el formulario
        if (!$('#copy').is(':checked')) {
            $('#snet').hide();
            $('#formaddclient1')[0].reset(); //reseteamos el formulario

        }
        $('#snet,#showuser,#showpass,#typeauth,#showprofiles').hide();
        $('#ro a[href="#dclient"]').tab('show');
        //fin de verificar
        $('#winnew').waiting({ fixed: true });
        $('#slcplan,#slcrouter').empty();
        //get routers

        $.ajax({
            "url": baseUrl+"/client/getclient/routers",
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
                $('#slcrouter').append($('<option>').text('Seleccione Router').attr('value', 'none').prop('selected', true));
                $.each(data, function(i, val) {
                    $('#slcrouter').append($('<option>').text(val['name']).attr('value', val.id));
                });
                $('#winnew').waiting('done');
            }

        });
    });
    //fin de obtener planes y routers

    //funcion para mostrar o ocultar usuario y contraseña agregar
    $(document).on('change', '#slauth', function(event) {
        event.preventDefault();
        /* Act on the event */

        if ($('#slauth').val() == 'userpass') {

            $('#showuser,#showpass').show('fast');

        } else if ($('#slauth').val() == 'binding') {
            $('#showuser').show('fast');
            $('#showpass').hide('fast');
        } else {
            $('#showuser,#showpass').hide('fast');
        }

    });

    //funcion para mostrar o ocultar usuario editar
    $(document).on('change', '#edit_slauth', function(event) {
        event.preventDefault();
        /* Act on the event */

        if ($('#edit_slauth').val() == 'userpass') {

            $('#showus,#showpa').show('fast');

        } else if ($('#edit_slauth').val() == 'binding') {
            $('#showus').show('fast');
            $('#showpa').hide('fast');
        } else {
            $('#showus,#showpa').hide('fast');
        }

    });

    //funcion para mostrar campos user name y pass si el router es hotspot o pppoe
    $(document).on("change", "#slcrouter", function() {

        var idr = $('#slcrouter').val();

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

                window.typecon = data.type;

                if (data.type == 'ho') {

                    //listamos todos los user profiles de este router y selecionamos el perfil si hay cohincidencia
                    $('#slcprofile').empty();

                    $.ajax({
                        "url": baseUrl+"/client/getclient/listprofileshotspot",
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

					if(data.type=='sq' || data.type=='st' || data.type=='dl' || data.type=='pc'){
						$('#showuser,#showpass,#lsprofiles,#showprofiles,#typeauth').hide('fast');
					}

					if(data.type=='nc'){
						$('#showuser,#showpass,#lsprofiles,#showprofiles,#typeauth').hide('fast');
					}

					if(data.type=='pp'){
						//listamos todos los perfiles de este router y selecionamos el perfil si hay cohincidencia
						$('#slcprofile').empty();

                    $.ajax({
                        "url": baseUrl+"/client/getclient/listprofilesppp",
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

                if (data.type=='pa' || data.type=='ps' || data.type=='pt') {
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

    //funcion para buscar y listar ips
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


    $('#edit_ip').typeahead({
        onSelect: function(item) {
            var id = item.value;
        },
        ajax: {
            url: baseUrl +"/network/getnetwork/ip",
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

    //recuperamos todos los planes del router seleccionado
    $(document).on("click", "#internet", function() {

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
    });
    //fin de recuperar los planes del router selecionado

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



    var caja_load=false;
    function multi_select(ident) {
        caja_load=false;
        if (ident != "") {
            $.ajax({
                "url": baseUrl+"/client/getclient/caja",
                "type": "POST",
                "data": { "id": ident },
                "dataType": "json",
                'error': function(xhr, ajaxOptions, thrownError) {
                    debug(xhr, thrownError);
                }
            }).done(function(data) {
                if (data.success == true) {
                    $('.content_zona_select').hide();
                    caja_load=true;
                    $('.content_port_s').show();
                    $('.zone_info').text(data.zone);
                    $('#zona_id_input').val(data.zone_id);
                    $('#zona_id_edit').val(data.zone_id);
                    $(".select_port").html('');
                    var add2 = "<option value=''>Seleccione puerto</option>";
                    $(".select_port").append(add2);
                    var cant = data.detail.port;
                    if (cant > 0) {
                        cant = cant + 1;
                    }
                    for (i = 1; i < cant; i++) {
                        var status_sw = true;
                        $.each(data.port, function(key, value) {
                            if (value == i) {
                                status_sw = false;
                            }
                        });
                        if (status_sw) {
                            var add = "<option value='" + i + "'>" + i + "</option>";
                            $(".select_port").append(add);
                        }

                    }
                }
            });
        }
    }



    $("#edit_zona_id").change(function() {
       var ident = $(this).val();
       $('#zona_id_edit').val(ident);

   });



    $("#zona_id").change(function() {
       var ident = $(this).val();
       $('#zona_id_input').val(ident);

   });

    $(".change_unus").change(function() {

      var ident = $(this).val();
      $('.content_zona_select').hide();

      var type = $('option:selected', this).data("type");
      //cargada
      if(!caja_load){
        //Buscamos el tipo
        if(type=="CPE"){
          $('.content_zona_select').show();
      }


  }

});

    $(".onchange_caja").change(function() {
        var ident = $(this).val();
        $('.zone_info').text('');
        $('.content_port_s').hide();
        multi_select(ident);
    });

    // editar cliente
    $(document).on("click", '.editar', function(event) {
        $('#winedit').waiting({ fixed: true });
        var idu = $(this).attr('id');
        $('[name=client]').val(idu);
        var fdata = $('#val').serialize();

        $('#ClientformEdit').find(".has-error").each(function () {
            $(this).find(".help-block").text("");
            $(this).removeClass("has-error");
        });

        $('#load').show();
        $.ajax({
            type: "POST",
            url: baseUrl+"/client/getclient/data",
            data: fdata,
            dataType: "json",
            'error': function(xhr, ajaxOptions, thrownError) {
                debug(xhr, thrownError);
            }
        }).done(function(data) {
            if (data.success) {
                //obtenemos todos los router y seleccionamos el router que tiene el usuario
                var router = data.router;
                var type_auth = data.type_auth;
                //get routers
                $('.zone_info').text('');
                $('.content_port_s').hide();

                $('#ClientformEdit2 input[name="edit_user"]').val(data.user);
                $('#ClientformEdit input[name="client_id"]').val(data.id);
                $('#ClientformEdit input[name="edit_name"]').val(data.name);
                $('#ClientformEdit input[name="edit_phone"]').val(data.phone);
                $('#ClientformEdit input[name="edit_email"]').val(data.email);
                $('#ClientformEdit input[name="edit_dni"]').val(data.dni);
                $('#ClientformEdit input[name="edit_dir"]').val(data.dir);
                $('#ClientformEdit #edit_typedoc_cod').val(data.typedoc_cod);
                $('#ClientformEdit #edit_economicactivity_cod').val(data.economicactivity_cod).trigger('change');
                $('#ClientformEdit #edit_municipio_cod').val(data.municipio_cod).trigger('change');
                $('#ClientformEdit #edit_typeresponsibility_cod').val(data.typeresponsibility_cod);
                $('#ClientformEdit #edit_typetaxpayer_cod').val(data.typetaxpayer_cod);
                $('#edit_onusn').val(data.onusn);
                $('#ClientformEdit input[name="location_edit"]').val(data.coordinates);
                $('#edit_odb_id').prop('selected', false).find('option:first').prop('selected', true);
                $('#edit_onu_id').prop('selected', false).find('option:first').prop('selected', true);
                $('.content_zona_select').hide();
                if (data.odb_id != null) {
                    $('#edit_odb_id').prop('selected', true).find("option[value=" + data.odb_id + "]").prop('selected', true);
                    var ok = multi_select(data.odb_id);
                    setTimeout(function () {

                        if (data.port != null) {
                            var id_s = data.port;
                            $("#select_port_edit").append("<option value='" + id_s + "'>" + id_s + "</option>");
                            $('#select_port_edit').prop('selected', true).find("option[value=" + id_s + "]").prop('selected', true);
                        }
                    }, 900);


                } else {
                    $('#edit_odb_id').prop('selected', false).find('option:first').prop('selected', true);
                }


                if (data.onu_id != null) {
                    $('#edit_onu_id').prop('selected', true).find("option[value=" + data.onu_id + "]").prop('selected', true);

                    if (data.type_onu == "CPE") {
                        $('#edit_zona_id').prop('selected', true).find("option[value=" + data.zona_id + "]").prop('selected', true);
                        $('.content_zona_select').show();
                    }


                } else {
                    $('#edit_onu_id').prop('selected', false).find('option:first').prop('selected', true);
                }

            }
        });
});
    //fin de editar cliente
    //funcion para recuperar herramientas del cliente
    $(document).on('click', '.tool', function(event) {
        event.preventDefault();
        /* Act on the event */

        startloading('body', 'Cargando...');
        //mostramos siempre el primer tab

        $('#rtool a[href="#pingclient"]').tab('show');


        var idc = $(this).attr('id');

        $('#clid').val(idc);

        $.ajax({
            "url": baseUrl+"/client/getservice/tools",
            "type": "POST",
            "data": { "id": idc },
            "dataType": "json",
            'error': function(xhr, ajaxOptions, thrownError) {
                debug(xhr, thrownError);
            }
        }).done(function(data) {

            if (data.success) {

                if (data.typecontrol == 'pc' || data.typecontrol == 'ha' || data.typecontrol == 'no') {
                    //ocultamos la pestaña trafico
                    $('#traf').hide();
                } else {
                    $('#traf').show();
                }
                $('#namecl').val(data.name);
                $('#ipt,#srca').val(data.ip);
                $('#rtid').val(data.router_id);
                $('#service_id').val(data.service_id);
                $('#interface,#slinterface').empty();
                $('#interface,#slinterface').append($('<option>').text('').attr('value', '').prop('selected', true));
                var lan = data.lan;

                $ps = $.each(data.interfaces, function(i, val) {
                    $('#interface,#slinterface').append($('<option>').text(val['name'] + '/' + val['default-name']).attr('value', val.name));
                });


                $.when($ps).done(function() {

                    $("#slinterface").children().filter(function() {
                        return $(this).val() == lan;
                    }).prop('selected', true);



                });

                $('body').loadingModal('destroy');

                $('#tools').modal('show');


            } else {

                $('body').loadingModal('destroy');

                alert('Error al obtener datos');

            }

        });



    });



    //funcion para hacer torch
    $(document).on('click', '#btntorch', function(event) {
        event.preventDefault();
        /* Act on the event */

        var data = $('#formtorch').serializeArray();
        data.push({ name: 'router', value: $('#rtid').val() });

        $('#btntorch').html('<i class="fa fa-cog fa-spin"></i> Haciendo torch...');

        $("#table-torch tbody tr").remove();

        $.ajax({
            "url": baseUrl+"/tools/torch",
            "type": "POST",
            "data": data,
            "dataType": "json",
            'error': function(xhr, ajaxOptions, thrownError) {
                debug(xhr, thrownError);
            }
        }).done(function(data) {



            $.each(data, function(i, val) {


                if (val['src-address'] === undefined)
                    var src_address = '-----'
                else
                    var src_address = val['src-address'];

                if (val['dst-address'] === undefined)
                    var dst_address = '-----';
                else
                    var dst_address = '<a href="http://' + val['dst-address'] + '" target="_blank">' + val['dst-address'] + '</a>';

                if (val['src-port'] === undefined)
                    var src_port = '-----';
                else
                    var src_port = val['src-port'];

                if (val['dst-port'] === undefined)
                    var dst_port = '-----';
                else
                    var dst_port = val['dst-port'];


                $('#table-torch').append('<tr><td>' + src_address + '</td><td>' + dst_address + '</td><td>' + src_port + '</td><td>' + dst_port + '</td><td>' + val['tx'] + '</td><td>' + val['rx'] + '</td><td>' + val['tx-packets'] + '</td><td>' + val['rx-packets'] + '</td></tr>');
            });

            $('#btntorch').html('Iniciar');

        });




    });

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

    // modal focus
    $('#add').on('shown.bs.modal', function() {
        $('#name').focus()
    });

    // end modal focus

    //Plugins adicionales
    $('.ip_address').mask('099.099.099.099');
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


    //funcion para mostrar o ocultar el password

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
        field: '#pass',
        control: '#showp'
    });

    //Here how to call above plugin from everywhere in your application document body
    $.toggleShowPassword({
        field: '#edit_pass2',
        control: '#edshowp'
    });


    //Here how to call above plugin from everywhere in your application document body
    $.toggleShowPassword({
        field: '#pass_hot',
        control: '#showp2'
    });

    //Here how to call above plugin from everywhere in your application document body
    $.toggleShowPassword({
        field: '#edit_pass',
        control: '#edshowp2'
    });



    //fin de plugins adicionales
    //inicio tooltips para botones


    var fecha = $('.date-picker').datepicker({
        language: 'es',
        autoclose: true,
        todayHighlight: true
            //startView: 'year',
        });



    //show datepicker when clicking on the icon

    // $.ajax({
    //     "url": baseUrl+"/users/isloginuser",
    //     "type": "POST",
    //     "data": {},
    //     "dataType": "json"
    // });

    //validate coordinates
    function validateNewPlantsForm(latlng) {
        var latlngArray = latlng.split(",");
        for (var i = 0; i < latlngArray.length; i++) {
            if (isNaN(latlngArray[i]) || latlngArray[i] < -127 || latlngArray[i] > 90) {
                msg('Coordenadas no validas.', 'error');
                return false;
            }
        }

        return latlngArray;
    }


    function openmap(lat, lon, windowSelector, searchBox, locatioBox, map_type = 'google_map') {

        switch (map_type) {
            case 'google_map':
                load_google_map(lat, lon, windowSelector, searchBox, locatioBox);
                break;
            case 'open_street_map':
                load_open_street_map(lat, lon, windowSelector, searchBox, locatioBox);
                break;
        }
    }

    function load_google_map(lat, lon, windowSelector, searchBox, locatioBox) {
        $('#' + windowSelector).locationpicker({

            location: {
                latitude: lat,
                longitude: lon
            },
            radius: 0,
            inputBinding: {
                locationNameInput: $(searchBox)
            },
            mapOptions: {mapTypeControl: true, streetViewControl: true},
            enableAutocomplete: true,
            //markerIcon: 'http://www.iconsdb.com/icons/preview/tropical-blue/map-marker-2-xl.png'
            onchanged: function (currentLocation, radius, isMarkerDropped) {
                $(locatioBox).val(currentLocation.latitude + "," + currentLocation.longitude);
            }
        });
    }

    function destroy_osm() {
        if (typeof window.osmMap != 'undefined' && window.osMap == null) {
            window.osmMap.remove();
            window.osmMap = null;
        }
    }

    function open_street_map(lat, lon, windowSelector, locatioBox) {
        if (typeof window.osmMap == 'undefined' || window.osMap == null) {
            let osm_layer = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '© OpenStreetMap'
            });
            window.osmMap = L.map(windowSelector, {
                center: [lat, lon],
                zoom: 15,
                layers: [osm_layer]
            });

            let esri_satellite_layer = L.tileLayer('https://server.arcgisonline.com/ArcGIS/rest/services/World_Imagery/MapServer/tile/{z}/{y}/{x}', {
                attribution: 'Tiles &copy; Esri &mdash; Source: Esri, i-cubed, USDA, USGS, AEX, GeoEye, Getmapping, Aerogrid, IGN, IGP, UPR-EGP, and the GIS User Community'
            });
            let esri_places_layer = L.tileLayer('https://server.arcgisonline.com/arcgis/rest/services/Reference/World_Boundaries_and_Places/MapServer/tile/{z}/{y}/{x}');
            var baseMaps = {
                "OpenStreetMap": osm_layer,
                "Satellite": esri_satellite_layer
            };

            var overlayMaps = {
                "Boundaries & Places": esri_places_layer
            };
            L.control.layers(baseMaps, overlayMaps).addTo(window.osmMap);
            window.osmMarker = L.marker([lat, lon], {
                'draggable': true
            }).addTo(window.osmMap);

            window.osmMarker.on('moveend', function () {
                let currentLocation = window.osmMarker.getLatLng();
                $(locatioBox).val(currentLocation.lat + "," + currentLocation.lng);
            });
        }
        return {
            'map': window.osmMap,
            'marker': window.osmMarker
        };
    }
    
    function load_open_street_map(lat, lon, windowSelector, searchBox, locatioBox) {

        let osm = open_street_map(lat, lon, windowSelector, locatioBox);

        $(`[data-map-type="open_street_map"] ${searchBox}`).typeahead({
            highlight: true,
        },
        {
            name: 'brands',
            display: 'value',
            source: function(query, syncResults, asyncResults) {
                return $.get('https://photon.komoot.io/api/?q=' + query, function(data) {
                    data_set = [];
                    $(data.features).each(function (index, item) {
                        const city = typeof item.properties.city != 'undefined' ? ` - ${item.properties.city}` : '';
                        const state = typeof item.properties.state != 'undefined' ? ` - ${item.properties.state}` : '';
                        const postcode = typeof item.properties.postcode != 'undefined' ? ` - ${item.properties.postcode}` : '';

                        data_set.push({
                            id: index,
                            latlng: item.geometry.coordinates, 
                            value: `${item.properties.name} ${city} ${state} - ${item.properties.country} ${postcode}`
                        });

                    });
                    return asyncResults(data_set);
                }, 'json');
            }
        });

        $(`[data-map-type="open_street_map"] ${searchBox}`).on('typeahead:selected', function(evt, item) {
            osm.map.flyTo([item.latlng[1], item.latlng[0]], 15);
            osm.marker.setLatLng([item.latlng[1], item.latlng[0]]);
            $(locatioBox).val(item.latlng[1] + "," + item.latlng[0]);
        })
    }

    $('#modalmap').on('shown.bs.modal', function() {
        destroy_osm();
        //$('#mapshow').locationpicker('autosize');
        let map_type = $(this).data('map-type');
        var cor = validateNewPlantsForm($('#location').val());

        if (cor != false) {

            var lat = cor[0];
            var lon = cor[1];

            openmap(lat, lon, 'us3', '#us3-address', '#location', map_type);

        } else {

            //intentamos recuperar la información de ubicacion del router
            $.ajax({
                "url": baseUrl+"/config/getconfig/defaultlocation",
                "type": "GET",
                "data": {},
                "dataType": "json",
                'error': function(xhr, ajaxOptions, thrownError) {
                    debug(xhr, thrownError);
                }
            }).done(function(data) {


                if (data.coordinates == '0') {
                    var lat = '-34.60368440000001';
                    var lon = '-58.381559100000004';
                } else {

                    var cor = validateNewPlantsForm(data.coordinates);

                    if (cor != false) {
                        var lat = cor[0];
                        var lon = cor[1];

                    } else {
                        var lat = '-34.60368440000001';
                        var lon = '-58.381559100000004';
                    }
                }

                openmap(lat, lon, 'us3', '#us3-address', '#location', map_type);

            }); //end ajax
        }

    });


    //mostrar mapa al editar router

    $('#modalmapedit').on('shown.bs.modal', function() {
        destroy_osm();
        let map_type = $(this).data('map-type');
        var map = validateNewPlantsForm($('#edilocation').val());

        if (map != false) {

            var lat_e = map[0];
            var lon_e = map[1];

            openmap(lat_e, lon_e, 'us4', '#us4-address', '#edilocation', map_type);

        } else {

            //intentamos recuperar la información de ubicacion del router
            $.ajax({
                "url": baseUrl+"/config/getconfig/defaultlocation",
                "type": "GET",
                "data": {},
                "dataType": "json",
                'error': function(xhr, ajaxOptions, thrownError) {
                    debug(xhr, thrownError);
                }
            }).done(function(data) {


                if (data.coordinates == '0') {
                    var lat_e = '-34.60368440000001';
                    var lon_e = '-58.381559100000004';
                } else {

                    var cor = validateNewPlantsForm(data.coordinates);

                    if (cor != false) {
                        var lat_e = cor[0];
                        var lon_e = cor[1];

                    } else {
                        var lat_e = '-34.60368440000001';
                        var lon_e = '-58.381559100000004';
                    }
                }

                openmap(lat_e, lon_e, 'us4', '#us4-address', '#edilocation', map_type);

            }); //end ajax
        }
    });



    ///End Google maps

    //fin de ready
});
