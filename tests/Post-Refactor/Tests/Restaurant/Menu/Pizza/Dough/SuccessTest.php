
<?php

use Laravel\Lumen\Testing\DatabaseTransactions;
use Laravel\Lumen\Testing\WithoutMiddleware;

use TestSetup\Restaurant as RestaurantSetup;
use TestSetup\DoughCategory as DoughCategorySetup;

class DoughSuccess extends TestCase
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
     * @group Dough
     * @group Create
    */
    public function testCreateDough()
    {
        $response = $this->json('PUT', "restaurant/".$this->restaurant->id."/pizza/dough-category/".$this->doughCategory->id."/dough", [
            "name" => "Massa média",
            "price" => 0,
            "description" => ""
        ]);
        $response->seeStatusCode(201);

        $lastDough = \Model\Dough::orderBy('id', 'desc')->get()->first();

        $this->assertEquals($lastDough->name, "Massa média");
        $this->assertEquals($lastDough->price, 0);
        $this->assertEquals($lastDough->description, "");
    }

    /**
     * @group Menu
     * @group Pizza
     * @group Dough
     * @group Update
    */
    public function testUpdateDough()
    {
        $response = $this->json('POST', "restaurant/".$this->restaurant->id."/pizza/dough-category/".$this->doughCategory->id."/dough/".$this->dough->id, [
            "name" => "Massa fina",
            "description" => "Massa mais fina que a normal",
            "price" => 2.50
        ]);
        $response->seeStatusCode(200);

        $updatedDough = \Model\Dough::find($this->dough->id);
        $this->assertEquals($updatedDough->name, "Massa fina");
        $this->assertEquals($updatedDough->description, "Massa mais fina que a normal");
        $this->assertEquals($updatedDough->price, 2.50);
    }

    /**
     * @group Menu
     * @group Pizza
     * @group Dough
     * @group Delete
    */
    public function testDeleteDough()
    {
        $response = $this->json('DELETE', "restaurant/".$this->restaurant->id."/pizza/dough-category/".$this->doughCategory->id."/dough/".$this->dough->id);
        $response->seeStatusCode(200);

        $this->missingFromDatabase('pizza_dough', ['name' => $this->dough->name, 'deleted_at' => null]);
    }
}