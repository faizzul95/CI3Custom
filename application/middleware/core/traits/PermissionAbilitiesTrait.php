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

			// initialize table
			// model('CompanyProfileRoles_model', 'profileM');

			// $dataProfiles = ci()->profileM::find(currentUserProfileID());
			// $permissionArray = json_decode($dataProfiles['abilities_json'], true);

			// if (hasData($permissionArray))
			// 	$permission = in_array($permissionHeader, $permissionArray) ? true : false;
			// else
			// 	$permission = false; // set false

		} else {
			$permission = true; // set true if no header x-permission to validate
		}

		return $permission;
	}
}
