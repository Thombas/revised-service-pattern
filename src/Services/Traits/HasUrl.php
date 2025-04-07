<?php

namespace Thombas\RevisedServicePattern\Services\Traits;

use Illuminate\Support\Facades\URL;

trait HasUrl
{
    protected string $url = '';

    protected bool $urlHasTrailingSlash = true;

    public function setBaseUrl(
        string $url
    ): static {
        $this->baseUrl = $url;

        return $this;
    }

    public function getBaseUrl(): string
    {
        return rtrim($this->baseUrl, '/');
    }

    public function setUrl(string $url): static
    {
        $this->url = $url;

        return $this;
    }

    public function getUrl(): string
    {
        return rtrim($this->url, '/') . ($this->urlHasTrailingSlash ? '/' : false);
    }

    public function getFullUrl(): string
    {
        return URL::format($this->getBaseUrl(), $this->getUrl());
    }
}