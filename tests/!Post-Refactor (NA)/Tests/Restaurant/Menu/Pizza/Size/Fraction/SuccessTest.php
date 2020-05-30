<?php
use Laravel\Lumen\Testing\DatabaseTransactions;
use Laravel\Lumen\Testing\WithoutMiddleware;

use TestSetup\Restaurant as RestaurantSetup;
use TestSetup\Size as SizeSetup;
use TestSetup\Fraction as FractionSetup;

class SizeFractionSuccess extends TestCase
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
        
        $flavorSetup = SizeSetup::createFlavorCategoryAndFlavor($this->restaurant->id);
        $this->flavorCategory = $flavorSetup['flavorCategory'];
        $this->flavor = $flavorSetup['flavor'];

        $this->size = SizeSetup::createRandomDummySize($this->restaurant->id, [], ['flavorCategory' => $this->flavorCategory], true);

        $this->fractionSetup = new FractionSetup($this->size->id, $this->flavor->id);
        $this->sizeFlavorAssoc = $this->fractionSetup->sizeFlavorAssoc;
        $this->fractions = $this->fractionSetup->fractions;
        $this->exception = $this->fractionSetup->exception;
    }

    /**
     * @group Menu
     * @group Pizza
     * @group Size
     * @group Create
     * @group Fraction
    */
    public function testCreateFraction()
    {
        $lastFraction = end($this->fractions);
        $lastFractionID = $lastFraction['id'];

        $response = $this->json('PUT', "restaurant/".$this->restaurant->id."/pizza/size/fractions", [
            "fractions" => [
                [
                    "pizzaPart" => 4,
                    "exception" => [],
                    "price" => 1
                ]
            ],
            "fractionID" => $lastFractionID
        ]);
        $response->seeStatusCode(200);

        $updatedSizeAndFlavorCatAssoc = \Model\SizeFlavorCategoryAssoc::where('size_id', $this->size->id)
                                ->with('fraction')
                                ->first()->toArray();

        $newFraction = end($updatedSizeAndFlavorCatAssoc['fraction']);
        $this->assertEquals($newFraction['price'], 1);
        $this->assertEquals($newFraction['pizza_part'], 4);
    }

    /**
     * @group Menu
     * @group Pizza
     * @group Size
     * @group Delete
     * @group Fraction
    */
    public function testDeleteFraction()
    {
        $lastFraction = end($this->fractions);
        $lastFractionID = $lastFraction['id'];

        $response = $this->json('POST', "restaurant/".$this->restaurant->id."/pizza/size/delete-fractions", [
            "fractionIDs" => [$lastFractionID]
        ]);
        $response->seeStatusCode(200);

        $this->missingFromDatabase('pizza_fraction', ['size_assoc_category_id' => $this->sizeFlavorAssoc['id'], 'pizza_part' => 3]);
    }

    /**
     * @group Menu
     * @group Pizza
     * @group Size
     * @group Update
     * @group Fraction
    */
    public function testUpdateFraction()
    {
        $response = $this->json('POST', "restaurant/".$this->restaurant->id."/pizza/size/".$this->restaurant->id."/fraction/".$this->fractions[0]['id'], [
            "price" => 2.5
        ]);
        $response->seeStatusCode(200);

        $this->seeInDatabase('pizza_fraction', ['id' => $this->fractions[0]['id'], 'price' => 2.5]);
    }

    /**
     * @group Menu
     * @group Pizza
     * @group Size
     * @group Fraction
     * @group Create
     * @group Exception
    */
    public function testCreateException()
    {
        $response = $this->json('PUT', "restaurant/".$this->restaurant->id."/pizza/size/".$this->restaurant->id."/fraction/".$this->fractions[0]['id']."/exception", [
            "flavorID" => $this->flavor->id,
            "price" => 9,
            "token" => "aquiehopatrick"
        ]);
        $response->seeStatusCode(200);

        $lastException = \Model\Exception::get()->last();
        $this->assertEquals($lastException->price, 9);
        $this->assertEquals($lastException->pizza_flavor_id, $this->flavor->id);
        $this->assertEquals($lastException->pizza_fraction_id, $this->fractions[0]['id']);
    }

    /**
     * @group Menu
     * @group Pizza
     * @group Size
     * @group Fraction
     * @group Update
     * @group Exception
    */
    public function testUpdateException()
    {
        $response = $this->json('POST', "restaurant/".$this->restaurant->id."/pizza/size/".$this->restaurant->id."/fraction/".$this->fractions[0]['id']."/exception/".$this->exception->id, [
            "price" => 9.99
        ]);
        $response->seeStatusCode(200);

        $exception = \Model\Exception::find($this->exception->id);
        $this->assertEquals($exception->price, 9.99);
    }

    /**
     * @group Menu
     * @group Pizza
     * @group Size
     * @group Fraction
     * @group Delete
     * @group Exception
    */
    public function testDeleteException()
    {
        $response = $this->json('DELETE', "restaurant/".$this->restaurant->id."/pizza/size/".$this->restaurant->id."/fraction/".$this->fractions[0]['id']."/exception/".$this->exception->id);
        $response->seeStatusCode(200);

        $this->missingFromDatabase('pizza_fraction_exception', ['id' => $this->exception->id]);
    }
}