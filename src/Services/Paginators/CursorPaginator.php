<?php

namespace Thombas\RevisedServicePattern\Services\Paginators;

use Closure;
use ReflectionClass;
use RuntimeException;
use Illuminate\Support\Str;
use Illuminate\Support\LazyCollection;
use Thombas\RevisedServicePattern\Services\Paginators\Interfaces\PaginatorInterface;

class CursorPaginator implements PaginatorInterface
{
    public function __construct(
        protected string $service,
        protected string $method,
        protected array  $items,
        protected ?string $nextCursor,
        protected string $key,
        protected array  $parameters,
        protected ?int   $total = null,
    ) {}

    public function count(): int
    {
        if ($this->total !== null) {
            return $this->total;
        }

        return $this->all()->count();
    }

    public function get(): LazyCollection
    {
        return LazyCollection::wrap($this->items);
    }

    public function all(): LazyCollection
    {
        return LazyCollection::make(function () {
            $page = $this;
            do {
                foreach ($page->get() as $item) {
                    yield $item;
                }
            } while (
                $page->nextCursor !== null &&
                ($page = $page->next())
            );
        });
    }

    public function first(): mixed
    {
        return $this->get()?->first();
    }

    public function next(): ?PaginatorInterface
    {
        if ($this->nextCursor === null) {
            return null;
        }

        $method = Str::camel(Str::afterLast($this->method, '\\'));

        return $this->service::{ Str::camel($method) }(
            ...array_merge($this->parameters, [
                $this->key => $this->nextCursor,
            ])
        );
    }

    public function previous(): ?PaginatorInterface
    {
        return null;
    }

    public function each(Closure $callback): void
    {
        $page = $this;
        do {
            $callback($page->get());
        } while (
            $page->nextCursor !== null &&
            ($page = $page->next())
        );
    }

    public static function fromCursorResponse(
        object $caller,
        array|\Illuminate\Support\Collection $items,
        ?string $nextCursor,
        ?int $total = null,
        string $pageKey = 'cursor'
    ): PaginatorInterface {
        $parameters = [];
        $service = null;
        $method = null;

        if ($caller) {
            $service = get_class($caller);

            foreach (debug_backtrace(DEBUG_BACKTRACE_IGNORE_ARGS) as $frame) {
                if (($frame['class'] ?? null) === $service) {
                    $method = $frame['function'] ?? null;
                    break;
                }
            }

            $reflection = new ReflectionClass($caller);
            if ($constructor = $reflection->getConstructor()) {
                foreach ($constructor->getParameters() as $param) {
                    $name = $param->getName();

                    if ($reflection->hasProperty($name)) {
                        $property = $reflection->getProperty($name);
                        $property->setAccessible(true);

                        if ($property->isInitialized($caller)) {
                            $parameters[$name] = $property->getValue($caller);
                        }
                    }
                }
            }
        } else {
            throw new RuntimeException('Caller object must be passed to fromApiResponse() to retrieve actual runtime values.');
        }

        $method = get_class($caller);

        $service = null;
        foreach (class_parents($caller) as $parent) {
            if (str_ends_with($parent, 'Service')) {
                $service = $parent;
                break;
            }
        }

        $service ??= $method;
        
        return new self(
            service    : $service,
            method     : $method,
            items      : is_array($items) ? $items : $items->all(),
            nextCursor : $nextCursor,
            key        : $pageKey,
            parameters : $parameters,
            total      : $total,
        );
    }
}
