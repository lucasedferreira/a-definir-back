<?php

namespace Product;

use Model\Product;

class Repository
{
    public static function get()
    {
        return Product::get();
    }

    public static function create($product)
    {
        Product::create($product);
    }
}