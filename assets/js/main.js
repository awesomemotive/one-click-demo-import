jQuery( function ( $ ) {
	'use strict';

	$( '.js-ocdi-import-data' ).on( 'click', function () {

		// Reset response div content.
		$( '.js-ocdi-ajax-response' ).empty();

		// Data for AJAX call.
		var data = {
			'action':    'ocdi_import_demo_data',
			'security':  ocdi.ajax_nonce,
			'selected':  $( '#demo-import-files' ).val()
		};

		// AJAX call.
		$.ajax({
			method:     'POST',
			url:        ocdi.ajax_url,
			data:       data,
			beforeSend: function() {
				$( '.js-ocdi-import-data' ).after( '<p class="js-ocdi-ajax-loader  ocdi__ajax-loader"><span class="spinner"></span>' + ocdi.loader_text + '</p>' );
			},
			complete:   function() {
				$( '.js-ocdi-ajax-loader' ).hide( 500, function(){ $( '.js-ocdi-ajax-loader' ).remove(); } );
			}
		})
		.done( function( response ) {
			if ( 'undefined' !== typeof response.message ) {
				$( '.js-ocdi-ajax-response' ).append( '<p>' + response.message + '</p>' );
			}
			else {
				$( '.js-ocdi-ajax-response' ).append( '<div class="error  below-h2"><p>' + response + '</p></div>' );
			}
		})
		.fail( function( error ) {
			$( '.js-ocdi-ajax-response' ).append( '<div class="error  below-h2"> Error: ' + error.statusText + ' (' + error.status + ')' + '</div>' );
		});

	});

});