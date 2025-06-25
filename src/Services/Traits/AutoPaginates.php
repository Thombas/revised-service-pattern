<?php

namespace Thombas\RevisedServicePattern\Services\Traits;

use Illuminate\Support\Collection;
use Thombas\RevisedServicePattern\Services\Paginators\PagePaginator;
use Thombas\RevisedServicePattern\Services\Paginators\CursorPaginator;
use Thombas\RevisedServicePattern\Services\Paginators\Interfaces\PaginatorInterface;

trait AutoPaginates
{
    public function asPaginator(
        array|Collection $items,
        ?int $total = null,
        ?int $perPage = null,
        int $page = 0,
        string $pageKey = 'page',
        ?string $nextCursor = null
    ): PaginatorInterface {
        if ($pageKey === 'cursor' || $nextCursor !== null) {
            return CursorPaginator::fromCursorResponse(
                caller     : $this,
                items      : $items,
                nextCursor : $nextCursor,
            );
        }

        return PagePaginator::fromLengthAware(
            caller   : $this,
            items    : $items,
            total    : $total ?? 0,
            perPage  : $perPage ?? count($items),
            page     : $page,
            pageKey  : $pageKey,
        );
    }
}
