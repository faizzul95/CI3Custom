<?php

namespace App\middleware\core\traits;

defined('BASEPATH') or exit('No direct script access allowed');

trait RateLimitingThrottleTrait
{
	/**
	 * Path to the directory where throttle files are stored.
	 */
	private $throttleDir = APPPATH . 'cache/throttle/rate/';

	/**
	 * Path to the directory where throttle files for blacklist ip are stored.
	 */
	private $throttleBlackListDir = APPPATH . 'cache/throttle/blacklist/';

	/**
	 * The cache file extension to use for storing request counts and blocked IPs.
	 */
	private $cacheFileExtension = '.cache';

	/**
	 * Default rate limit in requests per second.
	 */
	private $defaultLimit = 60;

	/**
	 * Rate limit increase in requests per second per temporary block.
	 */
	private $limitIncrease = 20;

	/**
	 * The the interval time before request reset in second.
	 */
	private $limitInterval = 60;

	/**
	 * Maximum number of requests allowed before rate limiting is applied.
	 */
	private $maxWarningRequests = 20;

	/**
	 * Maximum number of temporary blocked before permanent blocked
	 */
	private $maxTemporaryBlocked = 35;

	/**
	 * Time in seconds to increase the temporary blocked time after each attempt.
	 */
	private $blockedTimeIncrease = [
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
		180, // 3 minute
		300, // 5 minute
		300, // 5 minute
		300, // 5 minute
		300, // 5 minute
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
		3600, // 1 hour
		86400, // 1 day
		604800, // 1 week
		2592000, // 1 month
		31536000, // 1 year
	];

	/**
	 * IP whitelist to bypass rate limiting.
	 */
	private $ipWhitelist = [];

	/**
	 * URL whitelist to bypass rate limiting.
	 */
	private $urlWhitelist = [];

	/**
	 * IP spoofing protection flag.
	 */
	private $spoofProtection = true;

	/**
	 * Rate type is a variable use to check. if session is exist it will use session rate session name 
	 */
	private $rateType = ['ip', 'session'];

	/**
	 * Session name use to evaluate in rate type "session"
	 */
	private $rateSessionName = 'userID';

	/**
	 * Get the client IP address.
	 */
	private function getClientIp(): string
	{
		// If the X-Forwarded-For header is present, use it to get the client IP address
		if ($this->spoofProtection && isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
			$ipChain = explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
			return trim(end($ipChain));
		}

		// Otherwise, use the remote address
		return $_SERVER['REMOTE_ADDR'];
	}

	/**
	 * Get the throttle file name for the given IP address.
	 */
	private function getThrottleFileName(string $ip): string
	{
		$sessionVariableName = $this->rateSessionName;
		$sessionValue = function_exists($sessionVariableName) ? $sessionVariableName() : getSession($sessionVariableName);

		$folderThrottle = NULL;
		$filename = '';

		if (hasData($this->rateType)) {
			if (in_array('ip', $this->rateType)) {
				$filename = 'throttle_' . preg_replace('/[^a-zA-Z0-9_\-]/', '_', $ip) . $this->cacheFileExtension;
				$folderThrottle = 'ip';
			}

			if (in_array('session', $this->rateType) && hasData($sessionValue)) {
				if (!is_bool($sessionValue)) {
					$filename = 'throttle_' . preg_replace('/[^a-zA-Z0-9_\-]/', '_', $sessionValue) . $this->cacheFileExtension;
					$folderThrottle = 'session';
				}
			}
		} else {
			$filename = 'throttle_' . preg_replace('/[^a-zA-Z0-9_\-]/', '_', $ip) . $this->cacheFileExtension;
			$folderThrottle = 'ip';
		}

		if (hasData($folderThrottle) && !is_dir($this->throttleDir . $folderThrottle)) {
			mkdir($this->throttleDir . $folderThrottle, 0755, true);
		}

		return hasData($folderThrottle) ? $folderThrottle . '/' . $filename : $filename;
	}

	/**
	 * Get the data black listed array data
	 */
	private function getBlackListData(): array
	{
		$filename = 'blacklist' . $this->cacheFileExtension;

		if (!is_dir($this->throttleBlackListDir)) {
			mkdir($this->throttleBlackListDir, 0755, true);
		}

		$filePath = $this->throttleBlackListDir . $filename;

		if (file_exists($filePath)) {
			$data = json_decode(@file_get_contents($filePath), true);
			if ($data !== null) {
				return $data;
			}
		}

		return [];
	}

