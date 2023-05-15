// Dash Board
$(document).ready(function(e) {


//metod para cerrar la notificacion del sistema

$(document).on("click","#notifybtnclose",function(){

	$.ajax({
		  "type":"POST",
		  "url":"user/notifications/ntclose",
		  "data":{},
		  "dataType":"json"
	  }).done(function(data){
		  if(data.success!=true){ //redirect to home
		     alert("Error al procesar la petición");
		  }
	  }); //end ajax
});



$('#tickets,#routers,#notifier,#errRouter,#infolicence').hide();

function ajaxstats(){
	$.ajax({
		  "type":"POST",
		  "url":baseUrl+"/user/notifications/data",
		  "data":{},
		  "dataType":"json"
	  }).done(function(data){
		  if(data.success){
			  var num = 0;
			  //for licence info icon
			  if(data.license=='ex'){
				$('#infolicence').attr('title','Su licencia ha expirado.').show();
			  }

			  if(data.license=='bl'){
				$('#infolicence').attr('title','Su licencia ha sido cancelada.').show();
			  }

			  if(data.license=='ac'){
				$('#infolicence').hide();
			  }

			  if(data.tickets>0){
				  $('#tickets,#numTickets').show().text(data.tickets);
				  num++;
			  }
			  else{
				 $('#tickets').hide();
			  }

			  if(data.trouters>0){
				  $('#numRouters').show().text(data.trouters);
				  num++;
			  }
			  else{
				  $('#routers').hide();
			  }

			  if (data.tsms>0) {
			  	$('#numSms,#sms').show().text(data.tsms);
			  	num++;
			  }
			  else{
			  	$('#sms').hide();
			  }

			  if(data.chats>0){
				  $('#chats,#numChats').show().text(data.chats);
				  num++;
			  }
			  else{
				  $('#chats').hide();
			  }

			  if(num>0){
				$('#notifier').show('fast');
				if(num>1){
					var ms = ' Notificaciones';
				}else{
					var ms = ' Notificación';
				}
				$('#numNoti').text(num);
				$('#numNotifi').text(num+ms);
			  }
			  else{
				$('#notifier').hide('fast');
			  }


		  }

	  });

	//   //metodo para
}

ajaxstats();
//ejecuta ajax cada 7 segundos
// setInterval(function() {
//    ajaxstats();
// }, 6000); //6 seconds

//fin de ready
});
