<?php
/**
 * Create Demo Content - responsible for importing pre-created demo content.
 *
 * @package ocdi
 */

namespace OCDI\CreateDemoContent;

use OCDI\Helpers;
use OCDI\Importer;
use OCDI\Logger;
use OCDI\OneClickDemoImport;
use OCDI\PluginInstaller;

class DemoContentCreator {

	/**
	 * Holds all pre-created content.
	 *
	 * @var array
	 */
	private $content;

	/**
	 * Initialize everything needed for the demo content creator class to function properly.
	 */
	public function init() {
		$this->set_content();

		add_action( 'ocdi/demo_content_creator_after_import', array( $this, 'after_import_wpforms_setup' ) );

		add_action( 'wp_ajax_ocdi_import_created_content', array( $this, 'import_created_content' ) );
	}

	/**
	 * Get all default pre-created demo content data.
	 *
	 * @return array[]
	 */
	public function get_default_content() {
		return array(
			array(
				'slug'             => 'about-page',
				'name'             => esc_html__( 'About Page', 'one-click-demo-import' ),
				'description'      => esc_html__( 'Donec nec justo eget felis facilisis fermentum. Aliquam porttitor mauris sit amet orci.', 'one-click-demo-import' ),
				'required_plugins' => array(),
			),
			array(
				'slug'             => 'contact-page',
				'file'             => OCDI_PATH . 'assets/demo-content/contact-page.xml',
				'name'             => esc_html__( 'Contact Page', 'one-click-demo-import' ),
				'description'      => esc_html__( 'Donec nec justo eget felis facilisis fermentum. Aliquam porttitor mauris sit amet orci.', 'one-click-demo-import' ),
				'required_plugins' => array( 'wpforms-lite' ),
			),
			array(
				'slug'             => 'faq-page',
				'name'             => esc_html__( 'FAQ Page', 'one-click-demo-import' ),
				'description'      => esc_html__( 'Donec nec justo eget felis facilisis fermentum. Aliquam porttitor mauris sit amet orci.', 'one-click-demo-import' ),
				'required_plugins' => array( 'google-analytics-for-wordpress' ),
			),
			array(
				'slug'             => 'coming-soon-page',
				'name'             => esc_html__( 'Coming Soon Page', 'one-click-demo-import' ),
				'description'      => esc_html__( 'Donec nec justo eget felis facilisis fermentum. Aliquam porttitor mauris sit amet orci.', 'one-click-demo-import' ),
				'required_plugins' => array( 'coming-soon', 'wpforms-lite' ),
			),
			array(
				'slug'             => 'getting-started-page',
				'name'             => esc_html__( 'Getting Started Page', 'one-click-demo-import' ),
				'description'      => esc_html__( 'Donec nec justo eget felis facilisis fermentum. Aliquam porttitor mauris sit amet orci.', 'one-click-demo-import' ),
				'required_plugins' => array( 'all-in-one-seo-pack', 'google-analytics-for-wordpress', 'wpforms-lite' ),
			),
			array(
				'slug'             => 'portfolio-page',
				'name'             => esc_html__( 'Portfolio Page', 'one-click-demo-import' ),
				'description'      => esc_html__( 'Donec nec justo eget felis facilisis fermentum. Aliquam porttitor mauris sit amet orci.', 'one-click-demo-import' ),
				'required_plugins' => array(),
			),
		);
	}

	/**
	 * Set all pre-created demo pages.
	 * With our pre-created pages being set as defaults.
	 */
	public function set_content() {
		$all_content = array_merge( $this->get_default_content(), Helpers::apply_filters( 'ocdi/register_created_demo_content', array() ) );

		$this->content = array_filter(
			$all_content,
			function ( $item ) {
				if ( empty( $item['slug'] ) || empty( $item['name'] ) || empty( $item['file'] ) ) {
					return false;
				}

				return true;
			}
		);
	}

