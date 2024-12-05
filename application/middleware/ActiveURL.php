<?php

# application/middleware/ActiveURL.php

class ActiveURL implements Luthier\MiddlewareInterface
{
    public function run($args)
    {
        if (!isSuperadmin()) {
            $ci = &get_instance();
            model('SystemMenuNavigation_model');

            $isActive = $ci->SystemMenuNavigation_model->where('menu_url', uri_string())->where('is_active', '1')->count();

            if (!$isActive) {
                show_404();
            }
        }
    }
}
