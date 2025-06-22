<?php

namespace Thombas\RevisedServicePattern\Services\Traits;

use ReflectionClass;
use Illuminate\Http\Client\Response;
use Thombas\RevisedServicePattern\Exceptions\ServiceStubMissingException;

trait CanMockResponse
{
    protected function getStub(): Response
    {
        $this->stub = $this->stub ?? $this->defaultStub();
        
        if (!$this->stub) {
            throw new ServiceStubMissingException(message: 'A stub has not been set for this service: ' . get_class($this));
        }

        $response = str_replace(
            config('revised-service-pattern.folders.services', 'App\Services'),
            config('revised-service-pattern.folders.stubs','Tests\Stubs\Services'),
            get_class($this) . 'Stub'
        );

        $parameters = collect((new ReflectionClass($this))->getConstructor()->getParameters())
            ->mapWithKeys(fn ($param) => [$param->name => $this->{$param->name}]);

        return (new $response(code: $this->stub, parameters: $parameters))();
    }

    public function isMocking(): bool
    {
        return app()->environment() === 'testing';
    }

    public function defaultStub(): ?string
    {
        return null;
    }
}
