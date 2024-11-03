<?php

/**
 * Dump variables with formatting.
 * 
 * @param mixed ...$params The variables to dump
 * @return void
 * 
 * Example use:
 * 
 * dump($var1, $var2, $var3);
 */
// Check if the function dump() does not exist
if (!function_exists('dump')) {
    function dump(...$params)
    {
        array_map(function ($param) {
            echo '<pre>';
            print_r($param);
            echo '</pre>';
        }, $params);
    }
}

/**
 * Dump variables and end the script.
 * 
 * @param mixed ...$params The variables to dump
 * @return void
 * 
 * Example use:
 * 
 * dd($var1, $var2, $var3);
 */
// Check if the function dd() does not exist
if (!function_exists('dd')) {
    function dd(...$params)
    {
        array_map(function ($param) {
            echo '<pre>';
            print_r($param);
            echo '</pre>';
        }, $params);
        die;
    }
}

/**
 * Dump variable to console or HTML.
 * 
 * @param mixed $var The variable to dump
 * @param bool $jsconsole Whether to output to JavaScript console
 * @return void
 * 
 * Example use:
 * 
 * d($var, true); // Output to JavaScript console
 * d($var); // Output to HTML
 */
// Check if the function d() does not exist
if (!function_exists('d')) {
    function d($var, $jsconsole = false)
    {
        if (!$jsconsole) {
            echo '<pre>';
            print_r($var);
            echo '</pre>';
        } else {
            echo '<script>console.log(' . \json_encode($var) . ')</script>';
        }
    }
}
