<?php
/**
 * The install plugins page view.
 *
 * @package ocdi
 */

namespace OCDI;

$plugin_installer = new PluginInstaller();
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
							<?php esc_html_e( 'Want to use the best plugins for the job? Here is the list of awesome plugins that will help you achieve your goals.', 'one-click-demo-import' ); ?>
						</p>
					</div>
					<div class="ocdi-install-plugins-content-content">
						<?php foreach ( $plugin_installer->get_partner_plugins() as $plugin ) : ?>
							<?php $is_plugin_active = $plugin_installer->is_plugin_active( $plugin['slug'] ); ?>
							<label class="plugin-item plugin-item-<?php echo esc_attr( $plugin['slug'] ); ?><?php echo $is_plugin_active ? ' plugin-item--active' : ''; ?>" for="ocdi-<?php echo esc_attr( $plugin['slug'] ); ?>-plugin">
								<div class="plugin-item-content">
									<div class="plugin-item-content-title">
										<h3><?php echo esc_html( $plugin['name'] ); ?></h3>
									</div>
									<?php if ( ! empty( $plugin['description'] ) ) : ?>
										<p>
											<?php echo wp_kses_post( $plugin['description'] ); ?>
										</p>
									<?php endif; ?>
									<div class="plugin-item-error js-ocdi-plugin-item-error"></div>
									<div class="plugin-item-info js-ocdi-plugin-item-info"></div>
								</div>
								<span class="plugin-item-checkbox">
									<input type="checkbox" id="ocdi-<?php echo esc_attr( $plugin['slug'] ); ?>-plugin" name="<?php echo esc_attr( $plugin['slug'] ); ?>" <?php checked( ! empty( $plugin['preselected'] ) || $is_plugin_active ); ?><?php disabled( $is_plugin_active ) ?>>
									<span class="checkbox">
										<img src="<?php echo esc_url( OCDI_URL . 'assets/images/icons/check-solid-white.svg' ); ?>" class="ocdi-check-icon" alt="<?php esc_attr_e( 'Checkmark icon', 'one-click-demo-import' ); ?>">
										<img src="<?php echo esc_url( OCDI_URL . 'assets/images/loader.svg' ); ?>" class="ocdi-loading ocdi-loading-md" alt="<?php esc_attr_e( 'Loading...', 'one-click-demo-import' ); ?>">
									</span>
								</span>
							</label>
						<?php endforeach; ?>
					</div>
					<div class="ocdi-install-plugins-content-footer">
						<a href="<?php echo esc_url( $this->get_plugin_settings_url() ); ?>" class="button"><img src="<?php echo esc_url( OCDI_URL . 'assets/images/icons/long-arrow-alt-left-blue.svg' ); ?>" alt="<?php esc_attr_e( 'Back icon', 'one-click-demo-import' ); ?>"><span><?php esc_html_e( 'Go Back' , 'one-click-demo-import' ); ?></span></a>
						<a href="#" class="button button-primary js-ocdi-install-plugins"><?php esc_html_e( 'Install & Activate' , 'one-click-demo-import' ); ?></a>
					</div>
				</div>
			</div>
			<div class="ocdi__content-container-content--side">
				<?php echo wp_kses_post( ViewHelpers::small_theme_card() ); ?>
			</div>
		</div>

	</div>
</div>
