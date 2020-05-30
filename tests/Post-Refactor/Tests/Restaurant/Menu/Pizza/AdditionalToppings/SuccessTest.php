
<?php

use Laravel\Lumen\Testing\DatabaseTransactions;
use Laravel\Lumen\Testing\WithoutMiddleware;

use TestSetup\Restaurant as RestaurantSetup;
use TestSetup\AdditionalToppingsCategory as AdditionalToppingsCategorySetup;

class AdditionalToppingsSuccess extends TestCase
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

        $this->additionalToppingsCategorySetup = new AdditionalToppingsCategorySetup($this->restaurant->id, true);
        $this->additionalToppingsCategory = $this->additionalToppingsCategorySetup->additionalToppingsCategory;
        $this->additionalToppings = $this->additionalToppingsCategorySetup->additionalToppings;
    }

    /**
     * @group Menu
     * @group Pizza
     * @group AdditionalToppings
     * @group Create
    */
    public function testCreateAdditionalToppings()
    {
        $response = $this->json('PUT', "restaurant/".$this->restaurant->id."/pizza/additional-toppings-category/".$this->additionalToppingsCategory->id."/additional-toppings", [
            "name" => "Adicionais salgados",
            "price" => 1.5
        ]);
        $response->seeStatusCode(201);

        $lastAdditionalToppings = \Model\AdditionalToppings::orderBy('id', 'desc')->get()->first();

        $this->assertEquals($lastAdditionalToppings->name, "Adicionais salgados");
        $this->assertEquals($lastAdditionalToppings->price, 1.5);
    }

    /**
     * @group Menu
     * @group Pizza
     * @group AdditionalToppings
     * @group Update
    */
    public function testUpdateAdditionalToppings()
    {
        $response = $this->json('POST', "restaurant/".$this->restaurant->id."/pizza/additional-toppings-category/".$this->additionalToppingsCategory->id."/additional-toppings/".$this->additionalToppings->id, [
            "name" => "calabresa",
            "price" => 4.99
        ]);
        $response->seeStatusCode(200);

        $updatedAdditionalToppings = \Model\AdditionalToppings::find($this->additionalToppings->id);
        $this->assertEquals($updatedAdditionalToppings->name, "calabresa");
        $this->assertEquals($updatedAdditionalToppings->price, 4.99);
    }

    /**
     * @group Menu
     * @group Pizza
     * @group AdditionalToppings
     * @group Delete
    */
    public function testDeleteAdditionalToppings()
    {
        $response = $this->json('DELETE', "restaurant/".$this->restaurant->id."/pizza/additional-toppings-category/".$this->additionalToppingsCategory->id."/additional-toppings/".$this->additionalToppings->id);
        $response->seeStatusCode(200);

        $this->missingFromDatabase('pizza_additional_toppings', ['name' => $this->additionalToppings->name]);
    }
}