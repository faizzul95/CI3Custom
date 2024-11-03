<?php

include_once FCPATH . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

if (!defined('BASEPATH')) exit('No direct script access allowed');

if (!function_exists('value')) {
	/**
	 * Return the default value of the given value.
	 *
	 * @param mixed $value
	 *
	 * @return mixed
	 */
	function value($value = NULL)
	{
		return $value instanceof Closure ? $value() : $value;
	}
}

if (!function_exists('env')) {
	/**
	 * Gets the value of an environment variable.
	 *
	 * @param string $key
	 * @param mixed  $default
	 *
	 * @return mixed
	 */
	function env($key = NULL, $default = NULL)
	{
		if (file_exists(FCPATH . '/.env')) {
			$dotenv = Dotenv\Dotenv::createUnsafeImmutable(FCPATH);
			$dotenv->load();
		} else {
			die('env file not found');
		}

		$value = getenv($key);

		if ($value === false) {
			return value($default);
		}

		switch (strtolower($value)) {
			case 'true':
			case '(true)':
				return true;
			case 'false':
			case '(false)':
				return false;
			case 'empty':
			case '(empty)':
				return '';
			case 'null':
			case '(null)':
				return;
		}

		if (($valueLength = strlen($value)) > 1 && $value[0] === '"' && $value[$valueLength - 1] === '"') {
			return substr($value, 1, -1);
		}

		return $value;
	}
}

if (!function_exists('env_all')) {
	/**
	 * Gets all environment variables from the .env file.
	 *
	 * @return array An associative array of environment variables.
	 */
	function env_all()
	{
		if (file_exists(FCPATH . '/.env')) {
			$dotenv = Dotenv\Dotenv::createUnsafeImmutable(FCPATH);
			$dotenv->load();
		} else {
			die('env file not found');
		}

		return $_ENV ?? [];
	}
}

// DUMPER HELPER

/**
 * Dump variables to the output in a human-readable format.
 *
 * @param mixed ...$args
 */
if (!function_exists('d')) {
	function d(...$args)
	{
		array_map(function ($param) {
			echo '<pre>';
			print_r($param);
			echo '</pre>';
		}, $args);
	}
}

/**
 * Dump variables to the output in a human-readable format and terminate the script.
 *
 * @param mixed ...$args
 */
if (!function_exists('ddd')) {
	function ddd(...$args)
	{
		array_map(function ($param) {
			echo '<pre>';
			print_r($param);
			echo '</pre>';
		}, $args);
		die;
	}
}

/**
 * Log an action related to CRUD operations.
 *
 * @param string $type          The type of action (e.g., 'view', 'create', 'update', 'delete').
 * @param string|null $message  Additional information or description for the log entry.
 * @param string|null $model    The name of the model associated with the action.
 * @param string|null $function The name of the function/method where the action is logged.
 */
if (!function_exists('Logs')) {
	function Logs($type = 'view', $message = null, $model_name = null, $function_name = null)
	{
		CRUD_files_log::$type($message, $model_name, $function_name);
	}
}
