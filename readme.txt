=== One Click Demo Import ===
Contributors: capuderg, cyman
Tags: import, content, demo, data, widgets, settings
Requires at least: 4.0.0
Tested up to: 4.4.2
Stable tag: 1.0.0
License: GPLv3 or later

Import your demo content, widgets and theme settings with one click. Theme authors! Enable simple demo import for your theme demo data.

== Description ==

This plugin will create a submenu page under Appearance with the title **Import demo data**.

If the theme you are using does not have any predefined import files, then you will be presented with two file upload inputs. First one is required and you will have to upload a demo content XML file, for the actual demo import. The second one is optional and will ask you for a WIE or JSON file for widgets import.

This plugin is using the improved WP import that you can find here: https://github.com/humanmade/WordPress-Importer.

The best feature of this plugin is, that theme authors can define import files in their themes and so all you (the user of the theme) have to do is click on the "Import Demo Data" button.

**How do theme author define these files?** The answer is in the FAQ section.

All progress of this plugin's work is logged in a log file in the default WP upload directory, together with the demo content and widgets import files used in the importing process.

NOTE: This plugin is still a work in progress!

NOTE: There is no setting to "connect" authors from the demo import file to the existing users in your WP site (like there is in the original WP Importer plugin).

== Installation ==

Upload the One Click Demo Import plugin to your WordPress site, Activate it, and that's it.

Once the plugin is activated you will find the actual import setting page under *Appearance -> Import Demo Data*.

== Frequently Asked Questions ==

= I have activated the plugin. Where is the "Import Demo Data" page? =

You will find the import page in *wp-admin -> Appearance -> Import Demo Data*.

= Where are the demo import files and the log files saved? =

The files used in the demo import will be saved to the default WordPress uploads directory. An example of that directory would be: `../wp-content/uploads/2016/03/`.

= How to predefine demo imports? =

This question is for theme authors. To predefine demo imports, you just have to add the following code structure, with your own values to your theme (using the `pt-ocdi/import_files` filter):

`
function ocdi_import_files() {
	return array(
		array(
			'import_file_name'       => 'Demo Import 1',
			'import_file_url'        => 'http://www.your_domain.com/ocdi/demo-content.xml',
			'import_widget_file_url' => 'http://www.your_domain.com/ocdi/widgets.json'
		),
		array(
			'import_file_name'       => 'Demo Import 2',
			'import_file_url'        => 'http://www.your_domain.com/ocdi/demo-content2.xml',
			'import_widget_file_url' => 'http://www.your_domain.com/ocdi/widgets2.json'
		),
	);
}
add_filter( 'pt-ocdi/import_files', 'ocdi_import_files' );
`

= How to handle different "after import setups" depending on which predefined import was selected? =

This question might be asked by a theme author wanting to implement different after import setups for multiple predefined demo imports. Lets say we have predefined two demo imports with the following names: 'Demo Import 1' and 'Demo Import 2', the code for after import setup would be (using the `pt-ocdi/after_import` filter):

`
function ocdi_after_import( $selected_import ) {
	echo "This will be displayed on all after imports!";

	if ( 'Demo Import 1' === $selected_import['import_file_name'] ) {
		echo "This will be displayed only on after import if user selects Demo Import 1";

		// Set logo in customizer
		set_theme_mod( 'logo_img', get_template_directory_uri() . '/assets/images/logo1.png' );
	}
	elseif ( 'Demo Import 2' === $selected_import['import_file_name'] ) {
		echo "This will be displayed only on after import if user selects Demo Import 2";

		// Set logo in customizer
		set_theme_mod( 'logo_img', get_template_directory_uri() . '/assets/images/logo2.png' );
	}
}
add_action( 'pt-ocdi/after_import', 'ocdi_after_import' );
`

== Changelog ==

= 1.0.0 =

*Release Date - 25 March 2016*
