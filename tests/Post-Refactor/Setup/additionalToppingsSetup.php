<?php
namespace TestSetup;

class AdditionalToppingsCategory
{
    public $topping;
    public $restaurantID;

    public function __construct($restaurantID, $create_topping = false)
    {
        $this->additionalToppingsCategory = factory(\Model\AdditionalToppingsCategory::class)->create([
            'name' => 'Adicionais Doces',
            'restaurant_id' => $restaurantID
        ]);

        if($create_topping){
            $this->additionalToppings = factory(\Model\AdditionalToppings::class)->create([
                'name' => 'Chocolate',
                'price' => 5.0,
                'toppings_category_id' => $this->additionalToppingsCategory->id,
                'restaurant_id' => $restaurantID
            ]);
        }
    }
}