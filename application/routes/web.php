<?php

/**
 * Welcome to Luthier-CI!
 *
 * This is your main route file. Put all your HTTP-Based routes here using the static
 * Route class methods
 *
 * Examples:
 *
 *    Route::get('foo', 'bar@baz');
 *      -> $route['foo']['GET'] = 'bar/baz';
 *
 *    Route::post('bar', 'baz@fobie', [ 'namespace' => 'cats' ]);
 *      -> $route['bar']['POST'] = 'cats/baz/foobie';
 *
 *    Route::get('blog/{slug}', 'blog@post');
 *      -> $route['blog/(:any)'] = 'blog/post/$1'
 */

// Route::get('/', function(){
//    luthier_info();
// })->name('homepage');

// GENERAL
Route::set('default_controller', 'auth');

require __DIR__ . '/PAGES/authentication.php';
require __DIR__ . '/PAGES/system.php';
// require __DIR__ . '/PAGES/Error.php';

// Route::get('/profile', 'userProfile@index', ['middleware' => ['Sanctum']]);
// Route::get('/user', 'user@index', ['middleware' => ['Sanctum', 'ActiveURL']]);
Route::get('/dashboard', 'dashboard@index', ['middleware' => ['Sanctum', 'ActiveURL']]);
Route::get('/queue', 'QueueController@index', ['middleware' => ['Sanctum']]);
Route::get('/csv-user-import', 'ImportController@index', ['middleware' => ['Sanctum']]);

Route::set('404_override', function () {
    show_404();
});

Route::set('translate_uri_dashes', FALSE);
