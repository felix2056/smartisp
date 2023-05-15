// Dash Board
$(document).ready(function (e) {

//tabla ultimos logs

    var treload = $('#LastTickets').DataTable({
        "oLanguage": {
            "sUrl": Lang.app.datatables
        },
        bAutoWidth: false,
        dom: "<'row'<'col-md-6'l><'col-md-6'Bf>><'row'<'col-sm-12'tr>><'row'<'col-sm-6'i><'col-sm-6'p>>",
        processing: true,
        serverSide: true,
        pageLength: '10',
        responsive: true,
        destroy: true,
        buttons:[],
        "paging": false,
        "searching": false,
        "info": false,
        "ordering": false,
        //destroy: true,
        ajax: {
            "url": "portal/stats/lasttickets",
            "type": "POST",
            "cache": false,
            "dataSrc": ""
        },
        columns: [
            {data: 'section'},
            {data: 'subject'},
            {
                "mRender": function (data, type, full) {
                    if (full['status'] == 'work_in_progress') {
                        return '<span class="badge badge-success">Abierto</span>';
                    } else {
                        return '<span class="badge badge-primary">Cerrado</span>';
                    }
                }
            },
            {data: 'created_at'}
        ]
    });

// fin de tabla ultimos tickets


    function ajaxstats() {
        $.ajax({
            "type": "POST",
            "url": "portal/stats/data",
            "data": {},
            "dataType": "json"
        }).done(function (data) {
            if (data.success) {
                $('#stTicket').text(data.ntickets);
                $('#stBill').text(data.nbills);
                $('#stUnpayed').text(data.nnopayed);

            } else {
                var c = 0;
                if (c < 2) {
                    c++;
                    alert("Error al obtener datos estadísticos");
                }
            }
        });

        //metodo para
    }

    ajaxstats();
//ejecuta ajax cada 7 segundos
    setInterval(function () {
        ajaxstats();
        treload.ajax.reload();
    }, 7000); //5 seconds

//fin de ready
});
