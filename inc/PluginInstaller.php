<?php
/**
 * Plugin Installer class - responsible for installing other plugins.
 *
 * @package ocdi
 */

namespace OCDI;

class PluginInstaller {

	/**
	 * Holds all registered plugins.
	 *
	 * @var array
	 */
	private $plugins;

	/**
	 * Initialize everything needed for the plugin installer class to function properly.
	 */
	public function init() {
		$this->set_plugins();

		add_action( 'ocdi/plugin_intaller_before_plugin_activation', array( $this, 'before_plugin_activation' ) );
		add_action( 'ocdi/plugin_intaller_after_plugin_activation', array( $this, 'after_plugin_activation' ) );

		add_action( 'wp_ajax_ocdi_install_plugin', array( $this, 'install_plugin_callback' ) );
	}

	/**
	 * Prevent the auto redirects for our recommended plugins.
	 * This code is run before plugin is activated.
	 *
	 * @param string $slug The plugin slug.
	 */
	public function before_plugin_activation( $slug ) {
		// Disable the WPForms redirect after plugin activation.
		if ( $slug === 'wpforms-lite' ) {
			update_option( 'wpforms_activation_redirect', true );
		}

		// Disable the AIOSEO redirect after plugin activation.
		if ( $slug === 'all-in-one-seo-pack' ) {
			update_option( 'aioseo_activation_redirect', true );
		}

		// Disable the WP Mail SMTP redirect after plugin activation.
		if ( $slug === 'wp-mail-smtp' ) {
			update_option( 'wp_mail_smtp_activation_prevent_redirect', true );
		}
	}

	/**
	 * Prevent the auto redirects for our recommended plugins.
	 * This code is run after plugin is activated.
	 *
	 * @param string $slug The plugin slug.
	 */
	public function after_plugin_activation( $slug ) {
		// Disable the RafflePress redirect after plugin activation.
		if ( $slug === 'rafflepress' ) {
			delete_transient('_rafflepress_welcome_screen_activation_redirect');
		}

		// Disable the MonsterInsights redirect after plugin activation.
		if ( $slug === 'google-analytics-for-wordpress' ) {
			delete_transient('_monsterinsights_activation_redirect');
		}

		// Disable the SeedProd redirect after the plugin activation.
		if ( $slug === 'coming-soon' ) {
			delete_transient( '_seedprod_welcome_screen_activation_redirect' );
		}
	}

	/**
	 * Get all partner plugins data.
	 *
	 * @return array[]
	 */
	public function get_partner_plugins() {
		return array(
			array(
				'name'        => esc_html__( 'WPForms', 'one-click-demo-import' ),
				'description' => esc_html__( 'Join 4,000,000+ professionals who build smarter forms and surveys with WPForms.', 'one-click-demo-import' ),
				'slug'        => 'wpforms-lite',
				'required'    => false,
				'preselected' => true,
			),
			array(
				'name'        => esc_html__( 'All in One SEO', 'one-click-demo-import' ),
				'description' => esc_html__( 'Use All in One SEO Pack to optimize your WordPress site for SEO.', 'one-click-demo-import' ),
				'slug'        => 'all-in-one-seo-pack',
				'required'    => false,
				'preselected' => true,
			),
			array(
				'name'        => esc_html__( 'MonsterInsights', 'one-click-demo-import' ),
				'description' => esc_html__( 'The #1 Google Analytics Plugin for WordPress that’s easy and powerful.', 'one-click-demo-import' ),
				'slug'        => 'google-analytics-for-wordpress',
				'required'    => false,
				'preselected' => true,
			),
			array(
				'name'        => esc_html__( 'Custom Landing Pages by SeedProd', 'one-click-demo-import' ),
				'description' => esc_html__( 'Work on your site in private while visitors see a "Coming Soon" or "Maintenance Mode" page.', 'one-click-demo-import' ),
				'slug'        => 'coming-soon',
				'required'    => false,
			),
			array(
				'name'        => esc_html__( 'Smash Balloon Social Photo Feed', 'one-click-demo-import' ),
				'description' => esc_html__( 'Display beautifully clean, customizable, and responsive Instagram feeds.', 'one-click-demo-import' ),
				'slug'        => 'instagram-feed',
				'required'    => false,
			),
			array(
				'name'        => esc_html__( 'WP Mail SMTP', 'one-click-demo-import' ),
				'description' => esc_html__( 'Make email delivery easy for WordPress. Connect with SMTP, Gmail, Outlook, Mailgun, and more.', 'one-click-demo-import' ),
				'slug'        => 'wp-mail-smtp',
				'required'    => false,
			),
		);
	}

