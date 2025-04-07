<?php

namespace Thombas\RevisedServicePattern\Services\Traits;

use ReflectionClass;
use Illuminate\Http\Client\Response;

trait CanMockResponse
{
    protected string $stub;

    protected function getStub(): Response
    {
        $parameters = (new ReflectionClass($this))->getConstructor()->getParameters();

        $parameters = collect($parameters)->mapWithKeys(fn ($param) => [$param->name => $this->{$param->name}]);

        return (new $this->stub(...$parameters));
    }

    public function isMocking(): bool
    {
        return app()->environment() === 'testing';
    }
}
