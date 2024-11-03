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

if (!function_exists('recaptchaInputDiv')) {
    function recaptchaInputDiv($size = 'invisible', $callback = 'setResponse')
    {
        if (filter_var(env('RECAPTCHA_ENABLE'), FILTER_VALIDATE_BOOLEAN)) {
            $sitekey = env('RECAPTCHA_KEY');
            return '<div class="g-recaptcha" data-sitekey="' . $sitekey . '" data-size="' . $size . '" data-callback="' . $callback . '"></div>
					<input type="hidden" id="captcha-response" name="g-recaptcha-response" class="form-control" />';
        } else {
            return NULL;
        }
    }
}

if (!function_exists('gapiConfig')) {
    function gapiConfig()
    {
        $ci = get_instance();
        $ci->load->config('customs/google');

        return json_encode([
            'client_id' => $ci->config->item('client_id_auth'),
            'cookiepolicy' => $ci->config->item('cookie_policy'),
            'fetch_basic_profile' => true,
            'redirect_uri' => $ci->config->item('redirect_uri_auth'),
        ]);
    }
}
