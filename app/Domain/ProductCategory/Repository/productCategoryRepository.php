<?php

namespace ProductCategory;

use Model\ProductCategory;

class Repository
{
    public static function get()
    {
        return ProductCategory::with('products')->get();
    }
}