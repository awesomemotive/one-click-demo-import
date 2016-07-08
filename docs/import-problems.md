# Import Issues #

*Successfully importing data into WordPress is not something we can guaranty for all users. There are a lot of variables that come into play, over which we have no control. For example, one of the main issues are bad shared hosting servers. So this section of the docs is dedicated to help solve common issues with importing with our One Click Demo Import plugin.*

## Always check the log file ##

The log file should be available in the *wp-admin -> Media* section. Check for any useful information.

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

The best thing to do is to [enable the WordPress debug mode](https://codex.wordpress.org/Debugging_in_WordPress) and try the original [WordPress importer](https://wordpress.org/plugins/wordpress-importer/), with the same XML import file.
So just set the `WP_DEBUG` constant to `true` in your *wp-config.php* file and try the original WP import plugin. You should get a more detailed description of what went wrong and you should contact your hosting company and ask them to look at this error.

### Already experienced server errors: ###

1. **missing PHP modules:**

> Fatal error: Class 'DOMDocument' not found in .../wp-content/plugins/wordpress-importer/parsers.php on line 61

That means, that your hosting server is missing one of a very common PHP modules and it has to be enabled before any import functionality will work on your site. The missing PHP modules are: php-xml or/and php-dom. Please contact your hosting company and ask them to install that for you. These are very common modules, so I don't know why they do not install them by default.
