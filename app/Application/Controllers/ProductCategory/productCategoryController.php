<?php
namespace Controllers\ProductCategory;

use Illuminate\Http\Request;

use Resources\ProductCategoryCollection;

use Laravel\Lumen\Routing\Controller as BaseController;
class MainController extends BaseController
{
    public function get()
    {
        $categories = \ProductCategory\Repository::get();
        return ProductCategoryCollection::collection($categories);
    }
}