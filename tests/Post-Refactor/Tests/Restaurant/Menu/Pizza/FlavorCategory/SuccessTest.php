<?php

use Laravel\Lumen\Testing\DatabaseTransactions;
use Laravel\Lumen\Testing\WithoutMiddleware;

use TestSetup\Restaurant as RestaurantSetup;
use TestSetup\FlavorCategory as FlavorCategorySetup;

class FlavorCategorySuccess extends TestCase
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

        $this->flavorCategorySetup = new FlavorCategorySetup($this->restaurant->id, true);
        $this->flavorCategory = $this->flavorCategorySetup->flavorCategory;
        $this->flavor = $this->flavorCategorySetup->flavor;
    }

    /**
     * @group Menu
     * @group Pizza
     * @group FlavorCategory
     * @group Flavor
     * @group Get
    */
    public function testGetFlavorCategoriesAndOptions()
    {
        $response = $this->json('GET', "restaurant/".$this->restaurant->id."/pizza/flavor-category");
        $response->seeStatusCode(200);
        $flavorCategories = json_decode($response->response->getContent());

        $this->assertEquals($flavorCategories[0]->name, $this->flavorCategory->name);
        $this->assertEquals($flavorCategories[0]->flavors[0]->name, $this->flavor->name);
        $this->assertEquals($flavorCategories[0]->flavors[0]->description, $this->flavor->description);
    }

    /**
     * @group Menu
     * @group Pizza
     * @group FlavorCategory
     * @group Flavor
     * @group Create
    */
    public function testCreateFlavorCategoryAndOptions()
    {
        $response = $this->json('PUT', "restaurant/".$this->restaurant->id."/pizza/flavor-category", [
            "name" => "Doces",
            "description" => "",
            "restaurant_id" => $this->restaurant->id,
            "flavors" => [
                [
                    "name" => "Chocolate preto",
                    "description" => ""
                ],
                [
                    "name" => "Prestígio",
                    "description" => "Chocolate com coco"
                ]
            ]
        ]);
        $response->seeStatusCode(200);
        $flavorCategory = json_decode($response->response->getContent());

        $this->assertEquals($flavorCategory->name, "Doces");

        $this->assertEquals($flavorCategory->flavors[0]->name, "Chocolate preto");
        $this->assertEquals($flavorCategory->flavors[0]->description, "");

        $this->assertEquals($flavorCategory->flavors[1]->name, "Prestígio");
        $this->assertEquals($flavorCategory->flavors[1]->description, "Chocolate com coco");
    }

    /**
     * @group Menu
     * @group Pizza
     * @group FlavorCategory
     * @group Update
    */
    public function testUpdateFlavorCategory()
    {
        $response = $this->json('POST', "restaurant/".$this->restaurant->id."/pizza/flavor-category/".$this->flavorCategory->id, [
            "name" => "Especiais",
            "description" => "Sabores especiais (preço pode variar)"
        ]);
        $response->seeStatusCode(200);

        $updatedFlavorCategory = \Model\FlavorCategory::find($this->flavorCategory->id);
        $this->assertEquals($updatedFlavorCategory->name, "Especiais");
        $this->assertEquals($updatedFlavorCategory->description, "Sabores especiais (preço pode variar)");
    }

    /**
     * @group Menu
     * @group Pizza
     * @group FlavorCategory
     * @group Delete
    */
    public function testDeleteFlavorCategory()
    {
        $response = $this->json('DELETE', "restaurant/".$this->restaurant->id."/pizza/flavor-category/".$this->flavorCategory->id);
        $response->seeStatusCode(200);

        // $a = \Model\FlavorCategory::get();
        // dd($a);

        $this->missingFromDatabase('pizza_flavor_categories', ['name' => $this->flavorCategory->name, 'deleted_at' => null]);
    }
}