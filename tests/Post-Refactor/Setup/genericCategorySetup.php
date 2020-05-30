<?php
namespace TestSetup;

class GenericCategory
{
    public $genericCategory = [];
    public $genericProduct = [];
    public $restaurantID;

    public function __construct($restaurantID, $number_of_generic_categories = 1)
    {
        $this->genericCategory = self::createRandomDummyGenericCategory($restaurantID, $number_of_generic_categories);
    }

    public static function createRandomDummyGenericCategory($restaurantID, $number_of_generic_categories = [])
    {
        $genericCategories = factory(\Model\GenericCategory::class, $number_of_generic_categories)->create(['restaurant_id' => $restaurantID]);

        if(sizeof($genericCategories) == 1) return $genericCategories[0];

        return $genericCategories;
    }
}