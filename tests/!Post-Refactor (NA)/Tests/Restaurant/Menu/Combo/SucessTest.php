<?php

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Lumen\Testing\DatabaseTransactions;
use Laravel\Lumen\Testing\WithoutMiddleware;
use Resources\ComboCollection;

use TestSetup\Restaurant as RestaurantSetup;
use TestSetup\Combo as ComboSetup;
use TestSetup\ExtraCategory as ExtraCategorySetup;
use TestSetup\GenericCategory as GenericCategorySetup;
use TestSetup\GenericProduct as GenericProductSetup;
use TestSetup\Size as SizeSetup;

class ComboSuccess extends TestCase
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

        $this->comboSetup = new ComboSetup($this->restaurant->id);
        $this->combo = $this->comboSetup->combo;

        $this->extraCategorySetup = new ExtraCategorySetup($this->restaurant->id);
        $this->extraCategory = $this->extraCategorySetup->extraCategory;

        $this->genericCategorySetup = new GenericCategorySetup($this->restaurant->id);
        $this->genericCategory = $this->genericCategorySetup->genericCategory;

        $this->genericProduct = GenericProductSetup::createRandomDummyProduct($this->restaurant->id, $this->genericCategory->id, 1);

        $this->size = SizeSetup::createRandomDummySize($this->restaurant->id);

    }

    /**
     * @group Combo
     * @group Get
    */
    public function testGetCombos()
    {
        $response = $this->json('GET', "restaurant/".$this->restaurant->id."/combo");
        $response->seeStatusCode(200);
        $combos = json_decode($response->response->getContent());

        $this->assertEquals($combos[0]->name, "Pizza com hamburger de siri");
        $this->assertEquals($combos[0]->price, 45);
        $this->assertEquals($combos[0]->description, "O nome já explica bem");
    }

    /**
     * @group Combo
     * @group Create
    */
    public function testCreateCombo()
    {
        $response = $this->json('POST', "restaurant/".$this->restaurant->id."/combo",[
            "name" => "Vegetais do Mar",
            "price" => 45,
            "oldPrice" => 1.11,
            "extras"=> [
                "id" => $this->extraCategory->id
            ],
            "products"=> [
                "id" => $this->genericProduct->id
            ],
            "pizzaSizes"=>[
                "id" => $this->size->id
            ] 
        ]);

        $response->seeStatusCode(200);
        $comboResponse = json_decode($response->response->getContent());
        $combo = (\Combo\Repository::getByID($comboResponse->id))->toArray();

        $this->assertEquals($combo['name'], "Vegetais do Mar");
        $this->assertEquals($combo['assortment'], 1);
        $this->assertEquals($combo['price'], 45);
        $this->assertEquals($combo['extras'][0]['name'], 'Frutos do mar');
        $this->assertEquals($combo['products'][0]['name'], $this->genericProduct->name);
        $this->assertEquals($combo['pizza_sizes'][0]['name'], $this->size->name);
    }

    /**
     * @group Combo
     * @group Update
     * @group Assortment
    */
    public function testUpdateAssortment()
    {
        $response = $this->json('POST', "restaurant/".$this->restaurant->id."/combo/assortment",[
            [
                "id" => $this->combo[0]->id,
                "assortment" => 1
            ],
            [
                "id" => $this->combo[1]->id,
                "assortment" => 2
            ],
            [
                "id" => $this->combo[2]->id,
                "assortment" => 0
            ]
        ]);

        $response->seeStatusCode(200);

        $combos = \Model\Combo::get();

        $this->assertEquals($combos[0]->id, $this->combo[0]->id);
        $this->assertEquals($combos[0]->assortment, 1);

        $this->assertEquals($combos[1]->id, $this->combo[1]->id);
        $this->assertEquals($combos[1]->assortment, 2);

        $this->assertEquals($combos[2]->id, $this->combo[2]->id);
        $this->assertEquals($combos[2]->assortment, 0);
    }

    /**
     * @group Combo
     * @group Update
    */
    public function testUpdateCombo()
    {
        $response = $this->json('POST', "restaurant/".$this->restaurant->id."/combo/".$this->combo[0]->id,[
            "name" => "Pizza com hamburger de ciri",
            "price" => 50,
            "description" => "Compre uma pizza e ganhe hamburger de ciri"
        ]);

        $response->seeStatusCode(200);
        $combo = \Model\Combo::find($this->combo[0]->id);

        $this->assertEquals($combo->name, "Pizza com hamburger de ciri");
        $this->assertEquals($combo->price, 50);
        $this->assertEquals($combo->description, "Compre uma pizza e ganhe hamburger de ciri");
    }

    /**
     * @group Combo
     * @group Association
    */
    public function testAssociateComboWithObject()
    {
        $response = $this->json('PUT', "restaurant/".$this->restaurant->id."/combo/".$this->combo[0]->id."/extra",[
            "id" => $this->extraCategory->id
        ]);

        $response->seeStatusCode(200);
        
        $response = $this->json('GET', "restaurant/".$this->restaurant->id."/combo");
        $combos = json_decode($response->response->getContent());

        $combos = \Combo\Repository::getByID($this->combo[0]->id);
        $extras = ($combos->toArray())['extras'];
        $this->assertEquals($extras[0]['id'], $this->extraCategory->id);
        $this->assertEquals($extras[0]['name'], "Frutos do mar");
        $this->assertEquals($extras[0]['description'], "Extras de frutos do mar");
    }

    /**
     * @group Combo
     * @group Image
    */
    public function testSetImage()
    {
        $image = UploadedFile::fake()->image('image.png', 600, 600);
        $response = $this->call('POST', "restaurant/".$this->restaurant->id."/combo/".$this->combo[0]->id."/image",[
            "image" => $image
        ]);

        $updatedCombo = \Model\Combo::find($this->combo[0]->id);
        $imageName = str_replace('"', '', $response->getContent());

        $this->assertEquals($updatedCombo->image, $imageName);
        $this->assertFileExists('public/images/products/'.$imageName.'/lg_'.$imageName.'.jpg');
        $this->assertFileIsReadable('public/images/products/'.$imageName.'/lg_'.$imageName.'.jpg');
    }

    /**
     * @group Combo
     * @group Image
     * @group Delete
    */
    public function testDeleteComboImage()
    {
        $response = $this->json('DELETE', "restaurant/".$this->restaurant->id."/combo/".$this->combo[0]->id."/image");
        
        $response->seeStatusCode(200);
        
        $updatedCombo = \Model\Combo::find($this->combo[0]->id);
        $this->assertEquals($updatedCombo->image, '');
        $this->assertFileNotExists('public/images/products/'.$this->combo[0]->image.'/lg_'.$this->combo[0]->image.'.jpg');
    }

    /**
     * @group Combo
     * @group Delete
    */
    public function testDeleteCombo()
    {
        $response = $this->json('DELETE', "restaurant/".$this->restaurant->id."/combo/".$this->combo[0]->id);
        $response->seeStatusCode(200);

        $this->missingFromDatabase('combos', ['id' => $this->combo[0]->id]);
    }
}
