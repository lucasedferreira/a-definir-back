<?php

use Laravel\Lumen\Testing\DatabaseTransactions;
use Laravel\Lumen\Testing\WithoutMiddleware;

use TestSetup\Restaurant as RestaurantSetup;
use TestSetup\AdditionalToppingsCategory as AdditionalToppingsCategorySetup;

class AdditionalToppingsCategorySuccess extends TestCase
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
     * @group AdditionalToppingsCategory
     * @group AdditionalToppings
     * @group Get
    */
    public function testGetAdditionalToppingsCategoriesAndOptions()
    {
        $response = $this->json('GET', "restaurant/".$this->restaurant->id."/pizza/additional-toppings-category");
        $response->seeStatusCode(200);
        $additionalToppingsCategories = json_decode($response->response->getContent());

        $this->assertEquals($additionalToppingsCategories[0]->name, $this->additionalToppingsCategory->name);
        $this->assertEquals($additionalToppingsCategories[0]->options[0]->name, $this->additionalToppings->name);
        $this->assertEquals($additionalToppingsCategories[0]->options[0]->price, $this->additionalToppings->price);
    }

    /**
     * @group Menu
     * @group Pizza
     * @group AdditionalToppingsCategory
     * @group AdditionalToppings
     * @group Create
    */
    public function testCreateAdditionalToppingsCategoryAndOptions()
    {
        $response = $this->json('PUT', "restaurant/".$this->restaurant->id."/pizza/additional-toppings-category", [
            "name" => "Adicionais salgados",
            "description" => "Ex.: calabresa, catupiry, ...",
            "restaurant_id" => $this->restaurant->id,
            "options" => [
                [
                    "name" => "calabresa",
                    "price" => 1.5
                ],
                [
                    "name" => "Catupiry",
                    "price" => 3.99
                ]
            ]
        ]);
        $response->seeStatusCode(200);
        $additionalToppingsCategory = json_decode($response->response->getContent());

        $this->assertEquals($additionalToppingsCategory->name, "Adicionais salgados");

        $this->assertEquals($additionalToppingsCategory->options[0]->name, "calabresa");
        $this->assertEquals($additionalToppingsCategory->options[0]->price, 1.5);

        $this->assertEquals($additionalToppingsCategory->options[1]->name, "Catupiry");
        $this->assertEquals($additionalToppingsCategory->options[1]->price, 3.99);
    }

    /**
     * @group Menu
     * @group Pizza
     * @group AdditionalToppingsCategory
     * @group Update
    */
    public function testUpdateAdditionalToppingsCategory()
    {
        $response = $this->json('POST', "restaurant/".$this->restaurant->id."/pizza/additional-toppings-category/".$this->additionalToppingsCategory->id, [
            "name" => "adicionais updated",
            "description" => "description updated"
        ]);
        $response->seeStatusCode(200);

        $updatedAdditionalToppingsCategory = \Model\AdditionalToppingsCategory::find($this->additionalToppingsCategory->id);
        $this->assertEquals($updatedAdditionalToppingsCategory->name, "adicionais updated");
        $this->assertEquals($updatedAdditionalToppingsCategory->description, "description updated");
    }

    /**
     * @group Menu
     * @group Pizza
     * @group AdditionalToppingsCategory
     * @group Delete
    */
    public function testDeleteAdditionalToppingsCategory()
    {
        $response = $this->json('DELETE', "restaurant/".$this->restaurant->id."/pizza/additional-toppings-category/".$this->additionalToppingsCategory->id);
        $response->seeStatusCode(200);

        $this->missingFromDatabase('pizza_additional_toppings_categories', ['name' => $this->additionalToppingsCategory->name]);
    }
}