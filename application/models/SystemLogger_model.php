<?php

defined('BASEPATH') or exit('No direct script access allowed');

class SystemLogger_model extends MY_Model
{
    public $table = 'system_logger';
    public $primaryKey = 'id';

    public $fillable = [
        'errno',
        'errtype',
        'errstr',
        'errfile',
        'errline',
        'user_agent',
        'ip_address',
        'time'
    ];

    public $_validationRules = [
        'errno' => ['field' => 'errno', 'label' => 'Errno', 'rules' => 'required|trim|max_length[255]'],
        'errtype' => ['field' => 'errtype', 'label' => 'Errtype', 'rules' => 'required|trim|max_length[255]'],
        'errstr' => ['field' => 'errstr', 'label' => 'Errstr', 'rules' => 'required|trim|max_length[255]'],
        'errfile' => ['field' => 'errfile', 'label' => 'Errfile', 'rules' => 'required|trim|max_length[255]'],
        'errline' => ['field' => 'errline', 'label' => 'Errline', 'rules' => 'required|trim|max_length[255]'],
        'user_agent' => ['field' => 'user_agent', 'label' => 'User Agent', 'rules' => 'required|trim|max_length[100]'],
        'ip_address' => ['field' => 'ip_address', 'label' => 'Ip Address', 'rules' => 'required|trim|max_length[100]'],
        'time' => ['field' => 'time', 'label' => 'Time', 'rules' => 'required|trim|regex_match[/^\d{4}-\d{2}-\d{2} \d{2}:\d{2}:\d{2}$/]']
    ];

    function __construct()
    {
        parent::__construct();
    }
}
