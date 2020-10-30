<?php

namespace App\Traits;

use App\Facades\Cache\AppCache;
use App\Http\Resources\Common\BaseCollection;

trait Cacheable {
    public function sendCacheResponse($response) {
        if ($response instanceof BaseCollection) {
            AppCache::setCache($response->toResponse(request())->content());
        }

        if ($response instanceof \Dingo\Api\Http\Response) {
            AppCache::setCache($response->getContent());
        }

        return $response;
    }
}