	/**
	 * Check if the current client is whitelisted
	 * @return bool
	 */
	private function isWhitelisted(string $ip, string $url): bool
	{
		if (in_array($ip, $this->ipWhitelist)) {
			return true;
		}

		if (in_array($url, $this->urlWhitelist)) {
			return true;
		}

		return false;
	}

	/**
	 * Check if the current request is a spoofed IP address
	 * @return bool
	 */
	private function isSpoofedIP(string $ip): bool
	{
		if (in_array($ip, $this->ipWhitelist)) {
			return false;
		}

		// Get the IP address of the server
		if ($this->spoofProtection && isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
			$serverIP = $_SERVER['HTTP_X_FORWARDED_FOR'];
		} else {
			$serverIP = $_SERVER['REMOTE_ADDR'];
		}

		// If the IP addresses do not match, the request is being spoofed
		if ($ip !== $serverIP) {
			return true;
		}

		return false;
	}

	/**
	 * Load the throttle data for the given IP address.
	 */
	private function loadThrottleData(string $ip): array
	{
		$fileName = $this->getThrottleFileName($ip);
		$filePath = $this->throttleDir . $fileName;

		if (hasData($fileName) && file_exists($filePath)) {
			$data = json_decode(@file_get_contents($filePath), true);
			if ($data !== null) {
				return $data;
			}
		}
		return $this->getDefaultThrottleData();
	}

	/**
	 * Get the default throttle data.
	 */
	private function getDefaultThrottleData(): array
	{
		return [
			'requests' => 0,
			'warnings_count' => 0,
			'last_warning_timestamp' => NULL,
			'temp_blocked_received_count' => 0,
			'temp_blocked_timestamp' => NULL,
			'temp_blocked_until_time' => NULL,
			'is_temp_block' => false,
			'reset_request_interval' => time() + $this->limitInterval,
			'last_update' => time(),
		];
	}

	/**
	 * Save the throttle data for the given IP address.
	 */
	private function saveThrottleData(string $ip, array $data): void
	{
		$fileName = $this->getThrottleFileName($ip);
		$filePath = $this->throttleDir . $fileName;
		$data['last_update'] = time();

		if (hasData($fileName))
			@file_put_contents($filePath, json_encode($data));
	}

	/**
	 * Get the current rate limit for the given IP address.
	 */
	private function getCurrentLimit(array $throttleData): int
	{
		$blockedCount = $throttleData['temp_blocked_received_count'];
		return $this->defaultLimit + ($this->limitIncrease * $blockedCount);
	}

	/**
	 * Get the current block count for the given IP address.
	 */
	private function getCurrentBlockedCount(array $throttleData): int
	{
		return $throttleData['temp_blocked_received_count'];
	}

	/**
	 * Check if the given IP address has exceeded the maximum request count.
	 */
	private function isMaxRequestsExceeded(array $throttleData): bool
	{
		$currentRequestLimit = $this->getCurrentLimit($throttleData);
		return ($throttleData['requests'] >= $currentRequestLimit);
	}

	/**
	 * Check if the given IP address has exceeded the maximum warning count.
	 */
	private function isMaxWarningsReached(array $throttleData): bool
	{
		return ($throttleData['warnings_count'] >= $this->maxWarningRequests);
	}

	/**
	 * Check if the given IP address has exceeded the maximum temporary blocked count.
	 */
	private function isMaxTemporaryBlockedReached(array $throttleData): bool
	{
		return ($throttleData['temp_blocked_received_count'] >= $this->maxTemporaryBlocked);
	}

	/**
	 * Check if the given IP address is permanet blocked.
	 */
	private function isPermanentBlocked(string $ip): bool
	{
		$blackListIP = $this->getBlackListData();

		if (array_key_exists($ip, $blackListIP)) {
			return true;
		}

		return false;
	}

	/**
	 * Check if the given IP address is currently temporary blocked.
	 */
	private function isTempBlocked(array $throttleData): bool
	{
		return $throttleData['is_temp_block'];
	}

	/**
	 * Block the given IP address temporarily.
	 */
	private function blockIpTemporary(string $ip, array $throttleData): void
	{
		$blockedCount = $this->getCurrentBlockedCount($throttleData);

		$countSettingIncrease = count($this->blockedTimeIncrease);

		// get increase time for temporary blocked, default is 30 seconds
		$increaseTime = $blockedCount > $countSettingIncrease ? 30 : $this->blockedTimeIncrease[$blockedCount];

		$throttleData['is_temp_block'] = true;
		$throttleData['temp_blocked_received_count'] = $blockedCount + 1;
		$throttleData['temp_blocked_timestamp'] = timestamp();
		$throttleData['temp_blocked_until_time'] = time() + $increaseTime;
		$this->saveThrottleData($ip, $throttleData);
	}

