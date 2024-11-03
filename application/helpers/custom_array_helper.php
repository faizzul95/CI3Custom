<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

/**
 * Groups an array of associative arrays based on given column names.
 *
 * @param array $arr The input array to be grouped.
 * @param array $colNames An array of column names to group the input array by.
 * @return array The grouped array.
 */
if (!function_exists('groupArray')) {
    function groupArray($arr, $colNames)
    {
        // Create the grouped array
        $groupedArr = array();

        // Group the array based on the given column names
        foreach ($arr as $value) {
            $groupRef = &$groupedArr; // Start with the reference to the grouped array

            // Traverse through each column name and create nested arrays accordingly
            foreach ($colNames as $groupCol) {
                $key = $value[$groupCol];
                if (!isset($groupRef[$key])) {
                    $groupRef[$key] = array();
                }
                $groupRef = &$groupRef[$key];
            }

            // Append the value to the grouped array
            $groupRef[] = $value;
        }

        return $groupedArr;
    }
}

/**
 * Fills undefined indexes in the associative array with a default value.
 *
 * @param array $arr The input array to be filled.
 * @param array $colNames An array of column names to check and fill if undefined.
 * @param string $emptyIndexStr The default value to use when the index is undefined. Default is 'undefined'.
 * @return array The array with filled undefined indexes.
 */
if (!function_exists('fillUndefinedIndex')) {
    function fillUndefinedIndex($arr, $colNames, $emptyIndexStr = 'undefined')
    {
        // Iterate through each column name to check and fill undefined indexes
        foreach ($colNames as $colName) {
            foreach ($arr as $key => $value) {
                if (!isset($value[$colName])) {
                    // Fill the undefined index with the default value
                    $arr[$key][$colName] = $emptyIndexStr;
                }
            }
        }

        return $arr;
    }
}

/**
 * Recursively searches for the existence of a nested key in an array.
 *
 * @param string $valueSearch The dot-separated key to search for in the array.
 * @param array $array The array to search within.
 * @return bool True if the key exists, false otherwise.
 */
if (!function_exists('searchKeyExist')) {
    function searchKeyExist($valueSearch, $array)
    {
        // Split the dot-separated key into an array of nested keys
        $keys = explode('.', $valueSearch);

        // If there's only one key left, check if it exists in the array
        if (count($keys) == 1) {
            return isset($array[$keys[0]]);
        }

        // Pop the first key from the array and check if it exists in the current array
        $key = array_shift($keys);
        if (isset($array[$key])) {
            // Recursively call the function with the remaining keys and the nested array
            return searchKeyExist(implode('.', $keys), $array[$key]);
        }

        // If the key does not exist in the current array, return false
        return false;
    }
}

/**
 * Checks if the given string is a valid JSON.
 *
 * @param string $string The string to be checked.
 * @return bool Returns true if $string is a valid JSON, otherwise false.
 */
if (!function_exists('isJson')) {
    function isJson($string)
    {
        json_decode($string);
        return (json_last_error() == JSON_ERROR_NONE);
    }
}

/**
 * Checks if the given array is an associative array.
 *
 * @param array $arr The array to be checked.
 * @return bool Returns true if $arr is an associative array, otherwise false.
 */
if (!function_exists('isAssociativeArray')) {
    function isAssociativeArray($arr)
    {
        if (!empty($arr) && is_array($arr)) {
            foreach (array_keys($arr) as $key) {
                if (!is_int($key)) {
                    return true;
                }
            }
        }

        return false;
    }
}

/**
 * Checks if the given array is multidimensional.
 *
 * @param array $arr The array to be checked.
 * @return bool Returns true if $arr is multidimensional, otherwise false.
 */
if (!function_exists('isMultidimensionArray')) {
    function isMultidimensionArray($arr)
    {
        if (!empty($arr) && is_array($arr)) {
            rsort($arr); // Sort the array in reverse order.
            return isset($arr[0]) && is_array($arr[0]);
        }

        return false;
    }
}

