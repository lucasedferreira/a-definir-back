<?php

use Laravel\Lumen\Testing\DatabaseTransactions;
use Laravel\Lumen\Testing\WithoutMiddleware;

use TestSetup\Restaurant as RestaurantSetup;
use TestSetup\ExtraCategory as ExtraCategorySetup;
use TestSetup\Extra as ExtraSetup;

class ExtraCategorySuccess extends TestCase
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

        $this->extraCategorySetup = new ExtraCategorySetup($this->restaurant->id);
        $this->extraCategory = $this->extraCategorySetup->extraCategory;
        
        $this->extra = [];
        $this->extra[] = ExtraSetup::createRandomDummyExtra($this->restaurant->id, $this->extraCategory->id);
        $this->extra[] = ExtraSetup::createRandomDummyExtra($this->restaurant->id, $this->extraCategory->id, [
            'name' => 'Camarão',
            'assortment' => ++$this->extra[0]->assortment,
            'price' => 1.99
        ]);
        $this->extra[] = ExtraSetup::createRandomDummyExtra($this->restaurant->id, $this->extraCategory->id, [
            'name' => 'Algas',
            'assortment' => ++$this->extra[1]->assortment
        ]);
    }

    /**
     * @group ExtraCategory
     * @group Extra
     * @group Get
    */
    public function testGetExtraCategoriesAndOptions()
    {
        $response = $this->json('GET', "restaurant/".$this->restaurant->id."/extra-category");
        $response->seeStatusCode(200);
        $extraCategories = json_decode($response->response->getContent());

        $this->assertEquals($extraCategories[0]->name, $this->extraCategory->name);
        $this->assertEquals($extraCategories[0]->options[0]->name, $this->extra[0]->name);
        $this->assertEquals($extraCategories[0]->options[0]->description, $this->extra[0]->description);
    }

    /**
     * @group ExtraCategory
     * @group Extra
     * @group Create
     * @group TypeUnique
    */
    public function testCreateExtraCategoryTypeUniqueAndOptions()
    {
        $response = $this->json('PUT', "restaurant/".$this->restaurant->id."/extra-category",[
            "name" => "Vegetais do Mar",
            "type" => 1,
            "required" => 0,
            "options" => [
                [
                    "name" => "Plankton",
                    "description" => "Pode tentar roubar a fórmula do seu hambúrger",
                ],
                [
                    "name" => "Alga",
                    "price" => 1.99
                ]
            ]
        ]);

        $response->seeStatusCode(200);
        $extraCategories = json_decode($response->response->getContent());

        $this->assertEquals($extraCategories->name, "Vegetais do Mar");
        $this->assertEquals($extraCategories->type, 1);
        $this->assertEquals($extraCategories->required, 0);

        $this->assertEquals($extraCategories->options[0]->name, "Plankton");
        $this->assertEquals($extraCategories->options[0]->description, "Pode tentar roubar a fórmula do seu hambúrger");
        $this->assertEquals($extraCategories->options[0]->assortment, 1);

        $this->assertEquals($extraCategories->options[1]->name, "Alga");
        $this->assertEquals($extraCategories->options[1]->assortment, 2);
        $this->assertEquals($extraCategories->options[1]->price, 1.99);
    }

    /**
     * @group ExtraCategory
     * @group Extra
     * @group Create
     * @group TypeQuantity
    */
    public function testCreateExtraCategoryTypeQuantityAndOptions()
    {
        $response = $this->json('PUT', "restaurant/".$this->restaurant->id."/extra-category",[
            "name" => "Deseja adicionar algum molho?",
            "type" => 2,
            "qty_min" => 0,
            "qty_max" => 10,
            "options" => [
                [
                    "name" => "Molho de Salmão",
                ],
                [
                    "name" => "Molho de Mostarda",
                    "description" => "Apenas um molho de mostarda, sem referência aqui",
                    "price" => 0.5
                ]
            ]
        ]);

        $response->seeStatusCode(200);
        $extraCategories = json_decode($response->response->getContent());

        $this->assertEquals($extraCategories->name, "Deseja adicionar algum molho?");
        $this->assertEquals($extraCategories->type, 2);
        $this->assertEquals($extraCategories->qty_min, 0);
        $this->assertEquals($extraCategories->qty_max, 10);

        $this->assertEquals($extraCategories->options[0]->name, "Molho de Salmão");
        $this->assertEquals($extraCategories->options[0]->assortment, 1);

        $this->assertEquals($extraCategories->options[1]->name, "Molho de Mostarda");
        $this->assertEquals($extraCategories->options[1]->description, "Apenas um molho de mostarda, sem referência aqui");
        $this->assertEquals($extraCategories->options[1]->assortment, 2);
        $this->assertEquals($extraCategories->options[1]->price, 0.5);
    }

    /**
     * @group ExtraCategory
     * @group Extra
     * @group Update
    */
    public function testUpdateExtraCategoriesAndOptions()
    {
        $response = $this->json('POST', "restaurant/".$this->restaurant->id."/extra-category/".$this->extraCategory->id,[
            "name" => "Frutos do Mar",
            "description" => "Extra updated"
        ]);

        $response->seeStatusCode(200);
        $extraCategories = \Model\ExtraCategory::find($this->extraCategory->id);

        $this->assertEquals($extraCategories->name, "Frutos do Mar");
        $this->assertEquals($extraCategories->description, "Extra updated");
    }

     /**
     * @group ExtraCategory
     * @group Extra
     * @group Delete
    */
    public function testDeleteExtraCategoriesAndOptions()
    {
        $response = $this->json('DELETE', "restaurant/".$this->restaurant->id."/extra-category/".$this->extraCategory->id);
        $response->seeStatusCode(200);

        $this->missingFromDatabase('extra', ['id' => $this->extraCategory->id, 'deleted_at' => null]);
        $this->missingFromDatabase('extra_option', ['id' => $this->extra[0]->id, 'deleted_at' => null]);
    }
}
