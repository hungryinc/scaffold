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

Route::group(array('domain' => '{projectName}.scaffold.dev'), function()
{

	Route::get('/', function($projectName)
	{
        echo "Project Name: " . $projectName;
	});

	Route::get('/{uri}', 'ProjectController@displayEndpoint');

});



Route::get('/objects', 'ObjectController@getAllObjects');

Route::get('/objects/{id}', 'ObjectController@getObjectById');

Route::post('/objects', 'ObjectController@createObject');

Route::put('/objects/{id}', 'ObjectController@editObject');

Route::delete('/objects/{id}', 'ObjectController@removeObject');




Route::get('/endpoints', 'EndpointController@getAllEndpoints');

Route::get('/endpoints/{id}', 'EndpointController@getEndpointByID');

Route::post('/endpoints', 'EndpointController@createEndpoint');

Route::put('/endpoints/{id}', 'EndpointController@editEndpoint');

Route::delete('/endpoints/{id}', 'EndpointController@removeEndpoint');




Route::get('/projects/{id}', 'ProjectController@getProjectByID');

Route::post('/projects', 'ProjectController@createProject');

Route::put('/projects/{id}', 'ProjectController@editProject');

Route::delete('/projects/{id}', 'ProjectController@removeProject');



