<?php

defined('BASEPATH') or exit('No direct script access allowed');

class UserProfile_model extends MY_Model
{
    public $table = 'user_profile';
    public $primaryKey = 'id';
    public $softDelete = true;

    public $fillable = [
        'user_id',
        'role_id',
        'is_main',
        'profile_status'
    ];

    public $_validationRules = [
        'id' => ['field' => 'id', 'label' => 'Profile ID', 'rules' => 'trim|integer'],
        'user_id' => ['field' => 'user_id', 'label' => 'User ID', 'rules' => 'required|trim|integer'],
        'role_id' => ['field' => 'role_id', 'label' => 'Role ID', 'rules' => 'required|trim|integer'],
        'is_main' => ['field' => 'is_main', 'label' => 'Main Status', 'rules' => 'required|trim|max_length[1]|integer'],
        'profile_status' => ['field' => 'profile_status', 'label' => 'Profile Status', 'rules' => 'required|trim|max_length[1]|integer'],
    ];

    function __construct()
    {
        parent::__construct();
    }

    ###################################################################
    #                RELATIONSHIP BETWEEN MODEL                       #
    ###################################################################

    public function users()
    {
        return $this->belongsTo('User_model', 'user_id', 'id');
    }

    public function roles()
    {
        return $this->hasOne('MasterRole_model', 'id', 'role_id');
    }

    public function avatar()
    {
        return $this->hasOne('EntityFile_model', 'entity_id', 'id');
    }
}
