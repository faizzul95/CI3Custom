<?php

if (!defined('BASEPATH')) {
	exit('No direct script access allowed');
}

/**
 * Get the current date in the specified format.
 *
 * @param string $format The format to use for the date. Default is 'Y-m-d'.
 * @return string The current date in the specified format.
 */
if (!function_exists('currentDate')) {
    function currentDate($format = 'Y-m-d')
    {
        setAppTimezone();
        return date($format);
    }
}

/**
 * Get the current time in the specified format.
 *
 * @param string $format The format to use for the time. Default is 'H:i:s'.
 * @return string The current time in the specified format.
 */
if (!function_exists('currentTime')) {
    function currentTime($format = 'H:i:s')
    {
        setAppTimezone();
        return date($format);
    }
}

/**
 * Format a given date string to the specified format.
 *
 * @param string $date The date string to format.
 * @param string $format The format to use for the date. Default is 'd.m.Y'.
 * @param mixed $defaultValue The value to return if the date is empty. Default is NULL.
 * @return string|null The formatted date string or the default value if the input date is empty.
 */
if (!function_exists('formatDate')) {
    function formatDate($date, $format = 'd.m.Y', $defaultValue = NULL)
    {
        setAppTimezone();
        return hasData($date) ? date($format, strtotime($date)) : $defaultValue;
    }
}

/**
 * Get the current timestamp in the specified format.
 *
 * @param string $format The format to use for the timestamp. Default is 'Y-m-d H:i:s'.
 * @return string The current timestamp in the specified format.
 */
if (!function_exists('timestamp')) {
    function timestamp($format = 'Y-m-d H:i:s')
    {
        setAppTimezone();
        return date($format);
    }
}

/**
 * Calculate the difference in days between two dates.
 *
 * @param string $d1 The first date.
 * @param string $d2 The second date.
 * @return int The difference in days between the two dates.
 */
if (!function_exists('dateDiff')) {
    function dateDiff($d1, $d2)
    {
        setAppTimezone();
        return round(abs(strtotime($d1) - strtotime($d2)) / 86400);
    }
}

/**
 * Calculate the difference in minutes between two times.
 *
 * @param string $t1 The first time.
 * @param string $t2 The second time.
 * @return int The difference in minutes between the two times.
 */
if (!function_exists('timeDiff')) {
    function timeDiff($t1, $t2)
    {
        setAppTimezone();
        return round(abs(strtotime($t1) - strtotime($t2)) / 60);
    }
}

/**
 * Set the default timezone to the application timezone specified in the environment.
 */
if (!function_exists('setAppTimezone')) {
    function setAppTimezone()
    {
        date_default_timezone_set('Asia/Kuala_Lumpur');
    }
}
