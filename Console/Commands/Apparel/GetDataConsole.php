<?php

namespace App\Console\Commands\Apparel;

use Exception;
use App\Helpers\Util;
use Faker\Provider\UserAgent;
use GuzzleHttp\Client;
use GuzzleHttp\Cookie\CookieJar;
use GuzzleHttp\Exception\ClientException;
use App\Models\Apparel\Scraping\File as FileModel;
use App\Models\Apparel\Scraping\{Color, ColorsGroup, Product, Category, Size, Style, Weight};
use Illuminate\{Console\Command, Support\Facades\File, Support\Str};
use KubAT\PhpSimple\HtmlDomParser;

class GetDataConsole extends Command {
    const ENDPOINTS = [
        'Men'         => '/men',
        'Woman'       => '/women.html',
        'Kids'        => '/kids.html',
        'Accessories' => '/accessories.html',
    ];
    const ORGANIC_ENDPOINT = "/organic-range.html";
    const CURRENCY = "$";
    const DOMAIN = "https://www.ascolour.com";
    const ASCOLOUR_API = "https://u43tbzpa7g.execute-api.ap-southeast-2.amazonaws.com/production/";
    const DEFAULT_API_STORE_HASH = "hsi95a83fz";

    const LOW_RES_SIZE = "200w";
    const HIGH_RES_SIZE = "2000w";
    const COLOR_IMAGE_PATTERNS = ["%s", "%s - BACK"];
    const COLOR_IMAGE_RES = ["small_image" => self::LOW_RES_SIZE, "original_image" => self::HIGH_RES_SIZE];
    /*
     * IMAGE TYPES
     * ASCOLOUR_TYPE => APPAREL_TYPE
    */
    const GENERAL_IMAGE_TYPES = [
        "main"  => "main_image",
        "front" => "front_image",
        "turn"  => "turn_image",
        "side"  => "side_image",
        "back"  => "back_image",
    ];
    /*
     * STYLES RENAME ASSOCIATIONS
     * OLD_NAME => NEW NAME
     * */
    const ASSOCIATIONS = [
        "T-Shirt - Crew"       => "Crew Neck T-Shirts",
        "T-Shirt - V-Neck"     => "V-Neck T-Shirts",
        "Singlet"              => "Singlets",
        "T-Shirt - L/Sleeve"   => "Long Sleeve T-Shirts",
        "Polo Shirt"           => "Polo Shirts",
        "Shirt"                => "Shirts",
        "Sweat - Zip Hood"     => "Zip Hoodies",
        "Sweat - Hood"         => "Sweatshirts",
        "Sweat - Crew"         => "Crew Neck Sweatshirts",
        "Jacket"               => "Jackets",
        "Pants"                => "Pants",
        "Shorts"               => "Shorts",
        "Dress"                => "Dresses",
        "Tshirt - Crew"        => "Crew Neck T-Shirts",
        "T-Shirt - Scoop"      => "Scoop Neck T-Shirts",
        "Sizes: 2 - 6"         => "Small Sizes",
        "Bag"                  => "Bags",
        "Cap"                  => "Baseball Caps",
        "Cap / Hat"            => "Hats",
        "Beanie"               => "Beanies",
        "T-Shirt - Tall"       => "Tall T-Shirts",
        "T-Shirt - Tank"       => "Tank Tops",
        "Track - Pants/Shorts" => "Track Pants/Shorts",
    ];

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = "apparel:data:get";

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = "Get data from Ascolour web-site.";

    /**
     * @var Client
     */
    private Client $http;
    /**
     * @var Category
     */
    private Category $category;
    /**
     * @var Product
     */
    private Product $product;
    /**
     * @var FileModel
     */
    private FileModel $file;
    /**
     * @var Color
     */
    private Color $color;
    /**
     * @var Size
     */
    private Size $size;
    /**
     * @var Style
     */
    private Style $style;
    /**
     * @var Weight
     */
    private Weight $weight;
    /**
     * @var ColorsGroup
     */
    private ColorsGroup $colorsGroup;

