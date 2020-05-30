<?php
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Lumen\Testing\DatabaseTransactions;
use Laravel\Lumen\Testing\WithoutMiddleware;

use TestSetup\Restaurant as RestaurantSetup;
use TestSetup\Size as SizeSetup;

class SizeSuccess extends TestCase
{
    use DatabaseTransactions;
    use WithoutMiddleware;

    public $faker;

    public function setUp()
    {
        parent::setUp();
        $this->faker = Faker\Factory::create();

        $this->restaurantSetup = new RestaurantSetup();
        $this->restaurant = $this->restaurantSetup->restaurant;
        
        $this->requisites = SizeSetup::createAllRequisitesForSize($this->restaurant->id);

        $this->sizes = [];

        $this->sizes[] = SizeSetup::createRandomDummySize($this->restaurant->id, [
            'name' => 'Grande',
            'max_flavors' => 4,
            'price' => 40,
            'assortment' => 0,
            'price_behavior' => 'incremental',
        ], $this->requisites);

        $this->sizes[] = SizeSetup::createRandomDummySize($this->restaurant->id, [
            'name' => 'Média',
            'max_flavors' => 3,
            'price' => 30,
            'assortment' => 1,
            'price_behavior' => 'highest',
            'description' => 'por maior, com frações'
        ], $this->requisites);

        $this->sizes[] = SizeSetup::createRandomDummySize($this->restaurant->id, [
            'name' => 'Pequena',
            'max_flavors' => 2,
            'price' => 15,
            'assortment' => 2,
            'price_behavior' => 'average'
        ], $this->requisites);

        $this->sizes[] = SizeSetup::createRandomDummySize($this->restaurant->id, [
            'name' => 'Broto',
            'max_flavors' => 1,
            'price' => 10,
            'assortment' => 3,
            'available' => false
        ], $this->requisites);
    }

    /**
     * @group Menu
     * @group Pizza
     * @group Size
     * @group Get
    */
    public function testGetSizesAndFlavors()
    {
        $response = $this->json('GET', "restaurant/".$this->restaurant->id."/pizza/size");
        $response->seeStatusCode(200);
        $sizes = json_decode($response->response->getContent());

        foreach($sizes as $key => $size) {
            $this->assertEquals($size->id, $this->sizes[$key]->id);
            $this->assertEquals($size->name, $this->sizes[$key]->name);
            $this->assertEquals($size->price, $this->sizes[$key]->price);
            $this->assertEquals($size->assortment, $this->sizes[$key]->assortment);
            $this->assertEquals($size->available, $this->sizes[$key]->available);
            $this->assertEquals($size->maxFlavors, $this->sizes[$key]->max_flavors);
            $this->assertEquals($size->description, $this->sizes[$key]->description);
            $this->assertEquals($size->priceBehavior, $this->sizes[$key]->price_behavior);

            $this->assertEquals($size->crust, $this->requisites['crustCategory']->id);
            $this->assertEquals($size->dough, $this->requisites['doughCategory']->id);
            $this->assertEquals($size->flavors[0]->flavorCategoryID, $this->requisites['flavorCategory']->id);
        }
    }

