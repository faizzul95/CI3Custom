
<?php

defined('BASEPATH') or exit('No direct script access allowed');

/*
|--------------------------------------------------------------------------
| Throttle Status Enable
|--------------------------------------------------------------------------
|
| Set [TRUE/FALSE] to set Throttle.
|
*/
$config['throttle_enable'] = [
	'rate_limiting' => TRUE,
	'connection_limiting' => FALSE,
	'bandwidth_limiting' => FALSE,
];

/*
|--------------------------------------------------------------------------
| Throttle Override Settings
|--------------------------------------------------------------------------
|
| Set TRUE to use override the throttle settings below or set FALSE to use default
|
*/
$config['throttle_override'] = [
	'rate_config_override' => TRUE,
	'connection_config_override' => FALSE,
	'bandwidth_config_override' => FALSE,
];

/*
|--------------------------------------------------------------------------
| Throttle Custom Configuration
|--------------------------------------------------------------------------
|
| Define all Custom Limitting configuration
*/
$config['throttle_settings'] = [

	'rate_custom' => [
		'directory' => 'rate', // set path directory to store cache
		'request' => 60, // set default limit request for client
		'interval' => 60, // set the interval before request count will be reset in seconds (eg : 5 minute = 300)
		'warning' => 20, // set maximum message "Too many requests" before temporary blocked the IP
		'blocked' => 35, // set maximum "Temporary blocked" count before permanently block IP
		'blocked_temporary_time' => [
			30, // 30 seconds
			30, // 30 seconds
			30, // 30 seconds
			30, // 30 seconds
			30, // 30 seconds
			30, // 30 seconds
			30, // 30 seconds
			45, // 45 seconds
			45, // 45 seconds
			45, // 45 seconds
			60, // 1 minute
			60, // 1 minute
			60, // 1 minute
			60, // 1 minute
			60, // 1 minute
			120, // 2 minute
			120, // 2 minute
			120, // 2 minute
			120, // 2 minute
			180, // 3 minute
			180, // 3 minute
			180, // 3 minute
			300, // 5 minute
			300, // 5 minute
			300, // 5 minute
			300, // 5 minute
			600, // 10 minute
			600, // 10 minute
			600, // 10 minute
			600, // 10 minute
			600, // 10 minute
			900, // 15 minute
			900, // 15 minute
			900, // 15 minute
			900, // 15 minute
			1800, // 30 minute
			1800, // 30 minute
			1800, // 30 minute
			1800, // 30 minute
			3600, // 1 hour
			86400, // 1 day
			604800, // 1 week
			2592000, // 1 month
			31536000, // 1 year
		], // set increase limit time each time, default is 30 seconds
		'limit_increase' => 5, // set the increase request (will extend the default) per temporary blocked. Formula : request + (limit_increase x blocked)
		'rate_type' => ['ip', 'session'], // set either rate using 'ip' OR 'session' OR both. Default : ip & session, If empty or NULL will be use ip address
		'rate_session_name' => 'currentUserID' // set session name or specific function without bracket "()" that return specific current session name to be evaluated. Default : userID. Please don't return boolean OR NULL/empty value!
	],

	'connection_custom' => [
		'directory' => 'connection', // set path directory to store cache,
		'limit' => 30, // set total connection to 30 users
		'interval' => 60, // set the interval before request count will be reset in seconds (eg : 5 minute = 300)
	],

	'bandwidth_custom' => [
		'directory' => 'bandwidth', // set path directory to store cache,
		'size' => 5, // set max total download size in MB
		'interval' => 60, // set the interval before request count will be reset in seconds (eg : 5 minute = 300)
	]
];

/*
|--------------------------------------------------------------------------
| Throttle URL Exclusions
|--------------------------------------------------------------------------
|
| Define all route to exclude for limiting (use for all limiting)
*/
$config['throttle_exclude_url'] = [
	// 'auth/sign-in',
	'auth/logout',
];

/*
|--------------------------------------------------------------------------
| Throttle IP Exclusions
|--------------------------------------------------------------------------
|
| Define all IP to exclude for limiting (use for all limiting)
*/
$config['throttle_exclude_ips'] = [
	// '127.0.0.1',
	// '::1'
];
