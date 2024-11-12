<?php

defined('BASEPATH') or exit('No direct script access allowed');

class SystemPermission_model extends MY_Model
{
    public $table = 'system_permission';
    public $primaryKey = 'id';

    public $fillable = [
        'role_id',
        'abilities_id',
        'access_device_type'
    ];

    public $_validationRules = [
        'role_id' => ['field' => 'role_id', 'label' => 'Role ID', 'rules' => 'required|trim|integer'],
        'abilities_id' => ['field' => 'abilities_id', 'label' => 'Abilities ID', 'rules' => 'required|trim|integer'],
        'access_device_type' => ['field' => 'access_device_type', 'label' => 'Access Device Type', 'rules' => 'required|trim']
    ];

    function __construct()
    {
        parent::__construct();
    }

    ###################################################################
    #                RELATIONSHIP BETWEEN MODEL                       #
    ###################################################################

    public function abilities()
    {
        return $this->hasOne('SystemAbilities_model', 'id', 'abilities_id');
    }
}
