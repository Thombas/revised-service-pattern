<?php

namespace Thombas\RevisedServicePattern\Services\Traits;

use Illuminate\Support\Facades\Cache;
use Thombas\RevisedServicePattern\Exceptions\CacheKeyMissingException;

trait HasCacheMethods
{
    protected string $cache;
    
    public function getCacheKey(): string
    {
        if (!isset($this->cache)) {
            throw new CacheKeyMissingException;
        }

        return $this->cache;
    }

    public function getCache(
        ?string $extension,
    ): mixed {
        return Cache::get(
            key: implode('.', [$this->getCacheKey(), $extension]),
            default: null
        );
    }

    public function setCache(
        ?string $extension,
        mixed $value,
        int $expiresIn = 86400
    ): static {
        Cache::put(
            key: implode('.', [$this->getCacheKey(), $extension]),
            value: $value,
            ttl: $expiresIn
        );

        return $this;
    }

    public function clearCache(
        ?string $extension,
    ): static {
        Cache::forget(key: implode('.', [$this->getCacheKey(), $extension]));

        return $this;
    }
}