<?php

$router->group(['middleware' => 'throttle:5,1'], function () use ($router) {
    $router->get('posts', ['as' => 'post.index', 'uses' => 'PostController@index']);
    $router->get('posts/{id}', ['as' => 'post.show', 'uses' => 'PostController@show']);
});

$router->group(['middleware' => 'jwt.auth'], function () use ($router) {
    $router->put('posts/{id}', [
        'as' => 'post.update', 
        'uses' => 'PostController@update'
    ]);

    $router->delete('posts/{id}', [
        'as' => 'post.destroy', 
        'uses' => 'PostController@destroy'
    ]);
});