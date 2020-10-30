<?php

namespace App\Repositories\Apparel;

use App\Facades\Cache\AppCache;
use App\Repositories\BaseRepository;
use App\Models\Apparel\Attribute;

class AttributeRepository extends BaseRepository {

    protected \Illuminate\Database\Eloquent\Model $model;

    /**
     * @param Attribute $attribute
     * @return void
     */
    public function __construct(Attribute $attribute) {
        $this->model = $attribute;
    }

    /**
     * @param string $category
     *
     * @return array
     */
    public function findAllByCategory(string $category) {
        $objBuilder = $this->model->where("category_uuid", $category);
        AppCache::setClassOptions(self::class, "get");
        AppCache::setQueryString($objBuilder->toSql());

        if (AppCache::isCached()) {
            return [true, null];
        }

        return ([false, $objBuilder->get()->groupBy("attribute_type")]);
    }

    /**
     * @param string $categoryUuid
     * @param string $attributeType
     * @return mixed
     */
    public function getCategoryAttributeByType(string $categoryUuid, string $attributeType) {
        $objAttributes = $this->model->where("category_uuid", $categoryUuid)->where("attribute_type", $attributeType)
                                     ->get();

        return ($objAttributes);
    }

    /**
     * @param array $arrNewAttributeData
     * @return mixed
     */
    public function create($arrNewAttributeData) {
        $objAttribute = $this->model->create($arrNewAttributeData);

        return ($objAttribute);
    }

    /**
     * @param string $attributeUUID
     * @return mixed
     */
    public function findByUUID(string $attributeUUID) {
        $objAttribute = $this->model->where("attribute_uuid", $attributeUUID)->first();

        return ($objAttribute);
    }
}
