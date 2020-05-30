<?php
namespace TestSetup;

class CrustCategory
{
    public $crustCategory;
    public $crust;
    public $restaurantID;

    public function __construct($restaurantID, $create_crust = false)
    {
        $this->crustCategory = factory(\Model\CrustCategory::class)->create([
            'name' => 'Bordas',
            'restaurant_id' => $restaurantID
        ]);

        if($create_crust){
            $this->crust = factory(\Model\Crust::class)->create([
                'name' => 'Borda de Queijo',
                'price' => 5.0,
                'pizza_crust_category_id' => $this->crustCategory->id,
                'restaurant_id' => $restaurantID
            ]);
        }
    }
}