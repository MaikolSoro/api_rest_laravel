<?php

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/
/* cargando las clases */

use Api\Http\Middleware\ApiAuthMiddleware;
Route::get('/', function () {
    return view('welcome');
});

/**
 *
 * Rutas del controlador de usuarios
 *
 */


Route::post('/api/register','UserController@register');
Route::post('/api/login', 'UserController@login');
Route::put('/api/user/update', 'UserController@update');
Route::post('/api/user/upload','UserController@upload')->middlewre(\ApiAuthMiddleware::class);
Route::get('/api/user/avatar/{filenmae}','UserController@getImage');
Route::get('/api/user/detail/{id}', 'UserController@detail');


// Rutas del controlador de categorias

Route::resource('/api/category', 'CategoryController');

// Rutas del controlador post
Route::resource('/api/post', 'PostController');
