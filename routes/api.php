<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::group(['namespace' => 'Api'], function() {
    // Post api
    Route::get('/posts','PostController@index');

    Route::post('/post','PostController@store');
    
    Route::get('/posts/id/{id}','PostController@show');
    
    Route::put('/posts/id/{id}','PostController@update');
    
    Route::delete('/posts/id/{id}','PostController@destroy');
});
