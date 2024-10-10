<?php
/**
 * Class for the import actions used in the One Click Demo Import plugin.
 * Register default WP actions for OCDI plugin.
 *
 * @package ocdi
 */

namespace OCDI;

class ImportActions {
	/**
	 * Register all action hooks for this class.
	 */
	public function register_hooks() {
		// Before content import.
		add_action( 'ocdi/before_content_import_execution', array( $this, 'before_content_import_action' ), 10, 3 );

		// After content import.
		add_action( 'ocdi/after_content_import_execution', array( $this, 'before_widget_import_action' ), 10, 3 );
		add_action( 'ocdi/after_content_import_execution', array( $this, 'widgets_import' ), 20, 3 );
		add_action( 'ocdi/after_content_import_execution', array( $this, 'redux_import' ), 30, 3 );
		add_action( 'ocdi/after_content_import_execution', array( $this, 'wpforms_import' ), 40, 3 );

		// Customizer import.
		add_action( 'ocdi/customizer_import_execution', array( $this, 'customizer_import' ), 10, 1 );

		// After full import action.
		add_action( 'ocdi/after_all_import_execution', array( $this, 'after_import_action' ), 10, 3 );

		// Special widget import cases.
		if ( Helpers::apply_filters( 'ocdi/enable_custom_menu_widget_ids_fix', true ) ) {
			add_action( 'ocdi/widget_settings_array', array( $this, 'fix_custom_menu_widget_ids' ) );
		}
	}


	/**
	 * Change the menu IDs in the custom menu widgets in the widget import data.
	 * This solves the issue with custom menu widgets not having the correct (new) menu ID, because they
	 * have the old menu ID from the export site.
	 *
	 * @param array $widget The widget settings array.
	 */
	public function fix_custom_menu_widget_ids( $widget ) {
		// Skip (no changes needed), if this is not a custom menu widget.
		if ( ! array_key_exists( 'nav_menu', $widget ) || empty( $widget['nav_menu'] ) || ! is_int( $widget['nav_menu'] ) ) {
			return $widget;
		}

		// Get import data, with new menu IDs.
		$ocdi                = OneClickDemoImport::get_instance();
		$content_import_data = $ocdi->importer->get_importer_data();
		$term_ids            = $content_import_data['mapping']['term_id'];

		// Set the new menu ID for the widget.
		$widget['nav_menu'] = $term_ids[ $widget['nav_menu'] ];

		return $widget;
	}


	/**
	 * Execute the widgets import.
	 *
	 * @param array $selected_import_files Actual selected import files (content, widgets, customizer, redux).
	 * @param array $import_files          The filtered import files defined in `ocdi/import_files` filter.
	 * @param int   $selected_index        Selected index of import.
	 */
	public function widgets_import( $selected_import_files, $import_files, $selected_index ) {
		if ( ! empty( $selected_import_files['widgets'] ) ) {
			WidgetImporter::import( $selected_import_files['widgets'] );
		}
	}


	/**
	 * Execute the Redux import.
	 *
	 * @param array $selected_import_files Actual selected import files (content, widgets, customizer, redux).
	 * @param array $import_files          The filtered import files defined in `ocdi/import_files` filter.
	 * @param int   $selected_index        Selected index of import.
	 */
	public function redux_import( $selected_import_files, $import_files, $selected_index ) {
		if ( ! empty( $selected_import_files['redux'] ) ) {
			ReduxImporter::import( $selected_import_files['redux'] );
		}
	}

	/**
	 * Execute the WPForms import.
	 *
	 * @param array $selected_import_files Actual selected import files (content, widgets, customizer, redux).
	 * @param array $import_files          The filtered import files defined in `ocdi/import_files` filter.
	 * @param int   $selected_index        Selected index of import.
	 */
	public function wpforms_import( $selected_import_files, $import_files, $selected_index ) {
		if ( ! empty( $selected_import_files['wpforms'] ) ) {
			( new WPFormsImporter( $selected_import_files['wpforms'] ) )->import();
		}
	}

	/**
	 * Execute the customizer import.
	 *
	 * @param array $selected_import_files Actual selected import files (content, widgets, customizer, redux).
	 * @param array $import_files          The filtered import files defined in `ocdi/import_files` filter.
	 * @param int   $selected_index        Selected index of import.
	 */
	public function customizer_import( $selected_import_files ) {
		if ( ! empty( $selected_import_files['customizer'] ) ) {
			CustomizerImporter::import( $selected_import_files['customizer'] );
		}
	}


	/**
	 * Execute the action: 'ocdi/before_content_import'.
	 *
	 * @param array $selected_import_files Actual selected import files (content, widgets, customizer, redux).
	 * @param array $import_files          The filtered import files defined in `ocdi/import_files` filter.
	 * @param int   $selected_index        Selected index of import.
	 */
	public function before_content_import_action( $selected_import_files, $import_files, $selected_index ) {
		$this->do_import_action( 'ocdi/before_content_import', $import_files[ $selected_index ] );
	}


	/**
	 * Execute the action: 'ocdi/before_widgets_import'.
	 *
	 * @param array $selected_import_files Actual selected import files (content, widgets, customizer, redux).
	 * @param array $import_files          The filtered import files defined in `ocdi/import_files` filter.
	 * @param int   $selected_index        Selected index of import.
	 */
	public function before_widget_import_action( $selected_import_files, $import_files, $selected_index ) {
		$this->do_import_action( 'ocdi/before_widgets_import', $import_files[ $selected_index ] );
	}


	/**
	 * Execute the action: 'ocdi/after_import'.
	 *
	 * @param array $selected_import_files Actual selected import files (content, widgets, customizer, redux).
	 * @param array $import_files          The filtered import files defined in `ocdi/import_files` filter.
	 * @param int   $selected_index        Selected index of import.
	 */
	public function after_import_action( $selected_import_files, $import_files, $selected_index ) {
		$this->do_import_action( 'ocdi/after_import', $import_files[ $selected_index ] );
	}


	/**
	 * Register the do_action hook, so users can hook to these during import.
	 *
	 * @param string $action          The action name to be executed.
	 * @param array  $selected_import The data of selected import from `ocdi/import_files` filter.
	 */
	private function do_import_action( $action, $selected_import ) {
		if ( false !== Helpers::has_action( $action ) ) {
			$ocdi          = OneClickDemoImport::get_instance();
			$log_file_path = $ocdi->get_log_file_path();

			ob_start();
				Helpers::do_action( $action, $selected_import );
			$message = ob_get_clean();

			// Add this message to log file.
			$log_added = Helpers::append_to_file(
				$message,
				$log_file_path,
				$action
			);
		}
	}
}