	/**
	 * Block the given IP address permanent.
	 */
	private function blockIpPermanent(string $ip): void
	{
		$filename = 'blacklist' . $this->cacheFileExtension;

		if (!is_dir($this->throttleBlackListDir)) {
			mkdir($this->throttleBlackListDir, 0755, true);
		}

		$filePath = $this->throttleBlackListDir . $filename;

		$data[$ip]['time_unix'] = time();
		$data[$ip]['timestamp'] = timestamp();
		$data[$ip]['reason'] = 'abuse rate limiting';
		$data[$ip]['logs'] = $this->loadThrottleData($ip);

		@file_put_contents($filePath, json_encode($data));
		@unlink($this->throttleDir . $this->getThrottleFileName($ip)); // remove cache
	}

	/**
	 * Unblock the given IP address.
	 */
	private function unblockIp(string $ip, array $throttleData): array
	{
		$throttleData['requests'] = 0;
		$throttleData['warnings_count'] = 0;
		$throttleData['last_warning_timestamp'] = NULL;
		$throttleData['is_temp_block'] = false;
		$throttleData['temp_blocked_until_time'] = NULL;
		$this->saveThrottleData($ip, $throttleData);

		// return new data with new request interval
		return $this->loadThrottleData($ip);
	}

	/**
	 * Increment the request count for the given IP address.
	 */
	private function incrementRequestCount(string $ip, array $throttleData): void
	{
		$throttleData['requests']++;
		$this->saveThrottleData($ip, $throttleData);
	}

	/**
	 * Increment the warning count for the given IP address.
	 */
	private function incrementWarningCount(string $ip, array $throttleData): void
	{
		$throttleData['warnings_count']++;
		$throttleData['last_warning_timestamp'] = timestamp();
		$this->saveThrottleData($ip, $throttleData);
	}

	/**
	 * Check if the interval need to reset the request.
	 */
	private function isIntervalRequestReach(array $throttleData): bool
	{
		return (time() >= $throttleData['reset_request_interval']);
	}

	/**
	 * Reset the request count for the given IP address.
	 */
	private function resetRequestCount(string $ip, array $throttleData): array
	{
		$throttleData['requests'] = 0;
		$throttleData['warnings_count'] = 0;
		$throttleData['reset_request_interval'] = time() + $this->limitInterval; // set new interval
		$this->saveThrottleData($ip, $throttleData);

		// return new data with new request interval
		return $this->loadThrottleData($ip);
	}

	/**
	 * Reset the inactive IP after 2 days
	 */
	private function resetInactivityIP(string $ip, array $throttleData): array
	{
		// Calculate the difference in seconds
		$difference = time() - $throttleData['last_update'];

		// Calculate the number of seconds in 2 days
		$two_days_in_seconds = 2 * 24 * 60 * 60;

		// Reducing temporary block count
		// if total temp blocked under 10, then reset to 0. else reduce 2 temp blocked count
		$newTempBlockCount = $throttleData['temp_blocked_received_count'] < 10 ? 0 : $throttleData['temp_blocked_received_count'] - 2;

		// Compare if the difference
		if ($difference >= $two_days_in_seconds) {
			$throttleData['requests'] = 0;
			$throttleData['warnings_count'] = 0;
			$throttleData['last_warning_timestamp'] = NULL;
			$throttleData['temp_blocked_received_count'] = $newTempBlockCount;
			$throttleData['reset_request_interval'] = time() + $this->limitInterval; // set new interval

			$this->saveThrottleData($ip, $throttleData);
		}

		// return new data with new request interval
		return $this->loadThrottleData($ip);
	}

	/**
	 * Convert time() to readeable human text
	 */
	private function elapsedTime($timestamp)
	{
		$current_time = time();
		$time_difference = $timestamp - $current_time;

		$days = floor($time_difference / 86400);
		$time_difference -= $days * 86400;

		$hours = floor($time_difference / 3600);
		$time_difference -= $hours * 3600;

		$minutes = floor($time_difference / 60);
		$time_difference -= $minutes * 60;

		$seconds = $time_difference;

		$elapsed_time = array();

		if ($days > 0) {
			$elapsed_time[] = $days . ' day' . ($days > 1 ? 's' : '');
		}

		if ($hours > 0) {
			$elapsed_time[] = $hours . ' hour' . ($hours > 1 ? 's' : '');
		}

		if ($minutes > 0) {
			$elapsed_time[] = $minutes . ' minute' . ($minutes > 1 ? 's' : '');
		}

		if ($seconds > 0) {
			$elapsed_time[] = $seconds . ' second' . ($seconds > 1 ? 's' : '');
		}

		return implode(' ', $elapsed_time);
	}

