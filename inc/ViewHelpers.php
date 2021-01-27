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

	/**
	 * The HTML output of a small card with theme screenshot and title.
	 *
	 * @return string HTML output.
	 */
	public static function small_theme_card() {
		ob_start(); ?>
		<div class="ocdi__card ocdi__card--theme">
			<?php $theme = wp_get_theme(); ?>
			<div class="ocdi__card-content">
				<?php if ( $theme->get_screenshot() ) : ?>
					<div class="screenshot"><img src="<?php echo esc_url( $theme->get_screenshot() ); ?>" alt="<?php esc_attr_e( 'Theme screenshot', 'one-click-demo-import' ); ?>" /></div>
				<?php else : ?>
					<div class="screenshot blank"></div>
				<?php endif; ?>
			</div>
			<div class="ocdi__card-footer">
				<h3><?php echo esc_html( $theme->name ); ?></h3>
			</div>
		</div>
		<?php
		return ob_get_clean();
	}
}
