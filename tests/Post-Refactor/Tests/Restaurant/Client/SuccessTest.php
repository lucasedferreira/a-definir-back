<?php

use Laravel\Lumen\Testing\DatabaseTransactions;
use Laravel\Lumen\Testing\WithoutMiddleware;

use TestSetup\Client as ClientSetup;
use TestSetup\Restaurant as RestaurantSetup;

class ClientSuccess extends TestCase
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

        $this->client = [];
        $this->client[] = ClientSetup::createRandomDummyClient($this->restaurant->id, 1);
        $this->client[] = ClientSetup::createRandomDummyClient($this->restaurant->id, 1, [            
            'name' => 'Sandy Bochechas',
            'phone' => '99999999',
            'created_at' => 2018-11-11
        ]);
    }

    /**
     * @group Client 
     * @group ClientByName
    */
    public function testClientByName()
    {
        $response = $this->json('POST', "restaurant/".$this->restaurant->id."/client/search", [
            'name' => 'SaNdY BoChEcHaS'
        ]);
        $response->seeStatusCode(200);
        $client = json_decode($response->response->getContent());
        $this->assertEquals($client[0]->name, $this->client[1]->name);
        $this->assertEquals($client[0]->phone, $this->client[1]->phone);
    }

    /**
     * @group Client
     * @group ClientByPhone
    */
    public function testClientByPhone()
    {
        $response = $this->json('PUT', "restaurant/".$this->restaurant->id."/client/search", [
            'phone' => '40028922'
        ]);
        $response->seeStatusCode(200);
        $client = json_decode($response->response->getContent());
        $this->assertEquals($client[0]->name, $this->client[0]->name);
        $this->assertEquals($client[0]->phone, $this->client[0]->phone);
        $this->assertEquals($client[0]->street, $this->client[0]->street);
    }

    /**
     * @group Client
     * @group GetClients
    */
    public function testGetClients()
    {
        $response = $this->json('GET', "restaurant/".$this->restaurant->id."/client",[
            'dateTo' => date("Y-m-d"),
            'dateFrom' => date("Y-m-d")
        ]);
        $response->seeStatusCode(200);
        $client = json_decode($response->response->getContent());
        $this->assertEquals($client->clients[0]->name, $this->client[0]->name);
        $this->assertEquals($client->clients[0]->phone, $this->client[0]->phone);
        $this->assertEquals($client->clients[0]->street, $this->client[0]->street);
        $this->assertEquals($client->count, 2);
        $this->assertEquals($client->newCount, 1);
    }
}
