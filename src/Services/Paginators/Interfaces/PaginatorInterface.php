<?php

namespace Thombas\RevisedServicePattern\Services\Paginators\Interfaces;

use Closure;
use Illuminate\Support\LazyCollection;

interface PaginatorInterface
{
    public function count(): int;

    public function get(): LazyCollection;

    public function all(): LazyCollection;

    public function first(): mixed;

    public function next(): ?PaginatorInterface;

    public function previous(): ?PaginatorInterface;

    public function each(Closure $callback): void;
}