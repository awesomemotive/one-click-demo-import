jQuery( function ( $ ) {
	'use strict';

	/**
	 * ---------------------------------------
	 * ------------- Events ------------------
	 * ---------------------------------------
	 */

	/**
	 * No or Single predefined demo import button click.
	 */
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
		if ( $('#ocdi__redux-file-upload').length ) {
			data.append( 'redux_file', $('#ocdi__redux-file-upload')[0].files[0] );
			data.append( 'redux_option_name', $('#ocdi__redux-option-name').val() );
		}

		// AJAX call to import everything (content, widgets, before/after setup)
		ajaxCall( data );

	});


	/**
	 * Grid Layout import button click.
	 */
	$( '.js-ocdi-gl-import-data' ).on( 'click', function () {
		var selectedImportID = $( this ).val();
		var $itemContainer   = $( this ).closest( '.js-ocdi-gl-item' );

		// If the import confirmation is enabled, then do that, else import straight away.
		if ( ocdi.import_popup ) {
			displayConfirmationPopup( selectedImportID, $itemContainer );
		}
		else {
			gridLayoutImport( selectedImportID, $itemContainer );
		}
	});

	$( document ).on( 'ready', function() {
		$( '.js-ocdi-notice-wrapper' ).appendTo( '.js-ocdi-admin-notices-container' );
	} );

	/**
	 * Prevent a required plugin checkbox from changeing state.
	 */
	$( '.ocdi-install-plugins-content-content .plugin-item.plugin-item--required input[type=checkbox]' ).on( 'click', function( event ) {
		event.preventDefault();

		return false;
	} );

	/**
	 * Install plugins event.
	 */
	$( '.js-ocdi-install-plugins' ).on( 'click', function( event ) {
		event.preventDefault();

		var $button = $( this );

		if ( $button.hasClass( 'ocdi-button-disabled' ) ) {
			return false;
		}

		var pluginsToInstall = $( '.ocdi-install-plugins-content-content .plugin-item input[type=checkbox]' ).serializeArray();

		if ( pluginsToInstall.length === 0 ) {
			return false;
		}

		$button.addClass( 'ocdi-button-disabled' );

		installPluginsAjaxCall( pluginsToInstall, 0, $button, false );
	} );

	/**
	 * Install plugins before importing event.
	 */
	$( '.js-ocdi-install-plugins-before-import' ).on( 'click', function( event ) {
		event.preventDefault();

		var $button = $( this );

		if ( $button.hasClass( 'ocdi-button-disabled' ) ) {
			return false;
		}

		var pluginsToInstall = $( '.ocdi-install-plugins-content-content .plugin-item:not(.plugin-item--disabled) input[type=checkbox]' ).serializeArray();

		if ( pluginsToInstall.length === 0 ) {
			startImport( getUrlParameter( 'import' ) );

			return false;
		}

		$button.addClass( 'ocdi-button-disabled' );

		installPluginsAjaxCall( pluginsToInstall, 0, $button, true );
	} );


	/**
	 * Import the created content.
	 */
	$( '.js-ocdi-create-content' ).on( 'click', function( event ) {
		event.preventDefault();

		var $button = $( this );

		if ( $button.hasClass( 'ocdi-button-disabled' ) ) {
			return false;
		}

		var itemsToImport = $( '.ocdi-create-content-content .content-item input[type=checkbox]' ).serializeArray();

		if ( itemsToImport.length === 0 ) {
			return false;
		}

		$button.addClass( 'ocdi-button-disabled' );

		createDemoContentAjaxCall( itemsToImport, 0, $button );
	} );


	/**
	 * Install the SeedProd plugin.
	 */
	$( '.js-ocdi-install-coming-soon-plugin' ).on( 'click', function( event ) {
		event.preventDefault();

		var $button = $( this ),
			slug = 'coming-soon';

		if ( $button.hasClass( 'ocdi-button-disabled' ) ) {
			return false;
		}

		$button.addClass( 'ocdi-button-disabled' );

		$.ajax({
			method:      'POST',
			url:         ocdi.ajax_url,
			data:        {
				action: 'ocdi_install_plugin',
				security: ocdi.ajax_nonce,
				slug: slug,
			},
			beforeSend: function() {
				$button.text( ocdi.texts.installing );
			}
		})
			.done( function( response ) {
				if ( response.success ) {
					alert( ocdi.texts.successfully_installed );
					$button.text( ocdi.texts.installed );
				} else {
					alert( response.data );
					$button.removeClass( 'ocdi-button-disabled' );
				}
			})
			.fail( function( error ) {
				alert( error.statusText + ' (' + error.status + ')' );
				$button.removeClass( 'ocdi-button-disabled' );
			})
	} );

	/**
	 * Update "plugins to be installed" notice on Create Demo Content page.
	 */
	$( document ).on( 'change', '.ocdi--create-content .content-item input[type=checkbox]', function( event ) {
		var $checkboxes = $( '.ocdi--create-content .content-item input[type=checkbox]' ),
			$missingPluginNotice = $( '.js-ocdi-create-content-install-plugins-notice' ),
			missingPlugins = [];

		$checkboxes.each( function() {
			var $checkbox = $( this );
			if ( $checkbox.is( ':checked' ) ) {
				missingPlugins = missingPlugins.concat( getMissingPluginNamesFromImportContentPageItem( $checkbox.data( 'plugins' ) ) );
			}
		} );

		missingPlugins = missingPlugins.filter( onlyUnique ).join( ', ' );

		if ( missingPlugins.length > 0 ) {
			$missingPluginNotice.find( '.js-ocdi-create-content-install-plugins-list' ).text( missingPlugins );
			$missingPluginNotice.show();
		} else {
			$missingPluginNotice.find( '.js-ocdi-create-content-install-plugins-list' ).text( '' );
			$missingPluginNotice.hide();
		}
	} );


	/**
	 * Grid Layout categories navigation.
	 */
	(function () {
		// Cache selector to all items
		var $items = $( '.js-ocdi-gl-item-container' ).find( '.js-ocdi-gl-item' ),
			fadeoutClass = 'ocdi-is-fadeout',
			fadeinClass = 'ocdi-is-fadein',
			animationDuration = 200;

		// Hide all items.
		var fadeOut = function () {
			var dfd = jQuery.Deferred();

			$items
				.addClass( fadeoutClass );

			setTimeout( function() {
				$items
					.removeClass( fadeoutClass )
					.hide();

				dfd.resolve();
			}, animationDuration );

			return dfd.promise();
		};

		var fadeIn = function ( category, dfd ) {
			var filter = category ? '[data-categories*="' + category + '"]' : 'div';

			if ( 'all' === category ) {
				filter = 'div';
			}

			$items
				.filter( filter )
				.show()
				.addClass( 'ocdi-is-fadein' );

			setTimeout( function() {
				$items
					.removeClass( fadeinClass );

				dfd.resolve();
			}, animationDuration );
		};

		var animate = function ( category ) {
			var dfd = jQuery.Deferred();

			var promise = fadeOut();

			promise.done( function () {
				fadeIn( category, dfd );
			} );

			return dfd;
		};

		$( '.js-ocdi-nav-link' ).on( 'click', function( event ) {
			event.preventDefault();

			// Remove 'active' class from the previous nav list items.
			$( this ).parent().siblings().removeClass( 'active' );

			// Add the 'active' class to this nav list item.
			$( this ).parent().addClass( 'active' );

			var category = this.hash.slice(1);

			// show/hide the right items, based on category selected
			var $container = $( '.js-ocdi-gl-item-container' );
			$container.css( 'min-width', $container.outerHeight() );

			var promise = animate( category );

			promise.done( function () {
				$container.removeAttr( 'style' );
			} );
		} );
	}());


	/**
	 * Grid Layout search functionality.
	 */
	$( '.js-ocdi-gl-search' ).on( 'keyup', function( event ) {
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

	/**
	 * ---------------------------------------
	 * --------Helper functions --------------
	 * ---------------------------------------
	 */

	/**
	 * Redirect to the import page for the theme plugin installation step.
	 *
	 * @param int selectedImportID The selected import ID.
	 * @param obj $itemContainer The jQuery selected item container object.
	 */
	function gridLayoutImport( selectedImportID, $itemContainer ) {
		window.location.href = ocdi.import_page_url + '&import=' + selectedImportID;
	}

	/**
	 * Display the confirmation popup.
	 *
	 * @param int selectedImportID The selected import ID.
	 * @param obj $itemContainer The jQuery selected item container object.
	 */
	function displayConfirmationPopup( selectedImportID, $itemContainer ) {
		var $dialogContiner         = $( '#js-ocdi-modal-content' );
		var currentFilePreviewImage = ocdi.import_files[ selectedImportID ]['import_preview_image_url'] || ocdi.theme_screenshot;
		var previewImageContent     = '';
		var importNotice            = ocdi.import_files[ selectedImportID ]['import_notice'] || '';
		var importNoticeContent     = '';
		var dialogOptions           = $.extend(
			{
				'dialogClass': 'wp-dialog',
				'resizable':   false,
				'height':      'auto',
				'modal':       true
			},
			ocdi.dialog_options,
			{
				'buttons':
				[
					{
						text: ocdi.texts.dialog_no,
						click: function() {
							$(this).dialog('close');
						}
					},
					{
						text: ocdi.texts.dialog_yes,
						class: 'button  button-primary',
						click: function() {
							$(this).dialog('close');
							gridLayoutImport( selectedImportID, $itemContainer );
						}
					}
				]
			});

		if ( '' === currentFilePreviewImage ) {
			previewImageContent = '<p>' + ocdi.texts.missing_preview_image + '</p>';
		}
		else {
			previewImageContent = '<div class="ocdi__modal-image-container"><img src="' + currentFilePreviewImage + '" alt="' + ocdi.import_files[ selectedImportID ]['import_file_name'] + '"></div>'
		}

		// Prepare notice output.
		if( '' !== importNotice ) {
			importNoticeContent = '<div class="ocdi__modal-notice  ocdi__demo-import-notice">' + importNotice + '</div>';
		}

		// Populate the dialog content.
		$dialogContiner.prop( 'title', ocdi.texts.dialog_title );
		$dialogContiner.html(
			'<p class="ocdi__modal-item-title">' + ocdi.import_files[ selectedImportID ]['import_file_name'] + '</p>' +
			previewImageContent +
			importNoticeContent
		);

		// Display the confirmation popup.
		$dialogContiner.dialog( dialogOptions );
	}

	/**
	 * The main AJAX call, which executes the import process.
	 *
	 * @param FormData data The data to be passed to the AJAX call.
	 */
	function ajaxCall( data ) {
		$.ajax({
			method:      'POST',
			url:         ocdi.ajax_url,
			data:        data,
			contentType: false,
			processData: false,
			beforeSend:  function() {
				$( '.js-ocdi-install-plugins-content' ).hide();
				$( '.js-ocdi-importing' ).show();
			}
		})
		.done( function( response ) {
			if ( 'undefined' !== typeof response.status && 'newAJAX' === response.status ) {
				ajaxCall( data );
			}
			else if ( 'undefined' !== typeof response.status && 'customizerAJAX' === response.status ) {
				// Fix for data.set and data.delete, which they are not supported in some browsers.
				var newData = new FormData();
				newData.append( 'action', 'ocdi_import_customizer_data' );
				newData.append( 'security', ocdi.ajax_nonce );

				// Set the wp_customize=on only if the plugin filter is set to true.
				if ( true === ocdi.wp_customize_on ) {
					newData.append( 'wp_customize', 'on' );
				}

				ajaxCall( newData );
			}
			else if ( 'undefined' !== typeof response.status && 'afterAllImportAJAX' === response.status ) {
				// Fix for data.set and data.delete, which they are not supported in some browsers.
				var newData = new FormData();
				newData.append( 'action', 'ocdi_after_import_data' );
				newData.append( 'security', ocdi.ajax_nonce );
				ajaxCall( newData );
			}
			else if ( 'undefined' !== typeof response.message ) {
				$( '.js-ocdi-ajax-response' ).append( response.message );

				if ( 'undefined' !== typeof response.title ) {
					$( '.js-ocdi-ajax-response-title' ).html( response.title );
				}

				if ( 'undefined' !== typeof response.subtitle ) {
					$( '.js-ocdi-ajax-response-subtitle' ).html( response.subtitle );
				}

				$( '.js-ocdi-importing' ).hide();
				$( '.js-ocdi-imported' ).show();

				// Trigger custom event, when OCDI import is complete.
				$( document ).trigger( 'ocdiImportComplete' );
			}
			else {
				$( '.js-ocdi-ajax-response' ).append( '<img class="ocdi-imported-content-imported ocdi-imported-content-imported--error" src="' + ocdi.plugin_url + 'assets/images/error.svg" alt="' + ocdi.texts.import_failed + '"><p>' + response + '</p>' );
				$( '.js-ocdi-ajax-response-title' ).html( ocdi.texts.import_failed );
				$( '.js-ocdi-ajax-response-subtitle' ).html( ocdi.texts.import_failed_subtitle );
				$( '.js-ocdi-importing' ).hide();
				$( '.js-ocdi-imported' ).show();
			}
		})
		.fail( function( error ) {
			$( '.js-ocdi-ajax-response' ).append( '<img class="ocdi-imported-content-imported ocdi-imported-content-imported--error" src="' + ocdi.plugin_url + 'assets/images/error.svg" alt="' + ocdi.texts.import_failed + '"><p>Error: ' + error.statusText + ' (' + error.status + ')' + '</p>' );
			$( '.js-ocdi-ajax-response-title' ).html( ocdi.texts.import_failed );
			$( '.js-ocdi-ajax-response-subtitle' ).html( ocdi.texts.import_failed_subtitle );
			$( '.js-ocdi-importing' ).hide();
			$( '.js-ocdi-imported' ).show();
		});
	}

	/**
	 * Get the missing required plugin names for the Create Demo Content "plugins to install" notice.
	 *
	 * @param requiredPluginSlugs
	 *
	 * @returns {[]}
	 */
	function getMissingPluginNamesFromImportContentPageItem( requiredPluginSlugs ) {
		var requiredPluginSlugs = requiredPluginSlugs.split( ',' ),
			pluginList = [];

		ocdi.missing_plugins.forEach( function( plugin ) {
			if ( requiredPluginSlugs.indexOf( plugin.slug ) !== -1 ) {
				pluginList.push( plugin.name )
			}
		} );

		return pluginList;
	}

	/**
	 * Unique array helper function.
	 *
	 * @param value
	 * @param index
	 * @param self
	 *
	 * @returns {boolean}
	 */
	function onlyUnique( value, index, self ) {
		return self.indexOf( value ) === index;
	}

	/**
	 * The AJAX call for installing selected plugins.
	 *
	 * @param {Object[]} plugins   The array of plugin objects with name and value pairs.
	 * @param {int}      counter   The index of the plugin to import from the list above.
	 * @param {Object}   $button   jQuery object of the submit button.
	 * @param {bool}     runImport If the import should be run after plugin installation.
	 */
	function installPluginsAjaxCall( plugins, counter, $button , runImport ) {
		var plugin = plugins[ counter ],
			slug = plugin.name;

		$.ajax({
			method:      'POST',
			url:         ocdi.ajax_url,
			data:        {
				action: 'ocdi_install_plugin',
				security: ocdi.ajax_nonce,
				slug: slug,
			},
			beforeSend:  function() {
				var $currentPluginItem = $( '.plugin-item-' + slug );
				$currentPluginItem.find( '.js-ocdi-plugin-item-info' ).empty();
				$currentPluginItem.find( '.js-ocdi-plugin-item-error' ).empty();
				$currentPluginItem.find( '.js-ocdi-plugin-item-info' ).append( '<p>' + ocdi.texts.installing + '</p>' );
			}
		})
			.done( function( response ) {
				var $currentPluginItem = $( '.plugin-item-' + slug );

				$currentPluginItem.find( '.js-ocdi-plugin-item-info' ).empty();

				if ( response.success ) {
					$currentPluginItem.addClass( 'plugin-item--active' );
					$currentPluginItem.find( 'input[type=checkbox]' ).prop( 'disabled', true );
				} else {
					$currentPluginItem.find( '.js-ocdi-plugin-item-error' ).append( '<p>' + response.data + '</p>' );
				}
			})
			.fail( function( error ) {
				var $currentPluginItem = $( '.plugin-item-' + slug );
				$currentPluginItem.find( '.js-ocdi-plugin-item-info' ).empty();
				$currentPluginItem.find( '.js-ocdi-plugin-item-error' ).append( '<p>' + error.statusText + ' (' + error.status + ')</p>' );
			})
			.always( function() {
				counter++;

				if ( counter === plugins.length ) {
					$button.removeClass( 'ocdi-button-disabled' );

					if ( runImport ) {
						startImport( getUrlParameter( 'import' ) );
					}

				} else {
					installPluginsAjaxCall( plugins, counter, $button, runImport );
				}
			} );
	}

	/**
	 * The AJAX call for importing content on the create demo content page.
	 *
	 * @param {Object[]} items The array of content item objects with name and value pairs.
	 * @param {int}      counter The index of the plugin to import from the list above.
	 * @param {Object}   $button jQuery object of the submit button.
	 */
	function createDemoContentAjaxCall( items, counter, $button ) {
		var item = items[ counter ],
			slug = item.name;

		$.ajax({
			method:      'POST',
			url:         ocdi.ajax_url,
			data:        {
				action: 'ocdi_import_created_content',
				security: ocdi.ajax_nonce,
				slug: slug,
			},
			beforeSend:  function() {
				var $currentItem = $( '.content-item-' + slug );
				$currentItem.find( '.js-ocdi-content-item-info' ).empty();
				$currentItem.find( '.js-ocdi-content-item-error' ).empty();
				$currentItem.find( '.js-ocdi-content-item-info' ).append( '<p>' + ocdi.texts.importing + '</p>' );
			}
		})
			.done( function( response ) {
				if ( response.data && response.data.refresh ) {
					createDemoContentAjaxCall( items, counter, $button );
				}

				var $currentItem = $( '.content-item-' + slug ),
					$infoContainer = $currentItem.find( '.js-ocdi-content-item-info' );

				$infoContainer.empty();

				if ( response.success ) {
					$infoContainer.append( '<p>' + ocdi.texts.successful_import + '</p>' );
				} else {
					$currentItem.find( '.js-ocdi-content-item-error' ).append( '<p>' + response.data + '</p>' );
				}
			})
			.fail( function( error ) {
				var $currentItem = $( '.content-item-' + slug );
				$currentItem.find( '.js-ocdi-content-item-info' ).empty();
				$currentItem.find( '.js-ocdi-content-item-error' ).append( '<p>' + error.statusText + ' (' + error.status + ')</p>' );
			})
			.always( function( response ) {
				if ( response.data && response.data.refresh ) {
					return;
				}

				counter++;

				if ( counter === items.length ) {
					$button.removeClass( 'ocdi-button-disabled' );
				} else {
					createDemoContentAjaxCall( items, counter, $button );
				}
			} );
	}


	/**
	 * Get the parameter value from the URL.
	 *
	 * @param param
	 * @returns {boolean|string}
	 */
	function getUrlParameter( param ) {
		var sPageURL = window.location.search.substring( 1 ),
			sURLVariables = sPageURL.split( '&' ),
			sParameterName,
			i;

		for ( i = 0; i < sURLVariables.length; i++ ) {
			sParameterName = sURLVariables[ i ].split( '=' );

			if ( sParameterName[0] === param ) {
				return typeof sParameterName[1] === undefined ? true : decodeURIComponent( sParameterName[1] );
			}
		}

		return false;
	}

	/**
	 * Run the predefined imporrt.
	 */
	function startImport( selected ) {
		// Prepare data for the AJAX call
		var data = new FormData();
		data.append( 'action', 'ocdi_import_demo_data' );
		data.append( 'security', ocdi.ajax_nonce );
		data.append( 'selected', selected );

		// AJAX call to import everything (content, widgets, before/after setup)
		ajaxCall( data );
	}
} );
