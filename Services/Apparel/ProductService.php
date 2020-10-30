<?php

namespace App\Services\Apparel;

use App\Facades\Cache\AppCache;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Builder;
use App\Models\Apparel\{Attribute, Product};
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Repositories\Apparel\{AttributeRepository, ProductRepository};

class ProductService {
    /**
     * @var Product
     */
    private Product $product;
    /**
     * @var ProductRepository
     */
    private ProductRepository $productRepository;
    /**
     * @var AttributeRepository
     */
    private AttributeRepository $attributeRepository;
    /**
     * @var Attribute
     */
    private Attribute $attribute;

    /**
     * ProductService constructor.
     * @param Product $product
     * @param Attribute $attribute
     * @param ProductRepository $productRepository
     * @param AttributeRepository $attributeRepository
     */
    public function __construct(Product $product, Attribute $attribute, ProductRepository $productRepository, AttributeRepository $attributeRepository) {
        $this->product = $product;
        $this->attribute = $attribute;
        $this->productRepository = $productRepository;
        $this->attributeRepository = $attributeRepository;
    }

    /**
     * @param $objProducts
     * @param string $categoryUuid
     * @return Collection
     */
    public function getProductFilters($objProducts, string $categoryUuid): Collection {
        return $objProducts->load(["attributes" => function ($query) use ($categoryUuid) {
            $query->where("category_uuid", $categoryUuid);
        }])->pluck("attributes")->collapse()->unique("attribute_uuid")->sortBy(function ($product, $key) {
            if ($product["attribute_type"] == "size") {
                $arrSizes = ["XXS", "XSM", "SML", "MED", "LRG", "XLG", "2XL", "3XL", "4XL", "5XL", "OS"];
                $position = array_search($product["attribute_name"], $arrSizes);

                return $position === false ?
                    (intval($product["attribute_name"]) !== 0 ? count($arrSizes) + $product["attribute_name"] : $product["attribute_name"]) :
                    $position;
            }

            if ($product["attribute_type"] == "weight") {
                $arrWeight = ["Light", "Mid", "Heavy"];
                $position = array_search($product["attribute_name"], $arrWeight);
                return $position === false ? $product["attribute_name"] : $position;
            }

            return $product["attribute_name"];
        })->values()->groupBy("attribute_type");
    }

    public function getProductsForBootstrap() {
        return ($this->productRepository->getProductsForBootstrap());
    }

    /**
     * @param string $productUuid
     *
     * @param string|null $color
     * @return Builder|\Illuminate\Database\Eloquent\Model
     */
    public function getProduct(string $productUuid, ?string $color = null) {
        return ($this->productRepository->getProduct($productUuid, $color));
    }

