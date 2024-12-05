<?php

defined('BASEPATH') or exit('No direct script access allowed');

class MasterRole_model extends MY_Model
{
    public $table = 'master_roles';
    public $primaryKey = 'id';
    public $softDelete = true;

    public $fillable = [
        'role_name',
        'role_rank',
        'role_status'
    ];

    public $_validationRules = [
        'role_name' => ['field' => 'role_name', 'label' => 'Role Name', 'rules' => 'required|trim|max_length[255]'],
        'role_rank' => ['field' => 'role_rank', 'label' => 'Role Rank', 'rules' => 'trim|max_length[255]'],
        'role_status' => ['field' => 'role_status', 'label' => 'Role Status', 'rules' => 'required|trim']
    ];

    function __construct()
    {
        parent::__construct();
    }

    ###################################################################
    #                RELATIONSHIP BETWEEN MODEL                       #
    ###################################################################

    public function permissions()
    {
        return $this->hasMany('SystemPermission_model', 'role_id', 'id');
    }

    public function users()
    {
        return $this->hasMany('UserProfile_model', 'role_id', 'id');
    }

    ###################################################################
    #                    CUSTOM FUNCTION                              #
    ###################################################################

    public function getStatusAttribute()
    {
        return $this->role_status == 1 ? 'Active' : ($this->role_status == 0 ? 'Inactive' : '-');
    }

    public function getStatusBadgeAttribute()
    {
        return $this->role_status == 1 ? '<span class="badge badge-label bg-success">Active</span>' : ($this->role_status == 0 ? '<span class="badge badge-label bg-warning">Inactive</span>' : '-');
    }
}
