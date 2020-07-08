<?php
namespace Resources;

use Illuminate\Http\Resources\Json\JsonResource;
class ProductImageCollection extends JsonResource
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
            'url' => $this->url,
            'productID' => $this->product_id
        ];
    }
}