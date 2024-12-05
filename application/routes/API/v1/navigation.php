<?php

Route::group('navigation', ['middleware' => ['Sanctum', 'Api']], function () {
    Route::post('/list', 'Sys_rbac@listNavigationDt');
    Route::post('/save', 'Sys_rbac@saveNavigation');
    Route::get('/show/{num:id}', 'Sys_rbac@showNavigation');
    Route::delete('/delete/{num:id}', 'Sys_rbac@deleteNavigation');

    Route::post('/menu-select', 'Sys_rbac@getListMenuSelect');
    Route::post('/menu-order-select', 'Sys_rbac@getMenuOrderSelect');
});
