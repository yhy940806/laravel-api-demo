<?php

namespace App\Services\Apparel;

use App\Repositories\Apparel\CategoryRepository;

class CategoryService {
    /**
     * @var CategoryRepository
     */
    private CategoryRepository $categoryRepository;

    /**
     * @param CategoryRepository $categoryRepository
     *
     * @return void
     */
    public function __construct(CategoryRepository $categoryRepository) {
        $this->categoryRepository = $categoryRepository;
    }

    /**
     * @param mixed $id
     * @param bool $bnFailure
     *
     * @return \App\Models\Apparel\Category
     */
    public function find($id, bool $bnFailure = true) {
        return ($this->categoryRepository->find($id, $bnFailure));
    }
}
