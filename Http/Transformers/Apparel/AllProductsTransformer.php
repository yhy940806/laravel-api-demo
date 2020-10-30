<?php

namespace App\Http\Transformers\Apparel;

use App\Traits\StampCache;
use App\Models\Apparel\Product;
use League\Fractal\TransformerAbstract;

class AllProductsTransformer extends TransformerAbstract {

    use StampCache;

    public function transform(Product $objProduct)
    {
        $response = [
            "product_uuid"             => $objProduct->product_uuid,
            "product_name"             => $objProduct->product_name,
            "product_description"      => $objProduct->product_description,
            "product_meta_keywords"    => $objProduct->product_meta_keywords,
            "product_meta_description" => $objProduct->product_meta_description,
            "product_min_price"        => $objProduct->prices->min("product_price"),
            "product_max_price"        => $objProduct->prices->max("product_price"),
            "product_weight"           => $objProduct->product_weight,
            "thumbnail_url"            => $objProduct->currentStyle->thumbnails->pluck("full_url")
        ];

        return(array_merge($response, $this->stamp($objProduct)));
    }
}
