<?php
namespace Controllers\Product;

use Illuminate\Http\Request;

use Resources\ProductCollection;

use Laravel\Lumen\Routing\Controller as BaseController;
class MainController extends BaseController
{
    public function get()
    {
        $products = \Product\Repository::get();
        return ProductCollection::collection($products);
    }

    public function create(Request $request)
    {
        $product = $request->all();
        $productModel = \Product\Service::create($product);
        return new ProductCollection($productModel);
    }

    public function getByID($productID)
    {
        $product = \Product\Repository::getByID($productID);
        return new ProductCollection($product);
    }

    public function delete($productID)
    {
        \Product\Repository::delete($productID);
    }
}