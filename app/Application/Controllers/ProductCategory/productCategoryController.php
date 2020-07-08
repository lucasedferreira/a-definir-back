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

    public function create(Request $request)
    {
        $category = $request->all();
        return \ProductCategory\Repository::create($category);
    }

    public function delete($categoryID)
    {
        \ProductCategory\Repository::delete($categoryID);
    }
}