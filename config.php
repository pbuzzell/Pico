<?php 


// Override any of the default settings below:

$config['site_title'] = 'Development';			// Site title
$config['base_url'] = ''; 				// Override base URL (e.g. http://example.com)
$config['theme'] = 'dev'; 			// Set the theme (defaults to "default")
$config['date_format'] = 'm.d.Y';		// Set the PHP date format
$config['twig_config'] = array(			// Twig settings
	'cache' => false,					// To enable Twig caching change this to CACHE_DIR
	'autoescape' => false,				// Autoescape Twig vars
	'debug' => false					// Enable Twig debug
);
$config['pages_order_by'] = 'alpha';	// Order pages by "alpha" or "date"
$config['pages_order'] = 'desc';			// Order pages "asc" or "desc"
$config['excerpt_length'] = 55;			// The pages excerpt length (in words)
// To add a custom config setting:

$config['at_navigation']['class'] = 'nav navbar-nav';
$config['at_navigation']['exclude']['folder'] = array('blog/posts');

$config['custom_setting'] = 'Hello'; 	// Can be accessed by {{ config.custom_setting }} in a theme

