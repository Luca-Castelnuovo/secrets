<?php

use CQ\Middleware\JSON;
use CQ\Routing\Middleware;
use CQ\Routing\Route;

Route::$router = $router->get();
Middleware::$router = $router->get();

Route::options('*', function () {
    return 'CORS Allowed';
});

Route::get('/', 'SecretsController@listStores');
Route::post('/', 'SecretsController@createStore', JSON::class);

Route::post('/{id}', 'SecretsController@getStore', JSON::class);
Route::put('/{id}', 'SecretsController@updateStore', JSON::class);
Route::delete('/{id}', 'SecretsController@deleteStore');
