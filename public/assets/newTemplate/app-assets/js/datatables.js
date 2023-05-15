
$(document).ready(function(){
	var btcPrice=0;
	getValueBtc_2();
	$('#DataTables_Table_direct').DataTable( {

		"searching": false,
		"ordering": false,
		ajax: {
			url: '/api/getDirects',
			dataSrc: 'data',		
			dataFilter: function(data){

				var json = jQuery.parseJSON( data );
				
				json.recordsTotal = json.total;
				json.recordsFiltered = json.total;
				

						return JSON.stringify( json ); // return JSON string
					}
				},

				columns: [			

				{ data: 'username' },
				{ data: 'first_name' },
				{ data: 'last_name' },
				{ data: 'country_master_name' },
				{ data: 'name' },
				{ data: 'date_register' },

				
				],

				
			} );

	function getValueBtc_2(){
		$.get('https://bitpay.com/api/rates/BTC/USD')
		.done(function( data ) {    
			btcPrice=data.rate;
			// var val = parseInt(price_usd)/parseInt(data.rate);
			// val=val.toFixed(6);
			// $('#amount_pay_btc').text(val);
		});
	}

	$('#DataTables_Table_earning').DataTable( {

		"searching": false,
		"ordering": false,
		ajax: {
			url: '/api/getEarnings',
			dataSrc: 'data',		
			dataFilter: function(data){

				var json = jQuery.parseJSON( data );
				
				json.recordsTotal = json.total;
				json.recordsFiltered = json.total;
				

						return JSON.stringify( json ); // return JSON string
					}
				},

				columns: [

				{ 
					data: 'amount',
					render: function(data, type, row, meta){
						let amountBtc=parseInt(data)/parseInt(btcPrice);
						return "<p style='color:green'> +"+data+' USD ('+amountBtc.toFixed(7)+" BTC)</p>"
					} 
				},

				{ data: 'detail' },
				{ data: 'date_create' },

				
				],

				
			} );

	$('#DataTables_Table_0').DataTable( {

		"searching": false,
		"ordering": false,
		render: function(data, type, row){
			console.log("Habla11" + row  + data)

		},

		ajax: {
			url: '/api/getHistory',
			dataSrc: 'data',		
			dataFilter: function(data){

				var json = jQuery.parseJSON( data );
				
				json.recordsTotal = json.total;
				json.recordsFiltered = json.total;
				

						return JSON.stringify( json ); // return JSON string
					}
				},

				columns: [

				{ 
					data: 'amount',
					render: function(data, type, row, meta){

						let amountBtc=parseInt(data)/parseInt(btcPrice);
						if(row.transaction_type_id == "1"){
							return "<p style='color:green'> +"+data+' USD ('+amountBtc.toFixed(7)+" BTC)</p>"
						}

						if(row.transaction_type_id == "2"){
							
							return "<p style='color:red'>"+data+' USD ('+amountBtc.toFixed(7)+" BTC)</p>"
						}
					} 
				},

				{ 
					data: 'current_balance',
					render: function( data, type, row, meta ){
						let amountBtc=parseInt(data)/parseInt(btcPrice);
						return "<p> "+data+' USD ('+amountBtc.toFixed(7)+" BTC)</p>"
					} 
				},
				{ data: 'detail' },
				{ 
					data: "transaction_type_id",
					render: function( data, type, row, meta ){
						if(data == "1"){
							return "Earning"
						}
						else{
							return "Spending"
						}
					}
				},
				{ data: 'date_create' },
				],





			} );

})

