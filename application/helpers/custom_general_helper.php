<?php

if (!defined('BASEPATH')) {
	exit('No direct script access allowed');
}

// CURRENCY & MONEY HELPERS SECTION

/**
 * Format a number as a money value with a specified number of decimals.
 *
 * @param float $amount The amount to format.
 * @param int $decimal The number of decimal places to include in the formatted amount (default is 2).
 * @return string The formatted amount as a string.
 */
if (!function_exists('money_format')) {
	function money_format($amount, $decimal = 2)
	{
		return number_format((float)$amount, $decimal, '.', ',');
	}
}

/**
 * Retrieve a mapping of currency codes to their respective locale settings.
 * This function returns an array where each currency code is associated with an array
 * containing symbol, pattern, code, and decimal settings for formatting the currency.
 * 
 * @return array An associative array where currency codes are keys and their locale settings are values.
 */
if (!function_exists('getCurrencyMapping')) {
	function getCurrencyMapping()
	{
		// Map the country codes to their respective locale codes
		return array(
			'USD' => ['symbol' => '$', 'pattern' => '$ #,##0.00', 'code' => 'en_US', 'decimal' => 2], // United States Dollar (USD)
			'JPY' => ['symbol' => '¥', 'pattern' => '¥ #,##0', 'code' => 'ja_JP', 'decimal' => 2], // Japanese Yen (JPY)
			'GBP' => ['symbol' => '£', 'pattern' => '£ #,##0.00', 'code' => 'en_GB', 'decimal' => 2], // British Pound Sterling (GBP)
			'EUR' => ['symbol' => '€', 'pattern' => '€ #,##0.00', 'code' => 'en_GB', 'decimal' => 2], // Euro (EUR) - Using en_GB for Euro
			'AUD' => ['symbol' => 'A$', 'pattern' => 'A$ #,##0.00', 'code' => 'en_AU', 'decimal' => 2], // Australian Dollar (AUD)
			'CAD' => ['symbol' => 'C$', 'pattern' => 'C$ #,##0.00', 'code' => 'en_CA', 'decimal' => 2], // Canadian Dollar (CAD)
			'CHF' => ['symbol' => 'CHF', 'pattern' => 'CHF #,##0.00', 'code' => 'de_CH', 'decimal' => 2], // Swiss Franc (CHF)
			'CNY' => ['symbol' => '¥', 'pattern' => '¥ #,##0.00', 'code' => 'zh_CN', 'decimal' => 2], // Chinese Yuan (CNY)
			'SEK' => ['symbol' => 'kr', 'pattern' => 'kr #,##0.00', 'code' => 'sv_SE', 'decimal' => 2], // Swedish Krona (SEK)
			'MYR' => ['symbol' => 'RM', 'pattern' => 'RM #,##0.00', 'code' => 'ms_MY', 'decimal' => 2], // Malaysian Ringgit (MYR)
			'SGD' => ['symbol' => 'S$', 'pattern' => 'S$ #,##0.00', 'code' => 'en_SG', 'decimal' => 2], // Singapore Dollar (SGD)
			'INR' => ['symbol' => '₹', 'pattern' => '₹ #,##0.00', 'code' => 'en_IN', 'decimal' => 2], // Indian Rupee (INR)
			'IDR' => ['symbol' => 'Rp', 'pattern' => 'Rp #,##0', 'code' => 'id_ID', 'decimal' => 0], // Indonesian Rupiah (IDR)
		);
	}
}

/**
 * Retrieve the currency symbol for a given currency code.
 *
 * This function checks if the provided currency code exists in a currency mapping
 * and returns the corresponding currency symbol. If the currency code is not found,
 * it returns an error message indicating an invalid country code.
 *
 * @param string|null $currencyCode The currency code for which to retrieve the symbol.
 * @return string The currency symbol or an error message if the code is invalid.
 */
if (!function_exists('currencySymbol')) {
	function currencySymbol($currencyCode = NULL)
	{
		$localeMap = getCurrencyMapping();

		if (!array_key_exists($currencyCode, $localeMap)) {
			return "Error: Invalid country code.";
		}

		return $localeMap[$currencyCode]['symbol'];
	}
}

/**
 * Format a given numeric value into a localized currency representation using the "intl" extension.
 *
 * @param float $value The numeric value to format as currency.
 * @param string|null $code (Optional) The country code to determine the currency format (e.g., 'USD', 'EUR', 'JPY', etc.).
 * @param bool $includeSymbol (Optional) Whether to include the currency symbol in the formatted output (default is false).
 * @return string The formatted currency value as a string or an error message if the "intl" extension is not installed or enabled.
 */
