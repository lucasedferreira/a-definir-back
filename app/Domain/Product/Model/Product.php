<?php

namespace Model;

use Illuminate\Database\Eloquent\Model;
class Product extends Model
{
    protected $table = 'products';

    protected $fillable = [
        'name',
        'price',
        'description',
        'available',
        'category_id'
    ];

    public function category()
    {
        return $this->belongsTo('Model\ProductCategory', 'category_id');
    }

    public function images()
    {
        return $this->hasMany('Model\ProductImage', 'product_id');
    }
}