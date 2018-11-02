jQuery( document ).ready( function( $ ) {


	$( "#igapi-get-data" ).click(function(e) {
		e.preventDefault();

		// alert($('#igapi-test-url').val());

		var data = {
			'action': 'igapi_ajax',
			'url': $('#igapi-test-url').val()
		};

		$.post(ajaxurl, data, function(response) {
			$('#igapi-response').html('Response: <br>' + response);
		});
	});


} );