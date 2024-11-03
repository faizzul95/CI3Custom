<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Created by PhpStorm.
 * Author: Ufuk Özdemir
 * Date: 2019-05-24
 * Time: 16:40
 */

class CRUD_files_log
{
	protected $CI;

	static $folder;
	static $year;
	static $month;
	static $day;

	static $path_year;
	static $path_month;
	static $path_day;

	static $base_url;
	static $referer;
	static $user_referer;

	public function __construct()
	{
		$this->CI = get_instance();
		$this->CI->load->library('session');

		static::$folder         = 'logs';
		static::$year           = date("Y");
		static::$month          = date("m");
		static::$day            = date("d.m.Y") . '.json';
		static::$path_year      = APPPATH . static::$folder . DIRECTORY_SEPARATOR . static::$year;
		static::$path_month     = APPPATH . static::$folder . DIRECTORY_SEPARATOR . static::$year . DIRECTORY_SEPARATOR . static::$month;
		static::$path_day       = static::$path_month . DIRECTORY_SEPARATOR . static::$day;
		static::$base_url       = $this->CI->config->item('base_url');
		static::$referer        = $this->CI->input->server('HTTP_REFERER');
		static::$user_referer   = static::$base_url == static::$referer ? DIRECTORY_SEPARATOR : str_replace(static::$base_url, '', (string)static::$referer);
	}

	public static function log_save($log_data)
	{
		if (!file_exists(static::$path_year)) {
			mkdir(static::$path_year, 0755);
		}
		if (!file_exists(static::$path_month)) {
			mkdir(static::$path_month, 0755);
		}
		if (!file_exists(static::$path_day)) {
			touch(static::$path_day);
		}
		$current_data = file_get_contents(static::$path_day);
		$array_data = json_decode($current_data, true);
		$array_data[] = $log_data;
		$data_proccesed = json_encode($array_data, JSON_UNESCAPED_SLASHES);
		file_put_contents(static::$path_day, $data_proccesed);
	}

	public static function json_data($process_type, $process_log, $model_name = NULL, $function_name = NULL)
	{
		$json_data = array(
			'user_id'           => (isLoginCheck()) ? currentUserID() : 0,
			'user_name'         => (isLoginCheck()) ? currentUserFullName() : 'guest',
			'user_role'         => (isLoginCheck()) ? currentRoleID() : 0,
			'process_type'      => $process_type,
			'model_name'        => $model_name,
			'function_name'     => $function_name,
			'process_log'       => $process_log,
			'process_time'      => timestamp('d-m-Y h:i A'),
			'process_referer'   => static::$user_referer
		);

		return $json_data;
	}

	public static function other($log_message, $model_name = NULL, $function_name = NULL)
	{
		$text = static::json_data('other', $log_message, $model_name, $function_name);
		static::log_save($text);
	}

	public static function insert($log_message, $model_name = NULL, $function_name = NULL)
	{
		$text = static::json_data('insert', $log_message, $model_name, $function_name);
		static::log_save($text);
	}

	public static function update($log_message, $model_name = NULL, $function_name = NULL)
	{
		$text = static::json_data('update', $log_message, $model_name, $function_name);
		static::log_save($text);
	}

	public static function delete($log_message, $model_name = NULL, $function_name = NULL)
	{
		$text = static::json_data('delete', $log_message, $model_name, $function_name);
		static::log_save($text);
	}

	public static function view($log_message, $model_name = NULL, $function_name = NULL)
	{
		$text = static::json_data('view', $log_message, $model_name, $function_name);
		static::log_save($text);
	}
}
