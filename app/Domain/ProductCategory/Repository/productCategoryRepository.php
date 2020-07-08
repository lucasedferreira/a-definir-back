<?php

namespace ProductCategory;

use Model\ProductCategory;

class Repository
{
    public static function get()
    {
        return ProductCategory::with('products')->orderBy('id', 'DESC')->get();
    }

    public static function create($category)
    {
        return ProductCategory::create($category);
    }

    public static function delete($categoryID)
    {
        ProductCategory::find($categoryID)->delete();
    }
}