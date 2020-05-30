<?php

use Laravel\Lumen\Testing\DatabaseTransactions;
use Laravel\Lumen\Testing\WithoutMiddleware;

use TestSetup\Restaurant as RestaurantSetup;
use TestSetup\DoughCategory as DoughCategorySetup;

class DoughCategorySuccess extends TestCase
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

        $this->doughCategorySetup = new DoughCategorySetup($this->restaurant->id, true);
        $this->doughCategory = $this->doughCategorySetup->doughCategory;
        $this->dough = $this->doughCategorySetup->dough;
    }

    /**
     * @group Menu
     * @group Pizza
     * @group DoughCategory
     * @group Dough
     * @group Get
    */
    public function testGetDoughCategoriesAndOptions()
    {
        $response = $this->json('GET', "restaurant/".$this->restaurant->id."/pizza/dough-category");
        $response->seeStatusCode(200);
        $doughCategories = json_decode($response->response->getContent());

        $this->assertEquals($doughCategories[0]->name, $this->doughCategory->name);
        $this->assertEquals($doughCategories[0]->options[0]->name, $this->dough->name);
        $this->assertEquals($doughCategories[0]->options[0]->price, $this->dough->price);
        $this->assertEquals($doughCategories[0]->options[0]->description, $this->dough->description);
    }

    /**
     * @group Menu
     * @group Pizza
     * @group DoughCategory
     * @group Dough
     * @group Create
    */
    public function testCreateDoughCategoryAndOptions()
    {
        $response = $this->json('PUT', "restaurant/".$this->restaurant->id."/pizza/dough-category", [
            "name" => "Massas",
            "description" => "",
            "restaurant_id" => $this->restaurant->id,
            "options" => [
                [
                    "name" => "Massa normal",
                    "price" => 0,
                    "description" => ""
                ],
                [
                    "name" => "Massa grossa",
                    "price" => 1.99,
                    "description" => "Massa mais grossa que a normal"
                ]
            ]
        ]);
        $response->seeStatusCode(200);
        $doughCategory = json_decode($response->response->getContent());

        $this->assertEquals($doughCategory->name, "Massas");

        $this->assertEquals($doughCategory->options[0]->name, "Massa normal");
        $this->assertEquals($doughCategory->options[0]->price, 0);
        $this->assertEquals($doughCategory->options[0]->description, "");

        $this->assertEquals($doughCategory->options[1]->name, "Massa grossa");
        $this->assertEquals($doughCategory->options[1]->price, 1.99);
        $this->assertEquals($doughCategory->options[1]->description, "Massa mais grossa que a normal");
    }

    /**
     * @group Menu
     * @group Pizza
     * @group DoughCategory
     * @group Update
    */
    public function testUpdateDoughCategory()
    {
        $response = $this->json('POST', "restaurant/".$this->restaurant->id."/pizza/dough-category/".$this->doughCategory->id, [
            "name" => "Massas (updated)",
            "description" => "Massas normais"
        ]);
        $response->seeStatusCode(200);

        $updatedDoughCategory = \Model\DoughCategory::find($this->doughCategory->id);
        $this->assertEquals($updatedDoughCategory->name, "Massas (updated)");
        $this->assertEquals($updatedDoughCategory->description, "Massas normais");
    }

    /**
     * @group Menu
     * @group Pizza
     * @group DoughCategory
     * @group Delete
    */
    public function testDeleteDoughCategory()
    {
        $response = $this->json('DELETE', "restaurant/".$this->restaurant->id."/pizza/dough-category/".$this->doughCategory->id);
        $response->seeStatusCode(200);

        $this->missingFromDatabase('pizza_dough_categories', ['name' => $this->doughCategory->name]);
    }
}