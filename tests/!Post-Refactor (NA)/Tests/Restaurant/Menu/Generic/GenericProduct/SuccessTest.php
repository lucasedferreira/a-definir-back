
<?php

use Laravel\Lumen\Testing\DatabaseTransactions;
use Laravel\Lumen\Testing\WithoutMiddleware;

use TestSetup\Restaurant as RestaurantSetup;
use TestSetup\GenericCategory as GenericCategorySetup;
use TestSetup\ExtraCategory as ExtraCategorySetup;
use TestSetup\Extra as ExtraSetup;
use TestSetup\GenericProduct as GenericProductSetup;

class GenericProductSuccess extends TestCase
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

        $this->genericCategorySetup = new GenericCategorySetup($this->restaurant->id);
        $this->genericCategory = $this->genericCategorySetup->genericCategory;

        $this->extraCategorySetup = new ExtraCategorySetup($this->restaurant->id);
        $this->extraCategory = $this->extraCategorySetup->extraCategory;

        $this->extra = ExtraSetup::createRandomDummyExtra($this->restaurant->id, $this->extraCategory->id);

        $this->genericProduct = GenericProductSetup::createRandomDummyProduct($this->restaurant->id, $this->genericCategory->id, 3);
    }

    /**
     * @group Menu
     * @group Generic
     * @group GenericProduct
     * @group Create
    */
    public function testCreateGenericProduct()
    {
        $response = $this->json('POST', "restaurant/".$this->restaurant->id."/generic-category/".$this->genericCategory->id."/product", [
            "name" => "Hamburguer de Siri",
            "price" => 4.99,
            "oldPrice" => 9.99,
            "tag" => "Promoção",
            "description" => "Um hamburguer normal, mas com uma fórmula secreta"
        ]);

        $response->seeStatusCode(200);

        $lastProduct = \Model\GenericProduct::orderBy('id', 'desc')->get()->first();

        $this->assertEquals($lastProduct->name, "Hamburguer de Siri");
        $this->assertEquals($lastProduct->available, 1);
        $this->assertEquals($lastProduct->price, 4.99);
        $this->assertEquals($lastProduct->old_price, 9.99);
        $this->assertEquals($lastProduct->tag, "Promoção");
        $this->assertEquals($lastProduct->description, "Um hamburguer normal, mas com uma fórmula secreta");
    }

    /**
     * @group Menu
     * @group Generic
     * @group GenericProduct
     * @group UpdateAssortment
    */
    public function testUpdateAssortmentGenericProduct()
    {
        $response = $this->json('POST', "restaurant/".$this->restaurant->id."/generic-category/product", [
            [
                "id" => $this->genericProduct[0]->id,
                "assortment" => 2,
                "category_id" => $this->genericCategory->id
            ],
            [
                "id" => $this->genericProduct[1]->id,
                "assortment" => 0,
                "category_id" => $this->genericCategory->id
            ],
            [
                "id" => $this->genericProduct[2]->id,
                "assortment" => 1,
                "category_id" => $this->genericCategory->id
            ]
        ]);

        $response->seeStatusCode(200);

        $products = \Model\GenericProduct::get();

        $this->assertEquals($products[0]->id, $this->genericProduct[0]->id);
        $this->assertEquals($products[0]->assortment, 2);

        $this->assertEquals($products[1]->id, $this->genericProduct[1]->id);
        $this->assertEquals($products[1]->assortment, 0);

        $this->assertEquals($products[2]->id, $this->genericProduct[2]->id);
        $this->assertEquals($products[2]->assortment, 1);
    }

    /**
     * @group Menu
     * @group Generic
     * @group GenericProduct
     * @group Update
    */
    public function testUpdateGenericProduct()
    {
        $response = $this->json('POST', "restaurant/".$this->restaurant->id."/generic-category/".$this->genericCategory->id."/product/".$this->genericProduct[0]->id, [
            "name" => "Hambúrguer de Siri",
            "price" => 9.99,
            "oldPrice" => 0,
            "tag" => "",
            "available" => false
        ]);

        $response->seeStatusCode(200);

        $lastProduct = \Model\GenericProduct::find($this->genericProduct[0]->id);

        $this->assertEquals($lastProduct->name, "Hambúrguer de Siri");
        $this->assertEquals($lastProduct->available, 0);
        $this->assertEquals($lastProduct->price, 9.99);
        $this->assertEquals($lastProduct->old_price, 0);
        $this->assertEquals($lastProduct->tag, "");
    }

    /**
     * @group Menu
     * @group Generic
     * @group GenericProduct
     * @group Delete
    */
    public function testDeleteGenericProduct()
    {
        $response = $this->json('DELETE', "restaurant/".$this->restaurant->id."/generic-category/".$this->genericCategory->id."/product/".$this->genericProduct[0]->id);

        $response->seeStatusCode(200);

        $this->missingFromDatabase('generic_menu_product', ['name' => $this->genericProduct[0]->name, 'deleted_at' => null]);
    }

    /**
     * @group Menu
     * @group Generic
     * @group GenericProduct
     * @group ExtraAssociation
    */
    public function testGetAssociationExtrasByProduct()
    {
        GenericProductSetup::associateProductWithExtraCategory($this->genericProduct[0], $this->extraCategory->id);

        $response = $this->json('GET', "restaurant/".$this->restaurant->id."/generic-category/".$this->genericCategory->id."/product/".$this->genericProduct[0]->id."/extra");
        $response->seeStatusCode(200);

        $extraCategory = json_decode($response->response->getContent());

        $this->assertEquals($extraCategory[0]->id, $this->extraCategory->id);
        $this->assertEquals($extraCategory[0]->name, $this->extraCategory->name);
        $this->assertEquals($extraCategory[0]->description, $this->extraCategory->description);
        $this->assertEquals($extraCategory[0]->type, $this->extraCategory->type);

        foreach ($extraCategory[0]->options as $key => $option) {
            $this->assertEquals($option->id, $this->extra->id);
            $this->assertEquals($option->name, $this->extra->name);
            $this->assertEquals($option->description, $this->extra->description);
            $this->assertEquals($option->price, $this->extra->price);
        }
    }


    /**
     * @group Menu
     * @group Generic
     * @group GenericProduct
     * @group Create
     * @group ExtraAssociation
    */
    public function testCreateAssociationExtraProduct()
    {
        $route = "restaurant/".$this->restaurant->id
                ."/generic-category/".$this->genericCategory->id
                ."/product/".$this->genericProduct[0]->id
                ."/extra/".$this->extraCategory->id;

        $response = $this->json('PUT', $route);
        $response->seeStatusCode(200);

        $productsFromExtra = \Model\ExtraCategory::find($this->extraCategory->id)->products()->get();
// dd($productsFromExtra, $this->genericProduct);
        foreach ($productsFromExtra as $key => $product) {
            $this->assertEquals($product->name, $this->genericProduct[$key]->name);
            $this->assertEquals($product->available, $this->genericProduct[$key]->available);
            $this->assertEquals($product->price, $this->genericProduct[$key]->price);
            $this->assertEquals($product->old_price, $this->genericProduct[$key]->old_price);
            $this->assertEquals($product->tag, $this->genericProduct[$key]->tag);
            $this->assertEquals($product->description, $this->genericProduct[$key]->description);
        }
    }

    /**
     * @group Menu
     * @group Generic
     * @group GenericProduct
     * @group ExtraAssociation
    */
    public function testGetAssociationProductsByExtra()
    {
        $route = "restaurant/".$this->restaurant->id
                ."/generic-category/".$this->genericCategory->id
                ."/product/".$this->genericProduct[1]->id
                ."/extra/[".$this->extraCategory->id."]";
        $response = $this->json('GET', $route);
        $response->seeStatusCode(200);

        $extraCategory = json_decode($response->response->getContent());

        $this->assertEquals($extraCategory[0]->id, $this->extraCategory->id);
        $this->assertEquals($extraCategory[0]->name, $this->extraCategory->name);
        $this->assertEquals($extraCategory[0]->description, $this->extraCategory->description);
        $this->assertEquals($extraCategory[0]->type, $this->extraCategory->type);

        foreach ($extraCategory[0]->options as $key => $option) {
            $this->assertEquals($option->id, $this->extra->id);
            $this->assertEquals($option->name, $this->extra->name);
            $this->assertEquals($option->description, $this->extra->description);
            $this->assertEquals($option->price, $this->extra->price);
        }
    }
}