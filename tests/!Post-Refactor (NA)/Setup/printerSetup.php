<?php
namespace TestSetup;

class Printer
{
    public $printerSettings;
    public $restaurantID;

    public function __construct($restaurantID)
    {
        $this->restaurantID = $restaurantID;

        $this->printerSettings = factory(\Model\PrinterSettings::class)->create([
            'restaurant_id' => $restaurantID
        ]);
    }

    public function createPrinter($printer)
    {
        $printer['printer_settings_id'] = $this->printerSettings->id;
        $printer['restaurant_id'] = $this->restaurantID;

        return factory(\Model\Printers::class)->create($printer);
    }
}