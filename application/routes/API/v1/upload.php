<?php

Route::group('upload', ['middleware' => ['Sanctum', 'Api']], function () {
    Route::post('/import-user', 'ImportExportController@importUsers');
});
