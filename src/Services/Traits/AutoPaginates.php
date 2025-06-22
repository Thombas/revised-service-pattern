<?php

namespace Thombas\RevisedServicePattern\Services\Traits;

use Thombas\RevisedServicePattern\Services\Paginator;
use Illuminate\Support\Collection;

trait AutoPaginates
{
    public function asPaginator(
        array|Collection $items,
        int $total,
        int $perPage,
        int $page
    ): Paginator {
        return Paginator::fromApiResponse(
            items: $items,
            total: $total,
            perPage: $perPage,
            page: $page,
            caller: $this,
        );
    }
}
