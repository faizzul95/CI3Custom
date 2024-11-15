<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

if (!function_exists('getMenu')) {
    function getMenu($menuLocation = 1)
    {
        $menuData = getMenuAbilities($menuLocation);
        $arrayMenu = array();

        if ($menuData) {
            foreach ($menuData as $main) {
                array_push($arrayMenu, [
                    'menu_id' => $main['menu_id'],
                    'menu_title' => $main['menu_title'],
                    'menu_url' => $main['menu_url'],
                    'menu_order' => $main['menu_order'],
                    'menu_icon' => $main['menu_icon'],
                    'abilities_slug' => hasData($main, 'abilities.abilities_slug', true),
                    'submenu' => getMenuAbilities($menuLocation, $main['menu_id']),
                ]);
            }
        }

        return $arrayMenu;
    }
}

if (!function_exists('getMenuAbilities')) {
    function getMenuAbilities($menuloc = 1, $main_menu = 0)
    {
        $ci = &get_instance();
        model('SystemMenuNavigation_model');

        return $ci->SystemMenuNavigation_model
            ->select('id,menu_title,menu_url,menu_icon,abilities_id')
            ->with('abilities')
            ->where('is_active', '1')
            ->where('menu_location', $menuloc)
            ->where('is_main_menu', $main_menu)
            ->orderBy('menu_order', 'asc')
            ->safeOutputWithException(['menu_url', 'menu_icon'])
            ->get();
    }
}
