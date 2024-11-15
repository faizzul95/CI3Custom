<?php

if (!defined('BASEPATH')) {
	exit('No direct script access allowed');
}

// CI3 HELPERS SECTION

if (!function_exists('view')) {
	function view($page, $data = NULL, $blade = false)
	{
		$fileName = $blade ? $page . '.blade.php' : $page . '.php';

		if (file_exists(APPPATH . 'views' . DIRECTORY_SEPARATOR . $fileName)) {
			return get_instance()->load->view($fileName, $data);
		} else {
			error('404');
		}
	}
}

if (!function_exists('model')) {
	function model($modelName, $assignName = NULL)
	{
		if (hasData($assignName))
			return get_instance()->load->model($modelName, $assignName);

		return get_instance()->load->model($modelName);
	}
}

if (!function_exists('library')) {
	function library($libName)
	{
		return get_instance()->load->library($libName);
	}
}

if (!function_exists('helper')) {
	function helper($helperName)
	{
		return get_instance()->load->helper($helperName);
	}
}

if (!function_exists('error')) {
	function error($code = NULL, $data = NULL)
	{
		// get_instance()->load->view('errors/custom/error_' . $code, $data);
		if (empty($data))
			$data = ['title' => $code, 'message' => '', 'image' => asset('custom/images/nodata/404.png')];

		get_instance()->load->view('errors/custom/error_general', $data);
	}
}

// CI3 SECURITY HELPERS SECTION

if (!function_exists('input')) {
	function input($fieldName = NULL, $xss = TRUE)
	{
		return get_instance()->input->post_get($fieldName, $xss); // return with XSS Clean
	}
}

if (!function_exists('files')) {
	function files($fieldName = NULL, $relative_path  = false)
	{
		return get_instance()->security->sanitize_filename(input($fieldName), $relative_path);
	}
}

if (!function_exists('xssClean')) {
	function xssClean($data)
	{
		return get_instance()->security->xss_clean($data);
	}
}

// CI3 URL HELPERS SECTION

if (!function_exists('segment')) {
	function segment($segmentNo = 1)
	{
		return get_instance()->uri->segment($segmentNo);
	}
}

if ( ! function_exists('uri_string'))
{
	function uri_string()
	{
		return get_instance()->uri->uri_string();
	}
}

// Ci3 SESSION HELPERS SECTION

if (!function_exists('setSession')) {
	function setSession($param = NULL)
	{
		library('session');
		return get_instance()->session->set_userdata($param);
	}
}

if (!function_exists('getSession')) {
	function getSession($param = NULL)
	{
		library('session');
		return get_instance()->session->userdata($param);
	}
}

if (!function_exists('getAllSession')) {
	function getAllSession()
	{
		library('session');
		$allSession = get_instance()->session->userdata();
		unset($allSession['__ci_last_regenerate']);
		unset($allSession['PHPDEBUGBAR_STACK_DATA']);
		return $allSession;
	}
}

if (!function_exists('hasSession')) {
	function hasSession($param = NULL)
	{
		$getSession = !empty($param) ? getSession($param) : NULL;
		return !empty($getSession) ? true : false;
	}
}