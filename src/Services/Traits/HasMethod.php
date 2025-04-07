<?php

namespace Thombas\RevisedServicePattern\Services\Traits;

use Thombas\RevisedServicePattern\Enums\ServiceMethodEnum;

trait HasMethod
{
    protected ServiceMethodEnum $method = ServiceMethodEnum::Get;

    public function getMethod(): string
    {
        return $this->method->value;
    }

    public function setMethod(
        ServiceMethodEnum $method
    ): static {
        $this->method = $method;
        
        return $this;
    }
}