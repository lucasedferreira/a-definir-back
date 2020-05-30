<?php
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Lumen\Testing\DatabaseTransactions;
use Laravel\Lumen\Testing\WithoutMiddleware;

use TestSetup\Restaurant as RestaurantSetup;
use TestSetup\Size as SizeSetup;

class SizeFlavorSuccess extends TestCase
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
        
        $flavorSetup = SizeSetup::createFlavorCategoryAndFlavor($this->restaurant->id);
        $this->flavorCategory = $flavorSetup['flavorCategory'];
        $this->flavor = $flavorSetup['flavor'];

        $this->size = SizeSetup::createRandomDummySize($this->restaurant->id, [], ['flavorCategory' => $this->flavorCategory]);
    }

    /**
     * @group Menu
     * @group Pizza
     * @group Size
     * @group FlavorCategory
     * @group Associate
    */
    public function testDisassociateAndAssociateSizeWithFlavorCategory()
    {
        $response = $this->json('DELETE', "restaurant/".$this->restaurant->id."/pizza/size/".$this->size->id."/flavor-category/".$this->flavorCategory->id);
        $response->seeStatusCode(200);

        $this->missingFromDatabase('pizza_size_assoc_flavor_category', ['size_id' => $this->size->id, 'flavor_category_id' => $this->flavorCategory->id]);


        $response = $this->json('PUT', "restaurant/".$this->restaurant->id."/pizza/size/".$this->size->id."/flavor-category/".$this->flavorCategory->id, [
            "fractions" => [
                [
                    "pizzaPart" => 1,
                    "price" => 4,
                    "exception" => [
                        [
                            "pizzaPart" => 1,
                            "price" => 8,
                            "flavorID" => $this->flavor->id
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
                            "flavorID" => $this->flavor->id
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
                            "flavorID" => $this->flavor->id
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
                            "flavorID" => $this->flavor->id
                        ]
                    ]
                ]
            ]
        ]);

        $response->seeStatusCode(200);

        $this->seeInDatabase('pizza_size_assoc_flavor_category', ['size_id' => $this->size->id, 'flavor_category_id' => $this->flavorCategory->id]);

        $sizeAndFlavorCatAssoc = \Model\SizeFlavorCategoryAssoc::where('size_id', $this->size->id)->first();
        $fractionsAndExceptions = \Model\Fraction::where('size_assoc_category_id', $sizeAndFlavorCatAssoc->id)->with('exceptions')->get()->toArray();

        $this->assertEquals($fractionsAndExceptions[0]['pizza_part'], 1);
        $this->assertEquals($fractionsAndExceptions[0]['price'], 4);
        $this->assertEquals($fractionsAndExceptions[0]['exceptions'][0]['pizza_flavor_id'], $this->flavor->id);
        $this->assertEquals($fractionsAndExceptions[0]['exceptions'][0]['price'], 8);

        $this->assertEquals($fractionsAndExceptions[1]['pizza_part'], 2);
        $this->assertEquals($fractionsAndExceptions[1]['price'], 2);
        $this->assertEquals($fractionsAndExceptions[1]['exceptions'][0]['pizza_flavor_id'], $this->flavor->id);
        $this->assertEquals($fractionsAndExceptions[1]['exceptions'][0]['price'], 4);

        $this->assertEquals($fractionsAndExceptions[2]['pizza_part'], 3);
        $this->assertEquals($fractionsAndExceptions[2]['price'], 1.35);
        $this->assertEquals($fractionsAndExceptions[2]['exceptions'][0]['pizza_flavor_id'], $this->flavor->id);
        $this->assertEquals($fractionsAndExceptions[2]['exceptions'][0]['price'], 2.7);

        $this->assertEquals($fractionsAndExceptions[3]['pizza_part'], 4);
        $this->assertEquals($fractionsAndExceptions[3]['price'], 1);
        $this->assertEquals($fractionsAndExceptions[3]['exceptions'][0]['pizza_flavor_id'], $this->flavor->id);
        $this->assertEquals($fractionsAndExceptions[3]['exceptions'][0]['price'], 2);
    }
}