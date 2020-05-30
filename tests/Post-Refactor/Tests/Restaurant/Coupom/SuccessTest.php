<?php

use Laravel\Lumen\Testing\DatabaseTransactions;
use Laravel\Lumen\Testing\WithoutMiddleware;

use TestSetup\Coupom as CoupomSetup;
use TestSetup\Restaurant as RestaurantSetup;

class CoupomSuccess extends TestCase
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

        $this->setup = new CoupomSetup($this->restaurant->id);
        $this->coupom = $this->setup->coupom;
    }

    /**
     * @group Coupom
     * @group CreateCupom
    */
    public function testCreateCoupom()
    {
        $response = $this->json('POST', "restaurant/".$this->restaurant->id."/coupom", [
            'code' => 'melhorDiaDeTodos',
            'type' => 'percentDiscount',
            'fixDiscount' => '99.99',
        ]);
        $response->seeStatusCode(201);
        $coupom = json_decode($response->response->getContent());
        $this->assertEquals($coupom->code, $this->coupom->code);
    }

    /**
     * @group Coupom
     * @group GetCupom 
    */
    public function testGetCoupom()
    {
        $response = $this->json('GET', "restaurant/".$this->restaurant->id."/coupom");
        $response->seeStatusCode(200);

        $coupom = json_decode($response->response->getContent());
        $this->assertEquals($coupom[0]->code, $this->coupom->code);
    }

    /**
     * @group Coupom
     * @group DeleteCupom     
    */
    public function testDeleteCoupom()
    {
        $response = $this->json('DELETE', "restaurant/".$this->restaurant->id."/coupom/".$this->coupom->id);
        $response->seeStatusCode(200);
    }

}