if (!function_exists('formatCurrency')) {
	function formatCurrency($value, $code, $includeSymbol = false)
	{
		// Check if the "intl" extension is installed and enabled
		if (!extension_loaded('intl')) {
			return 'Error: The "intl" extension is not installed or enabled, which is required for number formatting.';
		}

		if (empty($value)) {
			$value = 0.0;
		}

		// Map the country codes to their respective locale codes
		$localeMap = getCurrencyMapping();

		if (!array_key_exists($code, $localeMap)) {
			return "Error: Invalid country code.";
		}

		// Validate the $includeSymbol parameter
		if (!is_bool($includeSymbol)) {
			return "Error: \$includeSymbol parameter must be a boolean value.";
		}

		$currencyData = $localeMap[$code];

		// Create a NumberFormatter instance with the desired locale (country code)
		$formatter = new NumberFormatter($currencyData['code'], NumberFormatter::DECIMAL);
		$formatter->setAttribute(NumberFormatter::FRACTION_DIGITS, $currencyData['decimal']); // Set fraction digits

		if ($includeSymbol) {
			$formatter->setPattern($currencyData['pattern']);
		}

		// Format the currency value using the NumberFormatter
		return $formatter->formatCurrency($value, $currencyData['code']);
	}
}

// ENCODE & DECODE HELPERS SECTION

/**
 * Encode a string to Base64 format, with URL-safe characters.
 *
 * @param string $sData The data to encode to Base64.
 * @return string The Base64-encoded data with URL-safe characters.
 */
if (!function_exists('encode_base64')) {
	function encode_base64($sData = NULL)
	{
		if (hasData($sData)) {
			// Encode the data to Base64
			$sBase64 = base64_encode($sData);

			// Replace URL-unsafe characters (+ and /) with URL-safe characters (- and _)
			return strtr($sBase64, '+/', '-_');
		} else {
			// Return an empty string if input data is empty or not provided
			return '';
		}
	}
}

/**
 * Decode a Base64-encoded string with URL-safe characters.
 *
 * @param string $sData The Base64-encoded data with URL-safe characters.
 * @return string|bool The decoded data, or false if decoding fails.
 */
if (!function_exists('decode_base64')) {
	function decode_base64($sData = NULL)
	{
		if (hasData($sData)) {
			// Replace URL-safe characters (- and _) with Base64 characters (+ and /)
			$sBase64 = strtr($sData, '-_', '+/');

			// Decode the Base64-encoded data
			return base64_decode($sBase64);
		} else {
			// Return an empty string if input data is empty or not provided
			return '';
		}
	}
}

// ASSETS/URL/REDIRECT HELPERS SECTION

/**
 * Generate an asset URL.
 *
 * @param string $param The asset path.
 * @param bool $public Whether the asset is in the public directory.
 * @return string The complete asset URL.
 */
if (!function_exists('asset')) {
	function asset($param, $public = true)
	{
		// Determine the public directory based on the $public parameter.
		$isPublic = $public ? 'public/' : '';

		// Return the complete asset URL.
		return base_url() . $isPublic . $param;
	}
}

/**
 * Redirect to a different URL.
 *
 * @param string $path The path to redirect to.
 * @param bool $permanent Whether the redirect is permanent (301) or temporary (302).
 */
if (!function_exists('redirect')) {
	function redirect($path, $permanent = false)
	{
		// Set the HTTP status code based on the $permanent parameter.
		$statusCode = $permanent ? 301 : 302;

		// Perform the redirection and exit.
		header('Location: ' . url($path), true, $statusCode);
		exit();
	}
}

/**
 * Generate a URL with proper encoding and sanitization.
 *
 * @param string $param The URL path.
 * @return string The complete URL.
 */
if (!function_exists('url')) {
	function url($param)
	{
		// HTML-encode the URL parameter.
		$param = htmlspecialchars($param, ENT_NOQUOTES, 'UTF-8');

		// Return the complete URL with sanitized parameters.
		return base_url() . filter_var($param, FILTER_SANITIZE_URL);
	}
}

/**
 * Create a new instance of a class from a given namespace.
 *
 * @param string $namespace  The fully-qualified class namespace.
 * @return object            An instance of the specified class.
 * @throws Exception        If the class or method does not exist.
 */
