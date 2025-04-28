<?php

namespace Thombas\RevisedServicePattern\Traits;

trait CanMockServicePattern
{
    public function mockService(
        string $endpoint,
        ?string $stub,
    ): static {
        $this->app
            ->when($endpoint)
            ->needs('$stub')
            ->give($stub);

        return $this;
    }
}