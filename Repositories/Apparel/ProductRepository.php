<?php

namespace App\Repositories\Apparel;

use App\Helpers\Util;
use App\Facades\Cache\AppCache;
use App\Repositories\BaseRepository;
use Illuminate\Database\Eloquent\Builder;
use App\Models\Apparel\{Category, Product, ProductStyle};
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ProductRepository extends BaseRepository {
    /**
     * @var AttributeRepository
     */
    private AttributeRepository $attributeRepository;

    /**
     * @param Product $product
     *
     * @param AttributeRepository $attributeRepository
     */
    public function __construct(Product $product, AttributeRepository $attributeRepository) {
        $this->model = $product;
        $this->attributeRepository = $attributeRepository;
    }

    /**
     * @param array $attributesProduct
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    private function getProductsBuilder($attributesProduct) {
        return $this->model->whereHas("productStyle")->whereHas("attributes", function (Builder $query) use ($attributesProduct) {
            $query->whereIn("merch_apparel_attributes.attribute_id", $attributesProduct);
        })->with([
            "productSizes:size_name,row_id,row_uuid,product_id",
            "productStyle:color_name,row_id,row_uuid,product_id,color_hash",
            "productStyle.images:merch_apparel_files.file_id,merch_apparel_files.file_uuid,file_name,merch_apparel_products_files.file_type",
            "productStyle.thumbnails:merch_apparel_files.file_id,merch_apparel_files.file_uuid,file_name,merch_apparel_products_files.file_type",
            "price",
        ])->select("product_id", "product_uuid", "product_name", "product_description");
    }

    /**
     * @return array
     */
    public function getProductsForBootstrap() {
        $categories = Category::all();
        $bootstrapData = [];

        foreach ($categories as $category) {
            $attributesProduct = $category->attributes()->pluck("attribute_id");
            $objProducts = $this->getProductsBuilder($attributesProduct)->inRandomOrder()->limit(3)->get();

            $bootstrapData[strtolower($category->category_name)] = $objProducts->transform(function ($value) {
                $value->product_style = $value->productStyle->transform(function($style) {
                    $style->thumbnail = $style->thumbnails->first()->full_url;
                    $style->image = $style->images->first()->full_url;

                    return $style->makeHidden(["thumbnails", "images"]);
                });

                $value->product_sizes = $value->productSizes->transform(function ($size) {
                    return $size["size_name"];
                });

                $value->price->makeHidden(["row_uuid", "product_uuid", "range_min", "range_max", "stamp_created",
                    "stamp_created_by", "stamp_updated", "stamp_updated_by"]);

                return $value;
            });
        }

        return ($bootstrapData);
    }

    /**
     * @param string $strProductUuid
     * @param string|null $color
     * @return array
     */
    public function getProduct(string $strProductUuid, ?string $color = null) {
        AppCache::setClassOptions(self::class, "getProduct");

        $product = $this->model->whereHas("productStyle")->with(["productSizes:size_name,row_id,product_id",
            "productStyle:color_name,row_id,row_uuid,product_id,color_hash",
            "prices:row_id,product_id,product_price,range_min,range_max",
            "productStyle.originalImages:merch_apparel_files.file_id,merch_apparel_files.file_uuid,file_name,merch_apparel_products_files.file_type",
            "productStyle.smallImages:merch_apparel_files.file_id,merch_apparel_files.file_uuid,file_name,merch_apparel_products_files.file_type",
            "productStyle.images:merch_apparel_files.file_id,file_name,merch_apparel_products_files.file_type",
            "productStyle.thumbnails:merch_apparel_files.file_id,file_name,merch_apparel_products_files.file_type",
            "relatedProducts:merch_apparel_products.product_id,merch_apparel_products.product_uuid,product_name,product_description,product_meta_description,product_meta_keywords",
            "relatedProducts.price:row_id,product_id,product_price,range_min,range_max",
            "relatedProducts.productSizes:size_name,row_id,row_uuid,product_id",
            "relatedProducts.productStyle:color_name,row_id,row_uuid,product_id,color_hash",
            "relatedProducts.productStyle.images:merch_apparel_files.file_id,merch_apparel_files.file_uuid,file_name,merch_apparel_products_files.file_type",
            "relatedProducts.productStyle.thumbnails:merch_apparel_files.file_id,merch_apparel_files.file_uuid,file_name,merch_apparel_products_files.file_type",
        ])->select("merch_apparel_products.product_id", "merch_apparel_products.product_uuid", "product_name",
            "product_description", "product_meta_description", "product_meta_keywords")
          ->where("merch_apparel_products." . Product::UUID, $strProductUuid);

        AppCache::setQueryString($product->toSql());

        if (AppCache::isCached()) {
            return ([true, null]);
        }

        $product = $product->first();

        if (is_null($product)) {
            throw new NotFoundHttpException("Product Not Found.");
        }

        $product->productStyle->each(function ($style) {
            $style->flag_has_back = $style->originalImages->contains(function ($value) {
                return $value->pivot->file_type === "original_image_back";
            });

            $style->image = $style->images->first()->full_url;
            $style->thumbnail = $style->thumbnails->first()->full_url;

            $style->makeHidden(["images", "thumbnails"]);
        });

        $product->product_sizes = $product->productSizes->transform(function($size) {
            return $size->size_name;
        });


        $product->related_products = $product->relatedProducts->transform(function ($value) {
            $value->product_style = $value->productStyle->transform(function($style) {
                $style->thumbnail = $style->thumbnails->first()->full_url;
                $style->image = $style->images->first()->full_url;

                return $style->makeHidden(["thumbnails", "images"]);
            });

            $value->product_sizes = $value->productSizes->transform(function($size) {
                return $size->size_name;
            });

            return $value->makeHidden("attributes");
        });

        if (is_null($color)) {
            $style = $product->productStyle->first();
            $color = isset($style) ? $style->row_uuid : "";
        }

        $product = $product->load(["currentStyle" => function ($query) use ($color) {
                $query->where("merch_apparel_products_colors." . ProductStyle::UUID, $color)
                    ->select("color_name","row_id","row_uuid","product_id");
            },
            "currentStyle.originalImages:merch_apparel_files.file_id,merch_apparel_files.file_uuid,file_name,merch_apparel_products_files.file_type",
            "currentStyle.smallImages:merch_apparel_files.file_id,merch_apparel_files.file_uuid,file_name,merch_apparel_products_files.file_type",
            "currentStyle.images:merch_apparel_files.file_id,merch_apparel_files.file_uuid,file_name,merch_apparel_products_files.file_type",
            "currentStyle.thumbnails:merch_apparel_files.file_id,merch_apparel_files.file_uuid,file_name,merch_apparel_products_files.file_type"
        ]);

        $product->currentStyle->flag_has_back = $product->currentStyle->originalImages->contains(function ($value) {
            return $value->pivot->file_type === "original_image_back";
        });

        $image = $product->currentStyle->images->first();
        $thumbnail = $product->currentStyle->images->first();

        $product->currentStyle->image = isset($image) ? $image->full_url : null;
        $product->currentStyle->thumbnail = isset($thumbnail) ? $thumbnail->full_url : null;

        $product->currentStyle->makeHidden(["images", "thumbnails"]);

        return ([false, $product]);
    }

    /**
     * @param int $perPage
     * @param array $arrSortSettings
     * @param string|null $strCategoryUUID
     * @return mixed
     */
    public function getAllProducts(int $perPage, array $arrSortSettings, string $strCategoryUUID = null){
        $query = $this->model;

        if($strCategoryUUID){
            $query = $query->whereHas("attributes", function ($query) use ($strCategoryUUID) {
                $query->where("merch_apparel_attributes.category_uuid", $strCategoryUUID);
            });
        }

        if (isset($this->model->sortFields[$arrSortSettings["field"]]) &&
            array_search($arrSortSettings["direction"], ["asc", "desc"]) !== false) {
            if ($arrSortSettings["field"] == "price"){
                $query = $query->leftJoin("merch_apparel_products_prices", function ($join) {
                    $join->on("merch_apparel_products.product_id", "merch_apparel_products_prices.product_id")
                        ->where("merch_apparel_products_prices.range_min", 1)
                        ->where("merch_apparel_products_prices.range_max", 3);
                })->orderBy("merch_apparel_products_prices.product_price", $arrSortSettings["direction"]);
            } else {
                $query = $query->orderBy($this->model->sortFields[$arrSortSettings["field"]], $arrSortSettings["direction"]);
            }
        }

        $objProducts = $query->paginate($perPage);

        return ($objProducts);
    }

    /**
     * @param $strProductUUID
     * @return mixed
     */
    public function getProductByUUID($strProductUUID){
        $objProduct = $this->model->where("product_uuid", $strProductUUID)
            ->with(["productSizes", "productStyle", "currentStyle", "prices"])
            ->first();

        return ($objProduct);
    }

    /**
     * @param $strProductUUID
     * @param $arrUpdateData
     * @return mixed
     */
    public function updateProduct($strProductUUID, $arrUpdateData){
        $boolResult = $this->model->where("product_uuid", $strProductUUID)->update($arrUpdateData);

        return ($boolResult);
    }

    /**
     * @param $strProductUUID
     * @return mixed
     */
    public function deleteProduct($strProductUUID){
        $boolRes = $this->model->where("product_uuid", $strProductUUID)->delete();

        return ($boolRes);
    }

    /**
     * @param $requestData
     * @return mixed
     * @throws \Exception
     */
    public function createProduct($requestData){
        $arrProductData      = $requestData["product"];
        $arrProductPriceData = $requestData["price"];

        /* Create New Product */
        $arrProductData["product_uuid"] = Util::uuid();
        $objProduct = $this->model->create($arrProductData);

        /* Create Product Price */
        $arrProductPriceData["product_id"]   = $objProduct->product_id;
        $arrProductPriceData["product_uuid"] = $objProduct->product_uuid;
        $arrProductPriceData["row_uuid"]     = Util::uuid();
        $objProduct->prices()->create($arrProductPriceData);

        /* Link Product Attribute */
        $objAttribute = $this->attributeRepository->findByUUID($requestData["attribute"]["attribute_uuid"]);
        $objAttribute->products()->attach(
            $objProduct->product_id,
            [
                "row_uuid"       => Util::uuid(),
                "product_uuid"   => $objProduct->product_uuid,
                "attribute_uuid" => $objAttribute->attribute_uuid
            ]
        );

        /* Create Product Color */
        $objProduct->productColors()->create([
            "row_uuid"       => Util::uuid(),
            "product_uuid"   => $objProduct->product_uuid,
            "attribute_id"   => $objAttribute->attribute_id,
            "attribute_uuid" => $objAttribute->attribute_uuid,
            "color_name"     => $requestData["color"]["color_name"],
            "color_hash"     => $requestData["color"]["color_hash"],
        ]);

        /* Create Product Sizes */
        foreach ($requestData["sizes"] as $strSize){
            $objProduct->productSizes()->create([
                "row_uuid"     => Util::uuid(),
                "product_id"   => $objProduct->product_id,
                "product_uuid" => $objProduct->product_uuid,
                "size_name"    => $strSize
            ]);
        }

        return ($objProduct);
    }

    /**
     * @param $objProduct
     * @param $arrProductPriceData
     * @return mixed
     * @throws \Exception
     */
    public function createProductPrice($objProduct, $arrProductPriceData){
        $arrProductPriceData["row_uuid"]   = Util::uuid();
        $arrProductPriceData["product_id"] = $objProduct->product_id;
        $objProductPrice = $objProduct->prices()->create($arrProductPriceData);

        return ($objProductPrice);
    }

    /**
     * @param $objProduct
     * @param string $productPriceUUID
     * @return mixed
     */
    public function deleteProductPrice($objProduct, string $productPriceUUID){
        $boolResult = $objProduct->prices()->where("row_uuid", $productPriceUUID)->delete();

        return ($boolResult);
    }

    /**
     * @param $objProduct
     * @param array $requestData
     * @param string $productPriceUUID
     * @return mixed
     */
    public function updateProductPrice($objProduct, array $requestData, string $productPriceUUID){
        $boolResult = $objProduct->prices()->where("row_uuid", $productPriceUUID)->update($requestData);

        return ($boolResult);
    }
}
