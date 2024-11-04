<?php

namespace App\services\google;

defined('BASEPATH') or exit('No direct script access allowed');

class GoogleAnalytic extends GoogleServices
{
	public function __construct()
	{
		parent::__construct('analytic');
	}

	public function getRealTimeData($viewId)
	{
	}
}