if (!function_exists('app')) {
	function app($namespace)
	{
		return new class($namespace)
		{
			private $namespace;
			private $obj;

			public function __construct($namespace)
			{
				$this->namespace = $namespace;
				$this->obj = new $namespace();
			}

			public function __call($method, $args)
			{
				if (method_exists($this->obj, $method)) {
					return $this->obj->$method(...$args);
				}

				throw new Exception("Method $method does not exist");
			}
		};
	}
}

// GENERAL HELPERS SECTION

/**
 * Check if the request was made via AJAX.
 *
 * @return bool Returns true if the request is an AJAX request, false otherwise.
 */
if (!function_exists('isAjax')) {
	function isAjax()
	{
		return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
	}
}

/**
 * Check if the user agent corresponds to a mobile device.
 *
 * @return bool True if it's a mobile device, false otherwise.
 */
if (!function_exists('isMobileDevice')) {
	function isMobileDevice()
	{
		// Check if the HTTP_USER_AGENT server variable is not empty.
		if (!empty($_SERVER['HTTP_USER_AGENT'])) {
			// Use a regular expression to match common mobile device keywords. This pattern is case-insensitive ('i' flag).
			$pattern = "/(android|webos|avantgo|iphone|ipad|ipod|blackberry|iemobile|bolt|boost|cricket|docomo|fone|hiptop|mini|opera mini|kitkat|mobi|palm|phone|pie|tablet|up\.browser|up\.link|webos|wos)/i";

			// Use preg_match to check if the pattern matches the user agent string.
			return preg_match($pattern, $_SERVER["HTTP_USER_AGENT"]);
		}

		// If HTTP_USER_AGENT is empty, it's not a mobile device.
		return false;
	}
}

/**
 * Generate a running number with optional prefix, suffix, and leading zeros.
 *
 * @param int|string $currentNo The current number to use as a base.
 * @param string|null $prefix The prefix to add to the generated number (default: NULL).
 * @param string|null $suffix The suffix to add to the generated number (default: NULL).
 * @param string|null $separator The separator between prefix, number, and suffix (default: NULL).
 * @param int $leadingZero The number of leading zeros for the generated number (default: 5).
 * @return array An array containing 'code' (the generated running number) and 'next' (the next number in sequence).
 */
if (!function_exists('genRunningNo')) {
	function genRunningNo($currentNo, $prefix = NULL, $suffix = NULL, $separator = NULL, $leadingZero = 5)
	{
		$nextNo = $currentNo + 1;

		// Concatenate prefix and suffix with optional separator
		$pref = empty($separator) ? $prefix : $prefix . $separator;
		$suf = !empty($suffix) ? (empty($separator) ? $suffix : $separator . $suffix) : NULL;

		// Generate the running number with leading zeros
		$generatedNumber = $pref . str_pad($nextNo, $leadingZero, '0', STR_PAD_LEFT) . $suf;

		return [
			'code' => $generatedNumber,
			'next' => $nextNo
		];
	}
}

/**
 * Generates a unique code based on a given string and a list of existing codes.
 *
 * @param string $string The input string used to generate the code.
 * @param array $codeList An array containing existing codes to avoid duplicates.
 * @param string $codeType The code type prefix.
 * @param int $codeLength The length of the code generated from the string.
 * @param int $numLength The length of the numerical part of the code.
 * @param int $counter The starting number for generating unique codes.
 * @return string The generated unique code.
 */
if (!function_exists('genCodeByString')) {
	function genCodeByString($string, $codeList = array(), $codeType = 'S', $codeLength = 4, $numLength = 4, $counter = 1)
	{
		$code = '';

		// Convert the string to uppercase and split it into an array of words
		$nameArr = explode(' ', strtoupper($string));

		// Array to keep track of the current index of each word in the string
		$wordIdx = array();
		$word = 0;

		// Generate the code by taking characters from the input string based on the specified length
		while ($codeLength != strlen($code)) {
			// Wrap around the words if the code length is longer than the string
			if ($word >= count($nameArr)) {
				$word = 0;
			}

			// Initialize the word index if it's not set
			if (!isset($wordIdx[$word])) {
				$wordIdx[$word] = 0;
			}

			// Wrap around the characters in a word if the code length is longer than the word
			if ($wordIdx[$word] >= strlen($nameArr[$word])) {
				$wordIdx[$word] = 0;
			}

			// Append the character from the current word to the code
			$code .= $nameArr[$word][$wordIdx[$word]];

			// Move to the next character in the word
			$wordIdx[$word]++;
			$word++;
		}

		// If a list of existing codes is provided, ensure the generated code is unique
		if (hasData($codeList)) {
			$found = false;
			while (!$found) {
				$tempcode = $codeType . $code . str_pad($counter, $numLength, '0', STR_PAD_LEFT);

				// Check if the tempcode exists in the code list
				if (!in_array($tempcode, $codeList)) {
					$code = $tempcode;
					$found = true;
				}

				// Increment the counter to generate a new unique code if the current one already exists
				$counter++;
			}
		}

		return $code;
	}
}

