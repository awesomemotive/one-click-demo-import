# Import Issues #

*Successfully importing data into WordPress is not something we can guaranty for all users. There are a lot of variables that come into play, over which we have no control. For example, one of the main issues are bad shared hosting servers. So this section of the docs is dedicated to help solve common issues with importing with our One Click Demo Import plugin.*

## Always check the log file ##

The log file should be available in the *wp-admin -> Media* section. Check for any useful information.

If you don't find the log file in the **Media** section, you can find them on your server. The log files used in the demo import will be saved to the default WordPress uploads directory. An example of that directory would be: `../wp-content/uploads/2016/07/`.

## General fall-back, if the plugin is not working ##

You should try to import the content manually, with the original [WordPress importer](https://wordpress.org/plugins/wordpress-importer/) plugin. If that does not work, then try the **gzip trick** described bellow:

### Gzip import trick ###

The "trick" is to gzip the XML import file and use it in the WordPress importer. You can gzip a file via the terminal, but you can also do it with an archiving software:

1. Download and Install 7zip (or any other software that can gzip a file).
2. Right click on your .XML file -> 7zip -> Add to Archive
3. Change the "Archive format" to gzip and hit "OK"
4. Try to import the file again (using the .gz you just created).

## Plugin can't be activated / Plugin does not work ##

This happens, because your hosting server is using a very old version of PHP. This plugin requires PHP version of at least **5.3.x**, but we recommend version **5.6.x** or even better **7.x**. Please contact your hosting company and ask them to update the PHP version for your site.

## Server error 500 ##

You clicked on the "Import Demo Data" button and the response from the server was something along the lines of:

> Server error 500

> Internal server error (500)

This usually indicates a poor server configuration, usually on a cheap shared hosting (low values for PHP settings, missing PHP modules, and so on).

There are two things you can do. You can change some One Click Demo Import settings via WordPress filters (code) or you can contact your hosting support and ask them to update some PHP settings for your site.

### Change plugin default settings ###
The most intensive task in the demo import is the image import process, which takes the most time and server memory. So, you can do two things to solve this issue:

**Change the default time of one AJAX call**

Plugin default is 25 seconds. Add this code at the end of your theme functions.php file:

	function ocdi_change_time_of_single_ajax_call() {
		return 10;
	}
	add_action( 'pt-ocdi/time_for_one_ajax_call', 'ocdi_change_time_of_single_ajax_call' );

This will "slice" the requests to smaller chunks and it might bypass the low server settings (timeouts and memory per request).

If you see that the 500 server error shows up, when the new AJAX request is being requested, then you can change the above nomber to something higher, like `return 180;`, to increase the single lenght of the AJAX request and that might resolve your issue.

**Disable the generation of smaller images during the import**

While importing, smaller versions of images are being generated, which takes up a lot of server memory, so you can disable that in the plugin with a line of code. Add this code at the end of your theme functions.php file:

`add_filter( 'pt-ocdi/regenerate_thumbnails_in_content_import', '__return_false' );`

If the import is complete and you used the above solution, please install this plugin: https://wordpress.org/plugins/regenerate-thumbnails/ and run it in Tools -> Regen. Thumbnails. This will then create the smaller versions of images, that we skipped in the import.

After the import, you should remove the added code from the functions.php file.

### Check your server settings ###

- upload_max_filesize (256M)
- max_input_time (300)
- memory_limit (256M)
- max_execution_time (300)
- post_max_size (512M)

These defaults are not perfect and it depends on how large of an import you are making. So the bigger the import, the higher the numbers should be.

### Debug your importing problem ###

If the above changes do not work, then the best thing to do is to [enable the WordPress debug mode](https://codex.wordpress.org/Debugging_in_WordPress) and try the original [WordPress importer](https://wordpress.org/plugins/wordpress-importer/), with the same XML import file.
So just set the `WP_DEBUG` constant to `true` in your *wp-config.php* file and try the original WP import plugin. You should get a more detailed description of what went wrong and you should contact your hosting company and ask them to look at this error. After they solve your issue, you can use the One Click Demo Import plugin to import your content.

### Already experienced server errors: ###

**1. missing PHP modules:**

> Fatal error: Class 'DOMDocument' not found in .../wp-content/plugins/wordpress-importer/parsers.php on line 61

That means, that your hosting server is missing one of a very common PHP modules and it has to be enabled before any import functionality will work on your site. The missing PHP modules are: **php-xml** or/and **php-dom**. Please contact your hosting company and ask them to install that for you. These are very common modules, so I don't know why they do not install them by default.

> Fatal error:  Class 'XMLReader' not found in .../wp-content/plugins/one-click-demo-import/vendor/proteusthemes/wp-content-importer-v2/src/WXRImporter.php on line 123

Similarly as above, but this time the [XMLReader](http://php.net/manual/en/book.xmlreader.php) PHP module/extention is missing. Please contact your hosting company and ask them to install that for you. These are very common modules, so I don't know why they do not install them by default.

**2. no errors, but media import failing**

If there are no errors in log file as well as no errors in the plugin importer screen or server logs, the issue might be a custom, changed WordPress installation. Some hosting providers install their own "improved" modified WordPress, with must-use plugins or other changes in the script. [We've received reports before (private link)](https://proteusthemes.zendesk.com/agent/tickets/11650) that the importer was not working due to these hosting provider "improvements":

> Our hosting and domain provider is using their "better" wordpress edition, there are some plugins added as default, i don´t know what else.
> This time i used just official wordpress and all was quick and smooth.

## Server error 504 - Gateway timeout ##
This means, that the server did not get a timely response and so it stopped with the current import. What you can try is to run the same import again. If you get the same error, you can try to run the same import a few times. A couple of import tries might finish the import till the end, becaue your server will be able to process the import data in smaller chunks.
