<?php

if (!defined('BASEPATH')) exit('No direct script access allowed');

// Check if the 'autoload' function already exists
if (!function_exists('autoload')) {
    /**
     * Autoload function to dynamically load classes when needed.
     */
    function autoload()
    {
        spl_autoload_register(function ($class) {
            // Check if the class doesn't start with 'CI_'
            if (substr($class, 0, 3) !== 'CI_') {
                $file = APPPATH . 'core/' . $class . '.php';
                // Check if the class file exists and require it
                if (file_exists($file)) {
                    require_once $file;
                }
            }
        });
    }
}

// Check if the 'redirect_ssl' function already exists
if (!function_exists('redirect_ssl')) {
    /**
	 * Redirects to HTTPS in production and staging environments.
     * Redirects to HTTP for specified exclusions.
     */
    function redirect_ssl()
    {
        $CI = get_instance();
        $class = $CI->router->class;
        $exclude = array('');

        // Check if the environment is production or staging
        if (env('ENVIRONMENT') == 'production' || env('ENVIRONMENT') == 'staging') {
            if (!in_array($class, $exclude)) {
                // Redirect to SSL (HTTP to HTTPS)
                $CI->config->config['base_url'] = str_replace('http://', 'https://', $CI->config->config['base_url']);
                // Check if the server port is not 443 (HTTPS)
                if (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] != 443) {
                    redirect($CI->uri->uri_string());
                }
            } else {
                // Redirect without SSL (HTTPS to HTTP)
                $CI->config->config['base_url'] = str_replace('https://', 'http://', $CI->config->config['base_url']);
                // Check if the server port is 443 (HTTPS)
                if (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443) {
                    redirect($CI->uri->uri_string());
                }
            }
        }
    }
}
