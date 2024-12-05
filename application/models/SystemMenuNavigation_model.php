<?php

defined('BASEPATH') or exit('No direct script access allowed');

class SystemMenuNavigation_model extends MY_Model
{
    public $table = 'system_menu_navigation';
    public $primaryKey = 'id';

    public $fillable = [
        'menu_title',
        'menu_description',
        'menu_url',
        'menu_order',
        'menu_icon',
        'is_main_menu',
        'menu_location',
        'is_active',
        'abilities_id'
    ];

    public $_validationRules = [
        'menu_title' => ['field' => 'menu_title', 'label' => 'Menu Title', 'rules' => 'required|trim|max_length[255]'],
        'menu_description' => ['field' => 'menu_description', 'label' => 'Menu Description', 'rules' => 'required|trim|max_length[255]'],
        'menu_url' => ['field' => 'menu_url', 'label' => 'Menu Url', 'rules' => 'required|trim|max_length[255]'],
        'menu_order' => ['field' => 'menu_order', 'label' => 'Menu Order', 'rules' => 'required|trim'],
        'menu_icon' => ['field' => 'menu_icon', 'label' => 'Menu Icon', 'rules' => 'required|trim|max_length[150]'],
        'is_main_menu' => ['field' => 'is_main_menu', 'label' => 'Is Main Menu', 'rules' => 'required|trim|integer'],
        'menu_location' => ['field' => 'menu_location', 'label' => 'Menu Location', 'rules' => 'required|trim'],
        'is_active' => ['field' => 'is_active', 'label' => 'Is Active', 'rules' => 'required|trim'],
        'abilities_id' => ['field' => 'abilities_id', 'label' => 'Abilities Slug', 'rules' => 'trim|integer'],
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

    ###################################################################
    #                    CUSTOM FUNCTION                              #
    ###################################################################

    public function getMenuLocationTypeAttribute()
    {
        $menu = $this->is_main_menu == 0 ? '<span class="badge bg-info"> Main Menu </span>' : '<span class="badge bg-info"> Sub Menu </span>';
        $categoryMenu = [
            '1' => 'Side Menu',
            '2' => 'Top Menu (Header)',
            '3' => 'Settings (Gear icon)'
        ];

        return $menu . ' - ' . hasData($categoryMenu, $this->menu_location, true);
    }

    public function getStatusAttribute()
    {
        return $this->is_active == 1 ? 'Active' : ($this->is_active == 0 ? 'Inactive' : '-');
    }

    public function getStatusBadgeAttribute()
    {
        return $this->is_active == 1 ? '<span class="badge badge-label bg-success">Active</span>' : ($this->is_actives == 0 ? '<span class="badge badge-label bg-warning">Inactive</span>' : '-');
    }
}
