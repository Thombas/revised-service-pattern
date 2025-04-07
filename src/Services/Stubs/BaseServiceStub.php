<?php

namespace Thombas\RevisedServicePattern\Services\Stubs;

use GuzzleHttp\Psr7\Response;

abstract class BaseServiceStub
{
    protected static string $code = 'success';

    public static function as(
        $code
    ): static {
        static::$code = $code;

        return new static;
    }

    public function __invoke(): Response
    {
        return new Response(
            status: $this->getResponseStatus(code: static::$code),
            headers: $this->getResponseHeaders(code: static::$code),
            body: json_encode($this->getResponseBody(code: static::$code)),
        );
    }

    abstract protected function getResponseStatus(string $code): int;

    abstract protected function getResponseHeaders(string $code): array;

    abstract protected function getResponseBody(string $code): array;
}