	public function after_import_wpforms_setup( $slug ) {
		// Perform WPForms setup only if this is a contact page import.
		if ( $slug !== 'contact-page' ) {
			return;
		}

		// Is WPForms plugin active?
		$plugin_installer = new PluginInstaller();

		if (
			! (
				$plugin_installer->is_plugin_active( 'wpforms-lite' ) ||
				$plugin_installer->is_plugin_active( 'wpforms' )
			)
		) {
			wp_send_json_error( esc_html__( 'Could not complete the import process for this page. Required WPForms plugin is not activated.', 'one-click-demo-import' ) );
		}

		if ( ! function_exists( 'wpforms' ) ) {
			wp_send_json_error( esc_html__( 'Could not complete the import process for this page. Required WPForms plugin doesn\'t exist.', 'one-click-demo-import' ) );
		}

		$form_id = $this->create_wpforms_form();

		if ( empty( $form_id ) ) {
			wp_send_json_error( esc_html__( 'Could not complete the import process for this page. Something went wrong while creating a WPForms contact form.', 'one-click-demo-import' ) );
		}

		$update_page = $this->update_contact_page_form_id( $form_id );

		if ( empty( $update_page ) ) {
			wp_send_json_error( esc_html__( 'Could not complete the import process for this page. Could not update the imported page with correct WPForms form ID.', 'one-click-demo-import' ) );
		}
	}

	/**
	 * AJAX callback for importing the pre-created demo content.
	 * Has to contain the `slug` POST parameter.
	 */
	public function import_created_content() {
		check_ajax_referer( 'ocdi-ajax-verification', 'security' );

		// Check if user has the WP capability to import content.
		if ( ! current_user_can( 'import' ) ) {
			wp_send_json_error( esc_html__( 'Could not import this page. You don\'t have permission to import content.', 'one-click-demo-import' ) );
		}

		$slug = ! empty( $_POST['slug'] ) ? sanitize_key( wp_unslash( $_POST['slug'] ) ) : '';

		if ( empty( $slug ) ) {
			wp_send_json_error( esc_html__( 'Could not import this page. Page slug is missing.', 'one-click-demo-import' ) );
		}

		// Install required plugins.
		$content_item = $this->get_content_data( $slug );
		$ocdi         = OneClickDemoImport::get_instance();
		$refresh      = false;

		if ( ! empty( $content_item['required_plugins'] ) ) {
			foreach ( $content_item['required_plugins'] as $plugin_slug ) {
				if ( ! $ocdi->plugin_installer->is_plugin_active( $plugin_slug ) ) {
					$ocdi->plugin_installer->install_plugin( $plugin_slug );
					$refresh = true;
				}
			}
		}

		if ( $refresh ) {
			wp_send_json_success( [ 'refresh' => true ] );
		}

		// Import the pre-created page.
		$error = $this->import_content( $slug );

		if ( ! empty( $error ) ) {
			wp_send_json_error(
				sprintf( /* translators: %s - The actual error message. */
					esc_html__( 'An error occured while importing this page: %s', 'one-click-demo-import' ),
					esc_html( $error )
				)
			);
		}

		wp_send_json_success();
	}

	/**
	 * Get the data of a registered pre-created content via the slug.
	 *
	 * @param string $slug The pre-created content slug.
	 *
	 * @return array
	 */
	public function get_content_data( $slug ) {
		$data = [];

		foreach ( $this->content as $item ) {
			if ( $item['slug'] === $slug ) {
				$data = $item;
				break;
			}
		}

		return $data;
	}

	/**
	 * Import the content for the selected pre-created content slug.
	 *
	 * @param string $slug The pre-created content slug.
	 *
	 * @return string
	 */
	private function import_content( $slug ) {
		$import_file = $this->get_import_file( $slug );

		if ( empty( $import_file ) ) {
			return esc_html__( 'The demo content import file is missing.', 'one-click-demo-import' );
		}

		// Change the date to allow same page import multiple times.
		add_filter( 'wxr_importer.pre_process.post', function ( $data ) {
			if ( $data['post_type'] === 'page' ) {
				$data['post_date'] = date( 'Y-m-d H:i:s' );
			}

			return $data;
		} );

		// Configure logger instance and set it to the importer.
		$logger            = new Logger();
		$logger->min_level = 'warning';

		// Create importer instance with proper parameters.
		$importer = new Importer(
			array(
				'fetch_attachments'      => true,
				'prefill_existing_posts' => false,
			),
			$logger
		);

		Helpers::do_action( 'ocdi/demo_content_creater_before_import', $slug );

		ob_start();
			$importer->import( $import_file );
		$message = ob_get_clean(); // Catch any output and clear the buffers.

		Helpers::do_action( 'ocdi/demo_content_creator_after_import', $slug );

		return $importer->logger->error_output;
	}

