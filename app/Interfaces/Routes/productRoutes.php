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
        $router->get('/', 'MainController@getByID');
        $router->delete('/', 'MainController@delete');
    });

});

$router->group([
    'namespace'   => '\Controllers\ProductCategory',
    'prefix' => 'product-category'
], function($router){
    $router->get('/', 'MainController@get');
    $router->post('/', 'MainController@create');

    $router->group([
        'prefix' => '{categoryID}'
    ], function($router){
        $router->delete('/', 'MainController@delete');
    });
});