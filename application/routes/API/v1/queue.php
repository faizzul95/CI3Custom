<?php

Route::group('queue', ['middleware' => ['Sanctum', 'Api']], function () {
    Route::post('/list', 'QueueController@listDt');
    Route::get('/show/{num:id}', 'QueueController@show');
    Route::delete('/delete/{num:id}', 'QueueController@delete');
});
