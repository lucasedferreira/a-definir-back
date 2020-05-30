<?php

use Laravel\Lumen\Testing\DatabaseTransactions;
use Laravel\Lumen\Testing\WithoutMiddleware;

use TestSetup\Restaurant as RestaurantSetup;
use TestSetup\CrustCategory as CrustCategorySetup;

class CrustCategorySuccess extends TestCase
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

        $this->crustCategorySetup = new CrustCategorySetup($this->restaurant->id, true);
        $this->crustCategory = $this->crustCategorySetup->crustCategory;
        $this->crust = $this->crustCategorySetup->crust;
    }

    /**
     * @group Menu
     * @group Pizza
     * @group CrustCategory
     * @group Crust
     * @group Get
    */
    public function testGetCrustCategoriesAndOptions()
    {
        $response = $this->json('GET', "restaurant/".$this->restaurant->id."/pizza/crust-category");
        $response->seeStatusCode(200);
        $crustCategories = json_decode($response->response->getContent());

        $this->assertEquals($crustCategories[0]->name, $this->crustCategory->name);
        $this->assertEquals($crustCategories[0]->options[0]->name, $this->crust->name);
        $this->assertEquals($crustCategories[0]->options[0]->price, $this->crust->price);
        $this->assertEquals($crustCategories[0]->options[0]->description, $this->crust->description);
    }

    /**
     * @group Menu
     * @group Pizza
     * @group CrustCategory
     * @group Crust
     * @group Create
    */
    public function testCreateCrustCategoryAndOptions()
    {
        $response = $this->json('PUT', "restaurant/".$this->restaurant->id."/pizza/crust-category", [
            "name" => "Krusty",
            "description" => "Trocadilho de Crust (borda) com Krusty (Krusty Krab = Siri Cascudo)",
            "restaurant_id" => $this->restaurant->id,
            "options" => [
                [
                    "name" => "Sem borda",
                    "price" => 0,
                    "description" => ""
                ],
                [
                    "name" => "Borda de Catupiry",
                    "price" => 3.99,
                    "description" => "Borda com recheio de Catupiry"
                ]
            ]
        ]);
        $response->seeStatusCode(200);
        $crustCategory = json_decode($response->response->getContent());

        $this->assertEquals($crustCategory->name, "Krusty");

        $this->assertEquals($crustCategory->options[0]->name, "Sem borda");
        $this->assertEquals($crustCategory->options[0]->price, 0);
        $this->assertEquals($crustCategory->options[0]->description, "");

        $this->assertEquals($crustCategory->options[1]->name, "Borda de Catupiry");
        $this->assertEquals($crustCategory->options[1]->price, 3.99);
        $this->assertEquals($crustCategory->options[1]->description, "Borda com recheio de Catupiry");
    }

    /**
     * @group Menu
     * @group Pizza
     * @group CrustCategory
     * @group Update
    */
    public function testUpdateCrustCategory()
    {
        $response = $this->json('POST', "restaurant/".$this->restaurant->id."/pizza/crust-category/".$this->crustCategory->id, [
            "name" => "Bordas (updated)",
            "description" => "Bordas tradicionais"
        ]);
        $response->seeStatusCode(200);

        $updatedCrustCategory = \Model\CrustCategory::find($this->crustCategory->id);
        $this->assertEquals($updatedCrustCategory->name, "Bordas (updated)");
        $this->assertEquals($updatedCrustCategory->description, "Bordas tradicionais");
    }

    /**
     * @group Menu
     * @group Pizza
     * @group CrustCategory
     * @group Delete
    */
    public function testDeleteCrustCategory()
    {
        $response = $this->json('DELETE', "restaurant/".$this->restaurant->id."/pizza/crust-category/".$this->crustCategory->id);
        $response->seeStatusCode(200);

        $this->missingFromDatabase('pizza_crust_categories', ['name' => $this->crustCategory->name]);
    }
}