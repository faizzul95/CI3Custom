<?php if (!defined('BASEPATH')) exit('No direct script access allowed');
/**
 * Check whether the site is offline or not.
 *
 */
class Maintenance_hook
{
	public function __construct()
	{
		log_message('debug', 'Accessing maintenance hook!');
	}

	public function offline_check()
	{
		$whiteListIps = [
			// '127.0.0.1',
			// '::1',
		];

		if (file_exists('maintenance.flag') && !is_cli() && !in_array($_SERVER['REMOTE_ADDR'], $whiteListIps)) {

			header('Status: 503 Service Temporarily Unavailable');
			header('Retry-After: 7200');

			if (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
				http_response_code(503);
				header('Content-Type: application/json');
				echo json_encode(['code' => 503, 'message' => 'Service Temporarily Unavailable'], JSON_PRETTY_PRINT);
			} else {
				header('HTTP/1.1 503 Service Temporarily Unavailable');
				include(APPPATH . 'views/errors/maintenance.php');
			}

			exit;
			die;
		}
	}
}
