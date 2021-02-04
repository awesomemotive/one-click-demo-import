<?php
/**
 * The plugin page view - the "settings" page of the plugin.
 *
 * @package ocdi
 */

namespace OCDI;

$predefined_themes = $this->import_files;

if ( ! empty( $this->import_files ) && isset( $_GET['import-mode'] ) && 'manual' === $_GET['import-mode'] ) {
	$predefined_themes = array();
}

/**
 * Hook for adding the custom plugin page header
 */
Helpers::do_action( 'ocdi/plugin_page_header' );
?>

<div class="ocdi">

	<?php echo wp_kses_post( ViewHelpers::plugin_header_output() ); ?>

	<div class="ocdi__content-container">

		<?php
		// Display warrning if PHP safe mode is enabled, since we wont be able to change the max_execution_time.
		if ( ini_get( 'safe_mode' ) ) {
			printf(
				esc_html__( '%sWarning: your server is using %sPHP safe mode%s. This means that you might experience server timeout errors.%s', 'pt-ocdi' ),
				'<div class="notice  notice-warning  is-dismissible"><p>',
				'<strong>',
				'</strong>',
				'</p></div>'
			);
		}
		?>

		<div class="ocdi__admin-notices js-ocdi-admin-notices-container"></div>

		<?php
		// Start output buffer for displaying the plugin intro text.
		ob_start();
		?>

		<div class="ocdi__intro-text">
			<p class="about-description">
				<?php esc_html_e( 'Importing demo data (post, pages, images, theme settings, etc.) is the quickest and easiest way to set up your new theme.', 'pt-ocdi' ); ?>
				<?php esc_html_e( 'It allows you to simply edit everything instead of creating content and layouts from scratch.', 'pt-ocdi' ); ?>
				<a href="#"><?php esc_html_e( 'Learn more', 'pt-ocdi' ); ?></a>.
			</p>
		</div>

		<?php
		$plugin_intro_text = ob_get_clean();

		// Display the plugin intro text (can be replaced with custom text through the filter below).
		echo wp_kses_post( Helpers::apply_filters( 'ocdi/plugin_intro_text', $plugin_intro_text ) );
		?>

		<?php if ( empty( $this->import_files ) ) : ?>
			<div class="notice  notice-info">
				<p><?php esc_html_e( 'There are no predefined import files available for this theme. Please upload the import files manually below.', 'pt-ocdi' ); ?></p>
			</div>
		<?php endif; ?>

		<?php $theme = wp_get_theme(); ?>

		<div class="ocdi__theme-about">
			<div class="ocdi__theme-about-screenshots">
				<?php if ( $theme->get_screenshot() ) : ?>
				<div class="screenshot"><img src="<?php echo esc_url( $theme->get_screenshot() ); ?>" alt="<?php esc_attr_e( 'Theme screenshot', 'one-click-demo-import' ); ?>" /></div>
				<?php else : ?>
				<div class="screenshot blank"></div>
				<?php endif; ?>
			</div>

			<div class="ocdi__theme-about-info">
				<div class="top-content">
					<div class="theme-title">
						<h2 class="theme-name"><?php echo esc_html( $theme->name ); ?></h2>
						<span class="theme-version">
							<?php
							/* translators: %s: Theme version. */
							printf( __( 'Version: %s' ), esc_html( $theme->version ) );
							?>
						</span>
					</div>
					<p class="theme-author">
						<?php
						/* translators: %s: Theme author link. */
						printf( __( 'By %s' ), wp_kses_post( $theme->author ) );
						?>
					</p>

					<p class="theme-description"><?php echo wp_kses_post( $theme->description ); ?></p>

					<?php if ( ! empty( $theme->tags ) ) : ?>
					<p class="theme-tags"><span><?php esc_html_e( 'Tags:' ); ?></span> <?php echo esc_html( implode( ', ', $theme->tags ) ); ?></p>
					<?php endif; ?>
				</div>
				<div class="bottom-content">
					<?php if ( ! empty( $this->import_files ) ) : ?>
						<?php if ( empty( $_GET['import-mode'] ) || 'manual' !== $_GET['import-mode'] ) : ?>
							<a href="<?php echo esc_url( $this->get_plugin_settings_url( array( 'import-mode' => 'manual' ) ) ); ?>" class="ocdi-import-mode-switch"><?php esc_html_e( 'Switch to Manual Import', 'pt-ocdi' ); ?></a>
						<?php else : ?>
							<a href="<?php echo esc_url( $this->get_plugin_settings_url() ); ?>" class="ocdi-import-mode-switch"><?php esc_html_e( 'Switch back to Theme Predefined Imports', 'pt-ocdi' ); ?></a>
						<?php endif; ?>
					<?php endif; ?>
				</div>
			</div>
		</div>

		<?php if ( empty( $predefined_themes ) ) : ?>

			<hr>

			<div class="ocdi__file-upload-container">
				<h2><?php esc_html_e( 'Manual demo files upload', 'pt-ocdi' ); ?></h2>

				<div class="ocdi__file-upload-container-items">
					<div class="ocdi__file-upload ocdi__card ocdi__card--three">
						<div class="ocdi__card-content">
							<label for="ocdi__content-file-upload">
								<img src="<?php echo esc_url( PT_OCDI_URL . 'assets/images/icons/content.svg' ); ?>" class="ocdi-icon" alt="<?php esc_attr_e( 'Content import icon', 'one-click-demo-import' ); ?>">
								<h3><?php esc_html_e( 'Import Content', 'pt-ocdi' ); ?></h3>
								<p><?php esc_html_e( 'Select an XML file to import.', 'pt-ocdi' ); ?></p>
							</label>
						</div>
						<div class="ocdi__card-footer">
							<label for="ocdi__content-file-upload" class="custom-file-upload-button">
								<?php esc_html_e( 'Select a File', 'one-click-demo-import' ); ?>
							</label>
							<input id="ocdi__content-file-upload" type="file" name="content-file-upload">
						</div>
					</div>

					<div class="ocdi__file-upload ocdi__card ocdi__card--three">
						<div class="ocdi__card-content">
							<label for="ocdi__widget-file-upload">
								<img src="<?php echo esc_url( PT_OCDI_URL . 'assets/images/icons/widgets.svg' ); ?>" class="ocdi-icon" alt="<?php esc_attr_e( 'Widgets import icon', 'one-click-demo-import' ); ?>">
								<h3><?php esc_html_e( 'Import Widgets', 'pt-ocdi' ); ?></h3>
								<p><?php esc_html_e( 'Select a JSON/WIE file to import.', 'pt-ocdi' ); ?></p>
							</label>
						</div>
						<div class="ocdi__card-footer">
							<label for="ocdi__widget-file-upload" class="custom-file-upload-button">
								<?php esc_html_e( 'Select a File', 'one-click-demo-import' ); ?>
							</label>
							<input id="ocdi__widget-file-upload" type="file" name="widget-file-upload">
						</div>
					</div>

					<div class="ocdi__file-upload ocdi__card ocdi__card--three">
						<div class="ocdi__card-content">
							<label for="ocdi__customizer-file-upload">
								<img src="<?php echo esc_url( PT_OCDI_URL . 'assets/images/icons/brush.svg' ); ?>" class="ocdi-icon" alt="<?php esc_attr_e( 'Customizer import icon', 'one-click-demo-import' ); ?>">
								<h3><?php esc_html_e( 'Import Customizer', 'pt-ocdi' ); ?></h3>
								<p><?php esc_html_e( 'Select a DAT file to import.', 'pt-ocdi' ); ?></p>
							</label>
						</div>
						<div class="ocdi__card-footer">
							<label for="ocdi__customizer-file-upload" class="custom-file-upload-button">
								<?php esc_html_e( 'Select a File', 'one-click-demo-import' ); ?>
							</label>
							<input id="ocdi__customizer-file-upload" type="file" name="customizer-file-upload">
						</div>
					</div>

					<?php if ( class_exists( 'ReduxFramework' ) ) : ?>
					<div class="ocdi__file-upload ocdi__card ocdi__card--three">
						<div class="ocdi__card-content">
							<label for="ocdi__redux-file-upload">
								<img src="<?php echo esc_url( PT_OCDI_URL . 'assets/images/icons/brush.svg' ); ?>" class="ocdi-icon" alt="<?php esc_attr_e( 'Redux import icon', 'one-click-demo-import' ); ?>">
								<h3><?php esc_html_e( 'Import Redux', 'pt-ocdi' ); ?></h3>
								<p><?php esc_html_e( 'Select a JSON file to import.', 'pt-ocdi' ); ?></p>
							</label>
							<div>
								<label for="ocdi__redux-option-name" class="ocdi__redux-option-name-label"><?php esc_html_e( 'Enter the Redux option name:', 'pt-ocdi' ); ?></label>
								<input id="ocdi__redux-option-name" type="text" name="redux-option-name">
							</div>
						</div>
						<div class="ocdi__card-footer">
							<label for="ocdi__redux-file-upload" class="custom-file-upload-button">
								<?php esc_html_e( 'Select a File', 'one-click-demo-import' ); ?>
							</label>
							<input id="ocdi__redux-file-upload" type="file" name="redux-file-upload">
						</div>
					</div>
					<?php endif; ?>

					<div class="ocdi__recommended-plugins ocdi__card ocdi__card--three">
						<div class="ocdi__card-content">
							<label>
								<img src="<?php echo esc_url( PT_OCDI_URL . 'assets/images/icons/plugins.svg' ); ?>" class="ocdi-icon" alt="<?php esc_attr_e( 'Recommended plugins icon', 'one-click-demo-import' ); ?>">
								<h3><?php esc_html_e( 'Recommended Plugins', 'pt-ocdi' ); ?></h3>
								<p><?php esc_html_e( 'Install our recommended plugins.', 'pt-ocdi' ); ?></p>
							</label>
						</div>
						<div class="ocdi__card-footer">
							<a href="<?php echo esc_url( $this->get_plugin_settings_url( array( 'step' => 'install-plugins' ) ) ); ?>" class="button"><?php esc_html_e( 'Install Plugins', 'one-click-demo-import' ); ?></a>
						</div>
					</div>

					<div class="ocdi__create-demo-content ocdi__card ocdi__card--three">
						<div class="ocdi__card-content">
							<label>
								<img src="<?php echo esc_url( PT_OCDI_URL . 'assets/images/icons/copy.svg' ); ?>" class="ocdi-icon" alt="<?php esc_attr_e( 'Create demo content icon', 'one-click-demo-import' ); ?>">
								<h3><?php esc_html_e( 'Create Demo Content', 'pt-ocdi' ); ?></h3>
								<p><?php esc_html_e( 'Create useful content with a few clicks.', 'pt-ocdi' ); ?></p>
							</label>
						</div>
						<div class="ocdi__card-footer">
							<a href="<?php echo esc_url( $this->get_plugin_settings_url( array( 'step' => 'create-content' ) ) ); ?>" class="button"><?php esc_html_e( 'Create Content', 'one-click-demo-import' ); ?></a>
						</div>
					</div>

					<div class="ocdi__create-landing-pages ocdi__card ocdi__card--three">
						<div class="ocdi__card-content">
							<label>
								<img src="<?php echo esc_url( PT_OCDI_URL . 'assets/images/icons/layout.svg' ); ?>" class="ocdi-icon" alt="<?php esc_attr_e( 'Create landing pages icon', 'one-click-demo-import' ); ?>">
								<h3><?php esc_html_e( 'Create Landing Pages', 'pt-ocdi' ); ?></h3>
								<p><?php esc_html_e( 'Create beautiful converting pages.', 'pt-ocdi' ); ?></p>
							</label>
						</div>
						<div class="ocdi__card-footer">
							<?php
								$plugin_installer = new PluginInstaller();
								$seedprod_active = $plugin_installer->is_plugin_active( 'coming-soon' );
							?>
							<a href="#" class="button js-ocdi-install-coming-soon-plugin<?php echo empty( $seedprod_active ) ? '' : ' ocdi-button-disabled'; ?>">
								<?php echo empty( $seedprod_active ) ? esc_html__( 'Install Plugin', 'one-click-demo-import' ) : esc_html__( 'Installed', 'one-click-demo-import' ); ?>
							</a>
						</div>
					</div>
				</div>
			</div>

			<p class="ocdi__button-container">
				<button class="ocdi__button  button  button-hero  button-primary  js-ocdi-import-data"><?php esc_html_e( 'Import Demo Data', 'pt-ocdi' ); ?></button>
			</p>

		<?php elseif ( 1 === count( $predefined_themes ) ) : ?>

			<div class="ocdi__demo-import-notice  js-ocdi-demo-import-notice"><?php
				if ( is_array( $predefined_themes ) && ! empty( $predefined_themes[0]['import_notice'] ) ) {
					echo wp_kses_post( $predefined_themes[0]['import_notice'] );
				}
			?></div>

			<p class="ocdi__button-container">
				<button class="ocdi__button  button  button-hero  button-primary  js-ocdi-import-data"><?php esc_html_e( 'Import Demo Data', 'pt-ocdi' ); ?></button>
			</p>

		<?php else : ?>

			<!-- OCDI grid layout -->
			<div class="ocdi__gl  js-ocdi-gl">
			<?php
				// Prepare navigation data.
				$categories = Helpers::get_all_demo_import_categories( $predefined_themes );
			?>
				<?php if ( ! empty( $categories ) ) : ?>
					<div class="ocdi__gl-header  js-ocdi-gl-header">
						<nav class="ocdi__gl-navigation">
							<ul>
								<li class="active"><a href="#all" class="ocdi__gl-navigation-link  js-ocdi-nav-link"><span><?php esc_html_e( 'All Demos', 'pt-ocdi' ); ?></span></a></li>
								<?php foreach ( $categories as $key => $name ) : ?>
									<li>
										<a href="#<?php echo esc_attr( $key ); ?>" class="ocdi__gl-navigation-link  js-ocdi-nav-link">
											<span>
												<?php echo esc_html( $name ); ?>
											</span>
										</a>
									</li>
								<?php endforeach; ?>
							</ul>
						</nav>
						<div clas="ocdi__gl-search">
							<input type="search" class="ocdi__gl-search-input  js-ocdi-gl-search" name="ocdi-gl-search" value="" placeholder="<?php esc_html_e( 'Search Demos...', 'pt-ocdi' ); ?>">
						</div>
					</div>
				<?php endif; ?>
				<div class="ocdi__gl-item-container js-ocdi-gl-item-container">
					<?php foreach ( $predefined_themes as $index => $import_file ) : ?>
						<?php
							// Prepare import item display data.
							$img_src = isset( $import_file['import_preview_image_url'] ) ? $import_file['import_preview_image_url'] : '';
							// Default to the theme screenshot, if a custom preview image is not defined.
							if ( empty( $img_src ) ) {
								$theme = wp_get_theme();
								$img_src = $theme->get_screenshot();
							}

						?>
						<div class="ocdi__gl-item js-ocdi-gl-item" data-categories="<?php echo esc_attr( Helpers::get_demo_import_item_categories( $import_file ) ); ?>" data-name="<?php echo esc_attr( strtolower( $import_file['import_file_name'] ) ); ?>">
							<div class="ocdi__gl-item-image-container">
								<?php if ( ! empty( $img_src ) ) : ?>
									<img class="ocdi__gl-item-image" src="<?php echo esc_url( $img_src ) ?>">
								<?php else : ?>
									<div class="ocdi__gl-item-image  ocdi__gl-item-image--no-image"><?php esc_html_e( 'No preview image.', 'pt-ocdi' ); ?></div>
								<?php endif; ?>
							</div>
							<div class="ocdi__gl-item-footer<?php echo ! empty( $import_file['preview_url'] ) ? '  ocdi__gl-item-footer--with-preview' : ''; ?>">
								<h4 class="ocdi__gl-item-title" title="<?php echo esc_attr( $import_file['import_file_name'] ); ?>"><?php echo esc_html( $import_file['import_file_name'] ); ?></h4>
								<span class="ocdi__gl-item-buttons">
									<?php if ( ! empty( $import_file['preview_url'] ) ) : ?>
										<a class="ocdi__gl-item-button  button" href="<?php echo esc_url( $import_file['preview_url'] ); ?>" target="_blank"><?php esc_html_e( 'Preview Demo', 'pt-ocdi' ); ?></a>
									<?php endif; ?>
									<button class="ocdi__gl-item-button  button  button-primary  js-ocdi-gl-import-data" value="<?php echo esc_attr( $index ); ?>"><?php esc_html_e( 'Import Demo', 'pt-ocdi' ); ?></button>
								</span>
							</div>
						</div>
					<?php endforeach; ?>
				</div>
			</div>

			<div id="js-ocdi-modal-content"></div>

		<?php endif; ?>

		<p class="ocdi__ajax-loader  js-ocdi-ajax-loader">
			<span class="spinner"></span> <?php esc_html_e( 'Importing, please wait!', 'pt-ocdi' ); ?>
		</p>

		<div class="ocdi__response  js-ocdi-ajax-response"></div>
	</div>
</div>

<?php
/**
 * Hook for adding the custom admin page footer
 */
Helpers::do_action( 'ocdi/plugin_page_footer' );
