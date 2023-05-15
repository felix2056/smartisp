// Functions for stats
$(document).ready(function (e) {

    var d = new Date();
    var year = d.getFullYear();


    $.ajax({
        "url": "stat/internet",
        "type": "GET",
        "data": {},
        "dataType": "json"
    }).done(function (data) {

        var ene = Number(data.ene);
        var feb = Number(data.feb);
        var mar = Number(data.mar);
        var abr = Number(data.abr);
        var may = Number(data.may);
        var jun = Number(data.jun);
        var jul = Number(data.jul);
        var ago = Number(data.ago);
        var sep = Number(data.sep);
        var oct = Number(data.oct);
        var nov = Number(data.nov);
        var dic = Number(data.dic);
        var money = data.money;

        /////////////////////////////





        $('#container').highcharts({
            chart: {
                type: 'column'
            },
            title: {
                text: Lang.app.monthlyIncomeFromInternetPayment+' ' + year
            },
            subtitle: {
                text: 'SmartISP'
            },
            xAxis: {
                type: 'category',
                labels: {
                    rotation: -45,
                    style: {
                        fontSize: '13px',
                        fontFamily: 'Verdana, sans-serif'
                    }
                }
            },
            yAxis: {
                min: 0,
                title: {
                    text: Lang.app.incomeIn+' (' + money + ')'
                }
            },
            legend: {
                enabled: false
            },
            tooltip: {
                pointFormat: Lang.app.raised+' <b>{point.y:.1f} ' + money + '</b>'
            },
            series: [{
                name: 'Population',
                data: [
                    ['Enero', ene],
                    ['Febrero', feb],
                    ['Marzo', mar],
                    ['Abril', abr],
                    ['Mayo', may],
                    ['Junio', jun],
                    ['Julio', jul],
                    ['Agosto', ago],
                    ['Septiembre', sep],
                    ['Octubre', oct],
                    ['Noviembre', nov],
                    ['Diciembre', dic],
                ],
                dataLabels: {
                    enabled: true,
                    rotation: -90,
                    color: '#FFFFFF',
                    align: 'right',
                    format: '{point.y:.1f}', // one decimal
                    y: 10, // 10 pixels down from the top
                    style: {
                        fontSize: '13px',
                        fontFamily: 'Verdana, sans-serif'
                    }
                }
            }]
        });
        ///////////////////////////
    }); //end ajax

    //obtenemos informacion global ingresos egresos saldo

    $.ajax({
        "url": "stat/general",
        "type": "GET",
        "data": {},
        "dataType": "json"
    }).done(function (data) {

        var in_ene = Number(data.in_ene);
        var in_feb = Number(data.in_feb);
        var in_mar = Number(data.in_mar);
        var in_abr = Number(data.in_abr);
        var in_may = Number(data.in_may);
        var in_jun = Number(data.in_jun);
        var in_jul = Number(data.in_jul);
        var in_ago = Number(data.in_ago);
        var in_sep = Number(data.in_sep);
        var in_oct = Number(data.in_oct);
        var in_nov = Number(data.in_nov);
        var in_dic = Number(data.in_dic);
        //egresos
        var ou_ene = Number(data.ou_ene);
        var ou_feb = Number(data.ou_feb);
        var ou_mar = Number(data.ou_mar);
        var ou_abr = Number(data.ou_abr);
        var ou_may = Number(data.ou_may);
        var ou_jun = Number(data.ou_jun);
        var ou_jul = Number(data.ou_jul);
        var ou_ago = Number(data.ou_ago);
        var ou_sep = Number(data.ou_sep);
        var ou_oct = Number(data.ou_oct);
        var ou_nov = Number(data.ou_nov);
        var ou_dic = Number(data.ou_dic);
        //saldo
        var sa_ene = Number(data.t_ene);
        var sa_feb = Number(data.t_feb);
        var sa_mar = Number(data.t_mar);
        var sa_abr = Number(data.t_abr);
        var sa_may = Number(data.t_may);
        var sa_jun = Number(data.t_jun);
        var sa_jul = Number(data.t_jul);
        var sa_ago = Number(data.t_ago);
        var sa_sep = Number(data.t_sep);
        var sa_oct = Number(data.t_oct);
        var sa_nov = Number(data.t_nov);
        var sa_dic = Number(data.t_dic);

        var money = data.money;

        $('#lasttwoyeras').highcharts({
            title: {
                text: Lang.app.globalEconomicInformation+' ' + year,
                x: -20 //center
            },
            subtitle: {
                text: 'SmartISP',
                x: -20
            },
            xAxis: {
                categories: ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun',
                    'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'
                ]
            },
            yAxis: {
                title: {
                    text: Lang.app.valuesExpressedIn+' (' + money + ')'
                },
                plotLines: [{
                    value: 0,
                    width: 1,
                    color: '#808080'
                }]
            },
            tooltip: {
                valueSuffix: money
            },
            legend: {
                layout: 'vertical',
                align: 'right',
                verticalAlign: 'middle',
                borderWidth: 0
            },
            series: [{
                name: Lang.app.balance,
                data: [sa_ene, sa_feb, sa_mar, sa_abr, sa_may, sa_jun, sa_jul, sa_ago, sa_sep, sa_oct, sa_nov, sa_dic],
                color: '#2F7ED8'
            }, {
                name: Lang.app.income,
                data: [in_ene, in_feb, in_mar, in_abr, in_may, in_jun, in_jul, in_ago, in_sep, in_oct, in_nov, in_dic],
                color: '#8BBC21'
            }, {
                name: Lang.app.expenses,
                data: [ou_ene, ou_feb, ou_mar, ou_abr, ou_may, ou_jun, ou_jul, ou_ago, ou_sep, ou_oct, ou_nov, ou_dic],
                color: '#910000'
            }]
        });


    }); //end ajax  // fin de obtener informacion general

    //reportes anuales
    $.ajax({
        "url": "stat/peryears",
        "type": "GET",
        "data": {},
        "dataType": "json"
    }).done(function (data) {

        var ac_in = Number(data.ac_input);
        var ac_ou = Number(data.ac_output);
        var fu_in = Number(data.fu_input);
        var fu_ou = Number(data.fu_output);
        var ac_year = Number(data.ac_year);
        var fu_year = Number(data.fu_year);

        var money = data.money;

        $('#years').highcharts({
            chart: {
                type: 'bar'
            },
            title: {
                text: Lang.app.economicInformationTotalEarningsAndExpensesPerYear
            },
            subtitle: {
                text: 'SmartISP'
            },
            xAxis: {
                categories: [ac_year, fu_year],
                title: {
                    text: null
                }
            },
            yAxis: {
                min: 0,
                title: {
                    text: Lang.app.valuesExpressedIn+' (' + money + ')',
                    align: 'high'
                },
                labels: {
                    overflow: 'justify'
                }
            },
            tooltip: {
                valueSuffix: money
            },
            plotOptions: {
                bar: {
                    dataLabels: {
                        enabled: true
                    }
                }
            },
            legend: {
                layout: 'horizontal',
                align: 'center',
                verticalAlign: 'middle',
                x: -40,
                y: 80,
                floating: true,
                borderWidth: 1,
                backgroundColor: ((Highcharts.theme && Highcharts.theme.legendBackgroundColor) || '#FFFFFF'),
                shadow: true
            },
            credits: {
                enabled: false
            },
            series: [{
                name: Lang.app.expenses,
                data: [ac_ou, fu_ou]
            }, {
                name: Lang.app.earnings,
                data: [ac_in, fu_in]
            }]
        });
    }); //end ajax
    //fin de reportes anuales

    //fin del ready
});