	/**
	 * Set all registered plugins.
	 * With our recommended plugins being set as defaults.
	 */
	public function set_plugins() {
		$all_plugins = array_merge( $this->get_partner_plugins(), Helpers::apply_filters( 'ocdi/register_plugins', array() ) );

		$this->plugins = $this->filter_plugins( $all_plugins );
	}

	/**
	 * Get all theme registered plugins.
	 * With our 3 top recommended plugins being set as defaults.
	 */
	public function get_theme_plugins() {
		$default_plugins = $this->get_top_partner_plugins();

		$theme_plugins = array_merge( $default_plugins, Helpers::apply_filters( 'ocdi/register_plugins', array() ) );

		return $this->filter_plugins( $theme_plugins );
	}

	/**
	 * Get the top 3 partner plugins if they are not already activated.
	 *
	 * @return array
	 */
	private function get_top_partner_plugins() {
		$installed_plugins = $this->get_plugins();
		$contact_form      = [
			'wpforms-lite/wpforms.php',
			'wpforms/wpforms.php',
			'formidable/formidable.php',
			'formidable/formidable-pro.php',
			'gravityforms/gravityforms.php',
			'ninja-forms/ninja-forms.php',
		];
		$seo               = [
			'all-in-one-seo-pack/all_in_one_seo_pack.php',
			'all-in-one-seo-pack-pro/all_in_one_seo_pack.php',
			'wordpress-seo/wp-seo.php',
			'wordpress-seo-premium/wp-seo-premium.php',
			'seo-by-rank-math/rank-math.php',
			'seo-by-rank-math-pro/rank-math-pro.php',
		];
		$google_analytics  = [
			'google-analytics-for-wordpress/googleanalytics.php',
			'exactmetrics-premium/exactmetrics-premium.php',
			'google-analytics-dashboard-for-wp/gadwp.php',
		];

		$plugins = array_slice( $this->get_partner_plugins(), 0, 3 );

		$plugins = array_map( function ( $plugin ) {
			unset( $plugin['preselected'] );

			return $plugin;
		} , $plugins );

		return array_filter(
			$plugins,
			function ( $plugin ) use ( $installed_plugins, $contact_form, $seo, $google_analytics ) {
				if ( empty( $plugin['slug'] ) || empty( $plugin['name'] ) ) {
					return false;
				}

				if ( 'wpforms-lite' === $plugin['slug'] ) {
					foreach ( $installed_plugins as $basename => $plugin_info ) {
						if ( in_array( $basename, $contact_form, true ) ) {
							return false;
						}
					}
				} elseif ( 'all-in-one-seo-pack' === $plugin['slug'] ) {
					foreach ( $installed_plugins as $basename => $plugin_info ) {
						if ( in_array( $basename, $seo, true ) ) {
							return false;
						}
					}
				} elseif ( 'google-analytics-for-wordpress' === $plugin['slug'] ) {
					foreach ( $installed_plugins as $basename => $plugin_info ) {
						if ( in_array( $basename, $google_analytics, true ) ) {
							return false;
						}
					}
				}

				return true;
			}
		);
	}

	/**
	 * AJAX callback for installing a plugin.
	 * Has to contain the `slug` POST parameter.
	 */
	public function install_plugin_callback() {
		check_ajax_referer( 'ocdi-ajax-verification', 'security' );

		// Check if user has the WP capability to install plugins.
		if ( ! current_user_can( 'install_plugins' ) ) {
			wp_send_json_error( esc_html__( 'Could not install the plugin. You don\'t have permission to install plugins.', 'one-click-demo-import' ) );
		}

		$slug = ! empty( $_POST['slug'] ) ? sanitize_key( wp_unslash( $_POST['slug'] ) ) : '';

		if ( empty( $slug ) ) {
			wp_send_json_error( esc_html__( 'Could not install the plugin. Plugin slug is missing.', 'one-click-demo-import' ) );
		}

		// Check if the plugin is already installed and activated.
		if ( $this->is_plugin_active( $slug ) ) {
			wp_send_json_success( esc_html__( 'Plugin is already installed and activated!', 'one-click-demo-import' ) );
		}

		// Activate the plugin if the plugin is already installed.
		if ( $this->is_plugin_installed( $slug ) ) {
			$activated = $this->activate_plugin( $this->get_plugin_basename_from_slug( $slug ), $slug );

			if ( ! is_wp_error( $activated ) ) {
				wp_send_json_success( esc_html__( 'Plugin was already installed! We activated it for you.', 'one-click-demo-import' ) );
			} else {
				wp_send_json_error( $activated->get_error_message() );
			}
		}

		// Check for file system permissions.
		if ( ! $this->filesystem_permissions_allowed() ) {
			wp_send_json_error( esc_html__( 'Could not install the plugin. Don\'t have file permission.', 'one-click-demo-import' ) );
		}

		// Do not allow WordPress to search/download translations, as this will break JS output.
		remove_action( 'upgrader_process_complete', [ 'Language_Pack_Upgrader', 'async_upgrade' ], 20 );

		// Prep variables for Plugin_Installer_Skin class.
		$extra         = array();
		$extra['slug'] = $slug; // Needed for potentially renaming of directory name.
		$source        = $this->get_download_url( $slug );
		$api           = empty( $this->get_plugin_data( $slug )['source'] ) ? $this->get_plugins_api( $slug ) : null;
		$api           = ( false !== $api ) ? $api : null;

		if ( ! empty( $api ) && is_wp_error( $api ) ) {
			wp_send_json_error( $api->get_error_message() );
		}

		if ( ! class_exists( '\Plugin_Upgrader', false ) ) {
			require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
		}

		$skin_args = array(
			'type'   => 'web',
			'plugin' => '',
			'api'    => $api,
			'extra'  => $extra,
		);

		$upgrader = new \Plugin_Upgrader( new PluginInstallerSkin( $skin_args ) );

		$upgrader->install( $source );

		// Flush the cache and return the newly installed plugin basename.
		wp_cache_flush();

		if ( $upgrader->plugin_info() ) {
			$activated = $this->activate_plugin( $upgrader->plugin_info(), $slug );

			if ( ! is_wp_error( $activated ) ) {
				wp_send_json_success(
					esc_html__( 'Plugin installed and activated succesfully.', 'one-click-demo-import' )
				);
			} else {
				wp_send_json_success( $activated->get_error_message() );
			}
		}

		wp_send_json_error( esc_html__( 'Could not install the plugin. WP Plugin installer could not retrieve plugin information.', 'one-click-demo-import' ) );
	}