	/**
	 * Get the demo import file for the provided slug.
	 *
	 * @param string $slug The pre-created content slug.
	 *
	 * @return string
	 */
	private function get_import_file( $slug ) {
		$content_data = $this->get_content_data( $slug );

		return ! empty( $content_data['file'] ) ? $content_data['file'] : '';
	}

	/**
	 * Create a WPForms contact form, for the pre-created Contact Page.
	 *
	 * @return false|int
	 */
	private function create_wpforms_form() {
		$title   = esc_html__( 'Contact Form', 'one-click-demo-import' );
		$form_id = wpforms()->form->add( $title );

		if ( empty( $form_id ) || is_wp_error( $form_id ) ) {
			return false;
		}

		$form_id = wpforms()->form->update(
			$form_id,
			array(
				'id'       => $form_id,
				'field_id' => '3',
				'fields'   => array(
					'0' => array(
						'id'       => '0',
						'type'     => 'name',
						'format'   => 'first-last',
						'label'    => esc_html__( 'Name', 'one-click-demo-import' ),
						'required' => '1',
						'size'     => 'medium',
					),
					'1' => array(
						'id'       => '1',
						'type'     => 'email',
						'label'    => esc_html__( 'Email', 'one-click-demo-import' ),
						'required' => '1',
						'size'     => 'medium',
					),
					'2' => array(
						'id'          => '2',
						'type'        => 'textarea',
						'label'       => esc_html__( 'Comment or Message', 'one-click-demo-import' ),
						'description' => '',
						'required'    => '1',
						'size'        => 'medium',
						'placeholder' => '',
						'css'         => '',
					),
				),
				'settings' => array(
					'form_title'             => $title,
					'notification_enable'    => '1',
					'notifications'          => array(
						'1' => array(
							'email'          => '{admin_email}',
							'sender_address' => '{admin_email}',
							'replyto'        => '{field_id="1"}',
							'message'        => '{all_fields}',
						),
					),
					'confirmations'          => array(
						'1' => array(
							'type'           => 'message',
							'message'        => esc_html__( 'Thanks for contacting us! We will be in touch with you shortly.', 'one-click-demo-import' ),
							'message_scroll' => '1',
						),
					),
					'antispam'               => '1',
					'submit_text'            => esc_html__( 'Submit', 'one-click-demo-import' ),
					'submit_text_processing' => esc_html__( 'Sending...', 'one-click-demo-import' ),
				),
			)
		);

		if ( empty( $form_id ) || is_wp_error( $form_id ) ) {
			return false;
		}

		return $form_id;
	}


	/**
	 * Find the imported contact page and update the form ID.
	 *
	 * @param int $form_id The WPForms form ID.
	 *
	 * @return bool
	 */
	private function update_contact_page_form_id( $form_id ) {
		$pages = get_posts( array(
			'post_type'  => 'page',
			'meta_query' => array(
				array(
					'key'   => 'ocdi_precreated_demo',
					'value' => 'contact-page',
				),
				array(
					'key'   => 'ocdi_precreated_demo_updated',
					'value' => 'no',
				)
			),
		) );

		if ( empty( $pages ) ) {
			return false;
		}

		$contact_page = $pages[0];

		// Replace the placeholder form ID with the newly created contact form.
		$contact_page->post_content = str_replace(
			'9999',
			(string) $form_id,
			$contact_page->post_content
		);

		$update_page = wp_update_post( $contact_page, true );

		if ( is_wp_error( $update_page ) ) {
			return false;
		}

		update_post_meta( $contact_page->ID, 'ocdi_precreated_demo_updated', 'yes' );

		return true;
	}
}
