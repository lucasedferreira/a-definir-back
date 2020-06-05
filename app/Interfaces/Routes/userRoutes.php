<?php

$router->group([
    'middleware'  => 'jwt.auth', 
    'namespace'   => '\Controllers\User'
], function ($router) {
    $router->group([
        'prefix' => 'user',
    ], function ($router) {
        $router->get('/', 'MainController@getByRestaurant'); 
        $router->post('/', 'MainController@create');
        $router->get('auth', 'MainController@getUserByJWT');

        $router->group([
            'prefix' => '{userID}'
        ], function($router){
            $router->put('/', 'MainController@update');
            $router->delete('/', 'MainController@delete');
            $router->get('old-password', 'MainController@checkIfPasswordIsOld');
            $router->post('password', 'MainController@updatePasswordByOld');
        });
    });
});

$router->group([
    'namespace'=>'\Controllers\User'
], function ($router){
    $router->group([
        'prefix'=>'auth'
    ], function ($router) {
        $router->post('register', 'MainController@register');
        $router->post('login', 'MainController@authenticate');
        $router->post('check', 'MainController@checkToken');
        $router->post('refresh-token', 'MainController@refreshToken');
        $router->post('forgot-password', 'MainController@forgotPassword');
        $router->post('check-password-recovery-token', 'MainController@checkRecoveryToken');
        $router->post('delete-token', 'MainController@deleteToken');
        $router->post('recover-password', 'MainController@recoverPassword');
    }); 
});