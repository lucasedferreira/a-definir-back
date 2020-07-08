<?php

namespace ProductImage;

use Model\ProductImage;

class Repository
{
    public static function getByProductID($productID)
    {
        return Product::where('product_id', $productID)->get();
    }

    public static function create($image)
    {
        return ProductImage::create($image);
    }
}