<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

/**
 * Check if the provided data contains non-empty values for the specified key.
 *
 * @param mixed       $data          The data to be checked (array or string).
 * @param string|null $arrKey        The key to check within the data.
 * @param bool        $returnData    If true, returns the data value if found.
 * @param mixed       $defaultValue  The default value to return if data is not found.
 *
 * @return bool|string|null Returns true if data exists, data value if $returnData is true and data exists, otherwise null or $defaultValue.
 */
if (!function_exists('hasData')) {
    function hasData($data = NULL, $arrKey = NULL, $returnData = false, $defaultValue = NULL)
    {
        // Base case 1: Check if data is not set, empty, or null
        if (!isset($data) || empty($data) || is_null($data) || $data == false) {
            return $returnData ? ($defaultValue ?? $data) : false;
        }

        // Base case 2: If arrKey is not provided, consider data itself as having data
        if (is_null($arrKey)) {
            return $returnData ? ($defaultValue ?? $data) : true;
        }

        // Replace square brackets with dots in arrKey
        $arrKey = str_replace(['[', ']'], ['.', ''], $arrKey);

        // Split the keys into an array
        $keys = explode('.', $arrKey);

        // Helper function to recursively traverse the data
        $traverse = function ($keys, $currentData) use (&$traverse, $returnData, $defaultValue) {
            if (empty($keys)) {
                return $returnData ? $currentData : true;
            }

            $key = array_shift($keys);

            // Check if $currentData is an array or an object
            if (is_array($currentData) && array_key_exists($key, $currentData)) {
                return !is_null($currentData[$key]) ? $traverse($keys, $currentData[$key]) : ($returnData ? ($defaultValue ?? NULL) : false);
            } elseif (is_object($currentData) && isset($currentData->$key)) {
                return !is_null($currentData->$key) ? $traverse($keys, $currentData->$key) : ($returnData ? ($defaultValue ?? NULL) : false);
            } else {
                // If the key doesn't exist, return the default value or false
                return $returnData ? $defaultValue : false;
            }
        };

        return $traverse($keys, $data);
    }
}

/**
 * Replaces placeholders in a string with corresponding values from the provided array.
 * Placeholders are of the form %placeholder%.
 * If a placeholder is not found in the array, the original placeholder is retained.
 *
 * @param {string} $string - The input string containing placeholders.
 * @param {Array} $arrayOfStringToReplace - An associative array containing key-value pairs for replacement.
 * @returns {string} The input string with placeholders replaced by array values.
 */
if (!function_exists('replaceTextWithData')) {
    function replaceTextWithData($string = NULL, $arrayOfStringToReplace = [])
    {
        $replacedString = str_replace(
            array_map(fn ($key) => "%$key%", array_keys($arrayOfStringToReplace)),
            array_values($arrayOfStringToReplace),
            $string
        );

        return $replacedString;
    }
}

/**
 * Check if a file exists at the given path.
 *
 * @param string|null $path The file path to check.
 * @return bool Returns true if the file exists, otherwise false.
 */
if (!function_exists('fileExist')) {
    function fileExist($path = NULL)
    {
        // Check if the path is not null and call the hasData function to validate the path.
        if (hasData($path)) {
            return file_exists($path) ? true : false;
        }

        return false;
    }
}

/**
 * Return data and set HTTP response code.
 *
 * This function logs the provided data and sets the HTTP response code.
 *
 * @param mixed $data The data to return.
 * @param int   $code The HTTP response code (default is 200).
 *
 * @return mixed The provided data.
 */
if (!function_exists('returnData')) {
	function returnData($data = null, $code = 200)
	{
		// Set the HTTP response code.
		http_response_code($code);

		// Return the provided data.
		return $data;
	}
}