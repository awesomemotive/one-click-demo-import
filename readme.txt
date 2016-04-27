=== One Click Demo Import ===
Contributors: capuderg, cyman
Tags: import, content, demo, data, widgets, settings
Requires at least: 4.0.0
Tested up to: 4.5
Stable tag: 1.0.3
License: GPLv3 or later

Import your demo content, widgets and theme settings with one click. Theme authors! Enable simple demo import for your theme demo data.

== Description ==

This plugin will create a submenu page under Appearance with the title **Import demo data**.

If the theme you are using does not have any predefined import files, then you will be presented with two file upload inputs. First one is required and you will have to upload a demo content XML file, for the actual demo import. The second one is optional and will ask you for a WIE or JSON file for widgets import. You create that file using the [Widget Importer & Exporter](https://wordpress.org/plugins/widget-importer-exporter/) plugin.

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

= Can I add some code before the widgets get imported? =

Of course you can, use the `pt-ocdi/before_widgets_import` filter. You can also target different predefined demo imports like in the example above. Here is a simple example code of the `pt-ocdi/before_widgets_import` filter:

`
function ocdi_before_widgets_import( $selected_import ) {
	echo "Add your code here that will be executed before the widgets get imported!";
}
add_action( 'pt-ocdi/before_widgets_import', 'ocdi_before_widgets_import' );
`

= I'm a theme author and I want to change the plugin intro text, how can I do that? =

You can change the plugin intro text by using the `pt-ocdi/plugin_intro_text` filter:

`
function ocdi_plugin_intro_text( $default_text ) {
	$default_text .= '<div class="ocdi__intro-text">This is a custom text added to this plugin intro text.</div>';

	return $default_text;
}
add_action( 'pt-ocdi/plugin_intro_text', 'ocdi_plugin_intro_text' );
`

To add some text in a separate "box", you should wrap your text in a div with a class of 'ocdi__intro-text', like in the code example above.

= I can't activate the plugin, because of a fatal error, what can I do? =

You want to activate the plugin, but this error shows up:

*Plugin could not be activated because it triggered a fatal error*

This happens, because your hosting server is using a very old version of PHP. This plugin requires PHP version of at least **5.3.x**, but we recommend version *5.6.x*. Please contact your hosting company and ask them to update the PHP version for your site.

== Changelog ==

= 1.0.3 =

*Release Date - 27 April 2016*

* Added filter to enable image regeneration,
* Added filter to change the plugin intro text,
* Added action to execute custom code before widget import,
* Disabled author imports.

= 1.0.2 =

*Release Date - 15 April 2016*

* Monkey fix for WP version 4.5. - disabled generation of multiple image sizes in the content import.

= 1.0.1 =

*Release Date - 2 April 2016*

Small code fixes:

* Fixed undefined variable bug,
* Fixed naming of downloaded files and their filters.

= 1.0.0 =

*Release Date - 25 March 2016*

* Initial release!
