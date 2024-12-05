<?php

Route::group('roles', ['middleware' => ['Sanctum', 'Api']], function () {
    Route::post('/list', 'Roles@listDt');
    Route::post('/save', 'Roles@save');
    Route::get('/show/{num:id}', 'Roles@show');
    Route::delete('/delete/{num:id}', 'Roles@delete');
    Route::get('/select/{num:id?}', 'Roles@select');
});
