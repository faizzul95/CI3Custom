<?php

Route::group('profile', ['middleware' => ['Sanctum', 'Api']], function () {
    Route::post('/list', 'UserProfile@listDt');
    Route::post('/save', 'UserProfile@save');
    Route::put('/set-default-profile', 'UserProfile@setDefaultProfile');
    Route::delete('/delete/{num:id}', 'UserProfile@delete');
});
