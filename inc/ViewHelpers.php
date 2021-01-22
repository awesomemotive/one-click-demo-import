<?php
/**
 * Static functions used in the OCDI plugin views.
 *
 * @package ocdi
 */

namespace OCDI;

/**
 * Class with static helper functions.
 */
class ViewHelpers {
	/**
	 * Filter through the array of import files and get rid of those who do not comply.
	 *
	 * @param  array $import_files list of arrays with import file details.
	 * @return array list of filtered arrays.
	 */
	public static function plugin_header_output() {
		ob_start(); ?>
		<div class="ocdi__title-container">
			<h1 class="ocdi__title-container-title"><?php esc_html_e( 'One Click Demo Import', 'pt-ocdi' ); ?></h1>
			<a href="#">
				<img class="ocdi__title-container-icon" src="<?php echo esc_url( PT_OCDI_URL . 'assets/images/icons/question-circle.svg' ); ?>" alt="<?php esc_attr_e( 'Questionmark icon', 'one-click-demo-import' ); ?>">
			</a>
		</div>
		<?php
		$plugin_title = ob_get_clean();

		// Display the plugin title (can be replaced with custom title text through the filter below).
		return Helpers::apply_filters( 'ocdi/plugin_page_title', $plugin_title );
	}
}
