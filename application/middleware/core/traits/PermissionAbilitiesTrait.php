<?php

namespace App\middleware\core\traits;

defined('BASEPATH') or exit('No direct script access allowed');

trait PermissionAbilitiesTrait
{
	public function hasPermissionAction()
	{
		$permissionHeader = get_instance()->input->get_request_header('x-permission', TRUE);

		// Access specific Axios header values
		if (hasData($permissionHeader)) {
			$permission = permission($permissionHeader);
		} else {
			$permission = true; // set true if no header x-permission to validate
		}

		return $permission;
	}
}
