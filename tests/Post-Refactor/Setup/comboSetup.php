<?php
namespace TestSetup;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class Combo
{
    public $combo;
    public $restaurantID;

    public function __construct($restaurantID)
    {

        $image = UploadedFile::fake()->image('image.png', 600, 600);    
        $comboImage = \ImageManager::createImage('products', $image);

        $this->combo[] = factory(\Model\Combo::class)->create([
            'name' => 'Pizza com hamburger de siri',
            'price' => 45,
            'description' => 'O nome já explica bem',
            'assortment' => 0,
            'image' => $comboImage, 
            'available' => 0,
            'restaurant_id' => $restaurantID
        ]);

        $this->combo[] = factory(\Model\Combo::class)->create([
            'name' => 'Pizza com molho tártaro',
            'price' => 45,
            'description' => 'O nome desse também explica bem',
            'assortment' => 0,
            'available' => 0,
            'restaurant_id' => $restaurantID
        ]);

        $this->combo[] = factory(\Model\Combo::class)->create([
            'name' => 'Pizza e suco de algas',
            'price' => 45,
            'description' => 'Abra a felicidade marítima ',
            'assortment' => 0,
            'available' => 1,
            'restaurant_id' => $restaurantID
        ]);
    }
}
