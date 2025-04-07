<?php

namespace Thombas\RevisedServicePattern\Services\Stubs;

use GuzzleHttp\Psr7\Response;

abstract class BaseServiceStub
{
    public function __invoke(): Response
    {
        return new Response(
            // status: $this->getResponseStatus(),
            // headers: $this->getResponseHeaders(),
            // body: $this->getResponseBody(),
        );
    }
}