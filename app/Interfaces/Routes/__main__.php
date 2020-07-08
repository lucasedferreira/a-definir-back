<?php
    $router->post('/', '\Controllers\Product\MainController@test');

    require 'userRoutes.php';
    require 'productRoutes.php';