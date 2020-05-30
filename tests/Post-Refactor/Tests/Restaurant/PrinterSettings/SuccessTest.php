<?php

use Laravel\Lumen\Testing\DatabaseTransactions;
use Laravel\Lumen\Testing\WithoutMiddleware;

use TestSetup\Restaurant as RestaurantSetup;
use TestSetup\Printer as PrinterSetup;

class PrinterSettingsSuccess extends TestCase
{
    use WithoutMiddleware;
    use DatabaseTransactions;

    public $faker;
    public $restaurantID;

    public function setUp()
    {
        parent::setUp();

        $this->restaurantSetup = new RestaurantSetup();
        $this->restaurant = $this->restaurantSetup->restaurant;

        $this->printerSetup = new PrinterSetup($this->restaurant->id);
        $this->printerSettings = $this->printerSetup->printerSettings;
    }

    /**
     * @group Printer
    */
    public function testUpdatePrinterSettings()
    {
        $this->seeInDatabase('printer_settings', [
            'restaurant_id' => $this->restaurant->id,
            'use_qz' => 0
        ]);

        $response = $this->json('POST', "/restaurant/".$this->restaurant->id."/printer-settings", [
            'useQZ'               => true,
        ]);

        $response->seeStatusCode(200);

        $this->seeInDatabase('printer_settings', [
            'restaurant_id' => $this->restaurant->id,
            'use_qz' => 1
        ]);
    }
}