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

Route::group(array('domain' => 'api.scaffold.dev'), function()
{
	Route::get('/test', function() {

		$unserialize = unserialize('a:3:{s:4:"name";s:6:"STRING";s:3:"age";s:6:"NUMBER";s:10:"likes_dogs";s:7:"BOOLEAN";}');
		print_r($unserialize);

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



	Route::get('/projects', 'ProjectController@getAllProjects');

	Route::get('/projects/{id}', 'ProjectController@getProjectByID');

	Route::post('/projects', 'ProjectController@createProject');

	Route::put('/projects/{id}', 'ProjectController@editProject');

	Route::delete('/projects/{id}', 'ProjectController@removeProject');

});

Route::group(array('domain' => '{projectName}.api.scaffold.dev'), function()
{

	Route::get('/', 'ProjectController@getProjectByName');

	Route::get('/endpoints', 'ProjectController@getAllEndpoints');

	Route::get('/objects', 'ProjectController@getAllObjects');

	Route::any('/{uri}', 'ProjectController@displayEndpoint')->where('uri', '.*');
	
	
});


