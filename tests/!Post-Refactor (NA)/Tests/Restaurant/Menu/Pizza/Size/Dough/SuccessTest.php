<?php
use Laravel\Lumen\Testing\DatabaseTransactions;
use Laravel\Lumen\Testing\WithoutMiddleware;

use TestSetup\Restaurant as RestaurantSetup;
use TestSetup\Size as SizeSetup;

class SizeDoughSuccess extends TestCase
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
        
        $this->doughCategory = SizeSetup::createDoughCategory($this->restaurant->id);

        $this->size = SizeSetup::createRandomDummySize($this->restaurant->id, [], ['doughCategory' => $this->doughCategory]);
    }

    /**
     * @group Menu
     * @group Pizza
     * @group Size
     * @group Dough
     * @group Associate
    */
    public function testAssociateSizeWithDough()
    {
        $response = $this->json('PUT', "restaurant/".$this->restaurant->id."/pizza/size/".$this->size->id."/dough-category/".$this->doughCategory->id);
        $response->seeStatusCode(200);

        $this->seeInDatabase('pizza_sizes', ['id' => $this->size->id, 'dough_category_id' => $this->doughCategory->id]);
    }

    /**
     * @group Menu
     * @group Pizza
     * @group Size
     * @group Dough
     * @group Disassociate
    */
    public function testDisassociateSizeDough()
    {
        $response = $this->json('DELETE', "restaurant/".$this->restaurant->id."/pizza/size/".$this->size->id."/dough-category");
        $response->seeStatusCode(200);

        $this->seeInDatabase('pizza_sizes', ['id' => $this->size->id, 'dough_category_id' => null]);
    }
}