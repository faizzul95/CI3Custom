<?php

defined('BASEPATH') or exit('No direct script access allowed');

// google auth
$config['client_id_auth'] = '';
$config['cookie_policy'] = 'single_host_origin';
$config['redirect_uri_auth'] = url('');

// google drive
$config['client_id'] = '';
$config['client_secret'] = '';
$config['redirect_uri'] = url('cron/backup');
$config['credentials_file_path'] = APPPATH . 'credentials.json';
$config['folder_id'] = [
	'database' => '',
	'system' => '',
];
