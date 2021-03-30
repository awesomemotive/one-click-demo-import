<?php
/**
 * The create content page view.
 *
 * @package ocdi
 */

namespace OCDI;

$demo_content_creator = new CreateDemoContent\DemoContentCreator();
$content_items        = $demo_content_creator->get_default_content();
?>

<div class="ocdi ocdi--create-content">

	<?php echo wp_kses_post( ViewHelpers::plugin_header_output() ); ?>

	<div class="ocdi__content-container">

		<div class="ocdi__admin-notices js-ocdi-admin-notices-container"></div>

		<div class="ocdi__content-container-content">
			<div class="ocdi__content-container-content--main">
				<div class="ocdi-create-content">
					<div class="ocdi-create-content-header">
						<h2><?php esc_html_e( 'Create Demo Content', 'one-click-demo-import' ); ?></h2>
						<p>
							<?php esc_html_e( 'Select which pre-built pages you want to import to use on your website. After that, all you need to do is customize the content to fit your needs and your page will be good to go.', 'one-click-demo-import' ); ?>
						</p>
					</div>
					<div class="ocdi-create-content-content">
						<div>
							<?php foreach ( $content_items as $item ) : ?>
								<label class="content-item content-item-<?php echo esc_attr( $item['slug'] ); ?>" for="ocdi-<?php echo esc_attr( $item['slug'] ); ?>-content-item">
									<div class="content-item-content">
										<div class="content-item-content-title">
											<h3><?php echo esc_html( $item['name'] ); ?></h3>
										</div>
										<?php if ( ! empty( $item['description'] ) ) : ?>
											<p>
												<?php echo wp_kses_post( $item['description'] ); ?>
											</p>
										<?php endif; ?>
										<div class="content-item-error js-ocdi-content-item-error"></div>
										<div class="content-item-info js-ocdi-content-item-info"></div>
									</div>
									<span class="content-item-checkbox">
										<input type="checkbox" id="ocdi-<?php echo esc_attr( $item['slug'] ); ?>-content-item" name="<?php echo esc_attr( $item['slug'] ); ?>" data-plugins="<?php echo esc_attr( implode( ',', $item['required_plugins'] ) ); ?>">
										<span class="checkbox">
											<img src="<?php echo esc_url( OCDI_URL . 'assets/images/icons/check-solid-white.svg' ); ?>" class="ocdi-check-icon" alt="<?php esc_attr_e( 'Checkmark icon', 'one-click-demo-import' ); ?>">
											<img src="<?php echo esc_url( OCDI_URL . 'assets/images/loader.svg' ); ?>" class="ocdi-loading ocdi-loading-md" alt="<?php esc_attr_e( 'Loading...', 'one-click-demo-import' ); ?>">
										</span>
									</span>
								</label>
							<?php endforeach; ?>
						</div>

						<div class="ocdi-create-content-content-notice js-ocdi-create-content-install-plugins-notice">
							<p>
								<?php esc_html_e( 'The following plugins will be installed for free: ', 'one-click-demo-import' ); ?>
								<span class="js-ocdi-create-content-install-plugins-list"></span>
							</p>
						</div>
					</div>
					<div class="ocdi-create-content-footer">
						<a href="<?php echo esc_url( $this->get_plugin_settings_url() ); ?>" class="button"><img src="<?php echo esc_url( OCDI_URL . 'assets/images/icons/long-arrow-alt-left-blue.svg' ); ?>" alt="<?php esc_attr_e( 'Back icon', 'one-click-demo-import' ); ?>"><span><?php esc_html_e( 'Go Back' , 'one-click-demo-import' ); ?></span></a>
						<a href="#" class="button button-primary js-ocdi-create-content"><?php esc_html_e( 'Import' , 'one-click-demo-import' ); ?></a>
					</div>
				</div>
			</div>
			<div class="ocdi__content-container-content--side">
				<?php echo wp_kses_post( ViewHelpers::small_theme_card() ); ?>
			</div>
		</div>

	</div>
</div>
