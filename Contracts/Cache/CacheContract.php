<?php

namespace App\Contracts\Cache;

interface CacheContract {
    public function setClassOptions(string $className, string $methodName);
    public function setRequestOptions(string $endpoint, array $requestBody);
    public function setQueryString(string $queryString);
    public function setCacheKey(string $key);
    public function getCacheKey();

    public function isCached(?string $key = null): bool;
    public function getCache(?string $key = null);
    public function setCache($cacheData, ?string $key = null);
}