/**
 * Explode a string into an array based on a delimiter and optionally return a specific index.
 *
 * @param string $param       The input string to be exploded.
 * @param string $type        The delimiter used to explode the string (default: ',').
 * @param int|null $returnIndex The index of the array to return (optional).
 * @return array|string|null  The exploded array or the value at the specified index (if provided).
 */
if (!function_exists('explodeArr')) {
    function explodeArr($param, $type = ',', $returnIndex = NULL)
    {
        $str = explode($type, $param);

        if (hasData($returnIndex)) {
            return $str[$returnIndex];
        }

        return $str;
    }
}

/**
 * Remove elements from an associative array based on the given key-value pair.
 *
 * @param array $arrToSearch    The input associative array to search and remove elements.
 * @param string $keyName       The key name to search for.
 * @param mixed $keyValueToRemove The value of the key to be removed.
 * @return array                The modified array with the specified elements removed.
 */
if (!function_exists('removeArraybyKey')) {
    function removeArraybyKey($arrToSearch, $keyName, $keyValueToRemove)
    {
        while (($idx = array_search($keyValueToRemove, array_column($arrToSearch, $keyName))) !== false) {
            unset($arrToSearch[$idx]);
            $arrToSearch = array_values($arrToSearch);
        }

        return $arrToSearch;
    }
}

/**
 * Extract an array of values for a specific key from an array of associative arrays.
 *
 * @param array $arrToExtract The input array containing associative arrays.
 * @param string $keyName     The key name whose values will be extracted.
 * @return array              An array containing the values of the specified key from each associative array.
 */
if (!function_exists('getKeyListFromArr')) {
    function getKeyListFromArr($arrToExtract, $keyName)
    {
        $keyList = [];
        foreach ($arrToExtract as $value) {
            if (isset($value[$keyName])) {
                $keyList[] = $value[$keyName];
            }
        }

        return $keyList;
    }
}

/**
 * Filter an array by removing elements that match values from another array based on a specified key.
 *
 * @param array $dataArr      The input array to be filtered.
 * @param array $filterArr    The array containing values to be filtered out.
 * @param string $keyName     The key name used to match values for filtering.
 * @return array              The filtered array with matching elements removed.
 */
if (!function_exists('filterArrayFromArray')) {
    function filterArrayFromArray($dataArr, $filterArr, $keyName)
    {
        $keyList = getKeyListFromArr($filterArr, $keyName);
        foreach ($keyList as $value) {
            $dataArr = removeArraybyKey($dataArr, $keyName, $value);
        }

        return $dataArr;
    }
}

/**
 * Retrieve a nested array value using dot notation from a given data array.
 *
 * @param string $string        The dot-separated string to access the nested array element.
 * @param mixed $data           The data array to extract the nested value from.
 * @param mixed|null $defaultValue The default value to return if the path is not found (default: NULL).
 *
 * @return mixed|null The value of the nested array element or the default value if not found.
 */
if (!function_exists('stringToNestedArray')) {
    function stringToNestedArray($string, $data, $defaultValue = null)
    {
        $keys = explode('.', $string);
        $result = $data;

        foreach ($keys as $key) {
            // If the key exists in the current level of the array, move to the next level.
            if (is_array($result) && array_key_exists($key, $result)) {
                $result = $result[$key];
            } elseif (is_object($result) && isset($result->$key)) {
                $result = $result->$key;
            } else {
                return $defaultValue;
            }
        }

        return $result;
    }
}

/**
 * Removes null or empty values from an associative array in place.
 *
 * @param array $array The input array from which to remove null or empty values.
 */
if (!function_exists('removeNullorEmptyValues')) {
    function removeNullorEmptyValues(array &$array)
    {
        foreach ($array as $key => $value) {
            if (is_null($value) || $value === '' || $value == 'null') {
                unset($array[$key]);
            }
        }
    }
}
