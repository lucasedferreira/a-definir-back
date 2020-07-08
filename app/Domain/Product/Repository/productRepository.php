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
        return Product::create($product);
    }

    public static function getByID($productID)
    {
        return Product::find($productID);
    }

    public static function delete($productID)
    {
        Product::find($productID)->delete();
    }
}