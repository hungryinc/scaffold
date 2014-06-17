<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the Closure to execute when that URI is requested.
|
 */

Route::get('/objects', 'ObjectController@getAllObjects');

Route::get('/objects/{id}', 'ObjectController@getObjectById');

Route::post('/objects', 'ObjectController@createObject');

Route::put('/objects/{id}', 'ObjectController@changeObject');

Route::delete('/objects/{id}', 'ObjectController@removeObject');




Route::get('/endpoints', 'EndpointController@getAllEndpoints');

Route::get('/endpoints/{id}', 'EndpointController@getEndpointByID');

Route::post('/endpoints', 'EndpointController@createEndpoint');

Route::put('/endpoints/{id}', 'EndpointController@changeEndpoint');

Route::delete('/endpoints/{id}', 'EndpointController@removeEndpoint');