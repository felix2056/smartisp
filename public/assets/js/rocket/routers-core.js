// routers Core - Funciones principales JQuery para routers
$(document).ready(function (e) {
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
        if (type == 'mkerror') {
            var clase = 'gritter-error';
            var tit = 'Error desde Mikrotik';
            var img = '';
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

    //end loading message ////////////////

    ///// funcion de depuracion
    function debug(xhr, thrownError) {
        $.ajax({
            "url": "config/getconfig/debug",
            "type": "GET",
            "data": {},
            "dataType": "json",
            'error': function (xhr, ajaxOptions, thrownError) {
                debug(xhr, thrownError);
            }
        }).done(function (deb) {

            if (deb.debug == '1') {
                msg('Error ' + xhr.status + ' ' + thrownError + ' ' + xhr.responseText, 'debug');
            } else
                alert(Lang.messages.aninternalerrorhasoccurredformoredetailtalktothedebugmode);
        });
    }

    //// fin de la funcion de depuracion
    bootbox.setDefaults("locale", locale) //traslate bootbox
    //receteamos el form
    $(document).on('click', '.peref', function (event) {
        event.stopImmediatePropagation();

        if (!$('#copy').is(':checked')) {
            $('#formaddrouter')[0].reset(); //reseamos el dormulario
            $("#textv").hide();
        }
    });

    // recargar tabla
    $(document).on("click", '.recargar', function (event) {
        event.stopImmediatePropagation();
        window.LaravelDataTables["router-table"].draw();
    });

    // fin de recarga de tabla

    //funcion automaticas segun el tipo de control
    $(document).on("change", "#typecontrol", function (event) {
        event.stopImmediatePropagation();
        if ($(this).val() == 'dl') {
            $('#dhcp').prop('checked', 'true');
        }

        //desactivar DCHP leases cuando selecciona PPPoE
        if ($(this).val() == 'pp' || $(this).val() == 'pa' || $(this).val() == 'ps') {
            $('#dhcp').removeAttr('checked'); //desactivamos pp
        }
    });

    //funcion para impedir cambiar dhcp si esta el control con dhcp leases
    $(document).on("change", "#dhcp", function (event) {
        event.stopImmediatePropagation();
        if ($('#typecontrol').val() == 'dl') {
            $('#dhcp').prop('checked', 'true');
        }
        //no se puede utilizar DHCP cuando esta selecionado PPPoe
        if ($('#typecontrol').val() == 'pp' || $('#typecontrol').val() == 'pa' || $('#typecontrol').val() == 'ps') {
            $('#dhcp').removeAttr('checked'); //desactivamos pp
        }
        //no se puede utilizar DHCP si esta activo el amarre
    });

    //función para mostrar advertencia si no vincula al router mikrotik
    $(document).on("change", "#vinr", function (event) {
        event.stopImmediatePropagation();
        var sl = $(this).val();

        if (sl == 1) {
            $("#ipapif,#passwordf,#loginf").hide('fast');
            $("#ipapi,#Password,#login").val('');

            $("#textv").html('<p class="text-danger">No se modificara la configuración y no se enviaran reglas al router. <i class="fa fa-exclamation-triangle" aria-hidden="true"></i></p>').show('fast');
        } else {
            $("#textv").hide('fast');
            $("#ipapif,#passwordf,#loginf").show('fast');
        }

    });

    //eliminar router
    $(document).on("click", '.del', function (event) {
        event.stopImmediatePropagation();
        var idr = $(this).attr("id");
        bootbox.confirm("¿ Esta seguro de eliminar el router, este no debe tener clientes asociados ?", function (result) {
            if (result) {
                $.ajax({
                    type: "POST",
                    url: "routers/delete",
                    data: {"id": idr},
                    dataType: "json",
                    'error': function (xhr, ajaxOptions, thrownError) {
                        debug(xhr, thrownError);
                    }
                }).done(function (data) {

                    if (data.msg == 'notfound')
                        msg('No se encontro el router en la BD.', 'error');
                    if (data.msg == 'existclients')
                        msg('No es posible eliminar el router se encontro clientes asociados.', 'error');
                    if (data.msg == 'success') {
                        msg('El router fue eliminado.', 'success');
                        window.LaravelDataTables["router-table"].draw();
                    }
                });
            }
        });
    });
    //fin de eliminar router

    //añadir router
    $(document).on("click", "#addbtnrouter", function (event) {
        event.stopImmediatePropagation();
        var routerdata = $('#formaddrouter').serialize();

        var $btn = $(this).button('loading');

        $.ajax({
            type: "POST",
            url: "routers/create",
            data: routerdata,
            dataType: "json",
            'error': function (xhr, ajaxOptions, thrownError) {
                debug(xhr, thrownError);
            }
        }).done(function (data) {
            //Mesajes personalizados
            if (data.msg == 'error') {
                var arr = data.errors;
                $.each(arr, function (index, value) {
                    if (value.length != 0) {
                        msg(value, 'error');
                    }
                });
            }

            if (data.msg == 'success') {
                $('#add').modal('toggle');


                window.LaravelDataTables["router-table"].draw();

                //buscamos datos y abrimos el modal editar router

                //$('#mytabs').waiting({ fixed: true});
                $('[name=router]').val(data.id);
                var fdata = $('#val').serialize();
                $('#load').show();

                // GetRouterEdit(fdata,'reedit','add');
            }
            //fin de mensajes personalizados
            //restore button
            $btn.button('reset');
        });
    });

    //fin de añadir router

    function updateRouter(msgload) {
        startloading('body', msgload);

        var routerdata = $('#RouterformEdit,#RouterformEdit2,#RouterformEdit3').serialize();

        $.ajax({
            type: "POST",
            url: "routers/update",
            data: routerdata,
            dataType: "json",
            'error': function (xhr, ajaxOptions, thrownError) {
                debug(xhr, thrownError);
                $btn.button('reset');
            }
        }).done(function (data) {

            //Mesajes personalizados
            if (data.msg == 'error') {

                $('body').loadingModal('destroy');

                var arr = data.errors;
                $.each(arr, function (index, value) {
                    if (value.length != 0) {
                        msg(value, 'error');
                    }
                });

            }

            if (data.msg == 'mkerror') {
                $('body').loadingModal('destroy');
                msg('Mac address or client-id must be present.', 'mkerror');
            }

            if (data.msg == 'not-found') {
                $('body').loadingModal('destroy');
                msg('No se encuentra el router en la BD.', 'error');
            }
            if (data.msg == 'errorConnect') {
                $('body').loadingModal('destroy');
                msg('Se produjo un error no se tiene acceso al router, verifique los datos de autentificación, además si esta encendido y conectado a la red.', 'error');
            }
            if (data.msg == 'errorConnectRadius') {
                $('body').loadingModal('destroy');
                msg('Se produjo un error. No se tiene conexión al Radius especificado.', 'error');
            }
            if (data.msg == 'noadv') {
                $('body').loadingModal('destroy');
                msg('No se pudo aplicar el aviso de corte por falta de configuración del mismo', 'error');
            }
            if (data.msg == 'noconf') {
                $('body').loadingModal('destroy');
                msg('No es posible aplicar los cambios, no ingreso la dirección ip del servidor diríjase a <b>configuración</b> pestaña <b>portal cliente</b>.', 'system');
                $('#adv').removeAttr('checked');
            }
            if (data.msg == 'success') {
                $('body').loadingModal('destroy');
                msg('El router fua actualizado correctamente.', 'info');
                $('#edit').modal('hide');
                window.LaravelDataTables["router-table"].draw();
            }

            //mikrotik errors
            if (data[0].msg == 'mkerror') {

                $.each(data, function (index, value) {
                    msg(value.message, 'mkerror');
                });
            }
        });
    }

    //guardar editar router
    $(document).on("click", ".savebtnrouter", function (event) {
        event.stopImmediatePropagation();
        if (window.ctrl != $('#typecontrol').val() && window.count > 0) {
            var st = confirm("Precaución está intentando cambiar de control, se recomienda configurar el router para el nuevo tipo de control ¿Desea continuar?");

            if (st != true) {
                return false;
            } else {

                var msgload = 'Migrando espere por favor…';
            }
        } else {

            var msgload = 'Guardando...';
        }

        //cotrol dchp
        if ($('#dhcp').is(':checked')) {
            var dhc = 1;
        } else {
            var dhc = 0;
        }

        var $btn = $(this).button('loading');

        if (dhc == 1) {

            startloading('body', msgload);

            $.ajax({
                type: "POST",
                url: "client/getclient/dhcp",
                data: {},
                dataType: "json"
            }).done(function (data) {

                if (parseInt(data.dhcp) > 0) {

                    $('body').loadingModal('destroy');


                    msg('No es posible guardar se encontraron clientes que no poseen una dirección mac , para usar la función dhcp todos los clientes del router deberán tener una dirección mac asignada.', 'error');


                } else {

                    $('body').loadingModal('destroy');

                    updateRouter(msgload);
                }
            });

            //restore button
            $btn.button('reset');
        } else {
            updateRouter(msgload);
            //restore button
            $btn.button('reset');
        }
    });

    //elimiar ip/red
    $(document).on('click', '.eliminar', function (event) {
        event.stopImmediatePropagation();
        var id = $(this).attr("id");
        var idro = $('#val').val();
        var faction = "routers/inte";

        startloading('body', 'Eliminando...');

        if (confirm('¿Esta seguro de quitar la IP/Red?')) {
            $.ajax({
                type: "POST",
                url: "routers/inte",
                data: {"id": id, "idro": idro},
                dataType: "json",
                'error': function (xhr, ajaxOptions, thrownError) {
                    debug(xhr, thrownError);
                }
            }).done(function (data) {

                if (data.msg == 'error') {
                    $('body').loadingModal('destroy');
                    msg('No se encontro la IP/Red en la BD.', 'error');
                }

                if (data.msg == 'success') {
                    $('body').loadingModal('destroy');
                    msg('La IP/Red fue eliminada.', 'success');
                    window.tred.ajax.reload();
                }

            }); //end ajax
        }
    });
    //fin de eliminar ip/red

    //añadir red
    $(document).on("click", "#savebtnNetwork", function (event) {
        event.stopImmediatePropagation();
        var router = $('#val').val();
        var newnetwork = $('#net').val();
        var interface = $('select[name=lan]').val();

        startloading('body', 'Guardando...');

        $.ajax({
            type: "POST",
            url: "routers/networks",
            data: {"id": router, "network": newnetwork, "interface": interface},
            dataType: "json",
            'error': function (xhr, ajaxOptions, thrownError) {
                debug(xhr, thrownError);
            }
        }).done(function (data) {
            //Mesajes personalizados
            if (data.msg == 'error') {
                $('body').loadingModal('destroy');
                var arr = data.errors;
                $.each(arr, function (index, value) {
                    if (value.length != 0) {
                        msg(value, 'error');
                    }
                });
            }
            //fin de mensajes personalizados
            if (data.msg == 'errorConnect') {
                $('body').loadingModal('destroy');
                msg('No es posible acceder al router.', 'error');
            }

            if (data.msg == 'success') {
                $('body').loadingModal('destroy');
                msg('Datos guardados.', 'success');
                $('#net').val('');
                window.tred.ajax.reload();
            }
        });
    });
    //fin de añadir red

    //funcion para verificar si esta configurado el ip del servidor
    $('#adv').change(function () {
        //verificamos si cambio la configuración de email por defecto del sistema
        $.ajax({
            "url": "config/getconfig/ipserver",
            "type": "POST",
            "data": {},
            "dataType": "json"
        }).done(function (ip) {

            if (ip.status) {
                $('#adv').removeAttr('checked');
                msg('No configuro el dirección ip del portal, ingrese a <b>configuración</b> pestaña portal cliente.', 'system');
            }

        });
    });

    // funcion para restaurar tab en editar router
    $('#edit').on('show.bs.modal', function (event) {
        $('#ro a[href="#router"]').tab('show');
    });

    function GetRouterEdit(fdata, op, ac) {

        startloading('body', 'Cargando...');

        $.ajax({
            type: "POST",
            url: "router/getrouter/data",
            data: fdata,
            dataType: "json",
            'error': function (xhr, ajaxOptions, thrownError) {
                debug(xhr, thrownError);
            }
        }).done(function (data) {

            if (data.success) {


                if (data.connect == 'bad') {
                    $('#ro a[href="#router"]').tab('show');
                    $("#redes").hide();
                    $("#internet").hide();

                    $('body').loadingModal('destroy');


                    $('#load').hide();
                    //reseto de campos
                    $('#typecontrol').val('');
                    $('#stcon').val('badConnect');
                    msg('Se produjo un error no se tiene acceso al router, verifique los datos de autentificación, además si esta encendido y conectado a la red.', 'error');
                    $('#mytabs').waiting('done');


                } else if (data.connect == 'ok') {

                    $("#redes").show();
                    $("#internet").show();
                    $('#stcon').val('');
                    //mostramos los campos ocultos
                    $("#internet,#modelfe,#ipapife,#loginfe,#passwordfe,#interfe").show();

                    if (ac == 'add') {
                        msg('El router fue añadido correctamente, por último deberá terminar de configurar el router.', 'success');
                    }


                } else { //modo sin conexion
                    $('#ro a[href="#router"]').tab('show')
                    $("#redes").show();
                    //ocultamos los campos inesesarios
                    $("#internet,#modelfe,#ipapife,#loginfe,#passwordfe,#interfe").hide();
                    if (ac == 'add') {
                        msg('El router fue añadido correctamente, por último deberá agregar IP - Redes para dejarlo configuraro correctamente.', 'success');
                    }

                }

                /**si existe informacion de radius**/
                if(data.radius_secret){
                /*    $('#radius_server').val(data.radius_server);
                    $('#radius_port').val(data.radius_port);
                    $('#radius_user').val(data.radius_user);
                    $('#radius_pass').val(data.radius_pass);
                    $('#radius_dbname').val(data.radius_dbname);*/
                    $('#radius_secret').val(data.radius_secret);
                    $('#radius_data').removeAttr('hidden');
                }

                $('#RouterformEdit input[name="router_id"]').val(data.id);
                $('#rot').val(data.id);
                $('#RouterformEdit input[name="name_edit"]').val(data.name);
                $('#RouterformEdit input[name="model_edit"]').val(data.model);
                $('#RouterformEdit input[name="ip_edit"]').val(data.ip);

                $('#RouterformEdit input[name="address_edit"]').val(data.address);
                if (data.location == '0') {
                    $('#RouterformEdit input[name="location_edit"]').val('');
                } else {
                    $('#RouterformEdit input[name="location_edit"]').val(data.location);
                }

                $('#RouterformEdit input[name="login_edit"]').val(data.login);
                $('#RouterformEdit input[name="port_edit"]').val(data.port);

                //cargamos la información de ip redes
                var idr = $('#val').val();
                $('#interf').empty();
                //inicio de la tabla ip/redes
                window.tred = $('#addresses').DataTable({
                    bAutoWidth: false,
                    processing: true,
                    responsive: true,
                    buttons: [],
                    "destroy": true,
                    "oLanguage": {
                        "sUrl": "assets/js/dataTables/dataTables.spanish.txt",
                        "sProcessing": $('#mytabs').waiting({fixed: true})
                    },
                    "columnDefs": [{
                        "targets": 3,
                        "data": "id",
                        "render": function (data, type, full) {
                            return '<button type="button" class="btn btn-xs btn-danger eliminar" id="' + full['id'] + '">Quitar</button>';
                        }
                    }
                    ],
                    ajax: {
                        "url": "routers/ips",
                        "type": "POST",
                        "cache": false,
                        "dataSrc": "",
                        complete: function () {
                            $('#mytabs').waiting('done');
                        }
                    },
                    fnServerParams: function (aoData) {
                        aoData.push({"name": "id", "value": idr});
                    },
                    columns: [
                        {data: 'network'},
                        {data: 'hosts'},
                        {data: 'gateway'}
                    ]
                });

                // obtenemos las interfaces
                $.ajax({
                    type: "POST",
                    url: "routers/interface",
                    data: {"id": idr},
                    dataType: 'json',
                    'error': function (xhr, ajaxOptions, thrownError) {
                        debug(xhr, thrownError);
                    },
                    success: function (data) {
                        $.each(data, function (i, val) {
                            $('#interf').append($('<option>').text(val['name'] + '/' + val['default-name']).attr('value', val.name));
                        });
                        $.ajax({
                            type: "POST",
                            url: "routers/routerinterface",
                            data: {"id": idr},
                            dataType: "json",
                            'error': function (xhr, ajaxOptions, thrownError) {
                                debug(xhr, thrownError);
                            }
                        }).done(function (data) {
                            if (data.sel != 'none') {
                                var myText = data.sel;
                                $("#interf").children().filter(function () {
                                    return $(this).val() == myText;
                                }).prop('selected', true);
                            }
                            if (data.msg == 'errorConnect')
                                msg('No es posible acceder al router, verifique que este en línea.', 'error');
                        });
                    }
                });
                // fin de obtener interfaces

                // obtenemos el control y seguridad

                $.ajax({
                    "url": "router/getrouter/control",
                    "type": "POST",
                    "data": {"id": idr},
                    "dataType": "json",
                    'error': function (xhr, ajaxOptions, thrownError) {
                        debug(xhr, thrownError);
                    }
                }).done(function (data) {

                    if (data.success) {
                        var myText = data.control;
                        window.ctrl = myText;
                        window.count = data.count;

                        $("#typecontrol").children().filter(function () {
                            return $(this).val() == myText;
                        }).prop('selected', true);

                        if (data.arpmac == 1)
                            $('#arps').prop('checked', 'true');
                        else
                            $('#arps').removeAttr('checked');

                        if (data.adv == 1)
                            $('#adv').prop('checked', 'true');
                        else
                            $('#adv').removeAttr('checked');

                        if (data.dhcp == 1)
                            $('#dhcp').prop('checked', 'true');
                        else
                            $('#dhcp').removeAttr('checked');

                        if (data.address_list == 1)
                            $('#address_list').prop('checked', 'true');
                        else
                            $('#address_list').removeAttr('checked');
                    }
                });
                // fin de obtener control y seguridad

                // Cargamos las ip/redes
                $.ajax({
                    url: "network/getnetwork/networks",
                    type: "POST",
                    data: {},
                    dataType: "json",
                    'error': function (xhr, ajaxOptions, thrownError) {
                        debug(xhr, thrownError);
                    }
                }).done(function (data) {
                    $('#net').empty();
                    $('#net').append($('<option>').text('').attr('value', '').prop('selected', true));
                    $.each(data, function (i, val) {
                        $('#net').append($('<option>').text(val['name'] + ' - ' + val['network']).attr('value', val.id));
                    });

                    $("#net").select2({

                        theme: "classic"
                    });


                });//end ajax
                // fin de cargar ip/redes

                //Fin de cargar la informacion de ip redes
                $('#mytabs').waiting('done');
                $('#load').hide();

                //mostramos la ventana modal
                $('#edit').modal('show');

                $('body').loadingModal('destroy');

            } else {
                $('#load').hide();
                msg('No se pudo cargar la información de la base de datos', 'error');
            }
        }); //end ajax
    }

    //fin de la funcion para editar router


    //fin de la funcion para editar router

    //get editar router
    $(document).on("click", '.editar', function (event) {
        event.stopImmediatePropagation();

        $('#mytabs').waiting({fixed: true});
        $('[name=router]').val($(this).attr('id'));
        var fdata = $('#val').serialize();
        $('#load').show();

        GetRouterEdit(fdata, 'edit');


    }); //fin editar router


    //funcion real time recuperar info router

    function ajaxInfRouter(idros) {
        $.ajax({
            "type": "POST",
            "url": "router/getinfo/data",
            "data": {"id": idros},
            "dataType": "json",
            'error': function (xhr, ajaxOptions, thrownError) {
                debug(xhr, thrownError);
            }
        }).done(function (data) {
            if (data.success) {
                $('#hardware').text(data.hardware);
                $('#os').text(data.os);
                $('#active').text(data.active);
                $('#cpu-load').text(data.cpu);
                $('#cpu').text(data.cpu2);
                $('#ram').text(data.ram);
                $('#disk').text(data.disk);
                $('#block').text(data.blocks);
            } else {
                var c = 0;
                if (c < 2) {
                    c++;
                    alert("Error al obtener datos estadísticos");
                }
            }
        });
    }

    //fin de funcion real time recuperar info router

    //info router
    $(document).on("click", ".infor", function (event) {
        event.stopImmediatePropagation();
        var idf = $(this).attr('id');
        $('#val').val(idf);
        $('#tbs a:first').tab('show') // Select first tab
        //recuperamos la info del router en tiempo real
        ajaxInfRouter(idf);
        //ejecuta ajax cada n segundos
        interval = setInterval(function () {
            ajaxInfRouter(idf);
        }, 1000); //1 seconds
    });

    $('#ifrouter').click(function () {
        idf = $('#val').val();
        ajaxInfRouter(idf);
        //ejecuta ajax cada n segundos
        interval = setInterval(function () {
            ajaxInfRouter(idf);
        }, 1000); //1 seconds

    });

    //desactivar set interval on close modal
    $('#info-router').on('hidden.bs.modal', function () {
        clearInterval(interval);
    });

    //fin lanzar set interval

    //desactivar set interval lan
    $('#tlan').click(function () {
        clearInterval(interval);
    });
    //desactivar set interval lan
    //fin de info router


    //logs del router
    $(document).on("click", "#lgs", function (event) {
        event.stopImmediatePropagation();
        var idrouter = $('#val').val();
        clearInterval(interval);

        $('#registros').DataTable({
            destroy: true,
            dom: "<'row'<'col-md-6'l><'col-md-6'Bf>><'row'<'col-sm-12'tr>><'row'<'col-sm-6'i><'col-sm-6'p>>",
            responsive: true,
            "oLanguage": {
                "sUrl": Lang.app.datatables,
                "sProcessing": $('#mytab').waiting({fixed: true})
            },
            buttons: [],
            ajax: {
                "url": "router/getlogs/log",
                "type": "POST",
                "dataSrc": "",
                complete: function () {
                    $('#mytab').waiting('done');
                }
            },
            fnServerParams: function (aoData) {
                aoData.push({"name": "id", "value": idrouter});
            },
            columns: [
                {data: 'time'},
                {data: 'topics'},
                {data: 'message'}
            ]
        });
    });
    //fin del log router

    //accesibilidad
    $('#add').on('shown.bs.modal', function () {
        $('#name').focus()
    })
    //fin de accesibilidad

    //aditional plugins
    $('.ip_address').mask('099.099.099.099');
    //end aditional plugins

    // $.ajax({
    //     "url": "users/isloginuser",
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


//google maps

    $('#modalmap').on('shown.bs.modal', function (event) {
        destroy_osm();
        event.stopImmediatePropagation();
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
                "url": "config/getconfig/defaultlocation",
                "type": "GET",
                "data": {},
                "dataType": "json",
                'error': function (xhr, ajaxOptions, thrownError) {
                    debug(xhr, thrownError);
                }
            }).done(function (data) {


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

            });//end ajax
        }


    });
    //end google maps

//mostrar mapa al editar router

    $('#modalmapedit').on('shown.bs.modal', function (event) {
        destroy_osm();
        event.stopImmediatePropagation();
        let map_type = $(this).data('map-type');
        let location_box_selector = $('#addEditModal').hasClass('in') ? '#coordinates' : '#edilocation';
        var map = validateNewPlantsForm($(location_box_selector).val());
        console.log('Hello form map edit');
        if (map != false) {

            var lat_e = map[0];
            var lon_e = map[1];

            openmap(lat_e, lon_e, 'us4', '#us4-address', location_box_selector, map_type);

        } else {

            //intentamos recuperar la información de ubicacion del router
            $.ajax({
                "url": "config/getconfig/defaultlocation",
                "type": "GET",
                "data": {},
                "dataType": "json",
                'error': function (xhr, ajaxOptions, thrownError) {
                    debug(xhr, thrownError);
                }
            }).done(function (data) {


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
                console.log('Hello from map edit before openmap');

                openmap(lat_e, lon_e, 'us4', '#us4-address', location_box_selector, map_type);

            });//end ajax
        }


    });


});//fin del ready
