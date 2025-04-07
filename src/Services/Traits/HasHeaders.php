<?php

namespace Thombas\RevisedServicePattern\Services\Traits;

trait HasHeaders
{
    protected array $headers = [];

    public function setHeader(string $key, string|int|array $value): static
    {
        $this->headers[$key] = $value;

        return $this;
    }

    public function revokeHeader(
        string $key
    ): static {
        unset($this->headers[$key]);
        
        return $this;
    }

    public function clearHeaders(): static
    {
        $this->headers = [];
        
        return $this;
    }

    public function setHeaders(
        array $headers
    ): static {
        $this->headers = [...$this->headers, ...$headers];

        return $this;
    }

    public function getHeaders(): ?array
    {
        return $this->headers ?? null;
    }
}