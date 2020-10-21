<?php

$router->get('users/profile', [
    'as' => 'profile.index',
    'uses' => 'ProfileController@index'
]);

$router->put('users/profile', [
    'as' => 'profile.update', 
    'uses' => 'ProfileController@update'
]);

$router->delete('users/profile', [
    'as' => 'profile.destroy',
    'uses' => 'ProfileController@destroy'
]);