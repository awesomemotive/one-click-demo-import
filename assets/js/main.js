jQuery( function ( $ ) {
	'use strict';

	$( '.js-ocdi-import-data' ).on( 'click', function () {

		var data = {
			'action':    'ocdi_prepare_import_data',
			'security':  ocdi.ajax_nonce,
			'selected':  $( '#demo-import-files' ).val()
		};

		$.ajax({
			method:     'POST',
			url:        ocdi.ajax_url,
			data:       data,
			beforeSend: function() {
				$( '.js-ocdi-import-data' ).after( '<p id="js-ocdi-loader-1" class="js-ocdi-ajax-loader  ocdi__ajax-loader"><span class="spinner"></span> Importing now, please wait!</p>' );
			},
			complete:   function() {
				$( '#js-ocdi-loader-1' ).hide();
			}
		})
		.done( function( response ) {
			if ( 'undefined' !== typeof response.import_file_paths ) {
				$( '.js-ocdi-ajax-response' ).append( '<p>' + response.message + '</p>' );
				import_demo_data( response.import_file_paths );
			}
			else {
				$( '.js-ocdi-ajax-response' ).append( '<p>' + response + '</p>' );
			}
		})
		.fail( function( error ) {
			console.log( error );
			$( '.js-ocdi-ajax-response' ).append( '<div class="error  below-h2"> Error: ' + error.statusText + ' (' + error.status + ')' + '</div>' );
		});

	});


	function import_demo_data( import_file_paths ) {

		var importData = {
			'action':            'ocdi_import_data',
			'security':          ocdi.ajax_nonce,
			'import_file_paths': import_file_paths
		};

		$.ajax({
			method:     'POST',
			url:        ocdi.ajax_url,
			data:       importData,
			beforeSend: function() {
				$( '.js-ocdi-ajax-response' ).after( '<p id="js-ocdi-loader-2" class="js-ocdi-ajax-loader  ocdi__ajax-loader"><span class="spinner"></span> Importing now, please wait!</p>' );
			},
			complete:   function() {
				$( '#js-ocdi-loader-2' ).hide();
			}
		})
		.done( function( response ) {
			if ( 'undefined' !== typeof response.import_file_paths ) {
				$( '.js-ocdi-ajax-response' ).append( '<p>' + response.message + '</p>' );
				import_widgets( response.import_file_paths );
			}
			else if ( 'undefined' !== typeof response.message ) {
				$( '.js-ocdi-ajax-response' ).append( '<p>' + response.message + '</p>' );
			}
			else {
				$( '.js-ocdi-ajax-response' ).append( '<p>' + response + '</p>' );
			}
		})
		.fail( function( error ) {
			console.log( error );
			$( '.js-ocdi-ajax-response' ).append( '<div class="error  below-h2"> Error: ' + error.statusText + ' (' + error.status + ')' + '</div>' );
		});
	}


	function import_widgets( import_file_paths ) {

		var importData = {
			'action':            'ocdi_import_widgets',
			'security':          ocdi.ajax_nonce,
			'import_file_paths': import_file_paths
		};

		$.ajax({
			method:     'POST',
			url:        ocdi.ajax_url,
			data:       importData,
			beforeSend: function() {
				$( '.js-ocdi-ajax-response' ).after( '<p id="js-ocdi-loader-3" class="js-ocdi-ajax-loader  ocdi__ajax-loader"><span class="spinner"></span> Importing now, please wait!</p>' );
			},
			complete:   function() {
				$( '#js-ocdi-loader-3' ).hide( 500, function(){ $( '.js-ocdi-ajax-loader' ).remove(); } );
			}
		})
		.done( function( response ) {
			$( '.js-ocdi-ajax-response' ).append( '<p>' + response + '</p>' );
		})
		.fail( function( error ) {
			console.log( error );
			$( '.js-ocdi-ajax-response' ).append( '<div class="error  below-h2"> Error: ' + error.statusText + ' (' + error.status + ')' + '</div>' );
		});
	}

});