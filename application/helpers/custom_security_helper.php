<?php

if (!function_exists('recaptchav2')) {
    function recaptchav2()
    {
        if (filter_var(env('RECAPTCHA_ENABLE'), FILTER_VALIDATE_BOOLEAN)) {
            library('recaptcha');
            return get_instance()->recaptcha->is_valid();
        } else {
            return ['success' => TRUE, 'error_message' => 'reCAPTCHA is currently disabled'];
        }
    }
}
