<?php
defined('BASEPATH') or exit('No direct script access allowed');

/*
| -------------------------------------------------------------------------
| Hooks
| -------------------------------------------------------------------------
| This file lets you define "hooks" to extend CI without hacking the core
| files.  Please see the user guide for info:
|
|	https://codeigniter.com/userguide3/general/hooks.html
|
*/

$hook = Luthier\Hook::getHooks();

$preSystem = [
	[
		'class'    => 'environment_hook',
		'function' => 'loadEnv',
		'filename' => 'environment_hook.php',
		'filepath' => 'hooks'
	],
	[
		'class'    => 'maintenance_hook',
		'function' => 'offline_check',
		'filename' => 'maintenance_hook.php',
		'filepath' => 'hooks'
	],
	[
		'class'     => '',
		'function'  => 'autoload',
		'filename'  => 'load_hook.php',
		'filepath'  => 'hooks',
		'params'    => ''
	],
];

foreach ($preSystem as $hookHelper) {
	$hook['pre_system'][] = $hookHelper;
}

$hook['post_controller_constructor'][] = array(
	'function' => 'redirect_ssl',
	'filename' => 'load_hook.php',
	'filepath' => 'hooks'
);

$hook['post_controller'][] = array(
	'class' => 'Log_Query',
	'function' => 'run',
	'filename' => 'log_query.php',
	'filepath' => 'hooks'
);

// Security improvments when using SSL
$hook['post_controller'][] = function () {
	// Check if the base url starts with HTTPS
	if (substr(base_url(), 0, 5) !== 'https') {
		return;
	}

	// If we are not using HTTPS and not in CLI
	if (!is_https() && !is_cli()) {
		// Redirect to the HTTPS version
		redirect(base_url(uri_string()));
	}

	// Get CI instance
	$CI = &get_instance();

	// Only allow HTTPS cookies (no JS)
	$CI->config->set_item('cookie_secure', TRUE);
	$CI->config->set_item('cookie_httponly', TRUE);

	// Set headers
	$CI->output->set_header("Strict-Transport-Security: max-age=2629800") // Force future requests to be over HTTPS (max-age is set to 1 month
		->set_header("X-Content-Type-Options: nosniff") // Disable MIME type sniffing
		->set_header("Referrer-Policy: strict-origin") // Only allow referrers to be sent withing the website
		->set_header("X-Frame-Options: DENY") // Frames are not allowed
		->set_header("X-XSS-Protection: 1; mode=block"); // Enable XSS protection in browser
};