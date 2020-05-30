<?php
namespace TestSetup;

use TestSetup\ExtraCategory as ExtraCategorySetup;

class GenericProduct
{
    public $genericCategory = [];
    public $genericProduct = [];
    public $restaurantID;

    public function __construct($restaurantID, $genericCategoryID, $number_of_products = 0)
    {
        $this->genericProduct = self::createRandomDummyProduct($restaurantID, $genericCategoryID, $number_of_products, $associate_with_extraID);
    }

    public static function createRandomDummyProduct($restaurantID, $genericCategoryID, $number_of_products = 0)
    {
        $products = factory(\Model\GenericProduct::class, $number_of_products)->create([
            'assortment' => 0,
            'category_id' => $genericCategoryID,
            'restaurant_id' => $restaurantID
        ])->each(function ($product)  use (&$number_of_products) {
            $product->update([
                'assortment' => $number_of_products --
            ]);
        });

        if(sizeof($products) == 1) return $products[0];

        return $products;
    }

    public static function associateProductWithExtraCategory($product, $extraCategoryID)
    {
        $product->extras()->attach($extraCategoryID, [
            'assortment' => 0
        ]);
    }
}