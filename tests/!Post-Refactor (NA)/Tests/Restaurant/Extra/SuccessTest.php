<?php

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Laravel\Lumen\Testing\WithoutMiddleware;
use Laravel\Lumen\Testing\DatabaseTransactions;

use TestSetup\Restaurant as RestaurantSetup;
use TestSetup\ExtraCategory as ExtraCategorySetup;
use TestSetup\Extra as ExtraSetup;

class ExtraSuccess extends TestCase
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
            'assortment' => ++$this->extra[0]->assortment
        ]);
        $this->extra[] = ExtraSetup::createRandomDummyExtra($this->restaurant->id, $this->extraCategory->id, [
            'assortment' => ++$this->extra[1]->assortment
        ]);

    }

    /**
     * @group Extra
     * @group Option
     * @group Create
     */
    public function testCreateExtraOption()
    {
        $image = UploadedFile::fake()->image('image.png', 600, 600);
        $response = $this->call('POST', "restaurant/".$this->restaurant->id."/extra-category/".$this->extraCategory->id."/extra",[
            "name" => "Salpé",
            "description" => "Pedassos de Salmão",
            "image" => $image

        ]);
        
        $lastExtra = \Model\Extra::all()->last();
        $imageName = str_replace('"', '', $lastExtra->image);
        $this->assertEquals($lastExtra->name, "Salpé");
        $this->assertEquals($lastExtra->assortment, 3);
        $this->assertEquals($lastExtra->description, "Pedassos de Salmão");
        $this->assertFileExists('public/images/products/'.$imageName.'/lg_'.$imageName.'.jpg');
        $this->assertFileIsReadable('public/images/products/'.$imageName.'/lg_'.$imageName.'.jpg');
    }

    /**
     * @group Extra
     * @group Option
     * @group Update
    */
    public function testUpdateExtraOption()
    {
        $response = $this->json('POST', "restaurant/".$this->restaurant->id."/extra-category/".$this->extraCategory->id."/extra/".$this->extra[0]->id,[
            "name" => "Salmão updated",
            "description" => "Updated"
        ]);
        $response->seeStatusCode(200);
        
        $updatedExtra = \Model\Extra::find($this->extra[0]->id);
        $this->assertEquals($updatedExtra->name, "Salmão updated");
        $this->assertEquals($updatedExtra->description, "Updated");
    }

    /**
     * @group Extra
     * @group Option
     * @group Create
     * @group Image
     */
    public function testUploadExtraImage()
    {
        $image = UploadedFile::fake()->image('image.png', 600, 600);
        $response = $this->call('POST', "restaurant/".$this->restaurant->id."/extra-category/".$this->extraCategory->id."/extra/".$this->extra[0]->id."/image",
        [
            "image" => $image
        ]);
        
        $updatedExtra = \Model\Extra::find($this->extra[0]->id);
        $imageName = str_replace('"', '', $response->getContent());
        $this->assertEquals($updatedExtra->image, $imageName);
        $this->assertFileExists('public/images/products/'.$imageName.'/lg_'.$imageName.'.jpg');
        $this->assertFileIsReadable('public/images/products/'.$imageName.'/lg_'.$imageName.'.jpg');
    }

    /**
     * @group Extra
     * @group Option
     * @group Delete
     * @group Image
    */
    public function testDeleteExtraImage()
    {
        $response = $this->json('DELETE', "restaurant/".$this->restaurant->id."/extra-category/".$this->extraCategory->id."/extra/".$this->extra[0]->id."/image");
        
        $response->seeStatusCode(200);
        
        $updatedExtra = \Model\Extra::find($this->extra[0]->id);
        $this->assertEquals($updatedExtra->image, '');
        $this->assertFileNotExists('public/images/products/'.$this->extra[0]->image.'/lg_'.$this->extra[0]->image.'.jpg');
    }

    /**
     * @group Extra
     * @group Option
     * @group UpdateAssortment
     */
    public function testUpdateAssortment()
    {
        $response = $this->json('PUT', "restaurant/".$this->restaurant->id."/extra-category/".$this->extraCategory->id."/extra",[
            [
                "id" => $this->extra[0]->id,
                "assortment" => 1
            ],
            [
                "id" => $this->extra[1]->id,
                "assortment" => 2
            ],
            [
                "id" => $this->extra[2]->id,
                "assortment" => 0
            ]
        ]);
        $response->seeStatusCode(200);
        
        $extras = \Model\Extra::get();

        $this->assertEquals($extras[0]->id, $this->extra[0]->id);
        $this->assertEquals($extras[0]->assortment, 1);

        $this->assertEquals($extras[1]->id, $this->extra[1]->id);
        $this->assertEquals($extras[1]->assortment, 2);

        $this->assertEquals($extras[2]->id, $this->extra[2]->id);
        $this->assertEquals($extras[2]->assortment, 0);
    }

    /**
     * @group Extra
     * @group Option
     * @group Delete
    */
    public function testDeleteExtraOption()
    {
        $response = $this->json('DELETE', "restaurant/".$this->restaurant->id."/extra-category/".$this->extraCategory->id."/extra/".$this->extra[0]->id);

        $response->seeStatusCode(200);

        $this->missingFromDatabase('extra_option', ['id' => $this->extra[0]->id, 'deleted_at' => null]);
    }
}
