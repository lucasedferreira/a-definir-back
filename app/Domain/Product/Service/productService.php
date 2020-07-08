<?php

namespace Product;

class Service
{
    public static function create($product)
    {
        $product = Parser::product($product);
        $productModel = Repository::create($product);

        if(key_exists('images', $product)) {
            $images = [];
            foreach ($product['images'] as $image) {
                $imageName = \ImageManager::createImage('products', $image);
                \ProductImage\Repository::create(['url' => $imageName, 'product_id' => $productModel->id]);
            }
        }

        return $productModel;
    }
}