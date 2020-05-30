<?php

use Laravel\Lumen\Testing\DatabaseTransactions;
use Laravel\Lumen\Testing\WithoutMiddleware;

use TestSetup\Card as CardSetup;
use TestSetup\Restaurant as RestaurantSetup;

class CardSuccess extends TestCase
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
        
        CardSetup::createCardTypes();
        $this->card = [];
        $this->card[] = CardSetup::createRandomDummyCard($this->restaurant->id, [
            'name' => 'Sirigueijo Company'
        ]);
        $this->card[] = CardSetup::createRandomDummyCard($this->restaurant->id, [            
            'card_type_id' => 1
        ]);
    }


     /**
     * @group Card
     * @group Create
     * @group CreateCustom 
    */
    public function testCreateCustomCard()
    {
        $response = $this->json('POST', "restaurant/".$this->restaurant->id."/card/custom",
        [
            'name' => 'Plankton Company'
        ]);
        $response->seeStatusCode(200);

        $card = json_decode($response->response->getContent());
        $this->assertEquals($card->name, 'Plankton Company');
        $this->assertEquals($card->restaurant_id, $this->restaurant->id);
    }

    /**
     * @group Card
     * @group Create 
     * @group CreateDefault
    */
    public function testCreateDefaultCard()
    {
        $response = $this->json('POST', "restaurant/".$this->restaurant->id."/card/default",
        [
            'cardTypeID' => 1
        ]);
        $response->seeStatusCode(200);

        $card = json_decode($response->response->getContent());
        $this->assertEquals($card->card_type_id, 1);
        $this->assertEquals($card->restaurant_id, $this->restaurant->id);
    }

    /**
     * @group Card
     * @group Get    
     * @group GetCustom 
    */
    public function testGetCustomCard()
    {
        $response = $this->json('GET', "restaurant/".$this->restaurant->id."/card");
        $response->seeStatusCode(200);

        $card = json_decode($response->response->getContent());
        $this->assertEquals($card[0]->name, $this->card[0]->name);
        $this->assertEquals($card[1]->name, 'Mastercard');
    }

    /**
     * @group Card
     * @group Get     
     * @group GetDefault
    */
    public function testGetDefaultCard()
    {
        $response = $this->json('GET', "/card");
        $response->seeStatusCode(200);

        $card = json_decode($response->response->getContent());
        $this->assertEquals($card[0]->name, 'Mastercard');
        $this->assertEquals($card[1]->name, 'Visa');
        $this->assertEquals($card[2]->name, 'Hipercard');
        $this->assertEquals($card[3]->name, 'Sodexo');
        $this->assertEquals($card[4]->name, 'Elo');
    }

    /**
     * @group Card
     * @group Delete
    */
    public function testDeleteCard()
    {
        $response = $this->json('DELETE', "restaurant/".$this->restaurant->id."/card/".$this->card[0]->id);
        $response->seeStatusCode(200);
    }
}