    /**
     * Create a new command instance.
     *
     * @param Category $category
     * @param Product $product
     * @param FileModel $file
     * @param Color $color
     * @param Size $size
     * @param Style $style
     * @param Weight $weight
     * @param ColorsGroup $colorsGroup
     */
    public function __construct(Category $category, Product $product, FileModel $file, Color $color, Size $size,
                                Style $style, Weight $weight, ColorsGroup $colorsGroup) {
        if (!defined("MAX_FILE_SIZE")) {
            define("MAX_FILE_SIZE", 1000000);
        }

        $this->http = new Client([
            "base_uri" => self::DOMAIN,
        ]);

        $this->colorsGroup = $colorsGroup;
        $this->category = $category;
        $this->product = $product;
        $this->weight = $weight;
        $this->style = $style;
        $this->color = $color;
        $this->file = $file;
        $this->size = $size;

        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     * @throws Exception
     */
    public function handle() {
        $groupModels = [];

        $colorsGroupsResponse = $this->http->get(self::ASCOLOUR_API . "frontend/configuration/colourgroup__CONFIGURATION", [
            "headers" => [
                "store-hash" => self::DEFAULT_API_STORE_HASH,
                "origin"     => "https://ascolour.com",
                "referer"    => "https://ascolour.com",
                "user-agent" => UserAgent::chrome(),
            ],
        ]);

        $colorsGroups = collect(json_decode($colorsGroupsResponse->getBody()->getContents(), true));

        foreach ($colorsGroups as $group) {
            $groupModels[$group["name"]] = $this->colorsGroup->firstOrCreate(["group_name" => $group["name"]], [
                "group_uuid" => Util::uuid(),
                "group_name" => $group["name"],
            ]);
        }

        foreach (self::ENDPOINTS as $categoryName => $endpoint) {
            /** @var Category $category */
            $category = $this->category->updateOrCreate(["category_name" => $categoryName], [
                "category_uuid" => Util::uuid(),
                "category_name" => $categoryName,
            ]);

            $page = 1;

            while (true) {
                try {
                    $productListingHtml = $this->http->get($endpoint . '?page=' . $page);
                    $content = $productListingHtml->getBody()->getContents();
                    $objProductList = HtmlDomParser::str_get_html($content);
                } catch (ClientException $exception) {
                    break;
                }

                $arrProducts = $objProductList->find(".productGrid .product");

                foreach ($arrProducts as $key => $objProduct) {
                    $shortDescription = null;
                    $savePdf = true;

                    $productLink = $objProduct->find("a", 0)->getAttribute("data-href");
                    $productResponse = $this->http->get($productLink);
                    $productHtml = $productResponse->getBody()->getContents();

                    $arrReplacement = ['~"recaptcha":{([^}{\r\n]*)},(?!\s)~', '~"summary":\"(.*?)\",(?!\s)~', '~"description":\"(.*?)\",(?!\s)~'];

                    preg_match('~window\.stencilBootstrap\("product", "(.*)"\)~', $productHtml, $arrProductMatches);

                    $validProductJson = preg_replace($arrReplacement, "", str_replace("\\", "", $arrProductMatches[1]));

                    try {
                        $arrPageData = json_decode($validProductJson, true);
                        $arrProductData = $arrPageData["product"];
                    } catch (\Exception $exception) {
                        file_put_contents("error.json", $validProductJson);
                        dd("error");
                    }

                    $objCustomFields = collect($arrProductData["custom_fields"]);
                    $arrImages = $arrProductData["images"];
                    $objImages = collect($arrImages);
                    $shortDescription = $objCustomFields->where("name", "=", "short_description")->first();

                    /** @var Product $objProductModel */
                    $objProductModel = $this->product->updateOrCreate(["ascolour_id" => $arrProductData["sku"]], [
                        "product_uuid"              => Util::uuid(),
                        "ascolour_ref"              => $arrProductData["id"],
                        "product_name"              => $arrProductData["title"],
                        "product_short_description" => $shortDescription["value"],
                        "product_meta_description"  => $arrProductData["meta_description"],
                        "product_meta_keywords"     => $arrProductData["meta_keywords"],
                        "ascolour_id"               => $arrProductData["sku"],
                        "product_url"               => $arrProductData["url"],
                        "product_price"             => $arrProductData["price"]["without_tax"]["value"],
                        "product_html"              => $productHtml,
                        "product_json"              => $validProductJson,
                    ]);

                    $catProduct = $category->products()->find($objProductModel->product_id);

                    if (is_null($catProduct)) {
                        $category->products()->attach($objProductModel->product_id, [
                            "row_uuid"      => Util::uuid(),
                            "product_uuid"  => $objProductModel->product_uuid,
                            "category_uuid" => $category->category_uuid,
                        ]);
                    }

                    foreach (self::GENERAL_IMAGE_TYPES as $ascolourType => $apparelType) {
                        $arrMainImage = collect($arrImages)->where("alt", strtoupper($ascolourType))->first();

                        if (!empty($arrMainImage)) {
                            $imageUrl = str_replace("{:size}", self::HIGH_RES_SIZE, $arrMainImage["data"]);

                            $objProductImage = $objProductModel->files()->wherePivot("type", $apparelType)->first();

                            if (isset($objProductImage)) {
                                if ($objProductImage->file_url === $imageUrl) {
                                    continue;
                                }

                                if ($objProductImage->file_url !== $imageUrl) {
                                    $objProductModel->files()->detach($objProductImage->file_id);
                                }
                            }

                            $objImage = $this->saveFile($imageUrl, "image");

                            if ($objImage) {
                                $objProductModel->files()->attach($objImage->file_id, [
                                    "row_uuid"     => Util::uuid(),
                                    "product_uuid" => $objProductModel->product_uuid,
                                    "file_uuid"    => $objImage->file_uuid,
                                    "type"         => $apparelType,
                                ]);
                            }
                        }
                    }

                    $arrPdfInfo = $objCustomFields->where("name", "product-info-sheet")->first();

                    if (isset($arrPdfInfo)) {
                        $objProductPdf = $objProductModel->files()->wherePivot("type", "pdf")->first();


                        if (isset($objProductPdf)) {
                            if ($objProductPdf->file_url === $arrPdfInfo["value"]) {
                                $savePdf = false;
                            }

                            if ($objProductPdf->file_url !== $arrPdfInfo["value"]) {
                                $objProductModel->files()->detach($objProductPdf->file_id);
                            }
                        }

                        if ($savePdf) {
                            /** @var FileModel $objPdf */
                            $objPdf = $this->saveFile($arrPdfInfo["value"], "pdf");

                            if ($objPdf) {
                                $objProductModel->pdf()->attach($objPdf->file_id, [
                                    "row_uuid"     => Util::uuid(),
                                    "product_uuid" => $objProductModel->product_uuid,
                                    "file_uuid"    => $objPdf->file_uuid,
                                    "type"         => "pdf",
                                ]);
                            }
                        }
                    }

                    $objOptions = collect($arrProductData["options"]);
                    $arrColors = $objOptions->where("display_name", "Colour")->first();

                    foreach ($arrColors["values"] as $value) {
                        $name = strtolower($value["label"]);
                        $arrGroup = $this->arrayWhereLike($colorsGroups->toArray(), "colors", $value["label"]);

                        if (empty($arrGroup)) {
                            $newGroupName = Str::title($name);

                            $colorsGroups = $colorsGroups->push([
                                "name"   => $newGroupName,
                                "colors" => [$value["label"]],
                            ]);

                            $colorGroupModel = $this->colorsGroup->firstOrCreate(["group_name" => $newGroupName], [
                                "group_uuid" => Util::uuid(),
                                "group_name" => $newGroupName,
                            ]);


                            $groupModels[Str::title($name)] = $colorGroupModel;
                        } else {
                            $colorGroupModel = $groupModels[$arrGroup[0]["name"]];
                        }

                        /** @var ColorsGroup $colorGroupModel */
                        $objColour = $colorGroupModel->colors()->updateOrCreate(["color_name" => $name], [
                            "color_uuid" => Util::uuid(),
                            "group_uuid" => $colorGroupModel->group_uuid,
                            "color_name" => $name,
                            "color_hash" => $value["data"],
                        ]);

                        $objProductColour = $objProductModel->colors()->find($objColour->color_id);

                        if (is_null($objProductColour)) {
                            $colorMainImage = $objImages->where("alt", strtoupper($value["label"]))->first();

                            if (is_null($colorMainImage)) {
                                continue;
                            }

                            $arrThumbnail = $objCustomFields->where("name", "cat_thumb_" . Str::title($value["label"]))
                                                            ->first();

                            if (isset($arrThumbnail)) {
                                $objThumbnail = $this->saveFile($arrThumbnail["value"], "image");
                            }


                            $objProductModel->colors()->attach($objColour->color_id, [
                                "row_uuid"       => Util::uuid(),
                                "product_uuid"   => $objProductModel->product_uuid,
                                "color_uuid"     => $objColour->color_uuid,
                                "thumbnail_id"   => isset($objThumbnail) ? $objThumbnail->file_id : null,
                                "thumbnail_uuid" => isset($objThumbnail) ? $objThumbnail->file_uuid : null,
                            ]);
                        }

                        foreach (self::COLOR_IMAGE_PATTERNS as $pattern) {
                            $colorImage = collect($arrImages)->where("alt", sprintf($pattern, strtoupper($name)))
                                                            ->first();

                            if (is_null($colorImage)) {
                                continue;
                            }

                            foreach (self::COLOR_IMAGE_RES as $imageType => $imgRes) {
                                $colourFileUrl = str_replace("{:size}", $imgRes, $colorImage["data"]);

                                if (strpos($colorImage["alt"], "BACK") !== false) {
                                    $imageType .= "_back";
                                }

                                $objProductColourImage = $objProductModel->files()->wherePivot("type", $imageType)
                                                                        ->wherePivot("color_id", $objColour->color_id)
                                                                        ->first();

                                if (isset($objProductColourImage)) {
                                    if ($objProductColourImage->file_url === $colourFileUrl) {
                                        continue;
                                    }

                                    if ($objProductColourImage->file_url !== $colourFileUrl) {
                                        $objProductModel->files()->detach($objProductColourImage->file_id);
                                    }
                                }

                                $objStyleFile = $this->saveFile($colourFileUrl, "image");

                                if (!$objStyleFile) {
                                    continue;
                                }

                                $objProductModel->files()->attach($objStyleFile->file_id, [
                                    "row_uuid"     => Util::uuid(),
                                    "product_uuid" => $objProductModel->product_uuid,
                                    "file_uuid"    => $objStyleFile->file_uuid,
                                    "color_id"     => $objColour->color_id,
                                    "color_uuid"   => $objColour->color_uuid,
                                    "type"         => $imageType,
                                ]);
                            }
                        }
                    }

                    $arrSize = $objOptions->where("display_name", "Size")->first();

                    foreach ($arrSize["values"] as $size) {
                        $objSize = $this->size->updateOrCreate(["size_name" => strtoupper($size["label"])], [
                            "size_uuid" => Util::uuid(),
                            "size_name" => strtoupper($size["label"]),
                        ]);

                        $objProductColour = $objProductModel->sizes()->find($objSize->size_id);

                        if (is_null($objProductColour)) {
                            $objProductModel->sizes()->attach($objSize->size_id, [
                                "row_uuid"     => Util::uuid(),
                                "product_uuid" => $objProductModel->product_uuid,
                                "size_uuid"    => $objSize->size_uuid,
                            ]);
                        }
                    }

                    $arrPrices = $this->arrayWhereLike($objCustomFields->toArray(), "name", "retail_price");

                    foreach ($arrPrices as $price) {
                        $arrPriceValues = explode("_", $price["value"]);
                        $priceRange = $arrPriceValues[1] ?? "1+";

                        $objProductModel->prices()->updateOrCreate(["product_price_range" => $priceRange], [
                            "row_uuid"            => Util::uuid(),
                            "product_uuid"        => $objProductModel->product_uuid,
                            "product_price"       => floatval(str_replace(self::CURRENCY, "", $arrPriceValues[0])) ?? 0.00,
                            "product_price_range" => $priceRange,
                        ]);
                    }

                    $arrStyle = $objCustomFields->where("name", "style")->first();

                    if (is_array($arrStyle)) {
                        $styleNames = explode("|", $arrStyle["value"]);

                        foreach ($styleNames as $styleName) {
                            $styleRenamedName = self::ASSOCIATIONS[trim($styleName)] ?? trim($styleName);
                            $objStyle = $this->style->updateOrCreate(["style_name" => $styleRenamedName], [
                                "style_uuid" => Util::uuid(),
                                "style_name" => $styleRenamedName,
                            ]);

                            $objProductColour = $objProductModel->style()->find($objStyle->style_id);

                            if (is_null($objProductColour)) {
                                $objProductModel->style()->attach($objStyle->style_id, [
                                    "row_uuid"     => Util::uuid(),
                                    "product_uuid" => $objProductModel->product_uuid,
                                    "style_uuid"   => $objStyle->style_uuid,
                                ]);
                            }
                        }
                    }

                    $arrWeight = $objCustomFields->where("name", "weight")->first();

                    if (is_array($arrWeight)) {
                        $weightNames = explode("|", $arrWeight["value"]);

                        foreach ($weightNames as $weightName) {
                            $objWeight = $this->weight->updateOrCreate(["weight_name" => trim($weightName)], [
                                "weight_uuid" => Util::uuid(),
                                "weight_name" => trim($weightName),
                            ]);

                            $objProductWeight = $objProductModel->weight()->find($objWeight->weight_id);

                            if (is_null($objProductWeight)) {
                                $objProductModel->weight()->attach($objWeight->weight_id, [
                                    "row_uuid"     => Util::uuid(),
                                    "product_uuid" => $objProductModel->product_uuid,
                                    "weight_uuid"  => $objWeight->weight_uuid,
                                ]);
                            }
                        }
                    }
                }

                $page++;
            }
        }

        try {
            $productListingHtml = $this->http->get(self::ORGANIC_ENDPOINT);
            $content = $productListingHtml->getBody()->getContents();
            $objOrganicList = HtmlDomParser::str_get_html($content);
        } catch (ClientException $exception) {
            return;
        }

        $arrOrganicProducts = $objOrganicList->find(".category-product-listing .product");

        $objOrganicStyle = $this->style->firstOrCreate(["style_name" => "Organic"], [
            "style_uuid" => Util::uuid(),
            "style_name" => "Organic",
        ]);

        foreach ($arrOrganicProducts as $objOrganicProduct) {
            $productRef = $objOrganicProduct->getAttribute("data-product-id");
            /** @var Product $organicProduct */
            $organicProduct = $this->product->where("ascolour_ref", $productRef)->first();

            if (is_null($organicProduct)) {
                continue;
            }

            $objProductColour = $organicProduct->style()->find($objOrganicStyle->style_id);

            if (is_null($objProductColour)) {
                $organicProduct->style()->attach($objOrganicStyle->style_id, [
                    "row_uuid"     => Util::uuid(),
                    "style_uuid"   => $objOrganicStyle->style_uuid,
                    "product_uuid" => $organicProduct->product_uuid,
                ]);
            }
        }

        $allProducts = $this->product->all();

        foreach ($allProducts as $product) {
            $objInfo = json_decode($product->product_json, true);
            $objProductJson = collect($objInfo["product"]);
            $objRelatedProducts = collect($objProductJson["similar_by_views"]);
            $ascolourIds = $objRelatedProducts->pluck("sku");

            foreach ($ascolourIds as $ascolourId) {
                $relatedProduct = $this->product->where("ascolour_id", $ascolourId)->first();

                if (is_null($relatedProduct)) {
                    continue;
                }

                $product->relatedProducts()->attach($relatedProduct, [
                    "product_ascolour_id" => $product->ascolour_id,
                    "product_uuid"        => $product->product_uuid,
                    "related_uuid"        => $relatedProduct->product_uuid,
                    "related_ascolour_id" => $ascolourId,
                ]);
            }
        }
    }

    private function saveFile($url, $fileType) {
        $pathInfo = explode("?", pathinfo($url, PATHINFO_EXTENSION));
        $ext = $pathInfo[0];
        $basePath = public_path(config("constant.apparel.file_base_path"));

        switch ($fileType) {
            case "pdf":
                $basePath .= "/details";
                break;
            case "image":
                $basePath .= "/images";
                break;
            default:
                break;
        }

        if (!File::isDirectory($basePath)) {
            File::makeDirectory($basePath, 0755, true);
        }

        $fileName = md5($url . time() . rand(PHP_INT_MIN, PHP_INT_MAX)) . "." . $ext;
        $fullPath = $basePath . "/" . $fileName;

        try {
            File::put($fullPath, file_get_contents($url));
        } catch (Exception $exception) {
            return false;
        }

        return $this->file->create([
            "file_uuid" => Util::uuid(),
            "file_url"  => $url,
            "file_name" => $fileName,
            "file_type" => $fileType,
        ]);
    }

    /**
     * @param $resource
     * @param string $key
     * @param string|null $value
     * @return array
     */
    private function arrayWhereLike(array $resource, string $key, ?string $value = null) {
        return (array_values(array_filter($resource, function ($resourceValue, $resourceKey) use ($key, $value) {
            if (is_array($resourceValue)) {
                if (!isset($resourceValue[$key])) {
                    return false;
                }

                return is_null($value) || (is_string($value) && (
                            is_string($resourceValue[$key]) && strpos(strtolower($resourceValue[$key]), strtolower($value)) !== false ||
                            is_array($resourceValue[$key]) && array_search($value, $resourceValue[$key]) !== false
                        ));
            }

            return (is_null($value) && $resourceKey == $key) || (is_string($value) && is_string($resourceValue) &&
                    strpos(strtolower($resourceValue), strtolower($value)) !== false);
        }, ARRAY_FILTER_USE_BOTH)));
    }
}
