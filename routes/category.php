<?php

$router->group(['middleware' => 'throttle:5,1'], function () use ($router) {
    $router->get('categories', ['as' => 'category.index', 'uses' => 'CategoryController@index']);
    $router->get('categories/{id}', ['as' => 'category.index', 'uses' => 'CategoryController@show']);
    $router->get('categories/{id}/posts', ['as' => 'category.posts', 'uses' => 'PostController@index']);
});

$router->group(['middleware' => 'jwt.auth'], function () use ($router) {
    $router->post('categories', ['as' => 'category.index', 'uses' => 'CategoryController@store']);
    $router->put('categories/{id}', ['as' => 'category.index', 'uses' => 'CategoryController@update']);
    $router->delete('categories/{id}', ['as' => 'category.index', 'uses' => 'CategoryController@destroy']);
});