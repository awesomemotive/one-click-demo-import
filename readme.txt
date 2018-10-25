=== One Click Demo Import ===
Contributors: capuderg, cyman, Prelc, proteusthemes
Tags: import, content, demo, data, widgets, settings, redux, theme options
Requires at least: 4.0.0
Tested up to: 4.9
Stable tag: 2.5.1
License: GPLv3 or later

Import your demo content, widgets and theme settings with one click. Theme authors! Enable simple demo import for your theme demo data.

== Description ==

The best feature of this plugin is, that theme authors can define import files in their themes and so all you (the user of the theme) have to do is click on the "Import Demo Data" button.

> **Are you a theme author?**
>
> Setup One Click Demo Imports for your theme and your users will thank you for it!
>
> [Follow this easy guide on how to setup this plugin for your themes!](http://proteusthemes.github.io/one-click-demo-import/)
>
> Also [sign up to the newsletter](http://proteusthemes.github.io/one-click-demo-import/#ocdi-newsletter-signup), so we can inform you about any updates, changes or improvements.

> **Are you a theme user?**
>
> Contact the author of your theme and [let them know about this plugin](http://proteusthemes.github.io/one-click-demo-import/theme-users.html). Theme authors can make any theme compatible with this plugin in 15 minutes and make it much more user-friendly.
>
> "[Where can I find the contact of the theme author?](http://proteusthemes.github.io/one-click-demo-import/theme-users.html)"

This plugin will create a submenu page under Appearance with the title **Import demo data**.

If the theme you are using does not have any predefined import files, then you will be presented with three file upload inputs. First one is required and you will have to upload a demo content XML file, for the actual demo import. The second one is optional and will ask you for a WIE or JSON file for widgets import. You create that file using the [Widget Importer & Exporter](https://wordpress.org/plugins/widget-importer-exporter/) plugin. The third one is also optional and will import the customizer settings, select the DAT file which you can generate from [Customizer Export/Import](https://wordpress.org/plugins/customizer-export-import/) plugin (the customizer settings will be imported only if the export file was created from the same theme). The final one is optional as well and will import your Redux framework settings. You can generate the export json file with the [Redux framework](https://wordpress.org/plugins/redux-framework/) plugin.

This plugin is using the improved WP import 2.0 that is still in development and can be found here: https://github.com/humanmade/WordPress-Importer.

All progress of this plugin's work is logged in a log file in the default WP upload directory, together with the demo import files used in the importing process.

NOTE: There is no setting to "connect" authors from the demo import file to the existing users in your WP site (like there is in the original WP Importer plugin). All demo content will be imported under the current user.

**Do you want to contribute?**

Please refer to the official [GitHub repository](https://github.com/proteusthemes/one-click-demo-import) of this plugin.

== Installation ==

**From your WordPress dashboard**

1. Visit 'Plugins > Add New',
2. Search for 'One Click Demo Import' and install the plugin,
3. Activate 'One Click Demo Import' from your Plugins page.

**From WordPress.org**

1. Download 'One Click Demo Import'.
2. Upload the 'one-click-demo-import' directory to your '/wp-content/plugins/' directory, using your favorite method (ftp, sftp, scp, etc...)
3. Activate 'One Click Demo Import' from your Plugins page.

**Once the plugin is activated you will find the actual import page in: Appearance -> Import Demo Data.**

== Frequently Asked Questions ==

= I have activated the plugin. Where is the "Import Demo Data" page? =

You will find the import page in *wp-admin -> Appearance -> Import Demo Data*.

= Where are the demo import files and the log files saved? =

The files used in the demo import will be saved to the default WordPress uploads directory. An example of that directory would be: `../wp-content/uploads/2016/03/`.

The log file will also be registered in the *wp-admin -> Media* section, so you can access it easily.

= How to predefine demo imports? =

This question is for theme authors. To predefine demo imports, you just have to add the following code structure, with your own values to your theme (using the `pt-ocdi/import_files` filter):

`
function ocdi_import_files() {
	return array(
		array(
			'import_file_name'           => 'Demo Import 1',
			'categories'                 => array( 'Category 1', 'Category 2' ),
			'import_file_url'            => 'http://www.your_domain.com/ocdi/demo-content.xml',
			'import_widget_file_url'     => 'http://www.your_domain.com/ocdi/widgets.json',
			'import_customizer_file_url' => 'http://www.your_domain.com/ocdi/customizer.dat',
			'import_redux'               => array(
				array(
					'file_url'    => 'http://www.your_domain.com/ocdi/redux.json',
					'option_name' => 'redux_option_name',
				),
			),
			'import_preview_image_url'   => 'http://www.your_domain.com/ocdi/preview_import_image1.jpg',
			'import_notice'              => __( 'After you import this demo, you will have to setup the slider separately.', 'your-textdomain' ),
			'preview_url'                => 'http://www.your_domain.com/my-demo-1',
		),
		array(
			'import_file_name'           => 'Demo Import 2',
			'categories'                 => array( 'New category', 'Old category' ),
			'import_file_url'            => 'http://www.your_domain.com/ocdi/demo-content2.xml',
			'import_widget_file_url'     => 'http://www.your_domain.com/ocdi/widgets2.json',
			'import_customizer_file_url' => 'http://www.your_domain.com/ocdi/customizer2.dat',
			'import_redux'               => array(
				array(
					'file_url'    => 'http://www.your_domain.com/ocdi/redux.json',
					'option_name' => 'redux_option_name',
				),
				array(
					'file_url'    => 'http://www.your_domain.com/ocdi/redux2.json',
					'option_name' => 'redux_option_name_2',
				),
			),
			'import_preview_image_url'   => 'http://www.your_domain.com/ocdi/preview_import_image2.jpg',
			'import_notice'              => __( 'A special note for this import.', 'your-textdomain' ),
			'preview_url'                => 'http://www.your_domain.com/my-demo-2',
		),
	);
}
add_filter( 'pt-ocdi/import_files', 'ocdi_import_files' );
`

You can set content import, widgets, customizer and Redux framework import files. You can also define a preview image, which will be used only when multiple demo imports are defined, so that the user will see the difference between imports. Categories can be assigned to each demo import, so that they can be filtered easily. The preview URL will display the "Preview" button in the predefined demo item, which will open this URL in a new tab and user can view how the demo site looks like.

= How to automatically assign "Front page", "Posts page" and menu locations after the importer is done? =

You can do that, with the `pt-ocdi/after_import` action hook. The code would look something like this:

`
function ocdi_after_import_setup() {
	// Assign menus to their locations.
	$main_menu = get_term_by( 'name', 'Main Menu', 'nav_menu' );

	set_theme_mod( 'nav_menu_locations', array(
			'main-menu' => $main_menu->term_id,
		)
	);

	// Assign front page and posts page (blog page).
	$front_page_id = get_page_by_title( 'Home' );
	$blog_page_id  = get_page_by_title( 'Blog' );

	update_option( 'show_on_front', 'page' );
	update_option( 'page_on_front', $front_page_id->ID );
	update_option( 'page_for_posts', $blog_page_id->ID );

}
add_action( 'pt-ocdi/after_import', 'ocdi_after_import_setup' );
`

= What about using local import files (from theme folder)? =

You have to use the same filter as in above example, but with a slightly different array keys: `local_*`. The values have to be absolute paths (not URLs) to your import files. To use local import files, that reside in your theme folder, please use the below code. Note: make sure your import files are readable!

`
function ocdi_import_files() {
	return array(
		array(
			'import_file_name'             => 'Demo Import 1',
			'categories'                   => array( 'Category 1', 'Category 2' ),
			'local_import_file'            => trailingslashit( get_template_directory() ) . 'ocdi/demo-content.xml',
			'local_import_widget_file'     => trailingslashit( get_template_directory() ) . 'ocdi/widgets.json',
			'local_import_customizer_file' => trailingslashit( get_template_directory() ) . 'ocdi/customizer.dat',
			'local_import_redux'           => array(
				array(
					'file_path'   => trailingslashit( get_template_directory() ) . 'ocdi/redux.json',
					'option_name' => 'redux_option_name',
				),
			),
			'import_preview_image_url'     => 'http://www.your_domain.com/ocdi/preview_import_image1.jpg',
			'import_notice'                => __( 'After you import this demo, you will have to setup the slider separately.', 'your-textdomain' ),
			'preview_url'                  => 'http://www.your_domain.com/my-demo-1',
		),
		array(
			'import_file_name'             => 'Demo Import 2',
			'categories'                   => array( 'New category', 'Old category' ),
			'local_import_file'            => trailingslashit( get_template_directory() ) . 'ocdi/demo-content2.xml',
			'local_import_widget_file'     => trailingslashit( get_template_directory() ) . 'ocdi/widgets2.json',
			'local_import_customizer_file' => trailingslashit( get_template_directory() ) . 'ocdi/customizer2.dat',
			'local_import_redux'           => array(
				array(
					'file_path'   => trailingslashit( get_template_directory() ) . 'ocdi/redux.json',
					'option_name' => 'redux_option_name',
				),
				array(
					'file_path'   => trailingslashit( get_template_directory() ) . 'ocdi/redux2.json',
					'option_name' => 'redux_option_name_2',
				),
			),
			'import_preview_image_url'     => 'http://www.your_domain.com/ocdi/preview_import_image2.jpg',
			'import_notice'                => __( 'A special note for this import.', 'your-textdomain' ),
			'preview_url'                  => 'http://www.your_domain.com/my-demo-2',
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

Of course you can, use the `pt-ocdi/before_widgets_import` action. You can also target different predefined demo imports like in the example above. Here is a simple example code of the `pt-ocdi/before_widgets_import` action:

`
function ocdi_before_widgets_import( $selected_import ) {
	echo "Add your code here that will be executed before the widgets get imported!";
}
add_action( 'pt-ocdi/before_widgets_import', 'ocdi_before_widgets_import' );
`

= How can I import via the WP-CLI? =

In the 2.4.0 version of this pugin we added two WP-CLI commands:

* `wp ocdi list` - Which will list any predefined demo imports currently active theme might have,
* `wp ocdi import` - which has a few options that you can use to import the things you want (content/widgets/customizer/predefined demos). Let's look at these options below.

`wp ocdi import` options:

`wp ocdi import [--content=<file>] [--widgets=<file>] [--customizer=<file>] [--predefined=<index>]`

* `--content=<file>` - will run the content import with the WP import file specified in the `<file>` parameter,
* `--widgets=<file>` - will run the widgets import with the widgets import file specified in the `<file>` parameter,
* `--customizer=<file>` - will run the customizer settings import with the customizer import file specified in the `<file>` parameter,
* `--predefined=<index>` - will run the theme predefined import with the index of the predefined import in the `<index>` parameter (you can use the `wp ocdi list` command to check which index is used for each predefined demo import)

The content, widgets and customizer options can be mixed and used at the same time. If the `predefined` option is set, then it will ignore all other options and import the predefined demo data.

= I'm a theme author and I want to change the plugin intro text, how can I do that? =

You can change the plugin intro text by using the `pt-ocdi/plugin_intro_text` filter:

`
function ocdi_plugin_intro_text( $default_text ) {
	$default_text .= '<div class="ocdi__intro-text">This is a custom text added to this plugin intro text.</div>';

	return $default_text;
}
add_filter( 'pt-ocdi/plugin_intro_text', 'ocdi_plugin_intro_text' );
`

To add some text in a separate "box", you should wrap your text in a div with a class of 'ocdi__intro-text', like in the code example above.

= How to disable generation of smaller images (thumbnails) during the content import =

This will greatly improve the time needed to import the content (images), but only the original sized images will be imported. You can disable it with a filter, so just add this code to your theme function.php file:

`add_filter( 'pt-ocdi/regenerate_thumbnails_in_content_import', '__return_false' );`

= How to change the location, title and other parameters of the plugin page? =

As a theme author you do not like the location of the "Import Demo Data" plugin page in *Appearance -> Import Demo Data*? You can change that with the filter below. Apart from the location, you can also change the title or the page/menu and some other parameters as well.

`
function ocdi_plugin_page_setup( $default_settings ) {
	$default_settings['parent_slug'] = 'themes.php';
	$default_settings['page_title']  = esc_html__( 'One Click Demo Import' , 'pt-ocdi' );
	$default_settings['menu_title']  = esc_html__( 'Import Demo Data' , 'pt-ocdi' );
	$default_settings['capability']  = 'import';
	$default_settings['menu_slug']   = 'pt-one-click-demo-import';

	return $default_settings;
}
add_filter( 'pt-ocdi/plugin_page_setup', 'ocdi_plugin_page_setup' );
`

= How to do something before the content import executes? =

In version 2.0.0 there is a new action hook: `pt-ocdi/before_content_import`, which will let you hook before the content import starts. An example of the code would look like this:

`
function ocdi_before_content_import( $selected_import ) {
	if ( 'Demo Import 1' === $selected_import['import_file_name'] ) {
		// Here you can do stuff for the "Demo Import 1" before the content import starts.
		echo "before import 1";
	}
	else {
		// Here you can do stuff for all other imports before the content import starts.
		echo "before import 2";
	}
}
add_action( 'pt-ocdi/before_content_import', 'ocdi_before_content_import' );
`

= How can I enable the `customize_save*` wp action hooks in the customizer import? =

It's easy, just add this to your theme:

`add_action( 'pt-ocdi/enable_wp_customize_save_hooks', '__return_true' );`

This will enable the following WP hooks when importing the customizer data: `customize_save`, `customize_save_*`, `customize_save_after`.


= How to configure the multi grid layout import popup confirmation? =

If you want to disable the popup confirmation modal window, use this filter:

`add_filter( 'pt-ocdi/enable_grid_layout_import_popup_confirmation', '__return_false' );`

If you want to just change some options for the jQuery modal window we use for the popup confirmation, then use this filter:

`
function my_theme_ocdi_confirmation_dialog_options ( $options ) {
	return array_merge( $options, array(
		'width'       => 300,
		'dialogClass' => 'wp-dialog',
		'resizable'   => false,
		'height'      => 'auto',
		'modal'       => true,
	) );
}
add_filter( 'pt-ocdi/confirmation_dialog_options', 'my_theme_ocdi_confirmation_dialog_options', 10, 1 );
`

= How can I disable the ProteusThemes branding notice after successful demo import? =

You can disable the branding notice with a WP filter. All you need to do is add this bit of code to your theme:

`add_filter( 'pt-ocdi/disable_pt_branding', '__return_true' );`

and the notice will not be displayed.


= I can't activate the plugin, because of a fatal error, what can I do? =

*Update: since version 1.2.0, there is now a admin error notice, stating that the minimal PHP version required for this plugin is 5.3.2.*

You want to activate the plugin, but this error shows up:

*Plugin could not be activated because it triggered a fatal error*

This happens, because your hosting server is using a very old version of PHP. This plugin requires PHP version of at least **5.3.x**, but we recommend version *5.6.x* or better yet *7.x*. Please contact your hosting company and ask them to update the PHP version for your site.

= Issues with the import, that we can't fix in the plugin =

Please visit this [docs page](https://github.com/proteusthemes/one-click-demo-import/blob/master/docs/import-problems.md), for more answers to issues with importing data.

== Screenshots ==

1. Example of multiple predefined demo imports, that a user can choose from.
2. How the import page looks like, when only one demo import is predefined.
3. Example of how the import page looks like, when no demo imports are predefined a.k.a manual import.

== Changelog ==

= 2.5.1 =

*Release Date - 25 October 2018*

* Fix missing translation strings

= 2.5.0 =

*Release Date - 8 January 2018*

* Add OCDI as a WordPress import tool in Tools -> Import,
* Add switching to the manual import, if the theme has predefined demo imports,
* Fix text domain loading

= 2.4.0 =

*Release Date - 23 August 2017*

* Add WP-CLI commands for importing with this plugin,
* Fix conflict with WooCommerce importer

= 2.3.0 =

*Release Date - 28 May 2017*

* Add preview button option to the predefined demo import items,
* Add custom JS event trigger when the import process is completed,
* Add custom filter for plugin page title,
* Remove content import as a required import. Now you can make separate imports for customizer, widgets or redux options.
* Fix custom menu widgets imports, the menus will now be set correctly.

= 2.2.1 =

*Release Date - 3 April 2017*

* Fix image importing error for server compressed files,
* Fix remapping of featured images,
* Fix custom post type existing posts check (no more multiple imports for custom post types).

= 2.2.0 =

*Release Date - 5 February 2017*

* Add ProteusThemes branding notice after successful import,
* Fix after import error reporting (duplicate errors were shown),
* Fix some undefined variables in the plugin, causing PHP notices.

= 2.1.0 =

*Release Date - 8 January 2017*

* Add grid layout import confirmation popup options filter,
* Fix term meta data double import,
* Fix WooCommerce product attributes import.

= 2.0.2 =

*Release Date - 13 December 2016*

* Fix issue with customizer options import

= 2.0.1 =

*Release Date - 12 December 2016*

* Fix issue with some browsers (Safari and IE) not supporting some FormData methods.

= 2.0.0 =

*Release Date - 10 December 2016*

* Add new layout for multiple predefined demo imports (a grid layout instead of the dropdown selector),
* Add support for Redux framework import,
* Change the code structure of the plugin (plugin rewrite, namespaces, autoloading),
* Now the whole import (content, widgets, customizer, redux) goes through even if something goes wrong in the content import (before content import errors blocked further import),
* Add `pt-ocdi/before_content_import` action hook, that theme authors can use to hook into before the content import starts,
* Fix frontend error reporting through multiple AJAX calls,
* Fix post formats (video/quote/gallery,...) not importing,
* Fix customizer import does not save some options (because of the missing WP actions - these can be enabled via a filter, more in the FAQ section).

= 1.4.0 =

*Release Date - 29 October 2016*

* Add support for WP term meta data in content importer,
* Fix the issue of having both plugins (OCDI and the new WP importer v2) activated at the same time.

= 1.3.0 =

*Release Date - 1 October 2016*

* Import/plugin page re-design. Updated the plugin page styles to match WordPress (thanks to Oliver Juhas).


= 1.2.0 =

*Release Date - 9 July 2016*

* Now also accepts predefined local import files (from theme folder),
* Fixes PHP fatal error on plugin activation, for sites using PHP versions older then 5.3.2 (added admin error notice),
* Register log file in *wp-admin -> Media*, so that it's easier to access,
* No more "[WARNING] Could not find the author for ..." messages in the log file.

= 1.1.3 =

*Release Date - 17 June 2016*

* Updated plugin design,
* Changed the plugin page setup filter name from `pt-ocdi/plugin-page-setup` to `pt-ocdi/plugin_page_setup` (mind the underscore characters instead of dashes).

= 1.1.2 =

*Release Date - 12 June 2016*

* An 'import notice' field has been added to the predefined demo import settings. This notice is displayed above the import button (it also accepts HTML),
* Now displays proper error message, if the file-system method is not set to "direct",
* This plugin is now compatible with the new [Humanmade content importer plugin](https://github.com/humanmade/WordPress-Importer),
* Added a filter to the plugin page creation, so that theme authors can now change the location of the plugin page (Demo data import) and some other parameters as well.


= 1.1.1 =

*Release Date - 22 May 2016*

* Preview import images can now be defined for multiple predefined import files (check FAQ "How to predefine demo imports?" for more info),
* You can now also import customizer settings.

= 1.1.0 =

*Release Date - 14 May 2016*

* Content import now imports in multiple AJAX calls, so there should be no more server timeout errors,
* The setting for generation of multiple image sizes in the content import is again enabled by default,
* Plugin textdomain was loaded, so that translations can be made.

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
