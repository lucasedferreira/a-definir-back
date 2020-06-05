<?php

$router->group([
    'middleware'  => 'jwt.auth', 
    'namespace'   => '\Controllers\Product',
    'prefix' => 'product'
], function ($router) {

    $router->get('/', 'MainController@get');
    $router->post('/', 'MainController@create');

    $router->group([
        'prefix' => '{productID}'
    ], function($router){
    });

    $router->group([
        'namespace'   => '\Controllers\ProductCategory',
        'prefix' => 'category'
    ], function($router){
        $router->get('/', 'MainController@get');
    });
});