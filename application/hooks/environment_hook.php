<?php

if (!defined('BASEPATH')) exit('No direct script access allowed');

class Environment_hook
{
	public function __construct()
	{
		log_message('debug', 'Accessing environment hook!');
		$this->loadEnv();
	}

	public function loadEnv()
	{
		if (file_exists(FCPATH . '/.env')) {
			$dotenv = Dotenv\Dotenv::createUnsafeImmutable(FCPATH);
			$dotenv->load();
		} else {
			die('env file not found');
		}
	}
}
