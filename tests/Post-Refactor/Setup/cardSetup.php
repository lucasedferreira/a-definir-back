<?php
namespace TestSetup;

class Card
{
    public $card;
    public $restaurant;
    
    public function __construct($restaurantID, $card = [])
    {
        $this->card = self::createRandomDummyCard($restaurantID, $card);
    }

    public static function createRandomDummyCard($restaurantID, $card = [])
    {
        $card = array_merge([
            'restaurant_id' => $restaurantID
        ], $card);

        return factory(\Model\RestaurantCardType::class)->create($card);
    }

    public static function createCardTypes()
    {
        \DB::statement('INSERT INTO card_types (id, name)
                        VALUES  (1, "Mastercard"),
                                (2, "Visa"),
                                (3, "Hipercard"),
                                (4, "Sodexo"),
                                (5, "Elo"); ');
    }
}
