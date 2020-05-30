
<?php

use Laravel\Lumen\Testing\DatabaseTransactions;
use Laravel\Lumen\Testing\WithoutMiddleware;

use TestSetup\Restaurant as RestaurantSetup;
use TestSetup\FlavorCategory as FlavorCategorySetup;
use TestSetup\Size as SizeSetup;
use TestSetup\Fraction as FractionSetup;

class FlavorSuccess extends TestCase
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

        $this->flavorCategorySetup = new FlavorCategorySetup($this->restaurant->id, true);
        $this->flavorCategory = $this->flavorCategorySetup->flavorCategory;
        $this->flavor = $this->flavorCategorySetup->flavor;
    }

    /**
     * @group Menu
     * @group Pizza
     * @group Flavor
     * @group Create
    */
    public function testCreateFlavor()
    {
        $response = $this->json('PUT', "restaurant/".$this->restaurant->id."/pizza/flavor-category/".$this->flavorCategory->id."/flavor", [
            "name" => "4 Queijos",
            "description" => ""
        ]);
        $response->seeStatusCode(201);

        $lastFlavor = \Model\Flavor::orderBy('id', 'desc')->get()->first();

        $this->assertEquals($lastFlavor->name, "4 Queijos");
        $this->assertEquals($lastFlavor->description, "");
    }

    /**
     * @group Menu
     * @group Pizza
     * @group Flavor
     * @group Update
    */
    public function testUpdateFlavor()
    {
        $response = $this->json('POST', "restaurant/".$this->restaurant->id."/pizza/flavor-category/".$this->flavorCategory->id."/flavor/".$this->flavor->id, [
            "name" => "Calabresa Acebolada",
            "description" => "Igual a calabresa normal, soq com cebola"
        ]);
        $response->seeStatusCode(200);

        $updatedFlavor = \Model\Flavor::find($this->flavor->id);
        $this->assertEquals($updatedFlavor->name, "Calabresa Acebolada");
        $this->assertEquals($updatedFlavor->description, "Igual a calabresa normal, soq com cebola");
    }

    /**
     * @group Menu
     * @group Pizza
     * @group Flavor
     * @group Delete
    */
    public function testDeleteFlavor()
    {
        $size = SizeSetup::createRandomDummySize($this->restaurant->id, [], ['flavorCategory' => $this->flavorCategory], true);
        new FractionSetup($size->id, $this->flavor->id);

        $response = $this->json('DELETE', "restaurant/".$this->restaurant->id."/pizza/flavor-category/".$this->flavorCategory->id."/flavor/".$this->flavor->id);
        $response->seeStatusCode(200);

        $this->missingFromDatabase('pizza_flavor', ['name' => $this->flavor->name, 'deleted_at' => null]);
        // $this->missingFromDatabase('pizza_fraction_exception', ['pizza_flavor_id' => $this->flavor->id]);
    }
}