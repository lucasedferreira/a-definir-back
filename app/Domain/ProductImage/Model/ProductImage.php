<?php

namespace Model;

use Illuminate\Database\Eloquent\Model;
class ProductImage extends Model
{
    protected $table = 'product_images';

    protected $fillable = [
        'url',
        'product_id'
    ];

    public function product()
    {
        return $this->belongsTo('Model\Product', 'product_id');
    }
}