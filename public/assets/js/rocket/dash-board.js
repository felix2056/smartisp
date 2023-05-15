// Dash Board
$(document).ready(function(e) {

//tabla ultimos logs

var treload = $('#last-logs').DataTable({
    destroy: true,
	"oLanguage": {
		"sUrl": Lang.app.datatables
	},
	bAutoWidth: false,
	responsive:true,
	buttons:[],
	"paging": false,
	"searching": false,
	"info": false,
	"ordering": false,
	 //destroy: true,
	 "columnDefs": [ {
	 	"targets": 3,
	 	"render": function ( data, type, full ) {
	 		if(full['type']=='info')
	 			return '<span class="label label-info arrowed">'+Lang.app.info+'</span>';
	 		if(full['type']=='danger')
	 			return '<span class="label label-danger">'+Lang.app.Important+'</span>';
	 		if(full['type']=='success')
	 			return '<span class="label label-success">'+Lang.app.new+'</span>';
	 		if(full['type']=='change')
	 			return '<span class="label label-warning">'+Lang.app.Changes+'</span>';
	 		else
	 			return '<span class="label label-success">'+Lang.app.new+'</span>';
	 	}
	 }
	 ],
	 ajax: {
	 	"url": "stats/logs",
	 	"type": "POST",
	 	"cache":false,
	 	"dataSrc": ""
	 },
	 columns: [
	 { data: 'detail' },
	 { data: 'user' },
	 { data: 'created_at' }
	 ]
	} );
// fin de tabla logs

function ajaxstats(){
	$.ajax({
		"type":"POST",
		"url":"stats/data",
		"data":{},
		"dataType":"json"
	}).done(function(data){
		if(data.success){

			$('#stClient').text(data.nclients);
			$('#stPlan').text(data.nplans);
			$('#stRouter').text(data.nrouters);
			$('#stUser').text(data.nusers);
			$('#stUserBan').text(data.nusersban);
			$('#stClientBan').text(data.nclientsban);
			$('#stTicket').text(data.ntickets);
		}
		else{
			var c = 0;
			if(c<2){
				c++;
				alert(Lang.messages.ErrorObtainingStatisticalData);
			}
		}
	});

	  //metodo para
	}


//ejecuta ajax cada 7 segundos
setInterval(function() {
	ajaxstats();
	treload.ajax.reload();
}, 7000); //5 seconds


// $.ajax({
// 	"url": "stat/payed",
// 	"type":"GET",
// 	"data":{},
// 	"dataType":"json"
// }).done(function(data){
//
// 	var prpayed =  data.prepay;
// 	var payed = data.payed;
// 	var money = data.money;
//
// 	$('#prpay').text(prpayed+' '+money);
// 	$('#pay').text(payed+' '+money);
//
// 	var placeholder = $('#piechart-placeholder').css({'width':'90%' , 'min-height':'150px'});
// 	var data = [
// 	{ label: Lang.app.income,  data: payed, color: "#68BC31"},
// 	{ label: Lang.app.expenses,  data: prpayed, color: "#BE0000"}
//
// 	]
// 	function drawPieChart(placeholder, data, position) {
// 		$.plot(placeholder, data, {
// 			series: {
// 				pie: {
// 					show: true,
// 					tilt:0.8,
// 					highlight: {
// 						opacity: 0.25
// 					},
// 					stroke: {
// 						color: '#fff',
// 						width: 2
// 					},
// 					startAngle: 2
// 				}
// 			},
// 			legend: {
// 				show: true,
// 				position: position || "ne",
// 				labelBoxBorderColor: null,
// 				margin:[-30,15]
// 			}
// 			,
// 			grid: {
// 				hoverable: true,
// 				clickable: true
// 			}
// 		})
// 	}
// 	drawPieChart(placeholder, data);
//
// 			 /**
// 			 we saved the drawing function and the data to redraw with different position later when switching to RTL mode dynamically
// 			 so that's not needed actually.
// 			 */
// 			 placeholder.data('chart', data);
// 			 placeholder.data('draw', drawPieChart);
//
// 			 //pie chart tooltip example
// 			 var $tooltip = $("<div class='tooltip top in'><div class='tooltip-inner'></div></div>").hide().appendTo('body');
// 			 var previousPoint = null;
//
// 			 placeholder.on('plothover', function (event, pos, item) {
// 			 	if(item) {
// 			 		if (previousPoint != item.seriesIndex) {
// 			 			previousPoint = item.seriesIndex;
// 			 			var tip = item.series['label'] + " : " + item.series['percent']+money;
// 			 			$tooltip.show().children(0).text(tip);
// 			 		}
// 			 		$tooltip.css({top:pos.pageY + 10, left:pos.pageX + 10});
// 			 	} else {
// 			 		$tooltip.hide();
// 			 		previousPoint = null;
// 			 	}
//
// 			 });
//
// });//end ajax

//execution aditional ajax//

//ajax for load sms inbox
$.ajax({
	"url":"sms/inbox",
	"type":"POST",
	"data":{},
	"dataType":"json"
});
//ajax for check clients online
$.ajax({
	"url":"crnc31hy55t",
	"type":"GET",
	"data":{},
	"dataType":"json"
});

//fin del ready
});
