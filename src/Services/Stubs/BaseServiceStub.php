<?php

namespace Thombas\RevisedServicePattern\Services\Stubs;

use GuzzleHttp\Psr7\Response as Psr7Response;
use Illuminate\Http\Client\Response as ClientResponse;
use Illuminate\Support\Collection;

abstract class BaseServiceStub
{
    public function __construct(
        public string $code,
        public Collection $parameters
    ) {
        
    }

    public function __invoke(): ClientResponse
    {
        return new ClientResponse(new Psr7Response(
            status: $this->status(),
            headers: $this->headers(),
            body: json_encode($this->body())
        ));
    }

    abstract public function status(): int;

    abstract public function headers(): array;

    abstract public function body(): array;
}