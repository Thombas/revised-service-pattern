<?php

namespace Thombas\RevisedServicePattern\Services;

use Closure;
use ReflectionClass;
use RuntimeException;
use Illuminate\Support\Str;
use Illuminate\Support\Collection;
use Illuminate\Support\LazyCollection;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator as BasePaginator;

class Paginator
{
    public function __construct(
        protected string $service,
        protected string $method,
        protected LengthAwarePaginator $paginator,
        protected string $key,
        protected array $parameters
    ) {
        
    }

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
                $items = $page->get();

                foreach ($items as $item) {
                    yield $item;
                }

                unset($items);

                if (function_exists('gc_collect_cycles')) {
                    gc_collect_cycles();
                }
            } while (
                $page->paginator->hasMorePages() &&
                ($page = $page->next())
            );
            
            unset($page);

            if (function_exists('gc_collect_cycles')) {
                gc_collect_cycles();
            }
        });
    }

    public function first()
    {
        return $this->paginator->items()[0];
    }

    public function next(): ?Paginator
    {
        if ($this->paginator->hasMorePages()) {
            $method = Str::camel(Str::afterLast($this->method, '\\'));

            return $this->service::$method(...array_merge($this->parameters, [
                $this->key => ($this->paginator->currentPage() + 1)
            ]));
        }

        return null;
    }

    public function previous(): ?Paginator
    {
        if (!$this->paginator->onFirstPage()) {
            $method = Str::camel(Str::afterLast($this->method, '\\'));

            return $this->service::$method(...array_merge($this->parameters, [
                $this->key => ($this->paginator->currentPage() - 1)
            ]));
        }

        return null;
    }

    public function each(
        Closure $callback
    ): void {
        $page = $this;

        do {
            $items = $page->get();
            $callback($items);

            unset($items);
            if (function_exists('gc_collect_cycles')) {
                gc_collect_cycles();
            }
        } while (
            $page->paginator->hasMorePages() && ($page = $page->next())
        );

        unset($page);
        gc_collect_cycles();
    }

    public static function fromApiResponse(
        object $caller,
        array|Collection $items,
        int $total,
        int $perPage,
        int $page,
        string $pageKey = 'page',
    ): self {
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