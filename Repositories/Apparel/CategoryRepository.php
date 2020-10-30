<?php

namespace App\Repositories\Apparel;

use App\Models\Apparel\Category;
use App\Repositories\BaseRepository;

class CategoryRepository extends BaseRepository {

    protected \Illuminate\Database\Eloquent\Model $model;

    /**
     * @param Category $category
     * @return void
     */
    public function __construct(Category $category) {
        $this->model = $category;
    }

    /**
     * @param array $requestData
     * @return bool
     */
    public function updateCategories(array $requestData) {
        foreach ($requestData as $category) {
            $boolResult = $this->updateCategory(
                $category["category_uuid"],
                [
                    "category_meta_keywords"    => $category["category_meta_keywords"],
                    "category_meta_description" => $category["category_meta_description"],
                ]
            );

            if (!$boolResult) {
                return (false);
            }
        }

        return (true);
    }

    /**
     * @param string $categoryUUID
     * @param array $newData
     * @return mixed
     */
    public function updateCategory(string $categoryUUID, array $newData) {
        $boolResult = $this->model->where("category_uuid", $categoryUUID)->update($newData);

        return ($boolResult);
    }
}
