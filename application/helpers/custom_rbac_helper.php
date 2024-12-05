<?php

if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

if (!function_exists('getMenu')) {
    function getMenu($menuLocation = 1)
    {
        $menuData = getMenuAbilities($menuLocation);
        $arrayMenu = array();

        if (hasData($menuData)) {
            foreach ($menuData as $main) {

                if (!hasData($main))
                    continue;

                array_push($arrayMenu, [
                    'id' => $main['id'],
                    'menu_title' => $main['menu_title'],
                    'menu_url' => $main['menu_url'],
                    'menu_order' => $main['menu_order'],
                    'menu_icon' => $main['menu_icon'],
                    'abilities_slug' => hasData($main, 'abilities.abilities_slug', true),
                    'submenu' => getMenuAbilities($menuLocation, $main['id']),
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
            ->select('id,menu_title,menu_url,menu_order,menu_icon,abilities_id')
            ->with('abilities')
            ->where('is_active', '1')
            ->where('menu_location', $menuloc)
            ->where('is_main_menu', $main_menu)
            ->orderBy('menu_order', 'asc')
            ->safeOutputWithException(['menu_url', 'menu_icon'])
            ->get();
    }
}

if (!function_exists('actionBtn')) {
    function actionBtn($actionType = 'create', $funcName = '', $id = null, $config = null)
    {
        // Define action types mapping
        $actionTypes = [
            'view' => ['view'],
            'create' => ['add', 'create'],
            'edit' => ['edit', 'update'],
            'delete' => ['delete', 'remove', 'del', 'destroy']
        ];

        // Get the normalized action type
        $currentAction = null;
        foreach ($actionTypes as $type => $aliases) {
            if (in_array($actionType, $aliases)) {
                $currentAction = $type;
                break;
            }
        }

        // Return null if action type is invalid
        if (!$currentAction) {
            return null;
        }

        // Default configurations
        $defaults = [
            'icons' => [
                'view' => 'fa fa-eye',
                'create' => 'fa fa-plus',
                'edit' => 'fa fa-edit',
                'delete' => 'fa fa-trash'
            ],
            'classes' => [
                'view' => 'btn-primary',
                'create' => 'btn-info',
                'edit' => 'btn-success',
                'delete' => 'btn-danger'
            ]
        ];

        // Build button attributes
        $attributes = [
            'class' => sprintf(
                'btn %s',
                $config['class'] ?? $defaults['classes'][$currentAction]
            ),
            'title' => ucfirst($actionType)
        ];

        // Add onclick attribute if function name is provided
        if ($funcName) {
            $attributes['onclick'] = $id
                ? sprintf('%s(%d)', $funcName, $id)
                : sprintf('%s()', $funcName);
        }

        // Add data-id attribute if ID is provided
        if ($id) {
            $attributes['data-id'] = $id;
        }

        // Skip button generation for non-create actions when ID is missing
        if (!$id && $currentAction !== 'create') {
            return null;
        }

        // Build HTML attributes string
        $attributesStr = array_reduce(
            array_keys($attributes),
            function ($carry, $key) use ($attributes) {
                return $carry . sprintf(' %s="%s"', $key, $attributes[$key]);
            },
            ''
        );

        // Build button content
        $icon = sprintf(
            '<i class="%s"></i>',
            $config['icon'] ?? $defaults['icons'][$currentAction]
        );
        $text = $config['text'] ?? '';

        // Return the complete button HTML
        return sprintf(
            '<button%s>%s %s</button>',
            $attributesStr,
            $icon,
            $text
        );
    }
}
