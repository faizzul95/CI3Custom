<?php

# application/middleware/Sanctum.php

use App\Constants\LoginPolicy;
class Sanctum implements Luthier\MiddlewareInterface
{
	public function run($args)
	{
		if (!isSuperadmin() && requirePasswordUpdate()) {
			redirect(LoginPolicy::PASSWORD_CHANGE_URL, true);
		}

		// Get the Authorization header
		$authorizationHeader = ci()->input->get_request_header('Authorization', TRUE);

		// Remove "Bearer " from the header value
		$token = !empty($authorizationHeader) ? str_replace('Bearer ', '', $authorizationHeader) : NULL;

		if (empty($token)) {
			if (!isLoginCheck()) {
				if (isAjax())
					return returnData(['code' => 401, 'message' => 'Login is required!'], 401);
				else
					redirect('', true);
			}
		} else {
			// Logic to handle token
		}
	}
}
