<?php

defined('BASEPATH') or exit('No direct script access allowed');

class User_model extends MY_Model
{
    public $table = 'users';
    public $primaryKey = 'id';
    public $softDelete = true;

    public $fillable = [
        'name',
        'user_preferred_name',
        'email',
        'user_contact_no',
        'user_gender',
        'user_dob',
        'username',
        'password',
        'password_last_changed',
        'password_must_changed',
        'user_status',
        'remember_token',
        'first_login',
        'email_verified_at'
    ];

    public $_validationRules = [
        'id' => ['field' => 'id', 'label' => 'User ID', 'rules' => 'trim|integer'],
        'name' => ['field' => 'name', 'label' => 'Full Name', 'rules' => 'required|trim|max_length[255]'],
        'user_preferred_name' => ['field' => 'user_preferred_name', 'label' => 'Preferred Name', 'rules' => 'required|trim|max_length[20]'],
        'email' => ['field' => 'email', 'label' => 'Email', 'rules' => 'required|trim|valid_email|is_unique[users.email]', 'errors' => ['valid_email' => 'Please enter a valid email address.', 'is_unique' => 'This %s already exists.']],
        'user_gender' => ['field' => 'user_gender', 'label' => 'Gender', 'rules' => 'required|trim|numeric'],
        'user_dob' => ['field' => 'user_dob', 'label' => 'Birthday', 'rules' => 'required|trim|max_length[10]'],
        'username' => ['field' => 'username', 'label' => 'Username', 'rules' => 'required|min_length[5]|max_length[12]|is_unique[users.username]', 'errors' => ['is_unique' => 'This %s already exists.']],
        'password' => ['field' => 'password', 'label' => 'Password', 'rules' => 'required|trim|min_length[8]|max_length[20]'],
        'password_last_changed' => ['field' => 'password_last_changed', 'label' => 'Last Change Password', 'rules' => 'trim|integer'],
        'password_must_changed' => ['field' => 'password_must_changed', 'label' => 'Must Change Password', 'rules' => 'trim|integer'],
        'user_status' => ['field' => 'user_status', 'label' => 'Status', 'rules' => 'required|trim|integer'],
        'first_login' => ['field' => 'first_login', 'label' => 'First login status', 'rules' => 'trim|integer']
    ];

    // public $appends = ['gender', 'status']; 

    function __construct()
    {
        parent::__construct();
    }

    ###################################################################
    #                RELATIONSHIP BETWEEN MODEL                       #
    ###################################################################

    public function main_profile()
    {
        return $this->hasOne('UserProfile_model', 'user_id', 'id');
    }

    public function profile()
    {
        return $this->hasMany('UserProfile_model', 'user_id', 'id');
    }

    public function avatar()
    {
        return $this->hasOne('EntityFile_model', 'entity_id', 'id');
    }

    ###################################################################
    #                    CUSTOM FUNCTION                              #
    ###################################################################

    public function getGenderAttribute()
    {
        return $this->user_gender == 1 ? 'Male' : ($this->user_gender == 2 ? 'Female' : '-');
    }

    public function getStatusAttribute()
    {
        return $this->user_status == 1 ? 'Active' : ($this->user_status == 0 ? 'Inactive' : '-');
    }

    public function getStatusBadgeAttribute()
    {
        return $this->user_status == 1 ? '<span class="badge text-bg-success">Active</span>' : ($this->user_status == 0 ? '<span class="badge text-bg-warning">Inactive</span>' : '-');
    }
}
