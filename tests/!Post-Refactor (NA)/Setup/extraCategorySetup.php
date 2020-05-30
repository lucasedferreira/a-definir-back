<?php
namespace TestSetup;

class ExtraCategory
{
    public $extraCategory;
    public $extra = [];
    public $restaurantID;

    public function __construct($restaurantID)
    {
        $this->extraCategory = factory(\Model\ExtraCategory::class)->create([
            'name' => 'Frutos do mar',
            'type' => 1,
            'description' => 'Extras de frutos do mar',
            'restaurant_id' => $restaurantID
        ]);
    }
}
