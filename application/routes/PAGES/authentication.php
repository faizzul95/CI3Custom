
<?php

// Open login page

Route::get('/', function () {
    // check user session if exist
    if (isLoginCheck()) {
        redirect('dashboard', true);
    } else {
        // isRememberCookieEnable();
        // if not redirect to page login
        render('auth/login', ['title' => 'Sign In', 'currentSidebar' => 'auth', 'currentSubSidebar' => null]);
    }
});

// Open forgot password

Route::get('/forgot-password', function () {
    if (isLoginCheck()) {
        redirect('dashboard', true);
    } else {
        render('auth/forgot', ['title' => 'Sign In', 'currentSidebar' => 'auth', 'currentSubSidebar' => null]);
    }
});

Route::get('/reset-new-password/{token}', function ($token) {
    // check user session if exist
    if (isLoginCheck()) {
        redirect('dashboard', true);
    } else {
        // Retrieve secret key from environment
        $secretKey = env('PRT_SECRET_KEY') ?: 'th!sI5aT0k3nF0rP@$$w0rdRe53tD3f@ult2o24';

        // Decode the token and revert URL-safe replacements
        $decodedToken = base64_decode(str_replace(['-', '_'], ['+', '/'], $token));

        if (!$decodedToken || strpos($decodedToken, '|') === false) {
            return false; // Invalid token format
        }

        $explodeData = explode('|', $decodedToken);

        if (!is_array($explodeData) && count($explodeData) < 3) {
            return false; // Invalid token format
        }

        // Split the token into data and hash parts
        [$email, $expiresAt, $hash] = $explodeData;

        // Check if the token is expired
        if ((int)$expiresAt < time()) {
            return false; // Token has expired
        }

        // Recalculate the hash to verify integrity
        $validHash = hash_hmac('sha256', "{$email}|{$expiresAt}", $secretKey, true);

        if (hash_equals($hash, $validHash)) {
            render('auth/reset_password_form', ['title' => 'Reset Password Form', 'currentSidebar' => 'auth', 'currentSubSidebar' => null, 'email' => $email, 'token' => $token]);
        } else {
            error(400);
        }
    }
});

Route::get('/logout', 'Auth@logout');