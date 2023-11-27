<?php
/**
 * The install plugins page view.
 *
 * @package ocdi
 */

namespace OCDI;

$plugin_installer = new PluginInstaller();
$theme_plugins    = $plugin_installer->get_theme_plugins();
$theme            = wp_get_theme();
?>

<div class="ocdi ocdi--install-plugins">

	<?php echo wp_kses_post( ViewHelpers::plugin_header_output() ); ?>

	<div class="ocdi__content-container">

		<div class="ocdi__admin-notices js-ocdi-admin-notices-container"></div>

		<div class="ocdi__content-container-content">
			<div class="ocdi__content-container-content--main">
				<?php if ( isset( $_GET['import'] ) ) : ?>
					<div class="ocdi-install-plugins-content js-ocdi-install-plugins-content">
						<div class="ocdi-install-plugins-content-header">
							<h2><?php esc_html_e( 'Before We Import Your Demo', 'one-click-demo-import' ); ?></h2>
							<p>
								<?php esc_html_e( 'To ensure the best experience, installing the following plugins is strongly recommended, and in some cases required.', 'one-click-demo-import' ); ?>
							</p>

							<?php if ( ! empty( $this->import_files[ $_GET['import'] ]['import_notice'] ) ) : ?>
								<div class="notice  notice-info">
									<p><?php echo wp_kses_post( $this->import_files[ $_GET['import'] ]['import_notice'] ); ?></p>
								</div>
							<?php endif; ?>
						</div>
						<div class="ocdi-install-plugins-content-content">
							<?php if ( empty( $theme_plugins ) ) : ?>
								<div class="ocdi-content-notice">
									<p>
										<?php esc_html_e( 'All required/recommended plugins are already installed. You can import your demo content.' , 'one-click-demo-import' ); ?>
									</p>
								</div>
							<?php else : ?>
								<div>
								<?php foreach ( $theme_plugins as $plugin ) : ?>
									<?php $is_plugin_active = $plugin_installer->is_plugin_active( $plugin['slug'] ); ?>
									<label class="plugin-item plugin-item-<?php echo esc_attr( $plugin['slug'] ); ?><?php echo $is_plugin_active ? ' plugin-item--active' : ''; ?><?php echo ! empty( $plugin['required'] ) ? ' plugin-item--required' : ''; ?>" for="ocdi-<?php echo esc_attr( $plugin['slug'] ); ?>-plugin">
										<div class="plugin-item-content">
											<div class="plugin-item-content-title">
												<h3><?php echo esc_html( $plugin['name'] ); ?></h3>
												<?php if ( in_array( $plugin['slug'], [ 'wpforms-lite', 'all-in-one-seo-pack', 'google-analytics-for-wordpress' ], true ) ) : ?>
													<span>
														<img src="<?php echo esc_url( OCDI_URL . 'assets/images/icons/star.svg' ); ?>" alt="<?php esc_attr_e( 'Star icon', 'one-click-demo-import' ); ?>">
													</span>
												<?php endif; ?>
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
											<input type="checkbox" id="ocdi-<?php echo esc_attr( $plugin['slug'] ); ?>-plugin" name="<?php echo esc_attr( $plugin['slug'] ); ?>" <?php checked( ! empty( $plugin['preselected'] ) || ! empty( $plugin['required'] ) || $is_plugin_active ); ?><?php disabled( $is_plugin_active ); ?>>
											<span class="checkbox">
												<img src="<?php echo esc_url( OCDI_URL . 'assets/images/icons/check-solid-white.svg' ); ?>" class="ocdi-check-icon" alt="<?php esc_attr_e( 'Checkmark icon', 'one-click-demo-import' ); ?>">
												<?php if ( ! empty( $plugin['required'] ) ) : ?>
													<img src="<?php echo esc_url( OCDI_URL . 'assets/images/icons/lock.svg' ); ?>" class="ocdi-lock-icon" alt="<?php esc_attr_e( 'Lock icon', 'one-click-demo-import' ); ?>">
												<?php endif; ?>
												<img src="<?php echo esc_url( OCDI_URL . 'assets/images/loader.svg' ); ?>" class="ocdi-loading ocdi-loading-md" alt="<?php esc_attr_e( 'Loading...', 'one-click-demo-import' ); ?>">
											</span>
										</span>
									</label>
								<?php endforeach; ?>
								</div>
								<div class="ocdi-content-notice ocdi-content-notice--warning">
									<p>
										<?php
											printf(
												esc_html__(
													'The plugins with %1$s are recommended by One Click Demo Import plugin to help you grow your website. They are not required for the %2$s theme to work.',
													'one-click-demo-import'
												),
												'<span class="ocdi-recommended-star"><img src="' . esc_url( OCDI_URL . 'assets/images/icons/star.svg' ) . '" alt="' . esc_attr__( 'Star icon', 'one-click-demo-import' ) . '"></span>',
												$theme->name
											);
										?>
									</p>
								</div>
							<?php endif; ?>
						</div>
						<div class="ocdi-install-plugins-content-footer">
							<a href="<?php echo esc_url( $this->get_plugin_settings_url() ); ?>" class="button"><img src="<?php echo esc_url( OCDI_URL . 'assets/images/icons/long-arrow-alt-left-blue.svg' ); ?>" alt="<?php esc_attr_e( 'Back icon', 'one-click-demo-import' ); ?>"><span><?php esc_html_e( 'Go Back' , 'one-click-demo-import' ); ?></span></a>
							<a href="#" class="button button-primary js-ocdi-install-plugins-before-import"><?php esc_html_e( 'Continue & Import' , 'one-click-demo-import' ); ?></a>
						</div>
					</div>
				<?php else : ?>
					<div class="js-ocdi-auto-start-manual-import"></div>
				<?php endif; ?>

				<div class="ocdi-importing js-ocdi-importing">
					<div class="ocdi-importing-header">
						<h2><?php esc_html_e( 'Importing Content' , 'one-click-demo-import' ); ?></h2>
						<p><?php esc_html_e( 'Please sit tight while we import your content. Do not refresh the page or hit the back button.' , 'one-click-demo-import' ); ?></p>
					</div>
					<div class="ocdi-importing-content">
						<img class="ocdi-importing-content-importing" src="<?php echo esc_url( OCDI_URL . 'assets/images/importing.svg' ); ?>" alt="<?php esc_attr_e( 'Importing animation', 'one-click-demo-import' ); ?>">
					</div>
				</div>

				<div class="ocdi-imported js-ocdi-imported">
					<div class="ocdi-imported-header">
						<h2 class="js-ocdi-ajax-response-title"><?php esc_html_e( 'Import Complete!' , 'one-click-demo-import' ); ?></h2>
						<div class="js-ocdi-ajax-response-subtitle">
							<p>
								<?php esc_html_e( 'Congrats, your demo was imported successfully. You can now begin editing your site.' , 'one-click-demo-import' ); ?>
							</p>
						</div>
					</div>
					<div class="ocdi-imported-content">
						<div class="ocdi__response  js-ocdi-ajax-response"></div>
					</div>
					<div class="ocdi-imported-footer">
						<?php echo wp_kses(
							$this->get_import_successful_buttons_html(),
							[
								'a' => [
									'href'   => [],
									'class'  => [],
									'target' => [],
								],
							]
						); ?>
					</div>
				</div>
			</div>
			<div class="ocdi__content-container-content--side">
				<?php
					$selected = isset( $_GET['import'] ) ? (int) $_GET['import'] : null;
					echo wp_kses_post( ViewHelpers::small_theme_card( $selected ) );
				?>
			</div>
		</div>

	</div>
</div>
