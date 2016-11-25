jQuery( function ( $ ) {
	'use strict';

	// No or Single predefined demo import button click.
	$( '.js-ocdi-import-data' ).on( 'click', function () {

		// Reset response div content.
		$( '.js-ocdi-ajax-response' ).empty();

		// Prepare data for the AJAX call
		var data = new FormData();
		data.append( 'action', 'ocdi_import_demo_data' );
		data.append( 'security', ocdi.ajax_nonce );
		data.append( 'selected', $( '#ocdi__demo-import-files' ).val() );
		if ( $('#ocdi__content-file-upload').length ) {
			data.append( 'content_file', $('#ocdi__content-file-upload')[0].files[0] );
		}
		if ( $('#ocdi__widget-file-upload').length ) {
			data.append( 'widget_file', $('#ocdi__widget-file-upload')[0].files[0] );
		}
		if ( $('#ocdi__customizer-file-upload').length ) {
			data.append( 'customizer_file', $('#ocdi__customizer-file-upload')[0].files[0] );
		}

		// AJAX call to import everything (content, widgets, before/after setup)
		ajaxCall( data );

	});


	// Grid Layout import button click.
	$( '.js-ocdi-gl-import-data' ).on( 'click', function () {

		// Reset response div content.
		$( '.js-ocdi-ajax-response' ).empty();

		// Prepare data for the AJAX call
		var data = new FormData();
		data.append( 'action', 'ocdi_import_demo_data' );
		data.append( 'security', ocdi.ajax_nonce );
		data.append( 'selected', $( this ).val() );

		// AJAX call to import everything (content, widgets, before/after setup)
		ajaxCall( data );

	});

	function ajaxCall( data ) {
		$.ajax({
			method:     'POST',
			url:        ocdi.ajax_url,
			data:       data,
			contentType: false,
			processData: false,
			beforeSend: function() {
				$( '.js-ocdi-ajax-loader' ).show();
			}
		})
		.done( function( response ) {

			if ( 'undefined' !== typeof response.status && 'newAJAX' === response.status ) {
				ajaxCall( data );
			}
			else if ( 'undefined' !== typeof response.message ) {
				$( '.js-ocdi-ajax-response' ).append( '<p>' + response.message + '</p>' );
				$( '.js-ocdi-ajax-loader' ).hide();
			}
			else {
				$( '.js-ocdi-ajax-response' ).append( '<div class="notice  notice-error  is-dismissible"><p>' + response + '</p></div>' );
				$( '.js-ocdi-ajax-loader' ).hide();
			}
		})
		.fail( function( error ) {
			$( '.js-ocdi-ajax-response' ).append( '<div class="notice  notice-error  is-dismissible"><p>Error: ' + error.statusText + ' (' + error.status + ')' + '</p></div>' );
			$( '.js-ocdi-ajax-loader' ).hide();
		});
	}

	// Switch preview images on select change event, but only if the img element .js-ocdi-preview-image exists.
	// Also switch the import notice (if it exists).
	$( '#ocdi__demo-import-files' ).on( 'change', function(){
		if ( $( '.js-ocdi-preview-image' ).length ) {

			// Attempt to change the image, else display message for missing image.
			var currentFilePreviewImage = ocdi.import_files[ this.value ]['import_preview_image_url'] || '';
			$( '.js-ocdi-preview-image' ).prop( 'src', currentFilePreviewImage );
			$( '.js-ocdi-preview-image-message' ).html( '' );

			if ( '' === currentFilePreviewImage ) {
				$( '.js-ocdi-preview-image-message' ).html( ocdi.texts.missing_preview_image );
			}
		}

		// Update import notice.
		var currentImportNotice = ocdi.import_files[ this.value ]['import_notice'] || '';
		$( '.js-ocdi-demo-import-notice' ).html( currentImportNotice );
	});

	// Grid Layout category navigation.
	$( '.js-ocdi-nav-link' ).on( 'click', function( event ) {
		event.preventDefault();

		// Remove 'active' class from the previous nav list items.
		$( this ).parent().siblings().removeClass( 'active' );

		// Add the 'active' class to this nav list item.
		$( this ).parent().addClass( 'active' );

		// Show all items if the 'hash' is equal to '#all'.
		if ( '#all' === this.hash ) {
			$( '.js-ocdi-gl-item-container' ).find( '.js-ocdi-gl-item' ).show();
		}
		else {
			// Hide all items.
			$( '.js-ocdi-gl-item-container' ).find( '.js-ocdi-gl-item' ).hide();

			// Show just the ones that have the correct category data.
			$( '.js-ocdi-gl-item-container' ).find( '.js-ocdi-gl-item[data-category="' + this.hash.slice(1) + '"]' ).show();
		}
	} );


	// Grid Layout search functionality.
	$( '.js-ocdi-mql-search' ).on( 'keyup', function( event ) {
		if ( 0 < $(this).val().length ) {
			// Hide all items.
			$( '.js-ocdi-gl-item-container' ).find( '.js-ocdi-gl-item' ).hide();

			// Show just the ones that have a match on the import name.
			$( '.js-ocdi-gl-item-container' ).find( '.js-ocdi-gl-item[data-name*="' + $(this).val().toLowerCase() + '"]' ).show();
		}
		else {
			$( '.js-ocdi-gl-item-container' ).find( '.js-ocdi-gl-item' ).show();
		}
	} );
} );
