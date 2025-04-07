<?php

namespace Thombas\RevisedServicePattern\Services\Traits;

trait HasParameters
{
    protected array $parameters = [];

    public function setParameter(string $key, string|int|array $value): static
    {
        $this->parameters[$key] = $value;

        return $this;
    }

    public function revokeParameter(
        string $key
    ): static {
        unset($this->parameters[$key]);
        
        return $this;
    }

    public function clearParameters(): static
    {
        $this->parameters = [];
        
        return $this;
    }

    public function setParameters(
        array $parameters
    ): static {
        $this->parameters = [...$this->parameters, ...$parameters];

        return $this;
    }

    public function getParameters(): ?array
    {
        return $this->parameters ?? null;
    }

    public function setPayload(
        array $data
    ): static {
        return $this->setParameters(parameters: $data);
    }
}