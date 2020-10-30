<?php

namespace App\Http\Transformers\Apparel;

use App\Traits\StampCache;
use App\Models\Apparel\Product;
use League\Fractal\TransformerAbstract;

class ProductTransformer extends TransformerAbstract {

    use StampCache;

    public function transform(Product $objProduct)
    {
        $response = [
            "product_id"               => $objProduct->product_id,
            "product_uuid"             => $objProduct->product_uuid,
            "ascolour_id"              => $objProduct->ascolour_id,
            "product_name"             => $objProduct->product_name,
            "product_description"      => $objProduct->product_description,
            "product_weight"           => $objProduct->product_weight,
            "product_meta_keywords"    => $objProduct->product_meta_keywords,
            "product_meta_description" => $objProduct->product_meta_description,
            "productSizes"             => $objProduct->productSizes,
            "productStyle"             => $objProduct->productStyle()->with("images")->get(),
            "prices"                   => $objProduct->prices,
            "colors"                   => $objProduct->productColors
        ];

        return(array_merge($response, $this->stamp($objProduct)));
    }
}
