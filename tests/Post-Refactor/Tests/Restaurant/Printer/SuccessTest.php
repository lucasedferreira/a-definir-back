<?php

use Laravel\Lumen\Testing\DatabaseTransactions;
use Laravel\Lumen\Testing\WithoutMiddleware;

use TestSetup\Restaurant as RestaurantSetup;
use TestSetup\Printer as PrinterSetup;

class PrinterSuccess extends TestCase
{
    use WithoutMiddleware;
    use DatabaseTransactions;

    public $faker;
    public $restaurantID;

    public function setUp()
    {
        parent::setUp();

        $this->faker = Faker\Factory::create();

        $this->restaurantSetup = new RestaurantSetup();
        $this->restaurant = $this->restaurantSetup->restaurant;

        $this->printerSetup = new PrinterSetup($this->restaurant->id);
        $this->printerSettings = $this->printerSetup->printerSettings;
    }

    /**
     * @group Printer
    */
    public function testCreatePrinter()
    {
        $printer = $this->fillPrinterData([
            'name' => 'Cozinha'
        ]);

        $response = $this->json('PUT', "/restaurant/".$this->restaurant->id."/printer", $printer);

        $response->seeStatusCode(201);
        
        $printer = $response->response->getContent();
        $printer = json_decode($printer, true);

        $this->assertEquals($printer['name'], 'Cozinha');

        $this->seeInDatabase('printers', ['name' => 'Cozinha']);
    }

    /**
     * @group Printer
    */
    public function testGetPrinter()
    {
        $this->printerSetup->createPrinter([
            'name' => 'Cozinha'
        ]);

        $response = $this->json('GET', "/restaurant/".$this->restaurant->id."/printer");
        $response->seeStatusCode(200);

        $printer = $response->response->getContent();
        $printer = json_decode($printer, true);
        $printer = collect($printer);
        
        $this->assertEquals(true, $printer->contains('name', 'Cozinha'));
    }

    /**
     * @group Printer
    */
    public function testUpdatePrinter()
    {
        $printer = $this->printerSetup->createPrinter([
            'name' => 'Cozinha'
        ]);

        $route = "/restaurant/".$this->restaurant->id."/printer/".$printer->id;

        $printer = $this->fillPrinterData([
            'id'        => $printer->id,
            'name'      => 'Produção'
        ]);

        $response = $this->json('POST', $route, $printer);

        $response->seeStatusCode(200);
        
        $this->seeInDatabase('printers', ['name' => 'Produção']);
    }

    /**
     * @group Printer
    */
    public function testDeletePrinter()
    {
        $printer = $this->printerSetup->createPrinter([
            'name' => 'Cozinha'
        ]);

        $route = "/restaurant/".$this->restaurant->id."/printer/".$printer->id;

        $response = $this->json('DELETE', $route);

        $response->seeStatusCode(200);
        
        $this->missingFromDatabase('printers', ['name' => 'Cozinha']);
    }

    public function fillPrinterData($printer)
    {
        $_printer = [
            'assortment'=> 0,
            'kitchen'   => 0,
            'motoboy'   => 0,
            'cutType'   => 'full',
            'lineSize'  => 41,
            'twoCopies' => 0,
            'paperWidth'    => 41,
            'specialChar'   => 0,
            'restaurantID'  => $this->restaurant->id,
            'genericDriver' => 0,
            'printerSettingsID'     => $this->printerSettings->id,
            'motoboyWithProducts'   => 0
        ];

        return array_merge($printer, $_printer);
    }
}