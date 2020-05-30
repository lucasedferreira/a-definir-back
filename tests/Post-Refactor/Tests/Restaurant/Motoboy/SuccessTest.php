<?php

use Laravel\Lumen\Testing\DatabaseTransactions;
use Laravel\Lumen\Testing\WithoutMiddleware;

use TestSetup\Restaurant as RestaurantSetup;
use TestSetup\Motoboy as MotoboySetup;
use TestSetup\Order as OrderSetup;

class MotoboySuccess extends TestCase
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

        $this->motoboy = MotoboySetup::createRandomDummyMotoboy($this->restaurant->id);

        $this->order = OrderSetup::createRandomDummyOrders([
            "motoboy_id" => $this->motoboy->id,
            "restaurant_id" => $this->restaurant->id
        ]);
    }

    /**
     * @group Motoboy
     * @group CreateMotoboy
    */
    public function testCreateMotoboy()
    {
        $response = $this->json('POST', "restaurant/".$this->restaurant->id."/motoboy", [
            'name' => 'Bob esponja'
        ]);
        $response->seeStatusCode(201);

        $motoboy = json_decode($response->response->getContent());
        $this->assertEquals($motoboy->name, 'Bob esponja');
    }

    /**
     * @group Motoboy
    */
    public function testGetMotoboy()
    {
        $response = $this->json('GET', "restaurant/".$this->restaurant->id."/motoboy");
        $response->seeStatusCode(200);

        $motoboy = json_decode($response->response->getContent());
        $this->assertEquals($motoboy[0]->name, $this->motoboy->name);
    }

     /**
     * @group Motoboy
    */
    public function testUpdateMotoboy()
    {
        $response = $this->json('PUT', "restaurant/".$this->restaurant->id."/motoboy/".$this->motoboy->id, [
            'name' => 'Bob esponja e Lula Molusco'
        ]);
        
        $response->seeStatusCode(200);

        $updatedMotoboy = \Model\Motoboy::find($this->motoboy->id);
        $this->assertEquals($updatedMotoboy->name, 'Bob esponja e Lula Molusco');
    }

    /**
     * @group Motoboy
    */
    public function testDeleteMotoboy()
    {
        $response = $this->json('DELETE', "restaurant/".$this->restaurant->id."/motoboy/".$this->motoboy->id);

        $response->seeStatusCode(200);
        $updatedOrder = \Model\Order::find($this->order->id);

        $this->assertEquals($updatedOrder->motoboy_id, null);
        $this->missingFromDatabase('motoboy', ['id' => $this->motoboy->id]);

    }
}