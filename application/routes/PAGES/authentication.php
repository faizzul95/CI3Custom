
<?php

// Open login page

Route::get('/', function () {
    // check user session if exist
    if (isLoginCheck()) {
        redirect('dashboard', true);
    } else {

        // $remember = app('App\services\modules\authentication\logics\RememberLogic')->logic();

        // if (hasData($remember, 'code') && isSuccess($remember['code'])) {
        //     redirect($remember['redirectUrl'], true);
        // }

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
