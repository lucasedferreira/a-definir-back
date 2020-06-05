<?php

namespace Model;

use Illuminate\Database\Eloquent\Model;
class ProductCategory extends Model
{
    protected $table = 'product_categories';

    protected $fillable = [
        'name'
    ];

    public function products()
    {
        return $this->hasMany('Model\Product', 'category_id');
    }
}