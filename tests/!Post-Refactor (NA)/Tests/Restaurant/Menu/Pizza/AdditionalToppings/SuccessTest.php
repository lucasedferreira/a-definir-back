<?php

use Laravel\Lumen\Testing\DatabaseTransactions;
use Laravel\Lumen\Testing\WithoutMiddleware;

use TestSetup\Restaurant as RestaurantSetup;
use TestSetup\AdditionalToppings as AdditionalToppingsSetup;

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

        $this->toppingSetup = new AdditionalToppingsSetup($this->restaurant->id);
        $this->topping = $this->toppingSetup->topping;
    }

    /**
     * @group Menu
     * @group Pizza
     * @group AdditionalToppings
     * @group Get
    */
    public function testGetAdditionalToppings()
    {
        $response = $this->json('GET', "restaurant/".$this->restaurant->id."/pizza/additional-toppings");
        $response->seeStatusCode(200);
        $toppings = json_decode($response->response->getContent());

        $this->assertEquals($toppings[0]->name, $this->topping->name);
        $this->assertEquals($toppings[0]->price, $this->topping->price);
    }

    /**
     * @group Menu
     * @group Pizza
     * @group AdditionalToppings
     * @group Create
    */
    public function testGetAdditionalToppingas()
    {
        $response = $this->json('PUT', "restaurant/".$this->restaurant->id."/pizza/additional-toppings", [
            "name" => "Picles",
            "price" => 1.25,
            "available" => false
        ]);
        $response->seeStatusCode(201);
        
        $lastTopping = \Model\AdditionalToppings::orderBy('id', 'desc')->get()->first();
        $this->assertEquals($lastTopping->name, "Picles");
        $this->assertEquals($lastTopping->price, 1.25);
        $this->assertEquals($lastTopping->available, 0);
    }

    /**
     * @group Menu
     * @group Pizza
     * @group AdditionalToppings
     * @group Update
    */
    public function testUpdateAdditionalToppingas()
    {
        $response = $this->json('POST', "restaurant/".$this->restaurant->id."/pizza/additional-toppings/".$this->topping->id, [
            "price" => 99.99
        ]);
        $response->seeStatusCode(200);
        
        $lastTopping = \Model\AdditionalToppings::find($this->topping->id);
        $this->assertEquals($lastTopping->name, $this->topping->name);
        $this->assertEquals($lastTopping->price, 99.99);
    }

    /**
     * @group Menu
     * @group Pizza
     * @group AdditionalToppings
     * @group Delete
    */
    public function testDeleteAdditionalToppingas()
    {
        $response = $this->json('DELETE', "restaurant/".$this->restaurant->id."/pizza/additional-toppings/".$this->topping->id);
        $response->seeStatusCode(200);
        
        $this->missingFromDatabase('pizza_additional_toppings', ['name' => $this->topping->name]);
    }
}