
<?php

use Laravel\Lumen\Testing\DatabaseTransactions;
use Laravel\Lumen\Testing\WithoutMiddleware;

use TestSetup\Restaurant as RestaurantSetup;
use TestSetup\GenericCategory as GenericCategorySetup;

class GenericCategorySuccess extends TestCase
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

        $this->genericCategory = GenericCategorySetup::createRandomDummyGenericCategory($this->restaurant->id, 3);
    }

    /**
     * @group Menu
     * @group Generic
     * @group GenericCategory
     * @group Create
    */
    public function testCreateGenericCategory()
    {
        $response = $this->json('POST', "restaurant/".$this->restaurant->id."/generic-category", [
            "name" => "Tradicionais",
            "available" => true
        ]);

        $response->seeStatusCode(200);

        $lastCategory = \Model\GenericCategory::orderBy('id', 'desc')->get()->first();

        $this->assertEquals($lastCategory->name, "Tradicionais");
        $this->assertEquals($lastCategory->available, 1);
    }

    /**
     * @group Menu
     * @group Generic
     * @group GenericCategory
     * @group Update
    */
    public function testUpdateGenericCategory()
    {
        $response = $this->json('PUT', "restaurant/".$this->restaurant->id."/generic-category/".$this->genericCategory[0]->id, [
            "name" => "Frutos do Mar",
            "available" => false,
            "description" => "Salmão, bacalhau, lagosta, etc"
        ]);

        $response->seeStatusCode(200);

        $category = \Model\GenericCategory::find($this->genericCategory[0]->id);

        $this->assertEquals($category->name, "Frutos do Mar");
        $this->assertEquals($category->available, 0);
        $this->assertEquals($category->description, "Salmão, bacalhau, lagosta, etc");
    }

    /**
     * @group Menu
     * @group Generic
     * @group GenericCategory
     * @group UpdateAssortment
    */
    public function testUpdateGenericCategoryAssortment()
    {
        $response = $this->json('POST', "restaurant/".$this->restaurant->id."/generic-category/assortment", [
            [
                "id" => $this->genericCategory[0]->id,
                "assortment" => 2
            ],
            [
                "id" => $this->genericCategory[1]->id,
                "assortment" => 0
            ],
            [
                "id" => $this->genericCategory[2]->id,
                "assortment" => 1
            ]
        ]);

        $response->seeStatusCode(200);

        $categories = \Model\GenericCategory::get();

        $this->assertEquals($categories[0]->id, $this->genericCategory[0]->id);
        $this->assertEquals($categories[0]->assortment, 2);

        $this->assertEquals($categories[1]->id, $this->genericCategory[1]->id);
        $this->assertEquals($categories[1]->assortment, 0);

        $this->assertEquals($categories[2]->id, $this->genericCategory[2]->id);
        $this->assertEquals($categories[2]->assortment, 1);
    }

    /**
     * @group Menu
     * @group Generic
     * @group GenericCategory
     * @group Delete
    */
    public function testDeleteGenericCategory()
    {
        $response = $this->json('DELETE', "restaurant/".$this->restaurant->id."/generic-category/".$this->genericCategory[0]->id);

        $response->seeStatusCode(200);

        $this->missingFromDatabase('generic_menu_categories', ['id' => $this->genericCategory[0]->id, 'deleted_at' => null]);
    }

}