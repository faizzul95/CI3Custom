<?php

# application/middleware/Api.php

use App\middleware\core\traits\RateLimitingThrottleTrait;
use App\middleware\core\traits\XssProtectionTrait;
use App\middleware\core\traits\PermissionAbilitiesTrait;

class Api implements Luthier\MiddlewareInterface
{
	use RateLimitingThrottleTrait;
	use XssProtectionTrait;
	use PermissionAbilitiesTrait;

	public function run($args)
	{
		if (!isAjax()) {
			return returnData(['code' => 422, 'message' => 'API is only accessible via AJAX REQUEST!'], 422);
		} else {
			if ($this->hasPermissionAction()) {
				if ($this->isXssAttack())
					return returnData(['code' => 422, 'message' => 'Protection against <b><i> Cross-site scripting (XSS) </i></b> activated!'], 422);
				else
					$this->isRateLimiting();
			} else {
				return returnData(['code' => 400, 'message' => 'You are not authorized to perform this action'], 400);
			}
		}
	}
}
