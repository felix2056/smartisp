// Trafic Graphics Client


function formatBytes(a,b){if(0==a)return"0 Bytes";var c=1024,d=b||2,e=["Bytes","KB","MB","GB","TB","PB","EB","ZB","YB"],f=Math.floor(Math.log(a)/Math.log(c));return parseFloat((a/Math.pow(c,f)).toFixed(d))+" "+e[f]}

$('#traf').click(function(e){

var namecl = $('#namecl').val();
var chart;

	function requestDatta(id) {
		$.ajax({
			url: baseUrl+'/client/getservice/trafic',
			type:"POST",
			data:{id:id},
			datatype: "json",
			success: function(data) {
				var midata = JSON.parse(data);
				if( midata.length > 0 ) {
					var TX=parseInt(midata[0].data);
					var RX=parseInt(midata[1].data);
					var x = (new Date()).getTime();
					shift=chart.series[0].data.length > 19;
					chart.series[0].addPoint([x, TX], true, shift);
					chart.series[1].addPoint([x, RX], true, shift);
					document.getElementById("trafico").innerHTML="<span style='color:#058DC7'>"+formatBytes(TX) + "</span> / <span style='color:#50B432'>" + formatBytes(RX)+"</span>";
				}else{
					document.getElementById("trafico").innerHTML="- / -";
				}
			},
			cache: false,
			error: function(XMLHttpRequest, textStatus, errorThrown) {
				console.error("Status: " + textStatus + " request: " + XMLHttpRequest); console.error("Error: " + errorThrown);
			}
		});
	}

			Highcharts.createElement('link', {
			    href: 'https://fonts.googleapis.com/css?family=Signika:400,700',
			    rel: 'stylesheet',
			    type: 'text/css'
			}, null, document.getElementsByTagName('head')[0]);

			// Add the background image to the container
			Highcharts.wrap(Highcharts.Chart.prototype, 'getContainer', function (proceed) {
			    proceed.call(this);
			    this.container.style.background =
			        'url(https://www.highcharts.com/samples/graphics/sand.png)';
			});


			Highcharts.setOptions({
				global: {
					useUTC: false
				}
			});

            chart = new Highcharts.Chart({
			   chart: {
			   	plotOptions: {
			        areaspline: {
			            fillOpacity: 0.5
			        }
			    },
				renderTo: 'tlan',
				animation: Highcharts.svg,
				type: 'areaspline',
				events: {
					load: function () {
					trafcl = setInterval(function () {
							requestDatta($('#clid').val());
						}, 1000);
					}
			}
		 },
		 title: {
			text: 'Tráfico - '+namecl
		 },
		 xAxis: {
			type: 'datetime',
				tickPixelInterval: 150,
				maxZoom: 20 * 1000
		 },
		 yAxis: {
			minPadding: 0.2,
				maxPadding: 0.2,
				title: {
					text: 'Tráfico actual',
					margin: 25
				}
		 },
            series: [{
                name: 'Descarga (Down)',
                data: []
            }, {
                name: 'Subida (Up)',
                data: []
            }]
	  });


  $('#tools').on('hidden.bs.modal', function () {
  		clearInterval(trafcl);
  });

  $('#pin,#torc').click(function(event) {
		clearInterval(trafcl);
  });

});
// End TRafic Graphics LAN
