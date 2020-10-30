<?php

namespace App\Services\Apparel;

use App\Helpers\Util;
use App\Repositories\Apparel\{AttributeRepository, CategoryRepository};

class AttributeService {
    /**
     * @var AttributeRepository
     */
    private AttributeRepository $attributeRepo;
    /**
     * @var CategoryRepository
     */
    private CategoryRepository $categoryRepo;
    /**
     * @var CategoryService
     */
    private CategoryService $categoryService;

    /**
     * @param AttributeRepository $attributeRepo
     * @param CategoryRepository $categoryRepo
     * @param CategoryService $categoryService
     */
    public function __construct(AttributeRepository $attributeRepo, CategoryRepository $categoryRepo, CategoryService $categoryService) {
        $this->attributeRepo = $attributeRepo;
        $this->categoryRepo = $categoryRepo;
        $this->categoryService = $categoryService;
    }

    /**
     * @param string $categoryUuid
     * @return array
     */
    public function findAllByCategory(string $categoryUuid) {
        return ($this->attributeRepo->findAllByCategory($categoryUuid));
    }

    /**
     * @param $requestData
     * @return mixed
     * @throws \Exception
     */
    public function createAttribute($requestData){
        $objCategory = $this->categoryService->find($requestData["category_uuid"]);
        $arrAttributeData = array_merge($requestData, ["category_id" => $objCategory->category_id, "attribute_uuid" => Util::uuid()]);
        $objAttribute = $this->attributeRepo->create($arrAttributeData);

        return ($objAttribute);
    }
}
