
<?php

use Laravel\Lumen\Testing\DatabaseTransactions;
use Laravel\Lumen\Testing\WithoutMiddleware;

use TestSetup\Restaurant as RestaurantSetup;
use TestSetup\CrustCategory as CrustCategorySetup;

class CrustSuccess extends TestCase
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
     * @group Crust
     * @group Create
    */
    public function testCreateCrust()
    {
        $response = $this->json('PUT', "restaurant/".$this->restaurant->id."/pizza/crust-category/".$this->crustCategory->id."/crust", [
            "name" => "Sem borda",
            "price" => 0,
            "description" => ""
        ]);
        $response->seeStatusCode(201);

        $lastCrust = \Model\Crust::orderBy('id', 'desc')->get()->first();

        $this->assertEquals($lastCrust->name, "Sem borda");
        $this->assertEquals($lastCrust->price, 0);
        $this->assertEquals($lastCrust->description, "");
    }

    /**
     * @group Menu
     * @group Pizza
     * @group Crust
     * @group Update
    */
    public function testUpdateCrust()
    {
        $response = $this->json('POST', "restaurant/".$this->restaurant->id."/pizza/crust-category/".$this->crustCategory->id."/crust/".$this->crust->id, [
            "name" => "Borda de Catupiry",
            "description" => "Borda com recheio de Catupiry",
            "price" => 4.99
        ]);
        $response->seeStatusCode(200);

        $updatedCrust = \Model\Crust::find($this->crust->id);
        $this->assertEquals($updatedCrust->name, "Borda de Catupiry");
        $this->assertEquals($updatedCrust->description, "Borda com recheio de Catupiry");
        $this->assertEquals($updatedCrust->price, 4.99);
    }

    /**
     * @group Menu
     * @group Pizza
     * @group Crust
     * @group Delete
    */
    public function testDeleteCrust()
    {
        $response = $this->json('DELETE', "restaurant/".$this->restaurant->id."/pizza/crust-category/".$this->crustCategory->id."/crust/".$this->crust->id);
        $response->seeStatusCode(200);

        $this->missingFromDatabase('pizza_crust', ['name' => $this->crust->name, 'deleted_at' => null]);
    }
}