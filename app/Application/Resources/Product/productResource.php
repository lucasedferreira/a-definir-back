<?php
namespace Resources;

use \Resources\ProductImageCollection;

use Illuminate\Http\Resources\Json\JsonResource;
class ProductCollection extends JsonResource
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
            'price' => $this->price,
            'description' => $this->description,
            'available' => $this->available,
            'categoryID' => $this->category_id,
            'images' => ProductImageCollection::collection($this->images)
        ];
    }
}