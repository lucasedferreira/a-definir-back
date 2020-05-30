<?php
namespace TestSetup;

class Fraction
{
    public function __construct($sizeID, $flavorID)
    {
        $this->sizeFlavorAssoc = \Model\SizeFlavorCategoryAssoc::where('size_id', $sizeID)
                                ->with('fraction')
                                ->first()->toArray();
        $this->fractions = $this->sizeFlavorAssoc['fraction'];

        $this->exception = factory(\Model\Exception::class)->create([
            'price' => 2,
            'pizza_flavor_id' => $flavorID,
            'pizza_fraction_id' => $this->fractions[0]['id']
        ]);
    }
}