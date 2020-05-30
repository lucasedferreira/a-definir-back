<?php
namespace TestSetup;

class Size
{
    public static function createFlavorCategoryAndFlavor($restaurantID)
    {
        $flavorCategorySetup = new FlavorCategory($restaurantID, true);
        return [
            "flavor" => $flavorCategorySetup->flavor,
            "flavorCategory" => $flavorCategorySetup->flavorCategory
        ];
    }

    public static function createCrustCategory($restaurantID)
    {
        $crustCategorySetup = new CrustCategory($restaurantID);
        return $crustCategorySetup->crustCategory;
    }

    public static function createDoughCategory($restaurantID)
    {
        $doughCategorySetup = new DoughCategory($restaurantID);
        return $doughCategorySetup->doughCategory;
    }

    public static function createAllRequisitesForSize($restaurantID)
    {
        $requisites = [];
        $requisites['crustCategory'] = self::createCrustCategory($restaurantID);
        $requisites['doughCategory'] = self::createDoughCategory($restaurantID);
        $requisites = array_merge(self::createFlavorCategoryAndFlavor($restaurantID), $requisites);
        return $requisites;
    }

    public static function createRandomDummySize($restaurantID, $size = [], $relationships = [], $create_fractions = false)
    {
        $size = array_merge([
            'name' => 'Grande',
            'max_flavors' => 4,
            'assortment' => 0,
            'restaurant_id' => $restaurantID
        ], $size);

        $sizeModel = factory(\Model\Size::class)->create($size);

        if(key_exists('crustCategory', $relationships)){
            $sizeModel->crustCategory()->associate($relationships['crustCategory'])->save();
        }

        if(key_exists('doughCategory', $relationships)){
            $sizeModel->doughCategory()->associate($relationships['doughCategory'])->save();
        }

        if(key_exists('flavorCategory', $relationships)){
            $sizeModel->flavorCategories()->attach([$relationships['flavorCategory']->id]);

            if($create_fractions){
                $sizeAndFlavorCatAssoc = \Model\SizeFlavorCategoryAssoc::where('size_id', $sizeModel->id)->first();
                foreach([0, 0, 0] as $index => $fractionPrice) {
                    $sizeAndFlavorCatAssoc->fraction()->create(
                        factory(\Model\Fraction::class)->make([
                            'pizza_part' => ++$index,
                            'price' => $fractionPrice,
                            'restaurant_id' => $restaurantID
                        ])->toArray()
                    );
                }
            }
        }

        return $sizeModel;
    }
}