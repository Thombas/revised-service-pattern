<?php

namespace Thombas\RevisedServicePattern\Services\Traits;

use Closure;
use Illuminate\Http\Client\Response;
use GuzzleHttp\Psr7\Response as Psr7Response;

trait ImplementsServiceApiMethods
{
    protected function exception(
        string $exception,
        ?Closure $closure = null,
        ...$params
    ): void {
        if ($closure) {
            $closure($this);
        }

        throw new $exception(...$params);
    }

    protected function getCustomResponse(
        array $body,
        array $headers = [],
        int $status = 200,
    ): Response {
        return new Response(
            new Psr7Response(
                status: $status,
                headers: $headers,
                body: json_encode($body)
            )
        );
    }
}
