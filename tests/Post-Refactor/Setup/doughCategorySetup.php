<?php
namespace TestSetup;

class DoughCategory
{
    public $doughCategory;
    public $dough;
    public $restaurantID;

    public function __construct($restaurantID, $create_dough = false)
    {
        $this->doughCategory = factory(\Model\DoughCategory::class)->create([
            'name' => 'Massas setup',
            'restaurant_id' => $restaurantID
        ]);

        if($create_dough){
            $this->dough = factory(\Model\Dough::class)->create([
                'name' => 'Massa fina',
                'price' => 2.0,
                'pizza_dough_category_id' => $this->doughCategory->id,
                'restaurant_id' => $restaurantID
            ]);
        }
    }
}
