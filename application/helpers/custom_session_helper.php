<?php

/**
 * Check if the user is logged in.
 * If not logged in, redirect to the login page.
 */
if (!function_exists('isLogin')) {
    function isLogin($param = 'isLoggedInSession', $redirect = 'auth/logout')
    {
        $ci = &get_instance();

        // If session not exist then redirect to login page
        if (!$ci->session->has_userdata($param)) {
            redirect(site_url($redirect));
        }
    }
}

if (!function_exists('isLoginCheck')) {
    function isLoginCheck($param = 'isLoggedInSession')
    {
        return hasSession($param);
    }
}

/**
 * Check if the user has the specified permissions.
 *
 * This function checks if a user has one or multiple specified permissions.
 * If the wildcard '*' is present in the user's permission list, it grants all permissions automatically.
 *
 * @param string|array $params A single permission slug (string) or an array of permission slugs to check.
 * @return mixed Returns true if '*' exists or the specified permission exists in the list.
 *               Returns an associative array with each permission set to true if '*' is present or if each permission exists in the list.
 */
if (!function_exists('permission')) {
    function permission($params)
    {
        $listPermission = getSession('permissions');

        // If '*' exists in listPermission, grant all permissions
        if (!empty($listPermission) && in_array('*', $listPermission)) {
            // If $params is an array, set each item to true
            if (is_array($params)) {
                return array_fill_keys($params, true);
            }
            // If $params is a string, return true
            return true;
        }

        // Check permissions for each item in params if it's an array
        if (is_array($params) && !empty($listPermission)) {
            return array_map(fn($permissionSlug) => in_array($permissionSlug, $listPermission), $params);
        }

        // If params is a string, check if it exists in listPermission
        if (is_string($params) && !empty($listPermission)) {
            return in_array($params, $listPermission);
        }

        return false;
    }
}

/**
 * Check if the user has the permission
 */
if (!function_exists('show_403')) {
    function show_403()
    {
        $ci = &get_instance();
        $ci->load->view('errors/html/error_general', [
            'heading' => "403 - Unauthorize",
            'message' => 'Ops, Look like you dont have permission to view this page'
        ]);
    }
}

if (!function_exists('getSession')) {
    function getSession($params)
    {
        $ci = &get_instance();
        return $ci->session->has_userdata($params) ? $ci->session->userdata($params) : null;
    }
}

// CUSTOM HELPER BY PROJECT

/**
 * Extracts the abilities_slug values from a given permission array.
 *
 * This function iterates through an array of permissions to gather unique ability slugs.
 * If the abilities_slug contains a wildcard '*', it returns ['*'] immediately, ignoring other entries.
 * If no wildcard is found, it collects all unique abilities_slug values.
 *
 * @param array|null $permission Array of permission data containing nested abilities.
 * @return array Returns an array of abilities slugs. If '*' is present, returns ['*'].
 */
if (!function_exists('getPermissionSlug')) {
    function getPermissionSlug($permission = null)
    {
        $slug = [];

        // Check if the permission array is valid
        if (empty($permission) || !is_array($permission)) {
            return $slug;
        }

        foreach ($permission as $perm) {
            // Check if abilities key exists and has an abilities_slug
            if (isset($perm['abilities']['abilities_slug'])) {
                $abilitySlug = $perm['abilities']['abilities_slug'];

                // If the wildcard '*' is found, return ['*'] immediately
                if ($abilitySlug === '*') {
                    return ['*'];
                }

                // Add the ability slug if it's not already in the slug array
                $slug[] = $abilitySlug;
            }
        }

        // Return unique slugs
        return array_unique($slug);
    }
}

if (!function_exists('currentUserID')) {
    function currentUserID()
    {
        return getSession('userID');
    }
}

if (!function_exists('isSuperadmin')) {
    function isSuperadmin()
    {
        return currentRoleID() == 99 ? true : false;
    }
}

if (!function_exists('isAdmin')) {
    function isAdmin()
    {
        return currentRoleID() == 2 ? true : false;
    }
}

if (!function_exists('currentRoleID')) {
    function currentRoleID()
    {
        return getSession('roleID');
    }
}

if (!function_exists('currentUserFullname')) {
    function currentUserFullname()
    {
        return getSession('userFullName');
    }
}

if (!function_exists('currentUserRoleName')) {
    function currentUserRoleName()
    {
        return getSession('profileName');
    }
}

if (!function_exists('currentUserAvatar')) {
    function currentUserAvatar()
    {
        return getSession('userAvatar');
    }
}