	/**
	 * Function to check rate limiting
	 */
	public function isRateLimiting()
	{
		$CI = &get_instance();
		$CI->load->config('security');
		$enableThrottleData = $CI->config->item('throttle_enable');

		// check if throttle is not enabled
		if (!filter_var($enableThrottleData['rate_limiting'], FILTER_VALIDATE_BOOLEAN)) {
			return;
		} else {

			// load settings exclude URLs & IPs
			$this->urlWhitelist = array_merge($this->urlWhitelist, $CI->config->item('throttle_exclude_url'));
			$this->ipWhitelist = array_merge($this->ipWhitelist, $CI->config->item('throttle_exclude_ips'));

			$overrideData = $CI->config->item('throttle_override');
			if (filter_var($overrideData['rate_config_override'], FILTER_VALIDATE_BOOLEAN)) {
				// load settings
				$settings = $CI->config->item('throttle_settings');
				if ($settings) {
					$this->throttleDir = APPPATH . 'cache/throttle/' . $settings['rate_custom']['directory'] . '/';
					$this->defaultLimit = $settings['rate_custom']['request'];
					$this->limitInterval = $settings['rate_custom']['interval'];
					$this->limitIncrease = $settings['rate_custom']['limit_increase'];
					$this->maxWarningRequests = $settings['rate_custom']['warning'];
					$this->maxTemporaryBlocked = $settings['rate_custom']['blocked'];
					$this->blockedTimeIncrease = $settings['rate_custom']['blocked_temporary_time'];
					$this->rateType = $settings['rate_custom']['rate_type'];
					$this->rateSessionName = $settings['rate_custom']['rate_session_name'];
				}
			}

			// $ip = $CI->input->ip_address();
			$ip = $this->getClientIp();
			$url = rtrim(segment(1) . '/' . segment(2), '/');

			// check if ip/url is in whitelist
			if ($this->isWhitelisted($ip, $url)) {
				return;
			} else {

				// check if ip is currently in permanent blocked
				if ($this->isPermanentBlocked($ip)) {
					return returnData(['code' => 403, 'message' => 'You are permanently blocked'], 403);
					exit;
				}

				// get throttle data using ip
				$throttleData = $this->loadThrottleData($ip);

				// reset inactivity in certain period times.
				$throttleData = $this->resetInactivityIP($ip, $throttleData);

				// check if ip is currently in temporary blocked
				if ($this->isTempBlocked($throttleData)) {
					// check if temporary blocked has reached, then block the ip permanently
					if ($this->isMaxTemporaryBlockedReached($throttleData)) {
						$this->blockIpPermanent($ip);
						log_message('error', "IP {$ip} is permanently blocked");
						return returnData(['code' => 403, 'message' => 'You are permanently blocked, Please contact support to further information'], 403);
						exit;
					}

					// Check if current time more then temporary blocked, unblock the ip
					if (time() >= $throttleData['temp_blocked_until_time']) {
						$throttleData = $this->unblockIp($ip, $throttleData); // get the latest throttle data
					} else {
						return returnData(['code' => 429, 'message' => 'Too many requests, You are temporarily blocked. Please try again in ' . $this->elapsedTime($throttleData['temp_blocked_until_time'])], 429);
						exit;
					}
				}

				// Check if ip is spoofing
				if ($this->isSpoofedIP($ip)) {
					$this->blockIpTemporary($ip, $throttleData);
					log_message('error', "IP {$ip} is spoofed");
					return returnData(['code' => 400, 'message' => 'IP addresses do not match, IP has been temporary blocked'], 400);
					exit;
				}

				// if interval request is reach. then reset the request count to 0
				if ($this->isIntervalRequestReach($throttleData)) {
					$throttleData = $this->resetRequestCount($ip, $throttleData); // get the latest throttle data
				}

				// Check if request limit is reached
				if ($this->isMaxRequestsExceeded($throttleData)) {

					// check if warning has reach
					if ($this->isMaxWarningsReached($throttleData)) {
						$this->blockIpTemporary($ip, $throttleData);
						return returnData(['code' => 429, 'message' => 'You are temporarily blocked, Please try again later'], 429);
						exit;
					}

					$this->incrementWarningCount($ip, $throttleData);
					return returnData(['code' => 429, 'message' => 'Too many requests'], 429);
					exit;
				}

				// increate request count
				$this->incrementRequestCount($ip, $throttleData);
			}
		}
	}
}
