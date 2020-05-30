<?php
namespace TestSetup;

class FlavorCategory
{
    public $flavorCategory;
    public $flavor;
    public $restaurantID;

    public function __construct($restaurantID, $create_flavor = false)
    {
        $this->flavorCategory = factory(\Model\FlavorCategory::class)->create([
            'name' => 'Tradicionais',
            'restaurant_id' => $restaurantID
        ]);

        if($create_flavor){
            $this->flavor = factory(\Model\Flavor::class)->create([
                'name' => 'Calabresa',
                'pizza_flavor_category_id' => $this->flavorCategory->id
            ]);
        }
    }
}
