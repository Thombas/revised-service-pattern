<?php

namespace Thombas\RevisedServicePattern\Services\Paginators;

use Closure;
use ReflectionClass;
use RuntimeException;
use Illuminate\Support\Str;
use Illuminate\Support\LazyCollection;
use Illuminate\Pagination\Paginator as BasePaginator;
use Illuminate\Pagination\LengthAwarePaginator;
use Thombas\RevisedServicePattern\Services\Paginators\Interfaces\PaginatorInterface;

class PagePaginator implements PaginatorInterface
{
    public function __construct(
        protected string $service,
        protected string $method,
        protected LengthAwarePaginator $paginator,
        protected string $key,
        protected array $parameters
    ) {}

    public function count(): int
    {
        return $this->paginator->total();
    }

    public function get(): LazyCollection
    {
        return LazyCollection::wrap($this->paginator->items());
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
                $page->paginator->hasMorePages() &&
                ($page = $page->next())
            );
        });
    }

    public function first(): mixed
    {
        return $this->paginator->items()[0] ?? null;
    }

    public function next(): ?PaginatorInterface
    {
        if (! $this->paginator->hasMorePages()) {
            return null;
        }

        $method = Str::camel(Str::afterLast($this->method, '\\'));

        return $this->service::$method(...array_merge(
            $this->parameters,
            [$this->key => $this->paginator->currentPage() + 1]
        ));
    }

    public function previous(): ?PaginatorInterface
    {
        if ($this->paginator->onFirstPage()) {
            return null;
        }

        $method = Str::camel(Str::afterLast($this->method, '\\'));

        return $this->service::$method(...array_merge(
            $this->parameters,
            [$this->key => $this->paginator->currentPage() - 1]
        ));
    }

    public function each(Closure $callback): void
    {
        $page = $this;
        do {
            $callback($page->get());
        } while (
            $page->paginator->hasMorePages() &&
            ($page = $page->next())
        );
    }

    public static function fromLengthAware(
        object $caller,
        array|\Illuminate\Support\Collection $items,
        int $total,
        int $perPage,
        int $page,
        string $pageKey = 'page'
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
            service: $service,
            method: $method ?? '__invoke',
            paginator: new LengthAwarePaginator(
                is_array($items) ? $items : $items->all(),
                $total,
                $perPage,
                $page,
                [
                    'path'     => BasePaginator::resolveCurrentPath(),
                    'pageName' => $pageKey,
                ]
            ),
            key: $pageKey,
            parameters: $parameters
        );
    }
}