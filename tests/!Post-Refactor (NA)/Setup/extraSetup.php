<?php
namespace TestSetup;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class Extra
{
    public $extraCategory;
    public $extra = [];
    public $restaurantID;

    public function __construct($restaurantID, $extraCategoryID, $extra = [], $number_of_extras = 1)
    {
        $this->extra = self::createRandomDummyExtra($restaurantID, $extraCategoryID, $extra, $number_of_extras);
    }

    public static function createRandomDummyExtra($restaurantID, $extraCategoryID, $extra = [], $number_of_extras = 1)
    {
        $image = UploadedFile::fake()->image('image.png', 600, 600);
        $extraImage = \ImageManager::createImage('products', $image);

        $extra = array_merge([
            'name' => 'Salmão',
            'assortment' => 0,
            'description' => 'Pedaços de salmão',
            'image' => $extraImage,
            'extra_id' => $extraCategoryID,
        ], $extra);

        $extras = factory(\Model\Extra::class, $number_of_extras)->create($extra);

        if(sizeof($extras) == 1) return $extras[0];

        return $extras;
    }
}
