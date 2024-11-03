<?php

defined('BASEPATH') or exit('No direct script access allowed');

class SystemAuditTrail_model extends MY_Model
{
    public $table = 'system_audit_trails';
    public $primaryKey = 'id';

    public $fillable = [
        'user_id',
        'role_id',
        'user_fullname',
        'event',
        'table_name',
        'old_values',
        'new_values',
        'url',
        'ip_address',
        'user_agent'
    ];

    public $_validationRules = [
        'user_id' => ['field' => 'user_id', 'label' => 'User Id', 'rules' => 'required|trim|integer'],
        'role_id' => ['field' => 'role_id', 'label' => 'Role Id', 'rules' => 'required|trim|integer'],
        'user_fullname' => ['field' => 'user_fullname', 'label' => 'User Fullname', 'rules' => 'required|trim|max_length[255]'],
        'event' => ['field' => 'event', 'label' => 'Event', 'rules' => 'required|trim|max_length[20]'],
        'table_name' => ['field' => 'table_name', 'label' => 'Table Name', 'rules' => 'required|trim|max_length[80]'],
        'old_values' => ['field' => 'old_values', 'label' => 'Old Values', 'rules' => 'required|trim'],
        'new_values' => ['field' => 'new_values', 'label' => 'New Values', 'rules' => 'required|trim'],
        'url' => ['field' => 'url', 'label' => 'Url', 'rules' => 'required|trim|max_length[150]'],
        'ip_address' => ['field' => 'ip_address', 'label' => 'Ip Address', 'rules' => 'required|trim|max_length[150]'],
        'user_agent' => ['field' => 'user_agent', 'label' => 'User Agent', 'rules' => 'required|trim|max_length[150]']
    ];

    function __construct()
    {
        parent::__construct();
    }
}
