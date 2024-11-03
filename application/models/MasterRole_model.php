<?php

defined('BASEPATH') or exit('No direct script access allowed');

class MasterRole_model extends MY_Model
{
    public $table = 'master_roles';
    public $primaryKey = 'id';

    public $fillable = [
        'role_name',
        'role_status'
    ];

    public $_validationRules = [
        'role_name' => ['field' => 'role_name', 'label' => 'Role Name', 'rules' => 'required|trim|max_length[255]'],
        'role_status' => ['field' => 'role_status', 'label' => 'Role Status', 'rules' => 'required|trim']
    ];

    function __construct()
    {
        parent::__construct();
    }
}