	/**
	 * Direct plugin install, without AJAX responses.
	 *
	 * @param string $slug The registered plugin slug to install.
	 *
	 * @return bool
	 */
	public function install_plugin( $slug ) {
		if ( empty( $slug ) ) {
			return false;
		}

		// Check if user has the WP capability to install plugins.
		if ( ! current_user_can( 'install_plugins' ) ) {
			return false;
		}

		// Check if the plugin is already installed and activated.
		if ( $this->is_plugin_active( $slug ) ) {
			return true;
		}

		// Activate the plugin if the plugin is already installed.
		if ( $this->is_plugin_installed( $slug ) ) {
			$activated = $this->activate_plugin( $this->get_plugin_basename_from_slug( $slug ), $slug );

			return ! is_wp_error( $activated );
		}

		// Check for file system permissions.
		if ( ! $this->filesystem_permissions_allowed() ) {
			return false;
		}

		// Do not allow WordPress to search/download translations, as this will break JS output.
		remove_action( 'upgrader_process_complete', [ 'Language_Pack_Upgrader', 'async_upgrade' ], 20 );

		// Prep variables for Plugin_Installer_Skin class.
		$extra         = array();
		$extra['slug'] = $slug; // Needed for potentially renaming of directory name.
		$source        = $this->get_download_url( $slug );
		$api           = empty( $this->get_plugin_data( $slug )['source'] ) ? $this->get_plugins_api( $slug ) : null;
		$api           = ( false !== $api ) ? $api : null;

		if ( ! empty( $api ) && is_wp_error( $api ) ) {
			return false;
		}

		if ( ! class_exists( '\Plugin_Upgrader', false ) ) {
			require_once ABSPATH . 'wp-admin/includes/class-wp-upgrader.php';
		}

		$skin_args = array(
			'type'   => 'web',
			'plugin' => '',
			'api'    => $api,
			'extra'  => $extra,
		);

		$upgrader = new \Plugin_Upgrader( new PluginInstallerSkinSilent( $skin_args ) );

		$upgrader->install( $source );

		// Flush the cache and return the newly installed plugin basename.
		wp_cache_flush();

		if ( $upgrader->plugin_info() ) {
			$activated = $this->activate_plugin( $upgrader->plugin_info(), $slug );

			if ( ! is_wp_error( $activated ) ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Activate the plugin with the before and after hooks.
	 *
	 * @param string $plugin_filename The plugin's basename(example: wpforms/wpforms.php).
	 * @param string $slug            The plugin's slug.
	 *
	 * @return null|WP_Error Null on success, WP_Error on invalid file.
	 */
	private function activate_plugin( $plugin_filename, $slug ) {
		Helpers::do_action( 'ocdi/plugin_intaller_before_plugin_activation', $slug );

		$activated = activate_plugin( $plugin_filename );

		Helpers::do_action( 'ocdi/plugin_intaller_after_plugin_activation', $slug );

		return $activated;
	}

	/**
	 * Helper function to check for the filesystem permissions.
	 *
	 * @return bool
	 */
	private function filesystem_permissions_allowed() {
		$ocdi  = OneClickDemoImport::get_instance();
		$url   = esc_url_raw( $ocdi->get_plugin_settings_url() );
		$creds = request_filesystem_credentials( $url, '', false, false, null );

		// Check for file system permissions.
		if ( false === $creds || ! WP_Filesystem( $creds ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Get the data of a registered plugin via the slug.
	 *
	 * @param string $slug The plugin slug.
	 *
	 * @return array
	 */
	public function get_plugin_data( $slug ) {
		$data = [];

		foreach ( $this->plugins as $plugin ) {
			if ( $plugin['slug'] === $slug ) {
				$data = $plugin;
				break;
			}
		}

		return $data;
	}

	/**
	 * Get the download URL for a plugin.
	 *
	 * @param  string $slug Plugin slug.
	 *
	 * @return string Plugin download URL.
	 */
	public function get_download_url( $slug ) {
		$plugin_data = $this->get_plugin_data( $slug );

		if ( ! empty( $plugin_data['source'] ) ) {
			return $plugin_data['source'];
		}

		return $this->get_wp_repo_download_url( $slug );
	}

	/**
	 * Get the download URL from the WP.org.
	 *
	 * @param string $slug Plugin slug.
	 *
	 * @return string Plugin download URL from WP.org.
	 */
	protected function get_wp_repo_download_url( $slug ) {
		$source = '';
		$api    = $this->get_plugins_api( $slug );

		if ( false !== $api && isset( $api->download_link ) ) {
			$source = $api->download_link;
		}

		return $source;
	}

	/**
	 * Try to grab information from WordPress API.
	 *
	 * @param string $slug Plugin slug.
	 *
	 * @return object Plugins_api response object on success, WP_Error on failure.
	 */
	protected function get_plugins_api( $slug ) {
		static $api = array(); // Cache received responses.

		if ( ! isset( $api[ $slug ] ) ) {
			if ( ! function_exists( 'plugins_api' ) ) {
				require_once ABSPATH . 'wp-admin/includes/plugin-install.php';
			}

			$api[ $slug ] = plugins_api( 'plugin_information', array( 'slug' => $slug, 'fields' => array( 'sections' => false ) ) );
		}

		return $api[ $slug ];
	}

	/**
	 * Wrapper around the core WP get_plugins function, making sure it's actually available.
	 *
	 * @param string $plugin_folder Optional. Relative path to single plugin folder.
	 *
	 * @return array Array of installed plugins with plugin information.
	 */
	public function get_plugins( $plugin_folder = '' ) {
		if ( ! function_exists( 'get_plugins' ) ) {
			require_once ABSPATH . 'wp-admin/includes/plugin.php';
		}

		return get_plugins( $plugin_folder );
	}

	/**
	 * Helper function to extract the plugin file path from the
	 * plugin slug, if the plugin is installed.
	 *
	 * @param string $slug Plugin slug (typically folder name) as provided by the developer.
	 *
	 * @return string|bool Either plugin file path for plugin if installed, or false.
	 */
	protected function get_plugin_basename_from_slug( $slug ) {
		$keys = array_keys( $this->get_plugins() );

		foreach ( $keys as $key ) {
			if ( preg_match( '/^' . $slug . '\//', $key ) ) {
				return $key;
			}
		}

		return false;
	}

	/**
	 * Check if a plugin is installed. Does not take must-use plugins into account.
	 *
	 * @param string $slug Plugin slug.
	 *
	 * @return bool True if installed, false otherwise.
	 */
	public function is_plugin_installed( $slug ) {
		return ( ! empty( $this->get_plugin_basename_from_slug( $slug ) ) );
	}

	/**
	 * Check if a plugin is active.
	 *
	 * @param string $slug Plugin slug.
	 *
	 * @return bool True if active, false otherwise.
	 */
	public function is_plugin_active( $slug ) {
		$plugin_path = $this->get_plugin_basename_from_slug( $slug );

		if ( empty( $plugin_path ) ) {
			return false;
		}

		return is_plugin_active( $plugin_path );
	}

	/**
	 * Get the list of plugins (with their data) of all non-active and non-installed registered plugins.
	 *
	 * @return array
	 */
	public function get_missing_plugins() {
		$missing = [];

		foreach ( $this->plugins as $plugin_data ) {
			if ( ! $this->is_plugin_active( $plugin_data['slug'] ) ) {
				$missing[] = $plugin_data;
			}
		}

		return $missing;
	}

	/**
	 * Return only plugins with required attributes:
	 * - name
	 * - slug
	 *
	 * @param array $plugins The array of plugin's data.
	 *
	 * @return array
	 */
	private function filter_plugins( $plugins ) {
		return array_filter(
			$plugins,
			function ( $plugin ) {
				if ( empty( $plugin['slug'] ) || empty( $plugin['name'] ) ) {
					return false;
				}

				return true;
			}
		);
	}
}
