<?php

defined('BASEPATH') or exit('No direct script access allowed');

class SystemAbilities_model extends MY_Model
{
    public $table = 'system_abilities';
    public $primaryKey = 'id';

    public $fillable = [
        'abilities_name',
        'abilities_slug',
        'abilities_desc'
    ];

    public $_validationRules = [
        'abilities_name' => ['field' => 'abilities_name', 'label' => 'Abilities Name', 'rules' => 'required|trim|max_length[50]'],
        'abilities_slug' => ['field' => 'abilities_slug', 'label' => 'Abilities Slug', 'rules' => 'required|trim|max_length[100]'],
        'abilities_desc' => ['field' => 'abilities_desc', 'label' => 'Abilities Desc', 'rules' => 'required|trim|max_length[255]']
    ];

    function __construct()
    {
        parent::__construct();
    }
}
