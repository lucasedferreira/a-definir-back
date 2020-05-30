<?php

use Laravel\Lumen\Testing\DatabaseTransactions;
use Laravel\Lumen\Testing\WithoutMiddleware;

use TestSetup\Restaurant as RestaurantSetup;
use TestSetup\GenericCategory as GenericCategorySetup;
use TestSetup\GenericProduct as GenericProductSetup;

class RestaurantSuccess extends TestCase
{
    use DatabaseTransactions;
    use WithoutMiddleware;

    public $faker;

    public function setUp()
    {
        parent::setUp();

        $this->faker = Faker\Factory::create();

        $this->setup = new RestaurantSetup();
        $this->restaurant = $this->setup->restaurant;

        $this->genericCategorySetup = new GenericCategorySetup($this->restaurant->id);
        $this->genericCategory = $this->genericCategorySetup->genericCategory;

        $this->genericProduct = GenericProductSetup::createRandomDummyProduct($this->restaurant->id, $this->genericCategory->id, 1);
    }

    /**
     * @group Restaurant
    */
    public function testGetRestaurant()
    {
        $response = $this->json('GET', "restaurant/".$this->restaurant->id);
        $response->seeStatusCode(200);

        $restaurant = json_decode($response->response->getContent());
        $this->assertEquals($restaurant->name, $this->restaurant->name);
        $this->assertEquals($restaurant->phone, $this->restaurant->phone);
        $this->assertEquals($restaurant->street, $this->restaurant->street);
    }

    /**
     * @group Restaurant
    */
    public function testUpdateRestaurant()
    {
        $response = $this->json('PUT', "restaurant/".$this->restaurant->id, [
            'name' => 'Siri Cascudo 2',
            'phone' => '55047999999999',
            'street' => 'Fenda do Biquini'
        ]);
        
        $response->seeStatusCode(200);

        $updatedRestaurant = \Model\Restaurant::find($this->restaurant->id);
        $this->assertEquals($updatedRestaurant->name, 'Siri Cascudo 2');
        $this->assertEquals($updatedRestaurant->phone, '55047999999999');
        $this->assertEquals($updatedRestaurant->street, 'Fenda do Biquini');
    }

    // /**
    //  * @group Restaurant
    //  * @group Menu
    // */
    // public function testGetMenuRestaurant()
    // {
    //     $response = $this->json('GET', "restaurant/".$this->restaurant->id."/menu");
    //     $response->seeStatusCode(200);
    //     $menu = json_decode($response->response->getContent());
    //     dd($menu);
    // }
}