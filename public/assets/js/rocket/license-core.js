// License Core - Funciones principales JQuery para licencia
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
///// funcion de depuracion
    function debug(xhr, thrownError) {
        $.ajax({
            "url": "config/getconfig/debug",
            "type": "GET",
            "data": {},
            "dataType": "json"
        }).done(function (deb) {

            if (deb.debug == '1') {
                msg('Error ' + xhr.status + ' ' + thrownError + ' ' + xhr.responseText, 'debug');
            } else
                alert(Lang.messages.aninternalerrorhasoccurredformoredetailtalktothedebugmode);
        });
    }


//// fin de la funcion de depuracion

//funcion para recuperar detalles de la licencia

    $("#productname,#versionname,#expires,#numpc,#registered,#emailreg,#loadinfo").hide();

    $('#faq-1-2').on('hidden.bs.collapse', function () {
        $(".lsdt>li").hide();
    });

    $(".lsdt>li").hide();

    $('#faq-1-2').on('show.bs.collapse', function (event) {
        event.stopImmediatePropagation();
        $("#loadinfo").show();
        $.ajax({
            "url": "license/details",
            "type": "POST",
            "data": {},
            "dataType": "json"

        }).done(function (data) {
            //Mesajes personalizados

            //fin de mensajes personalizados
            if (data.success == true) {

                $('#productname').text(data.product).show();
                $('#versionname').text(data.version).show();
                $('#expires').text(data.expires).show();
                $('#numpc').text(data.numpc + ' PC').show();
                $('#registered').text(data.registered).show();
                $('#emailreg').text(data.email_register).show();
                $('#cli').text(data.clientes_cant).show();
                $("#loadinfo").hide();
                $('.lsdt>li').show();


            }


        });

    });

    $("#btnActivelicensia").click(function (event) {
        event.stopImmediatePropagation();
        $("#btnActivelicensia").text('Validando...');
        var licencia = $("#licencia").val();
        if (licencia.length > 0) {
            $("#loadinfo").show();
            $.ajax({
                "url": "license/activate",
                "type": "POST",
                "data": {'licencia': licencia},
                "dataType": "json"

            }).done(function (data) {
                $("#btnActivelicensia").text('Activar');
                if (data.status == 200) {
                    $("#loadinfo").hide();
                    $('#estado_lic').html('<span class="label label-success"> Activado </span>');
                    $('#st').html('<span class="label label-success"> Activado </span>');
                    var clase = 'gritter-success';
                    var tit = Lang.app.registered;
                    var img = 'assets/img/ok.png';
                    var stincky = false;
                    $.gritter.add({
                        title: tit,
                        text: data.memssage,
                        image: img,
                        sticky: stincky,
                        class_name: clase
                    });

                } else {

                    $("#loadinfo").hide();
                    var clase = 'gritter-error';
                    var tit = Lang.app.error;
                    var img = 'assets/img/error.png';
                    var stincky = false;
                    $.gritter.add({
                        title: tit,
                        text: data.memssage,
                        image: img,
                        sticky: stincky,
                        class_name: clase
                    });
                }


            });

        } else {
            alert('La licencia es requerida');
        }

    });


    //fin del ready
});