    /**
     * @group Menu
     * @group Pizza
     * @group Size
     * @group Create
    */
    public function testCreateSize()
    {
        $data = [
            "name" => "Família",
            "price" => 50,
            "oldPrice" => 60,
            "description" => "Uma pizza bem grande",
            "priceBehavior" => "incremental",
            "maxFlavors" => 4,
            "tag" => "test",
            "crust" => [ "id" => $this->requisites['crustCategory']->id ],
            "dough" => [ "id" => $this->requisites['doughCategory']->id ],
            "flavors" => [
                [
                    "id" => $this->requisites['flavorCategory']->id,
                    "fractions" => [
                        [
                            "pizzaPart" => 1,
                            "price" => 4,
                            "exception" => [
                                [
                                    "pizzaPart" => 1,
                                    "price" => 8,
                                    "flavorID" => $this->requisites['flavor']->id
                                ]
                            ]
                        ],
                        [
                            "pizzaPart" => 2,
                            "price" => 2,
                            "exception" => [
                                [
                                    "pizzaPart" => 2,
                                    "price" => 4,
                                    "flavorID" => $this->requisites['flavor']->id
                                ]
                            ]
                        ],
                        [
                            "pizzaPart" => 3,
                            "price" => 1.35,
                            "exception" => [
                                [
                                    "pizzaPart" => 3,
                                    "price" => 2.7,
                                    "flavorID" => $this->requisites['flavor']->id
                                ]
                            ]
                        ],
                        [
                            "pizzaPart" => 4,
                            "price" => 1,
                            "exception" => [
                                [
                                    "pizzaPart" => 4,
                                    "price" => 2,
                                    "flavorID" => $this->requisites['flavor']->id
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ];

        $response = $this->json('POST', "restaurant/".$this->restaurant->id."/pizza/size", $data);
        $response->seeStatusCode(200);
        $newSize = json_decode($response->response->getContent());

        $this->assertEquals($newSize->name, "Família");
        $this->assertEquals($newSize->price, 50);
        $this->assertEquals($newSize->oldPrice, 60);
        $this->assertEquals($newSize->assortment, 4);
        $this->assertEquals($newSize->available, true);
        $this->assertEquals($newSize->maxFlavors, 4);
        $this->assertEquals($newSize->tag, "test");
        $this->assertEquals($newSize->description, "Uma pizza bem grande");
        $this->assertEquals($newSize->priceBehavior, "incremental");
        $this->assertEquals($newSize->type, "pizza");
        $this->assertEquals($newSize->hasAdditionalToppings, true);

        $this->assertEquals($newSize->crust, $this->requisites['crustCategory']->id);
        $this->assertEquals($newSize->dough, $this->requisites['doughCategory']->id);

        $this->assertEquals($newSize->flavors[0]->flavorCategoryID, $this->requisites['flavorCategory']->id);

        $this->assertEquals($newSize->flavors[0]->fractions[0]->pizzaPart, 1);
        $this->assertEquals($newSize->flavors[0]->fractions[0]->price, 4);
        $this->assertEquals($newSize->flavors[0]->fractions[0]->exception[0]->flavorID, $this->requisites['flavor']->id);
        $this->assertEquals($newSize->flavors[0]->fractions[0]->exception[0]->price, 8);

        $this->assertEquals($newSize->flavors[0]->fractions[1]->pizzaPart, 2);
        $this->assertEquals($newSize->flavors[0]->fractions[1]->price, 2);
        $this->assertEquals($newSize->flavors[0]->fractions[1]->exception[0]->flavorID, $this->requisites['flavor']->id);
        $this->assertEquals($newSize->flavors[0]->fractions[1]->exception[0]->price, 4);

        $this->assertEquals($newSize->flavors[0]->fractions[2]->pizzaPart, 3);
        $this->assertEquals($newSize->flavors[0]->fractions[2]->price, 1.35);
        $this->assertEquals($newSize->flavors[0]->fractions[2]->exception[0]->flavorID, $this->requisites['flavor']->id);
        $this->assertEquals($newSize->flavors[0]->fractions[2]->exception[0]->price, 2.7);

        $this->assertEquals($newSize->flavors[0]->fractions[3]->pizzaPart, 4);
        $this->assertEquals($newSize->flavors[0]->fractions[3]->price, 1);
        $this->assertEquals($newSize->flavors[0]->fractions[3]->exception[0]->flavorID, $this->requisites['flavor']->id);
        $this->assertEquals($newSize->flavors[0]->fractions[3]->exception[0]->price, 2);
    }

    /**
     * @group Menu
     * @group Pizza
     * @group Size
     * @group Assortment
    */
    public function testAssortmentSizes()
    {
        $response = $this->json('POST', "restaurant/".$this->restaurant->id."/pizza/size/assortment", [
            [
                "id" => $this->sizes[0]->id,
                "assortment" => 2
            ],
            [
                "id" => $this->sizes[1]->id,
                "assortment" => 0
            ],
            [
                "id" => $this->sizes[2]->id,
                "assortment" => 1
            ],
            [
                "id" => $this->sizes[3]->id,
                "assortment" => 3
            ]
        ]);
        $response->seeStatusCode(200);

        $sizes = \Model\Size::get()->toArray();

        $this->assertEquals($sizes[0]['id'], $this->sizes[0]->id);
        $this->assertEquals($sizes[0]['assortment'], 2);

        $this->assertEquals($sizes[1]['id'], $this->sizes[1]->id);
        $this->assertEquals($sizes[1]['assortment'], 0);

        $this->assertEquals($sizes[2]['id'], $this->sizes[2]->id);
        $this->assertEquals($sizes[2]['assortment'], 1);

        $this->assertEquals($sizes[3]['id'], $this->sizes[3]->id);
        $this->assertEquals($sizes[3]['assortment'], 3);
    }

    /**
     * @group Menu
     * @group Pizza
     * @group Size
     * @group Update
    */
    public function testUpdateSize()
    {
        $response = $this->json('POST', "restaurant/".$this->restaurant->id."/pizza/size/".$this->sizes[0]->id, [
            "name" => "Pizza Grande",
            "oldPrice" => 50
        ]);
        $response->seeStatusCode(200);

        $size = \Model\Size::find($this->sizes[0]->id);
        $this->assertEquals($size->name, "Pizza Grande");
        $this->assertEquals($size->old_price, 50);
    }

    /**
     * @group Menu
     * @group Pizza
     * @group Size
     * @group Delete
    */
    public function testDeleteSize()
    {
        $response = $this->json('DELETE', "restaurant/".$this->restaurant->id."/pizza/size/".$this->sizes[0]->id);
        $response->seeStatusCode(200);

        $this->missingFromDatabase('pizza_sizes', ['name' => $this->sizes[0]->name, 'deleted_at' => null]);
    }

    /**
     * @group Menu
     * @group Pizza
     * @group Size
     * @group Upload
     * @group Image
    */
    public function testUploadImageSize()
    {
        $image = UploadedFile::fake()->image('image.png', 600, 600);
        $response = $this->call('POST', "restaurant/".$this->restaurant->id."/pizza/size/".$this->sizes[0]->id."/image", [
            "image" => $image
        ]);

        $updatedSize = \Model\Size::find($this->sizes[0]->id);
        $imageName = str_replace('"', '', $response->getContent());
        $this->assertEquals($updatedSize->image, $imageName);
        $this->assertFileExists('public/images/products/'.$imageName.'/lg_'.$imageName.'.jpg');
        $this->assertFileIsReadable('public/images/products/'.$imageName.'/lg_'.$imageName.'.jpg');
    }

    /**
     * @group Menu
     * @group Pizza
     * @group Size
     * @group Delete
     * @group Image
    */
    public function testDeleteImageSize()
    {
        $image = UploadedFile::fake()->image('image.png', 600, 600);
        $response = $this->json('DELETE', "restaurant/".$this->restaurant->id."/pizza/size/".$this->sizes[0]->id."/image");

        $response->seeStatusCode(200);
        
        $updatedSize = \Model\Size::find($this->sizes[0]->id);
        $this->assertEquals($updatedSize->image, '');
        $this->assertFileNotExists('public/images/products/'.$this->sizes[0]->image.'/lg_'.$this->sizes[0]->image.'.jpg');
    }
}