    public function getProducts(string $categoryUuid, array $filters, array $arrSortSettings) {
        AppCache::setClassOptions(self::class, "getProducts");

        if (empty($filters)) {
            $objProductsBuilder = $this->product->whereHas("productStyle")
                                                ->whereHas("attributes", function ($query) use ($categoryUuid) {
                                                    $query->where("merch_apparel_attributes.category_uuid", $categoryUuid);
                                                });
        } else {
            $objProductsBuilder = $this->product->newQuery();

            if (isset($filters["style"])) {
                $objProductsBuilder = $this->addFilterLayer($objProductsBuilder, $categoryUuid, $filters["style"], "style");
            }

            if (isset($filters["weight"])) {
                $objProductsBuilder = $this->addFilterLayer($objProductsBuilder, $categoryUuid, $filters["weight"], "weight");
            }

            if (isset($filters["color"])) {
                $objProductsBuilder = $this->addFilterLayer($objProductsBuilder, $categoryUuid, $filters["color"], "color");
            }

            if (isset($filters["size"])) {
                $objProductsBuilder = $this->addFilterLayer($objProductsBuilder, $categoryUuid, $filters["size"], "size");
            }
        }

        if (isset($this->product->sortFields[$arrSortSettings["field"]]) &&
            array_search($arrSortSettings["direction"], ["asc", "desc"]) !== false) {
            $arrSortSettings["field"] = $this->product->sortFields[$arrSortSettings["field"]];

            if ($arrSortSettings["field"] === "product_price") {
                $objProductsBuilder->leftJoin("merch_apparel_products_prices", function ($join) {
                    $join->on("merch_apparel_products.product_id", "merch_apparel_products_prices.product_id")
                         ->where("merch_apparel_products_prices.range_min", 1)
                         ->where("merch_apparel_products_prices.range_max", 3);
                })->orderBy("merch_apparel_products_prices.product_price", $arrSortSettings["direction"]);
            } else {
                $objProductsBuilder = $objProductsBuilder->orderBy($arrSortSettings["field"], $arrSortSettings["direction"]);
            }
        }

        $objProductsBuilder->with(["productSizes:size_name,row_id,row_uuid,product_id",
            "productStyle" => function (HasMany $builder) use ($filters) {
                $builder = $builder->select([
                    "row_id", "row_uuid", "product_id", "product_uuid", "attribute_uuid", "color_name", "color_hash",
                ]);

                if (isset($filters["color"])) {
                    $filterArr = explode("\\", $filters["color"]);
                    $placeholders = implode(', ', array_fill(0, count($filterArr), '?'));

                    $builder = $builder->orderByRaw("FIELD(merch_apparel_products_colors.attribute_uuid, {$placeholders}) DESC", $filterArr);
                }

                return $builder;
            },
            "productStyle.images:merch_apparel_files.file_id,merch_apparel_files.file_uuid,file_name,merch_apparel_products_files.file_type",
            "productStyle.thumbnails:merch_apparel_files.file_id,merch_apparel_files.file_uuid,file_name,merch_apparel_products_files.file_type",
            "price",
        ])->select("merch_apparel_products.product_id", "merch_apparel_products.product_uuid", "product_name", "product_description");

        AppCache::setQueryString($objProductsBuilder->toSql());

        $objProducts = $objProductsBuilder->get();

        $objProducts = $objProducts->transform(function ($value) {
            $value->product_style = $value->productStyle->transform(function ($style) {
                $style->thumbnail = $style->thumbnails->first()->full_url;
                $style->image = $style->images->first()->full_url;

                return $style->makeHidden(["thumbnails", "images", "attribute_uuid", "product_uuid"]);
            });

            $value->product_sizes = $value->productSizes->transform(function ($size) {
                return $size->size_name;
            });

            $value->price->makeHidden(["row_uuid", "product_uuid", "range_min", "range_max", "stamp_created", "stamp_updated",
                "stamp_created_by", "stamp_updated_by"]);

            return $value->makeHidden("attributes");
        });

        return [false, $objProducts];
    }

    private function addFilterLayer(Builder $builder, string $strCategoryUuid, string $strFiler, string $strFields): Builder {
        return $builder->whereHas("attributes", function ($query) use ($strCategoryUuid, $strFiler, $strFields) {
            $filterArr = explode("\\", $strFiler);

            $query->where("merch_apparel_attributes.category_uuid", $strCategoryUuid)
                ->whereIn("merch_apparel_attributes.attribute_uuid", $filterArr)
                ->where("merch_apparel_attributes.attribute_type", $strFields);
        });
    }

    /**
     * @param int $perPage
     * @param string|null $strCategoryUUID
     * @param array $arrSortSettings
     * @return mixed
     */
    public function getAllProducts(int $perPage, array $arrSortSettings, string $strCategoryUUID = null) {
        $objProducts = $this->productRepository->getAllProducts($perPage, $arrSortSettings, $strCategoryUUID);

        return ($objProducts);
    }

    /**
     * @param $arrRequestData
     * @return mixed
     * @throws \Exception
     */
    public function createProductPrice($arrRequestData) {
        $objProduct = $this->productRepository->getProductByUUID($arrRequestData["product_uuid"]);

        if (is_null($objProduct)) {
            abort(404, "Product not found.");
        }

        $objProductPrice = $this->productRepository->createProductPrice($objProduct, $arrRequestData);

        return ($objProductPrice);
    }

    /**
     * @param string $productUUID
     * @param string $productPriceUUID
     * @return mixed
     */
    public function deleteProductPrice(string $productUUID, string $productPriceUUID) {
        $objProduct = $this->productRepository->getProductByUUID($productUUID);

        if (is_null($objProduct)) {
            abort(404, "Product not found.");
        }

        $boolResult = $this->productRepository->deleteProductPrice($objProduct, $productPriceUUID);

        return ($boolResult);
    }

    /**
     * @param array $requestData
     * @param string $productUUID
     * @param string $productPriceUUID
     * @return mixed
     */
    public function editProductPrice(array $requestData, string $productUUID, string $productPriceUUID) {
        $objProduct = $this->productRepository->getProductByUUID($productUUID);

        if (is_null($objProduct)) {
            abort(404, "Product not found.");
        }

        $boolResult = $this->productRepository->updateProductPrice($objProduct, $requestData, $productPriceUUID);

        return ($boolResult);
    }
}
