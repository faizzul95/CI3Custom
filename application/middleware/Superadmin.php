<?php

# application/middleware/Superadmin.php

class Superadmin implements Luthier\MiddlewareInterface
{
	public function run($args)
	{
		if (!isAjax()) {
			if (!isLoginCheck()) {
				error(401, 'Login required!');
			} else if (!isSuperadmin()) {
				error(403, 'Unauthorized: Access is denied');
			}
		} else {
			if (!isLoginCheck()) {
				return returnData(['code' => 401, 'message' => 'Login required!'], 401);
			} else if (!isSuperadmin()) {
				return returnData(['code' => 403, 'message' => 'Unauthorized: Access is denied'], 403);
			}
		}
	}
}
