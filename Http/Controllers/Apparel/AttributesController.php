<?php

namespace App\Http\Controllers\Apparel;

use App\Http\Controllers\Controller;
use App\Services\Apparel\AttributeService;
use App\Http\Resources\Common\BaseCollection;
use App\Repositories\Apparel\AttributeRepository;
use App\Http\Requests\Apparel\CreateAttributeRequest;
use App\Http\Transformers\Apparel\AttributeTransformer;

class AttributesController extends Controller
{
    /**
     * @var AttributeService
     */
    private AttributeService $attributeService;
    /**
     * @var AttributeRepository
     */
    private AttributeRepository $attributeRepository;

    /**
     * AttributesController constructor.
     * @param AttributeRepository $attributeRepository
     * @param AttributeService $attributeService
     */
    public function __construct(AttributeRepository $attributeRepository, AttributeService $attributeService) {
        $this->attributeRepository = $attributeRepository;
        $this->attributeService = $attributeService;
    }

    /**
     * @group Office
     * @urlParam category_uuid required Category UUID
     * @urlParam attribute_type required Attribute Type
     * @param $strCategoryUUID
     * @param $strAttributeType
     * @return BaseCollection
     */
    public function getCategoryAttributesByType($strCategoryUUID, $strAttributeType){
        $objAttributes = $this->attributeRepository->getCategoryAttributeByType($strCategoryUUID, $strAttributeType);

        return (new BaseCollection($objAttributes));
    }

    /**
     * @group Office
     * @bodyParam category_uuid string required Category UUID
     * @bodyParam attribute_name string required Attribute Name
     * @bodyParam attribute_type string required Attribute Type
     * @param CreateAttributeRequest $request
     * @return \Dingo\Api\Http\Response
     * @throws \Exception
     */
    public function createAttribute(CreateAttributeRequest $request){
        $objAttribute = $this->attributeService->createAttribute($request->all());

        return ($this->response->item($objAttribute, new AttributeTransformer));
    }
}
