jQuery( function ( $ ) {
	'use strict';

	$( '.js-ocdi-import-data' ).on( 'click', function () {

		var data = {
			'action':   'ocdi_import_data',
			'security': ocdi.ajax_nonce,
			'file':     ocdi.file
		};

		$.ajax({
			method:     'POST',
			url:        ocdi.ajax_url,
			data:       data,
			beforeSend: function() {
				$( '.js-ocdi-import-data' ).after( '<p class="js-ocdi-ajax-loader  ocdi__ajax-loader"><span class="spinner"></span> Importing now, please wait!</p>' );
			},
			complete:   function() {
				$( '.js-ocdi-ajax-loader' ).remove();
			}
		})
		.done( function( response ) {
			$( '.js-ocdi-ajax-response' ).append( response );
		})
		.fail( function( error ) {
			$( '.js-ocdi-ajax-response' ).append( error );
		});

	});

});