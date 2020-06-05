<?php
namespace Resources;

use \Resources\ProductCollection;

use Illuminate\Http\Resources\Json\JsonResource;
class ProductCategoryCollection extends JsonResource
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        return [
            'id' => $this->id,
            'name' => $this->name,

            'products' => ProductCollection::collection($this->whenLoaded('products'))
        ];
    }
}