/**
 * Truncates a given string to a specified length and appends a suffix if needed.
 *
 * @param string $string The input string to truncate.
 * @param int $length The maximum length of the truncated string.
 * @param string $suffix The suffix to append at the end of the truncated string.
 * @return string|null The truncated string or NULL if the input string is empty.
 */
if (!function_exists('truncateText')) {
	function truncateText($string, $length = 10, $suffix = '...')
	{
		$truncated = NULL;

		// Check if the input string has data (i.e., not empty or NULL)
		if (hasData($string)) {
			// If the string is shorter than or equal to the maximum length, return the string as is
			if (strlen($string) <= $length) {
				return $string;
			}

			// Truncate the string to the specified length
			$truncated = substr($string, 0, $length);

			// If the truncated string ends with a space, remove the space
			if (substr($truncated, -1) == ' ') {
				$truncated = substr($truncated, 0, -1);
			}

			// Append the suffix to the truncated string
			$truncated .= $suffix;
		}

		return $truncated;
	}
}

/**
 * Recursively delete a folder and its contents, excluding specified files.
 *
 * @param string $folder         The path to the folder to delete.
 * @param array  $excludedFiles  An array of files to exclude from deletion.
 * @return void
 */
if (!function_exists('deleteFolder')) {
	function deleteFolder($folder, $excludedFiles = [])
	{
		// Define the default files to exclude
		$defaultExcludedFiles = ['index.html', '.htaccess'];

		// Merge the default and user-defined excluded files
		$excFile = array_merge($defaultExcludedFiles, $excludedFiles);

		if (is_dir($folder)) {
			// Get a list of files and subdirectories in the folder
			$files = scandir($folder);

			foreach ($files as $file) {
				if ($file != '.' && $file != '..' && !in_array($file, $excFile)) {
					$filePath = $folder . DIRECTORY_SEPARATOR . $file;

					if (is_dir($filePath)) {
						// Recursively delete subdirectories
						deleteFolder($filePath, $excFile);
					} else {
						// Delete files
						unlink($filePath);
					}
				}
			}

			// Check if the folder is empty, then remove it
			if (count(glob("$folder/*")) === 0) {
				rmdir($folder);
			}
		}
	}
}

// PAGE ERROR (NODATA) HELPER

if (!function_exists('nodata')) {
	function nodata($showText = true, $filesName = '5.png')
	{
		echo "<div id='nodata' class='col-lg-12 mb-4 mt-2'>
          <center>
            <img src='" . url('public/custom/images/nodata/' . $filesName) . "' class='img-fluid mb-3' width='38%'>
            <h4 style='letter-spacing :2px; font-family: Quicksand, sans-serif !important;margin-bottom:15px'> 
             <strong> NO INFORMATION FOUND </strong>
            </h4>";
		if ($showText) {
			echo "<h6 style='letter-spacing :2px; font-family: Quicksand, sans-serif !important;font-size: 13px;'> 
                Here are some action suggestions for you to try :- 
            </h6>";
		}
		echo "</center>";
		if ($showText) {
			echo "<div class='row d-flex justify-content-center w-100'>
            <div class='col-lg m-1 text-left' style='max-width: 350px !important;letter-spacing :1px; font-family: Quicksand, sans-serif !important;font-size: 12px;'>
              1. Try the registrar function (if any).<br>
              2. Change your word or search selection.<br>
              3. Contact the system support immediately.<br>
            </div>
          </div>";
		}
		echo "</div>";
	}
}

if (!function_exists('nodataAccess')) {
	function nodataAccess($filesName = '403.png')
	{
		echo "<div id='nodata' class='col-lg-12 mb-4 mt-2'>
          <center>
            <img src='" . url('public/custom/images/nodata/' . $filesName) . "' class='img-fluid mb-2' width='30%'>
            <h3 style='letter-spacing :2px; font-family: Quicksand, sans-serif !important;margin-bottom:15px'> 
             <strong> NO ACCESS TO THIS INFORMATION </strong>
            </h3>";
		echo "</center>";
		echo "</div>";
	}
}