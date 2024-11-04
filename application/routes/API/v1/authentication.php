<?php

// For Authentication

Route::group('auth', function () {
    
    Route::get('/logout', 'Auth@logout');
    
    Route::post('/sign-in', 'Auth@authorize', ['middleware' => ['Api']]);
    Route::post('/socialite', 'Auth@socialite', ['middleware' => ['Api']]);
    Route::post('/sent-mail-forgot', 'Auth@mail_reset_password', ['middleware' => ['Api']]);

    // Route::post('/verify-user', 'Auth@verify2FA', ['middleware' => ['Api']]);
    // Route::post('/change-profile', 'Auth@change_profile', ['middleware' => ['Api']]);

    // Route::get('/impersonate/{num:id?}', 'Auth@impersonateUser', ['middleware' => ['Superadmin', 'Api']]);
    // Route::get('/leaveImpersonate', 'Auth@impersonateUser');
});
