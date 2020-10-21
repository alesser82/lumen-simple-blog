<?php

/** @var \Laravel\Lumen\Routing\Router $router */

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It is a breeze. Simply tell Lumen the URIs it should respond to
| and give it the Closure to call when that URI is requested.
|
*/

$router->get('/', function () use ($router) {
    return $router->app->version();
});

$router->group(['middleware' => 'jwt.auth'], function () use ($router) {
    $router->post('users/logout', ['as' => 'logout', 'uses' => 'AuthController@logout']);

});

$router->group(['middleware' => 'throttle:5,1'], function () use ($router) {
    $router->post('users/login', ['as' => 'login', 'uses' => 'AuthController@login']);
    $router->post('users/register', ['as' => 'register', 'uses' => 'RegisterController@index']);
    $router->post('users/refresh-token', ['as' => 'refresh-token', 'uses' => 'AuthController@refreshToken']);
    $router->get('users/{id}/posts', ['as' => 'user.show', 'uses' => 'UserController@posts']);
    $router->get('users/{id}', ['as' => 'user.show', 'uses' => 'UserController@show']);
});

$router->get('restart', ['as' => 'restart', 'uses' => 'SettingController@restart']);
