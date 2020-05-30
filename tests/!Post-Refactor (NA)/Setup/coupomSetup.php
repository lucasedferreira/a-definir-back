<?php
namespace TestSetup;

class Coupom
{
    public $coupom;
    public $restaurant;
    
    public function __construct($restaurantID)
    {
        $this->coupom = factory(\Model\Coupom::class)->create([
            'code' => 'melhorDiaDeTodos',
            'type' => 'percentDiscount',
            'percent_discount' => '99.99',
            'restaurant_id' => $restaurantID
        ]);
    }
}
