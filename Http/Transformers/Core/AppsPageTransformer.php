<?php

namespace App\Http\Transformers\Core;

use App\Models\Core\AppsPage;
use App\Traits\StampCache;
use League\Fractal\TransformerAbstract;

class AppsPageTransformer extends TransformerAbstract {

    use StampCache;

    public function transform(AppsPage $page)
    {
        $response = [
            "page_uuid"        => $page->page_uuid,
            "page_url"         => $page->page_url,
            "page_url_params"  => $page->page_url_params,
            "page_title"       => $page->page_title,
            "page_description" => $page->page_description,
            "page_keywords"    => $page->page_keywords,
            "page_image"       => env("CORE_CLOUD") . DIRECTORY_SEPARATOR . "pages" . DIRECTORY_SEPARATOR . $page->page_image,
            "app"              => $page->app
        ];

        return(array_merge($response, $this->stamp($page)));
    }
}
