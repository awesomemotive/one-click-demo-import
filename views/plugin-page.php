<?php
/**
 * The plugin page view - the "settings" page of the plugin.
 *
 * @package ocdi
 */

?>

<div class="ocdi  wrap  about-wrap">

	<h1 class="ocdi__title  dashicons-before  dashicons-upload"><?php esc_html_e( 'One Click Demo Import', 'pt-ocdi' ); ?></h1>

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

	// Start output buffer for displaying the plugin intro text.
	ob_start();
	?>

	<div class="ocdi__intro-notice  notice  notice-warning  is-dismissible">
		<p><?php esc_html_e( 'Before you begin, make sure all the required plugins are activated.', 'pt-ocdi' ); ?></p>
	</div>

	<div class="ocdi__intro-text">
		<p class="about-description">
			<?php esc_html_e( 'Importing demo data (post, pages, images, theme settings, ...) is the easiest way to setup your theme.', 'pt-ocdi' ); ?>
			<?php esc_html_e( 'It will allow you to quickly edit everything instead of creating content from scratch.', 'pt-ocdi' ); ?>
		</p>

		<hr>

		<p><?php esc_html_e( 'When you import the data, the following things might happen:', 'pt-ocdi' ); ?></p>

		<ul>
			<li><?php esc_html_e( 'No existing posts, pages, categories, images, custom post types or any other data will be deleted or modified.', 'pt-ocdi' ); ?></li>
			<li><?php esc_html_e( 'Posts, pages, images, widgets and menus will get imported.', 'pt-ocdi' ); ?></li>
			<li><?php esc_html_e( 'Please click "Import Demo Data" button only once and wait, it can take a couple of minutes.', 'pt-ocdi' ); ?></li>
		</ul>

		<hr>
	</div>

	<?php
	$plugin_intro_text = ob_get_clean();

	// Display the plugin intro text (can be replaced with custom text through the filter below).
	echo wp_kses_post( apply_filters( 'pt-ocdi/plugin_intro_text', $plugin_intro_text ) );
	?>


	<?php if ( empty( $this->import_files ) ) : ?>

		<div class="notice  notice-info  is-dismissible">
			<p><?php esc_html_e( 'There are no predefined import files available in this theme. Please upload the import files manually!', 'pt-ocdi' ); ?></p>
		</div>

		<div class="ocdi__file-upload-container">
			<h2><?php esc_html_e( 'Manual demo files upload', 'pt-ocdi' ); ?></h2>

			<div class="ocdi__file-upload">
				<h3><label for="content-file-upload"><?php esc_html_e( 'Choose a XML file for content import:', 'pt-ocdi' ); ?></label></h3>
				<input id="ocdi__content-file-upload" type="file" name="content-file-upload">
			</div>

			<div class="ocdi__file-upload">
				<h3><label for="widget-file-upload"><?php esc_html_e( 'Choose a WIE or JSON file for widget import:', 'pt-ocdi' ); ?></label> <span><?php esc_html_e( '(*optional)', 'pt-ocdi' ); ?></span></h3>
				<input id="ocdi__widget-file-upload" type="file" name="widget-file-upload">
			</div>

			<div class="ocdi__file-upload">
				<h3><label for="customizer-file-upload"><?php esc_html_e( 'Choose a DAT file for customizer import:', 'pt-ocdi' ); ?></label> <span><?php esc_html_e( '(*optional)', 'pt-ocdi' ); ?></span></h3>
				<input id="ocdi__customizer-file-upload" type="file" name="customizer-file-upload">
			</div>
		</div>

	<?php elseif ( 1 < count( $this->import_files ) ) : ?>

		<div class="ocdi__multi-select-import">
			<h2><?php esc_html_e( 'Choose which demo you want to import:', 'pt-ocdi' ); ?></h2>

			<select id="ocdi__demo-import-files" class="ocdi__demo-import-files">
				<?php foreach ( $this->import_files as $index => $import_file ) : ?>
					<option value="<?php echo esc_attr( $index ); ?>">
						<?php echo esc_html( $import_file['import_file_name'] ); ?>
					</option>
				<?php endforeach; ?>
			</select>

			<?php
			// Check if at least one preview image is defined, so we can prepare the structure for display.
			$preview_image_is_defined = false;
			foreach ( $this->import_files as $import_file ) {
				if ( isset( $import_file['import_preview_image_url'] ) ) {
					$preview_image_is_defined = true;
					break;
				}
			}

			if ( $preview_image_is_defined ) :
			?>

			<div class="ocdi__demo-import-preview-container">
				<p><?php esc_html_e( 'Import preview:', 'pt-ocdi' ); ?></p>

				<p class="ocdi__demo-import-preview-image-message  js-ocdi-preview-image-message"><?php
					if ( ! isset( $this->import_files[0]['import_preview_image_url'] ) ) {
						esc_html_e( 'No preview image defined for this import.', 'pt-ocdi' );
					}
					// Leave the img tag below and the p tag above available for later changes via JS.
				?></p>

				<img id="ocdi__demo-import-preview-image" class="js-ocdi-preview-image" src="<?php echo ! empty( $this->import_files[0]['import_preview_image_url'] ) ? esc_url( $this->import_files[0]['import_preview_image_url'] ) : ''; ?>">
			</div>
			<?php endif; ?>
		</div>

	<?php endif; ?>

	<div class="ocdi__demo-import-notice  js-ocdi-demo-import-notice"><?php
		if ( is_array( $this->import_files ) && ! empty( $this->import_files[0]['import_notice'] ) ) {
			echo wp_kses_post( $this->import_files[0]['import_notice'] );
		}
	?></div>

	<p class="ocdi__button-container">
		<button class="ocdi__button  button  button-hero  button-primary  js-ocdi-import-data"><?php esc_html_e( 'Import Demo Data', 'pt-ocdi' ); ?></button>
	</p>

	<p class="ocdi__ajax-loader  js-ocdi-ajax-loader">
		<span class="spinner"></span> <?php esc_html_e( 'Importing, please wait!', 'pt-ocdi' ); ?>
	</p>

	<div class="ocdi__response  js-ocdi-ajax-response"></div>
</div>
