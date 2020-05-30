<?php
namespace TestSetup;

class Motoboy
{
    public $motoboy;
    public $restaurant;
    
    public function __construct($restaurantID)
    {
        $this->client = self::createRandomDummyMotoboy($restaurantID);
    }

    public static function createRandomDummyMotoboy($restaurantID)
    {
        return factory(\Model\Motoboy::class)->create(['restaurant_id' => $restaurantID]);
    }
}