<?php

namespace App\constants;

final class DefaultProfileRoleAbility
{
    public const SUPERADMIN = 1;
    public const ADMIN = 2;
    
    public const ROLE_ABILITIES = [
        self::SUPERADMIN => ['*'],
        self::ADMIN => ['dashboard-view', 'settings-view-info', 'settings-change-password'],
    ];
}