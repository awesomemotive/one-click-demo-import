<?php
/**
 * The install plugins page view.
 *
 * @package ocdi
 */

namespace OCDI;
?>

<div class="ocdi ocdi--install-plugins">

	<?php echo wp_kses_post( ViewHelpers::plugin_header_output() ); ?>

	<div class="ocdi__content-container">

		<div class="ocdi__admin-notices js-ocdi-admin-notices-container"></div>

		<div class="ocdi__content-container-content">
			<div class="ocdi__content-container-content--main">
				<div class="ocdi-install-plugins-content">
					<div class="ocdi-install-plugins-content-header">
						<h2><?php esc_html_e( 'Install Recommended Plugins', 'one-click-demo-import' ); ?></h2>
						<p>
							<?php esc_html_e( 'Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Donec odio. Quisque volutpat mattis eros. Nullam malesuada erat ut turpis. Suspendisse urna nibh.', 'one-click-demo-import' ); ?>
						</p>
					</div>
					<div class="ocdi-install-plugins-content-content">
						<label class="plugin-item" for="ocdi-wpforms-plugin">
							<div class="plugin-item-content">
								<h3><?php esc_html_e( 'WPForms', 'one-click-demo-import' ); ?></h3>
								<p>
									<?php esc_html_e( 'Join 3,000,000+ professionals who build smarter forms and surveys with WPForms.', 'one-click-demo-import' ); ?>
								</p>
							</div>
							<span class="plugin-item-checkbox">
								<input type="checkbox" id="ocdi-wpforms-plugin" name="wpforms">
								<span class="checkbox">
									<img src="<?php echo esc_url( PT_OCDI_URL . 'assets/images/icons/check-solid-white.svg' ); ?>" alt="<?php esc_attr_e( 'Checkmark icon', 'one-click-demo-import' ); ?>">
								</span>
							</span>
						</label>
						<label class="plugin-item" for="ocdi-aioseo-plugin">
							<div class="plugin-item-content">
								<h3><?php esc_html_e( 'All in One SEO Pack', 'one-click-demo-import' ); ?></h3>
								<p>
									<?php esc_html_e( 'Use All in One SEO Pack to optimize your WordPress site for SEO.', 'one-click-demo-import' ); ?>
								</p>
							</div>
							<span class="plugin-item-checkbox">
								<input type="checkbox" id="ocdi-aioseo-plugin" name="aioseo">
								<span class="checkbox">
									<img src="<?php echo esc_url( PT_OCDI_URL . 'assets/images/icons/check-solid-white.svg' ); ?>" alt="<?php esc_attr_e( 'Checkmark icon', 'one-click-demo-import' ); ?>">
								</span>
							</span>
						</label>
					</div>
					<div class="ocdi-install-plugins-content-footer">
						<a href="<?php echo esc_url( $this->get_plugin_settings_url() ); ?>" class="button"><img src="<?php echo esc_url( PT_OCDI_URL . 'assets/images/icons/long-arrow-alt-left-blue.svg' ); ?>" alt="<?php esc_attr_e( 'Back icon', 'one-click-demo-import' ); ?>"><span><?php esc_html_e( 'Go Back' , 'one-click-demo-import' ); ?></span></a>
						<a href="#" class="button button-primary js-ocdi-install-plugins"><?php esc_html_e( 'Install Plugins' , 'one-click-demo-import' ); ?></a>
					</div>
				</div>
			</div>
			<div class="ocdi__content-container-content--side">
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
			</div>
		</div>

	</div>
</div>
