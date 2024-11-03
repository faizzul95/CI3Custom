<?php

defined('BASEPATH') or exit('No direct script access allowed');

class Database_logger
{
	/**
	 * ci
	 *
	 * @param instance object
	 */
	private $_ci;

	/**
	 * log table name
	 *
	 * @param string
	 */
	private $_log_enable;
	private $_log_table_name;

	public $levels = array(
		E_ERROR             => 'Error',
		E_WARNING           => 'Warning',
		E_PARSE             => 'Parsing Error',
		E_NOTICE            => 'Notice',
		E_CORE_ERROR        => 'Core Error',
		E_CORE_WARNING      => 'Core Warning',
		E_COMPILE_ERROR     => 'Compile Error',
		E_COMPILE_WARNING   => 'Compile Warning',
		E_USER_ERROR        => 'User Error',
		E_USER_WARNING      => 'User Warning',
		E_USER_NOTICE       => 'User Notice',
		E_STRICT            => 'Runtime Notice',
		E_RECOVERABLE_ERROR => 'Catchable error',
		E_DEPRECATED        => 'Runtime Notice',
		E_USER_DEPRECATED   => 'User Warning'
	);

	/**
	 * constructor
	 *
	 */
	public function __construct()
	{
		$this->_ci = &get_instance();
		set_error_handler(array($this, 'error_handler'));
		set_exception_handler(array($this, 'exception_handler'));
		// Load database driver
		$this->_ci->load->database();
		// Load config file
		$this->_ci->load->config('customs/log');
		$this->_log_enable = ($this->_ci->config->item('log_enable')) ? $this->_ci->config->item('log_enable') : false;
		$this->_log_table_name = ($this->_ci->config->item('log_table_name')) ? $this->_ci->config->item('log_table_name') : 'system_logs';
	}

	/**
	 * PHP Error Handler
	 *
	 * @param   int
	 * @param   string
	 * @param   string
	 * @param   int
	 * @return void
	 */
	public function error_handler($severity, $message, $filepath, $line)
	{

		// error exempt server
		$errorExempt = array(
			'err1' => "Undefined index: times ",
			'err2' => "getimagesize(): http:// wrapper is disabled in the server configuration by allow_url_fopen=0",
			'err3' => "file_get_contents(): http:// wrapper is disabled in the server configuration by allow_url_fopen=0// ",
			'err4' => "chr() expects parameter 1 to be int, string given",
			'err5' => "Trying to access array offset on value of type null",
		);

		$data = array(
			'errno' => $severity,
			'errtype' => isset($this->levels[$severity]) ? $this->levels[$severity] : $severity,
			'errstr' => $message,
			'errfile' => $filepath,
			'errline' => $line,
			'user_agent' => $this->_ci->input->user_agent(),
			'ip_address' => $this->_ci->input->ip_address(),
			'time' => date('Y-m-d H:i:s')
		);

		if (!in_array($message, $errorExempt)) {
			$this->_ci->db->insert($this->_log_table_name, $data);
		}
	}

	/**
	 * PHP Error Handler
	 *
	 * @param   object
	 * @return void
	 */
	public function exception_handler($exception)
	{
		if($this->_log_enable)
		{
			$data = array(
				'errno' => $exception->getCode(),
				'errtype' => isset($this->levels[$exception->getCode()]) ? $this->levels[$exception->getCode()] : $exception->getCode(),
				'errstr' => $exception->getMessage(),
				'errfile' => $exception->getFile(),
				'errline' => $exception->getLine(),
				'user_agent' => $this->_ci->input->user_agent(),
				'ip_address' => $this->_ci->input->ip_address(),
				'time' => timestamp()
			);

			$this->_ci->db->insert($this->_log_table_name, $data);
		}
	}
}

/* End of file Lib_log.php */
