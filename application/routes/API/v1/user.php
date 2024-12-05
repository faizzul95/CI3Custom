<?php

Route::group('user', ['middleware' => ['Sanctum', 'Api']], function () {
    Route::post('/list', 'User@listDt');
    Route::post('/reset-password', 'User@resetPassword');
    Route::post('/upload-profile', 'User@uploadProfile');
    Route::post('/save', 'User@save');
    Route::get('/show/{num:id}', 'User@show');
    Route::delete('/delete/{num:id}', 'User@delete');
});