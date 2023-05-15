// Dash Board
$(document).ready(function(e) {


$('#bills,#notifier,#chats').hide();

function ajaxstats(){
	$.ajax({
		  "type":"POST",
		  "url":"notifications/data",
		  "data":{},
		  "dataType":"json"
	  }).done(function(data){
		  if(data.success){
			  var num = 0;

			  if(data.tickets>0){
				  $('#tickets,#numTickets').show().text(data.tickets);
				  num++;
			  }
			  else{
				 $('#tickets').hide();
			  }

			  if(data.bills>0){
				  $('#bills,#numBills').show().text(data.bills);
				  num++;
			  }
			  else{
				  $('#bills').hide();
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

	  //metodo para
}

ajaxstats();
//ejecuta ajax cada 7 segundos
setInterval(function() {
   ajaxstats();
}, 6000); //6 seconds

//fin de ready
});
