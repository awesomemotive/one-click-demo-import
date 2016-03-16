# One Click Demo Import #
**Contributors:** capuderg, cyman
**Tags:** import, content, demo data, widgets, settings
**Requires at least:** 4.0.0
**Tested up to:** 4.4.2
**Stable tag:** 1.0
**License:** GPLv3 or later

Import your demo data, widgets and theme settings with one click. Theme authors! Enable simple demo import for your theme demo data.

## Description ##

This plugin will create a submenu page under Appearance with the title *Import demo data*.

If the theme you are using does not have any predefined import files, then you will be presented with two file upload inputs. First one is required and you will have to upload a demo data XML file, for the actual demo import. The second one is optional and will ask you for a WIE or JSON file for widgets import.

This plugin is using the improved WP import that you can find here: https://github.com/humanmade/WordPress-Importer.

The best feature of this plugin is, that theme authors can define import files in their themes and so all you (the user of the theme) have to do is click on the "Import Demo Data" button.

How do theme author define these files?

They just need to add this code (a WP filter: `pt-ocdi/import_files`) to their themes:

	function ocdi_import_files() {
		return array(
			array(
				'import_file_name'       => 'Demo Import 1',
				'import_file_url'        => 'http://www.your_domain.com/ocdi/demo-data.xml',
				'import_widget_file_url' => 'http://www.your_domain.com/ocdi/widgets.json'
			),
			array(
				'import_file_name'       => 'Demo Import 2',
				'import_file_url'        => 'http://www.your_domain.com/ocdi/demo-data2.xml',
				'import_widget_file_url' => 'http://www.your_domain.com/ocdi/widgets2.json'
			),array(
				'import_file_name'       => 'Demo Import 3',
				'import_file_url'        => 'http://www.your_domain.com/ocdi/demo-data3.xml',
				'import_widget_file_url' => 'http://www.your_domain.com/ocdi/widgets3.json'
			),
		);
	}
	add_filter( 'pt-ocdi/import_files', 'ocdi_import_files' );


If the theme authors need to add some special after import setup (after demo data and widgets get imported), they can add the code to the `pt-ocdi/after_import` WP action. Code example:

	function ocdi_after_import( $selected_import ) {

		// Menus to Import and assign - you can remove or add as many as you want
		$main_menu = get_term_by( 'name', 'Main Menu', 'nav_menu' );

		set_theme_mod( 'nav_menu_locations', array(
			'main-menu'    => $main_menu->term_id,
			)
		);

		// Set options for front page and blog page
		$front_page_id = get_page_by_title( 'Home' );
		$blog_page_id  = get_page_by_title( 'News' );

		update_option( 'show_on_front', 'page' );
		update_option( 'page_on_front', $front_page_id->ID );
		update_option( 'page_for_posts', $blog_page_id->ID );

		// Set logo in customizer
		set_theme_mod( 'logo_img', get_template_directory_uri() . '/assets/images/logo.png' );
		set_theme_mod( 'logo2x_img', get_template_directory_uri() . '/assets/images/logo2x.png' );
	}
	add_action( 'pt-ocdi/after_import', 'ocdi_after_import' );


All progress of this plugin's work is logged in a log file in the default WP upload directory, together with the demo data and widgets import files used in the importing process.

NOTE: This plugin is still a work in progress!

NOTE: There is no setting to "connect" authors from the demo import file to the existing users in your WP site (like there is on the WP Importer plugin).

## Installation ##

Upload the One Click Demo Import plugin to your WordPress site, Activate it, and that's it.

Once the plugin is activated you will find the actual import setting page under *Appearance -> Import Demo Data*.

## Frequently Asked Questions ##

Will be added, once there will be questions to answer...

## Screenshots ##

TODO

## Changelog ##

TODO
