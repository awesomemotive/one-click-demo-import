<?php
/**
 * Static functions used in the OCDI plugin views.
 *
 * @package ocdi
 */

namespace OCDI;

class ViewHelpers {
	/**
	 * The HTML output of the plugin page header.
	 *
	 * @return string HTML output.
	 */
	public static function plugin_header_output() {
		ob_start(); ?>
		<div class="ocdi__title-container">
			<h1 class="ocdi__title-container-title"><?php esc_html_e( 'One Click Demo Import', 'one-click-demo-import' ); ?></h1>
			<a href="https://ocdi.com/user-guide/" target="_blank" rel="noopener noreferrer">
				<img class="ocdi__title-container-icon" src="<?php echo esc_url( OCDI_URL . 'assets/images/icons/question-circle.svg' ); ?>" alt="<?php esc_attr_e( 'Questionmark icon', 'one-click-demo-import' ); ?>">
			</a>
		</div>
		<?php
		$plugin_title = ob_get_clean();

		// Display the plugin title (can be replaced with custom title text through the filter below).
		return Helpers::apply_filters( 'ocdi/plugin_page_title', $plugin_title );
	}

	/**
	 * The HTML output of a small card with theme screenshot and title.
	 *
	 * @return string HTML output.
	 */
	public static function small_theme_card( $selected = null ) {
		$theme      = wp_get_theme();
		$screenshot = $theme->get_screenshot();
		$name       = $theme->name;

		if ( isset( $selected ) ) {
			$ocdi          = OneClickDemoImport::get_instance();
			$selected_data = $ocdi->import_files[ $selected ];
			$name          = ! empty( $selected_data['import_file_name'] ) ? $selected_data['import_file_name'] : $name;
			$screenshot    = ! empty( $selected_data['import_preview_image_url'] ) ? $selected_data['import_preview_image_url'] : $screenshot;
		}

		ob_start(); ?>
		<div class="ocdi__card ocdi__card--theme">
			<div class="ocdi__card-content">
				<?php if ( $screenshot ) : ?>
					<div class="screenshot"><img src="<?php echo esc_url( $screenshot ); ?>" alt="<?php esc_attr_e( 'Theme screenshot', 'one-click-demo-import' ); ?>" /></div>
				<?php else : ?>
					<div class="screenshot blank"></div>
				<?php endif; ?>
			</div>
			<div class="ocdi__card-footer">
				<h3><?php echo esc_html( $name ); ?></h3>
			</div>
		</div>
		<?php
		return ob_get_clean();
	}
}
