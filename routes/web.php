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

Route::get('/', function () {
    dd(1);
    return view('welcome');
});

Route::get('/gm', 'TestController@gm');

//Route::get('/test', 'TestController@test');

Route::get('/getData', 'TestController@getData');

//Route::any('/notifyUrl', 'TestController@notifyUrl');

//Route::any('/test2', 'TestController@test2');

Route::any('/server', 'AbcController@server');
Route::any('/page', 'AbcController@page');

