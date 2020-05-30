<?php
use Laravel\Lumen\Testing\DatabaseTransactions;
use Laravel\Lumen\Testing\WithoutMiddleware;

use TestSetup\Restaurant as RestaurantSetup;
use TestSetup\Size as SizeSetup;

class SizeCrustSuccess extends TestCase
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
        
        $this->crustCategory = SizeSetup::createCrustCategory($this->restaurant->id);

        $this->size = SizeSetup::createRandomDummySize($this->restaurant->id, [], ['crustCategory' => $this->crustCategory]);
    }

    /**
     * @group Menu
     * @group Pizza
     * @group Size
     * @group Crust
     * @group Associate
    */
    public function testAssociateSizeWithCrust()
    {
        $response = $this->json('PUT', "restaurant/".$this->restaurant->id."/pizza/size/".$this->size->id."/crust-category/".$this->crustCategory->id);
        $response->seeStatusCode(200);

        $this->seeInDatabase('pizza_sizes', ['id' => $this->size->id, 'crust_category_id' => $this->crustCategory->id]);
    }

    /**
     * @group Menu
     * @group Pizza
     * @group Size
     * @group Crust
     * @group Disassociate
    */
    public function testDisassociateSizeCrust()
    {
        $response = $this->json('DELETE', "restaurant/".$this->restaurant->id."/pizza/size/".$this->size->id."/crust-category");
        $response->seeStatusCode(200);

        $this->seeInDatabase('pizza_sizes', ['id' => $this->size->id, 'crust_category_id' => null]);
    }
}