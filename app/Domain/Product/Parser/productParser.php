<?php
    namespace Product;

    use \BaseParser;
    class Parser extends BaseParser
    {
        public static function product($product)
        {
            return parent::parse([
                'categoryID' => 'category_id'
            ], $product);
        }
    }