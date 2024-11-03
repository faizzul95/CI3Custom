<?php

defined('BASEPATH') or exit('No direct script access allowed');

class SystemLoginHistory_model extends MY_Model
{
    public $table = 'system_login_history';
    public $primaryKey = 'id';

    public $fillable = [
        'user_id',
        'ip_address',
        'login_type',
        'operating_system',
        'browsers',
        'time',
        'user_agent'
    ];

    public $_validationRules = [
        'user_id' => ['field' => 'user_id', 'label' => 'User Id', 'rules' => 'required|trim|integer'],
        'ip_address' => ['field' => 'ip_address', 'label' => 'Ip Address', 'rules' => 'required|trim|max_length[128]'],
        'login_type' => ['field' => 'login_type', 'label' => 'Login Type', 'rules' => 'required|trim'],
        'operating_system' => ['field' => 'operating_system', 'label' => 'Operating System', 'rules' => 'required|trim|max_length[50]'],
        'browsers' => ['field' => 'browsers', 'label' => 'Browsers', 'rules' => 'required|trim|max_length[50]'],
        'time' => ['field' => 'time', 'label' => 'Time', 'rules' => 'required|trim|regex_match[/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/]'],
        'user_agent' => ['field' => 'user_agent', 'label' => 'User Agent', 'rules' => 'required|trim|max_length[200]']
    ];

    function __construct()
    {
        parent::__construct();
    }
}
