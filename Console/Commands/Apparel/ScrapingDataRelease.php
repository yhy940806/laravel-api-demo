<?php

namespace App\Console\Commands\Apparel;

use App\Helpers\Util;
use App\Models\Apparel\Attribute;
use App\Models\Apparel\Category;
use App\Models\Apparel\Product;
use App\Models\Apparel\File;
use App\Models\Apparel\Scraping\Category as ScrapingCategory;
use App\Models\Apparel\Scraping\Product as ScrapingProduct;
use App\Models\Apparel\Scraping\File as ScrapingFile;
use Illuminate\Console\Command;

class ScrapingDataRelease extends Command {
    const MIN_RANGE = 1;
    const MAX_RANGE = 999999;
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = "apparel:data:release";

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "Release Data From Scraping Tables";
    /**
     * @var Category
     */
    private Category $category;
    /**
     * @var Product
     */
    private Product $product;
    /**
     * @var File
     */
    private File $file;
    /**
     * @var ScrapingFile
     */
    private ScrapingFile $scrapingFile;

    /**
     * Create a new command instance.
     *
     * @param Category $category
     * @param Product $product
     * @param File $file
     * @param ScrapingFile $scrapingFile
     */
    public function __construct(Category $category, Product $product, File $file, ScrapingFile $scrapingFile) {
        parent::__construct();
        $this->category = $category;
        $this->product = $product;
        $this->file = $file;
        $this->scrapingFile = $scrapingFile;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     * @throws \Exception
     */
    public function handle() {
        $categories = ScrapingCategory::all();

        foreach ($categories as $category) {
            /** @var Category $frontCategory */
            $frontCategory = $this->category->updateOrCreate(["category_name" => $category->category_name], [
                "category_id"   => $category->category_id,
                "category_uuid" => $category->category_uuid,
                "category_name" => $category->category_name,
            ]);

            /** @var ScrapingProduct $scrapProduct */
            foreach ($category->products as $scrapProduct) {
                /** @var Product $objProductFront */
                $objProductFront = $this->product->updateOrCreate(["ascolour_id" => $scrapProduct->ascolour_id], [
                    "product_id"               => $scrapProduct->product_id,
                    "product_uuid"             => $scrapProduct->product_uuid,
                    "ascolour_id"              => $scrapProduct->ascolour_id,
                    "product_name"             => $scrapProduct->product_name,
                    "product_description"      => $scrapProduct->product_short_description,
                    "product_meta_keywords"    => $scrapProduct->product_meta_keywords,
                    "product_meta_description" => $scrapProduct->product_meta_description,
                ]);

                foreach ($scrapProduct->colors as $scrapColor) {
                    $colorGroup = $scrapColor->colorGroup;

                    if (isset($colorGroup)) {
                        /** @var Attribute $colourAttribute */
                        $colourAttribute = $frontCategory->attributes()->firstOrCreate([
                            "attribute_name" => $colorGroup->group_name,
                            "category_id"    => $frontCategory->category_id,
                        ], [
                            "attribute_uuid" => Util::uuid(),
                            "attribute_name" => $colorGroup->group_name,
                            "attribute_type" => "color",
                            "category_id"    => $frontCategory->category_id,
                            "category_uuid"  => $frontCategory->category_uuid,
                        ]);

                        $attributeProduct = $colourAttribute->products()->find($objProductFront->product_id);

                        if (is_null($attributeProduct)) {
                            $colourAttribute->products()->attach($objProductFront->product_id, [
                                "row_uuid"       => Util::uuid(),
                                "attribute_uuid" => $colourAttribute->attribute_uuid,
                                "product_uuid"   => $objProductFront->product_uuid,
                            ]);
                        }
                    }

                    $objScrapThumbnail = $this->scrapingFile->find($scrapColor->pivot->thumbnail_id);
                    $productColor = $objProductFront->productStyle()->firstOrCreate(["color_name" => $scrapColor->color_name], [
                        "row_uuid"       => Util::uuid(),
                        "product_uuid"   => $objProductFront->product_uuid,
                        "attribute_id"   => isset($colourAttribute) ? $colourAttribute->attribute_id : null,
                        "attribute_uuid"   => isset($colourAttribute) ? $colourAttribute->attribute_uuid : null,
                        "color_name"     => $scrapColor->color_name,
                        "color_hash"     => $scrapColor->color_hash,
                    ]);

                    if ($productColor->wasRecentlyCreated) {
                        $objThumbnailFile = $this->file->firstOrCreate(["file_uuid" => $objScrapThumbnail->file_uuid], [
                            "file_id"   => $objScrapThumbnail->file_id,
                            "file_uuid" => $objScrapThumbnail->file_uuid,
                            "file_name" => $objScrapThumbnail->file_name,
                            "file_type" => $objScrapThumbnail->file_type,
                        ]);

                        if ($objThumbnailFile)

                            $objProductFront->files()->attach($objThumbnailFile->file_id, [
                                "row_uuid"     => Util::uuid(),
                                "file_uuid"    => $objThumbnailFile->file_uuid,
                                "product_uuid" => $objProductFront->product_uuid,
                                "color_id"     => $productColor->row_id,
                                "color_uuid"   => $productColor->row_uuid,
                                "file_type"    => "thumbnail",
                            ]);
                    }

                    $objScrapingColorFiles = $scrapProduct->files()->wherePivot("color_id", $scrapColor->color_id)
                                                          ->get();

                    foreach ($objScrapingColorFiles as $objScrapingColorFile) {
                        /** @var File $objColorFile */
                        $objColorFile = $this->file->firstOrCreate(["file_uuid" => $objScrapingColorFile->file_uuid], [
                            "file_id"   => $objScrapingColorFile->file_id,
                            "file_uuid" => $objScrapingColorFile->file_uuid,
                            "file_name" => $objScrapingColorFile->file_name,
                            "file_type" => $objScrapingColorFile->file_type,
                        ]);

                        if ($objColorFile->wasRecentlyCreated) {
                            $objProductFront->files()->attach($objColorFile->file_id, [
                                "row_uuid"     => Util::uuid(),
                                "file_uuid"    => $objColorFile->file_uuid,
                                "product_uuid" => $objProductFront->product_uuid,
                                "color_id"     => $productColor->row_id,
                                "color_uuid"   => $productColor->row_uuid,
                                "file_type"    => $objScrapingColorFile->pivot->type,
                            ]);
                        }
                    }
                }

                foreach ($scrapProduct->sizes as $scrapProductSize) {
                    /** @var Attribute $sizeAttribute */
                    $sizeAttribute = $frontCategory->attributes()
                                                   ->firstOrCreate(["attribute_name" => $scrapProductSize->size_name], [
                                                       "attribute_uuid" => Util::uuid(),
                                                       "attribute_name" => $scrapProductSize->size_name,
                                                       "attribute_type" => "size",
                                                       "category_id"    => $frontCategory->category_id,
                                                       "category_uuid"  => $frontCategory->category_uuid,
                                                   ]);

                    $attributeProduct = $sizeAttribute->products()->find($objProductFront->product_id);

                    if (is_null($attributeProduct)) {
                        $sizeAttribute->products()->attach($objProductFront->product_id, [
                            "row_uuid"       => Util::uuid(),
                            "attribute_uuid" => $sizeAttribute->attribute_uuid,
                            "product_uuid"   => $objProductFront->product_uuid,
                        ]);
                    }

                    $objProductFront->productSizes()->firstOrCreate(["size_name" => $scrapProductSize->size_name], [
                        "row_uuid"     => Util::uuid(),
                        "product_uuid" => $objProductFront->product_uuid,
                        "size_name"    => $scrapProductSize->size_name,
                    ]);
                }

                foreach ($scrapProduct->style as $scrapProductStyle) {
                    /** @var Attribute $styleAttribute */
                    $styleAttribute = $frontCategory->attributes()
                                                    ->firstOrCreate(["attribute_name" => $scrapProductStyle->style_name], [
                                                        "attribute_uuid" => Util::uuid(),
                                                        "attribute_name" => $scrapProductStyle->style_name,
                                                        "attribute_type" => "style",
                                                        "category_id"    => $frontCategory->category_id,
                                                        "category_uuid"  => $frontCategory->category_uuid,
                                                    ]);

                    $attributeProduct = $styleAttribute->products()->find($objProductFront->product_id);

                    if (is_null($attributeProduct)) {
                        $styleAttribute->products()->attach($objProductFront->product_id, [
                            "row_uuid"       => Util::uuid(),
                            "attribute_uuid" => $styleAttribute->attribute_uuid,
                            "product_uuid"   => $objProductFront->product_uuid,
                        ]);
                    }
                }

                foreach ($scrapProduct->weight as $scrapProductWeight) {
                    /** @var Attribute $weightAttribute */
                    $weightAttribute = $frontCategory->attributes()
                                                     ->firstOrCreate(["attribute_name" => $scrapProductWeight->weight_name], [
                                                         "attribute_uuid" => Util::uuid(),
                                                         "attribute_name" => $scrapProductWeight->weight_name,
                                                         "attribute_type" => "weight",
                                                         "category_id"    => $frontCategory->category_id,
                                                         "category_uuid"  => $frontCategory->category_uuid,
                                                     ]);

                    $attributeProduct = $weightAttribute->products()->find($objProductFront->product_id);

                    if (is_null($attributeProduct)) {
                        $weightAttribute->products()->attach($objProductFront->product_id, [
                            "row_uuid"       => Util::uuid(),
                            "attribute_uuid" => $weightAttribute->attribute_uuid,
                            "product_uuid"   => $objProductFront->product_uuid,
                        ]);
                    }
                }

                foreach ($scrapProduct->prices as $scrapProductPrice) {
                    $arrPriceRange = explode("–", $scrapProductPrice->product_price_range);

                    $objProductFront->prices()->updateOrCreate([
                        "range_min" => intval($arrPriceRange[0] ?? self::MIN_RANGE),
                        "range_max" => intval($arrPriceRange[1] ?? self::MAX_RANGE),
                    ], [
                        "row_uuid"      => $scrapProductPrice->row_uuid,
                        "product_uuid"  => $objProductFront->product_uuid,
                        "product_price" => $scrapProductPrice->product_price,
                        "range_min"     => intval($arrPriceRange[0] ?? self::MIN_RANGE),
                        "range_max"     => intval($arrPriceRange[1] ?? self::MAX_RANGE),
                    ]);
                }

                foreach ($scrapProduct->generalFiles as $generalFile) {
                    $existingGeneralFile = $objProductFront->files()->wherePivot("file_type", $generalFile->pivot->type)
                                                           ->find($generalFile->file_id);

                    if (is_null($existingGeneralFile)) {
                        $objGeneralFile = $this->file->updateOrCreate(["file_uuid" => $generalFile->file_uuid], [
                            "file_id"   => $generalFile->file_id,
                            "file_uuid" => $generalFile->file_uuid,
                            "file_name" => $generalFile->file_name,
                            "file_type" => $generalFile->file_type,
                        ]);

                        $objProductFront->files()->attach($objGeneralFile->file_id, [
                            "row_uuid"     => Util::uuid(),
                            "file_uuid"    => $objGeneralFile->file_uuid,
                            "product_uuid" => $objProductFront->product_uuid,
                            "file_type"    => $generalFile->pivot->type,
                        ]);
                    }
                }
            }
        }

        /** @var ScrapingProduct $scrapProduct */
        foreach (ScrapingProduct::all() as $scrapProduct) {
            $scrapRelatedProducts = $scrapProduct->relatedProducts;
            /** @var Product $frontProduct */
            $frontProduct = $this->product->where("ascolour_id", $scrapProduct->ascolour_id)->first();

            if (is_null($frontProduct)) {
                continue;
            }

            foreach ($scrapRelatedProducts as $relatedProduct) {
                $frontRelated = $this->product->where("ascolour_id", $relatedProduct->ascolour_id)->first();
                $frontProduct->relatedProducts()->attach($frontRelated->product_id, [
                    "row_uuid" => Util::uuid(),
                    "product_uuid" => $frontProduct->product_uuid,
                    "related_uuid" => $frontRelated->product_uuid
                ]);
            }
        }
    }
}
