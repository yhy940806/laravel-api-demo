<?php

namespace App\Http\Controllers\Apparel;

use App\Traits\Cacheable;
use Illuminate\Http\Request;
use App\Facades\Cache\AppCache;
use App\Http\Controllers\Controller;
use App\Http\Resources\Common\BaseCollection;
use Illuminate\Pagination\LengthAwarePaginator;
use App\Models\{Apparel\Attribute, Apparel\Category};
use App\Services\Apparel\{ProductService, AttributeService};

class CategoriesController extends Controller {

    use Cacheable;

    /**
     * @var Category
     */
    private Category $category;
    /**
     * @var Attribute
     */
    private Attribute $attribute;

    /**
     * CategoriesController constructor.
     * @param Category $category
     * @param Attribute $attribute
     */
    public function __construct(Category $category, Attribute $attribute) {
        $this->category = $category;
        $this->attribute = $attribute;
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function get(Request $request) {
        AppCache::setRequestOptions($request->path(), []);
        AppCache::setClassOptions(self::class, "get");
        AppCache::setQueryString($this->category->toSql());

        if (AppCache::isCached()) {
            return response()->json(AppCache::getCache());
        }

        return ($this->sendCacheResponse($this->apiReply($this->category->all())));
    }

    /**
     * @param string $categoryUuid
     * @param Request $request
     * @param ProductService $productService
     * @return BaseCollection|\Dingo\Api\Http\Response|\Illuminate\Http\JsonResponse
     */
    public function getProducts(string $categoryUuid, Request $request, ProductService $productService) {
        $arrSort = [
            "field" => $request->input("sort_field", "title"),
            "direction" => $request->input("sort_dir", "asc")
        ];

        $intPerPage = $request->get("per_page", 20);
        $intPage = $request->get("page", 1);

        AppCache::setRequestOptions($request->path(), array_merge($request->all(), $arrSort,
            ["per_page" => $intPerPage, "page" => $intPage]));

        [$isCached, $objProducts] = $productService->getProducts($categoryUuid, $request->only(["color", "style", "weight", "fit"]), $arrSort);

        if ($isCached) {
            return response()->json(AppCache::getCache());
        }

        $productsPerPage = $objProducts->forPage($intPage, $intPerPage)->values();

        $objAvailableFilters = $productService->getProductFilters($objProducts, $categoryUuid);
        $objProducts = new LengthAwarePaginator($productsPerPage, $objProducts->count(), $intPerPage,
            null, ["path" => ""]);

        return $this->sendCacheResponse((new BaseCollection($objProducts))->additional([
            "filters" => $objAvailableFilters->toArray()
        ]));
    }

    public function getAttributes(string $categoryUuid, AttributeService $attributeService, Request $request) {
        try {
            AppCache::setRequestOptions($request->path(), []);

            [$isCached, $attributes] = $attributeService->findAllByCategory($categoryUuid);

            if ($isCached) {
                return response()->json(AppCache::getCache());
            }

            return ($this->sendCacheResponse($this->apiReply($attributes)));
        } catch (\Exception $e) {
            throw $e;
        }
    }

    /**
     * @param ProductService $productService
     *
     * @return \Dingo\Api\Http\Response
     */
    public function bootstrap(ProductService $productService) {
        $bootstrapData = $productService->getProductsForBootstrap();

        $sliderImages = [
            "slider/1.webp",
            "slider/2.webp"
        ];
        $bootstrapData["slider"] = $sliderImages;

        return ($this->apiReply($bootstrapData));
    }
}
