jQuery( function ( $ ) {
	'use strict';

	/**
	 * ---------------------------------------
	 * ------------- DOM Ready ---------------
	 * ---------------------------------------
	 */

	// Move the admin notices inside the appropriate div.
	$( '.js-ocdi-notice-wrapper' ).appendTo( '.js-ocdi-admin-notices-container' );

	// Auto start the manual import if on the import page and the 'js-ocdi-auto-start-manual-import' element is present.
	if ( $( '.js-ocdi-auto-start-manual-import' ).length ) {
		startImport( false );
	}

	/**
	 * ---------------------------------------
	 * ------------- Events ------------------
	 * ---------------------------------------
	 */

	/**
	 * No predefined demo import button click (manual import).
	 */
	$( '.js-ocdi-start-manual-import' ).on( 'click', function ( event ) {
		event.preventDefault();

		var $button = $( this );

		if ( $button.hasClass( 'ocdi-button-disabled' ) ) {
			return false;
		}

		// Prepare data for the AJAX call
		var data = new FormData();
		data.append( 'action', 'ocdi_upload_manual_import_files' );
		data.append( 'security', ocdi.ajax_nonce );

		if ( $('#ocdi__content-file-upload').length && $('#ocdi__content-file-upload').get(0).files.length ) {
			var contentFile = $('#ocdi__content-file-upload')[0].files[0];
			var contentFileExt = contentFile.name.split('.').pop();

			if ( -1 === [ 'xml' ].indexOf( contentFileExt.toLowerCase() ) ) {
				alert( ocdi.texts.content_filetype_warn );

				return false;
			}

			data.append( 'content_file', contentFile );
		}
		if ( $('#ocdi__widget-file-upload').length && $('#ocdi__widget-file-upload').get(0).files.length ) {
			var widgetsFile = $('#ocdi__widget-file-upload')[0].files[0];
			var widgetsFileExt = widgetsFile.name.split('.').pop();

			if ( -1 === [ 'json', 'wie' ].indexOf( widgetsFileExt.toLowerCase() ) ) {
				alert( ocdi.texts.widgets_filetype_warn );

				return false;
			}

			data.append( 'widget_file', widgetsFile );
		}
		if ( $('#ocdi__customizer-file-upload').length && $('#ocdi__customizer-file-upload').get(0).files.length ) {
			var customizerFile = $('#ocdi__customizer-file-upload')[0].files[0];
			var customizerFileExt = customizerFile.name.split('.').pop();

			if ( -1 === [ 'dat' ].indexOf( customizerFileExt.toLowerCase() ) ) {
				alert( ocdi.texts.customizer_filetype_warn );

				return false;
			}

			data.append( 'customizer_file', customizerFile );
		}
		if ( $('#ocdi__redux-file-upload').length && $('#ocdi__redux-file-upload').get(0).files.length ) {
			var reduxFile = $('#ocdi__redux-file-upload')[0].files[0];
			var reduxFileExt = reduxFile.name.split('.').pop();

			if ( -1 === [ 'json' ].indexOf( reduxFileExt.toLowerCase() ) ) {
				alert( ocdi.texts.redux_filetype_warn );

				return false;
			}

			data.append( 'redux_file', reduxFile );
			data.append( 'redux_option_name', $('#ocdi__redux-option-name').val() );
		}

		$button.addClass( 'ocdi-button-disabled' );

		// AJAX call to upload all selected import files (content, widgets, customizer and redux).
		$.ajax({
			method: 'POST',
			url: ocdi.ajax_url,
			data: data,
			contentType: false,
			processData: false,
		})
			.done( function( response ) {
				if ( response.success ) {
					window.location.href = ocdi.import_url;
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
	 * Remove the files from the manual import upload controls (when clicked on the "cancel" button).
	 */
	$( '.js-ocdi-cancel-manual-import').on( 'click', function() {
		$( '.ocdi__file-upload-container-items input[type=file]' ).each( function() {
			$( this ).val( '' ).trigger( 'change' );
		} );
	} );

	/**
	 * Show and hide the file upload label and input on file input change event.
	 */
	$( document ).on( 'change', '.ocdi__file-upload-container-items input[type=file]', function() {
		var $input = $( this ),
			$label = $input.siblings( 'label' ),
			fileIsSet = false;

		if( this.files && this.files.length > 0 ) {
			$input.removeClass( 'ocdi-hide-input' ).blur();
			$label.hide();
		} else {
			$input.addClass( 'ocdi-hide-input' );
			$label.show();
		}

		// Enable or disable the main manual import/cancel buttons.
		$( '.ocdi__file-upload-container-items input[type=file]' ).each( function() {
			if ( this.files && this.files.length > 0 ) {
				fileIsSet = true;
			}
		} );

		$( '.js-ocdi-start-manual-import' ).prop( 'disabled', ! fileIsSet );
		$( '.js-ocdi-cancel-manual-import' ).prop( 'disabled', ! fileIsSet );

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

		installPluginsAjaxCall( pluginsToInstall, 0, $button, false, false );
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

		installPluginsAjaxCall( pluginsToInstall, 0, $button, true, false );
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

		if ( $button.hasClass( 'button-disabled' ) ) {
			return false;
		}

		$button.addClass( 'button-disabled' );

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
					$button.text( ocdi.texts.installed );
				} else {
					alert( response.data );
					$button.text( ocdi.texts.install_plugin );
					$button.removeClass( 'button-disabled' );
				}
			})
			.fail( function( error ) {
				alert( error.statusText + ' (' + error.status + ')' );
				$button.removeClass( 'button-disabled' );
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
				$( '.js-ocdi-ajax-response-subtitle' ).html( '<p>' + ocdi.texts.import_failed_subtitle + '</p>' );
				$( '.js-ocdi-importing' ).hide();
				$( '.js-ocdi-imported' ).show();
			}
		})
		.fail( function( error ) {
			$( '.js-ocdi-ajax-response' ).append( '<img class="ocdi-imported-content-imported ocdi-imported-content-imported--error" src="' + ocdi.plugin_url + 'assets/images/error.svg" alt="' + ocdi.texts.import_failed + '"><p>Error: ' + error.statusText + ' (' + error.status + ')' + '</p>' );
			$( '.js-ocdi-ajax-response-title' ).html( ocdi.texts.import_failed );
			$( '.js-ocdi-ajax-response-subtitle' ).html( '<p>' + ocdi.texts.import_failed_subtitle + '</p>' );
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
	 * @param {Object[]} plugins             The array of plugin objects with name and value pairs.
	 * @param {int}      counter             The index of the plugin to import from the list above.
	 * @param {Object}   $button             jQuery object of the submit button.
	 * @param {bool}     runImport           If the import should be run after plugin installation.
	 * @param {bool}     pluginInstallFailed If there were any failed plugin installs.
	 */
	function installPluginsAjaxCall( plugins, counter, $button , runImport, pluginInstallFailed ) {
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
				$currentPluginItem.addClass( 'plugin-item--loading' );
			}
		})
			.done( function( response ) {
				var $currentPluginItem = $( '.plugin-item-' + slug );

				$currentPluginItem.removeClass( 'plugin-item--loading' );

				if ( response.success ) {
					$currentPluginItem.addClass( 'plugin-item--active' );
					$currentPluginItem.find( 'input[type=checkbox]' ).prop( 'disabled', true );
				} else {

					if ( -1 === response.data.indexOf( '<p>' ) ) {
						response.data = '<p>' + response.data + '</p>';
					}

					$currentPluginItem.find( '.js-ocdi-plugin-item-error' ).append( response.data );
					$currentPluginItem.find( 'input[type=checkbox]' ).prop( 'checked', false );
					pluginInstallFailed = true;
				}
			})
			.fail( function( error ) {
				var $currentPluginItem = $( '.plugin-item-' + slug );
				$currentPluginItem.removeClass( 'plugin-item--loading' );
				$currentPluginItem.find( '.js-ocdi-plugin-item-error' ).append( '<p>' + error.statusText + ' (' + error.status + ')</p>' );
				pluginInstallFailed = true;
			})
			.always( function() {
				counter++;

				if ( counter === plugins.length ) {
					if ( runImport ) {
						if ( ! pluginInstallFailed ) {
							startImport( getUrlParameter( 'import' ) );
						} else {
							alert( ocdi.texts.plugin_install_failed );
						}
					}

					$button.removeClass( 'ocdi-button-disabled' );
				} else {
					installPluginsAjaxCall( plugins, counter, $button, runImport, pluginInstallFailed );
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
				$currentItem.addClass( 'content-item--loading' );
			}
		})
			.done( function( response ) {
				if ( response.data && response.data.refresh ) {
					createDemoContentAjaxCall( items, counter, $button );
					return;
				}

				var $currentItem = $( '.content-item-' + slug );

				$currentItem.removeClass( 'content-item--loading' );

				if ( response.success ) {
					$currentItem.find( '.js-ocdi-content-item-info' ).append( '<p>' + ocdi.texts.successful_import + '</p>' );
				} else {
					$currentItem.find( '.js-ocdi-content-item-error' ).append( '<p>' + response.data + '</p>' );
				}
			})
			.fail( function( error ) {
				var $currentItem = $( '.content-item-' + slug );
				$currentItem.removeClass( 'content-item--loading' );
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
	 * Run the main import with a selected predefined demo or with manual files (selected = false).
	 *
	 * Files for the manual import have already been uploaded in the '.js-ocdi-start-manual-import' event above.
	 */
	function startImport( selected ) {
		// Prepare data for the AJAX call
		var data = new FormData();
		data.append( 'action', 'ocdi_import_demo_data' );
		data.append( 'security', ocdi.ajax_nonce );

		if ( selected ) {
			data.append( 'selected', selected );
		}

		// AJAX call to import everything (content, widgets, before/after setup)
		ajaxCall( data );
	}
} );
