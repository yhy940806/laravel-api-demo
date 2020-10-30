<?php

namespace App\Helpers;

use Illuminate\Container\Container;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Collection;

class CollectionHelper
{

    public static function paginate(Collection $results, $total, $pageSize)
    {
        $page = Paginator::resolveCurrentPage("page");

        return(self::paginator($results->forPage($page, $pageSize), $total, $pageSize, $page, [
            "path" => Paginator::resolveCurrentPath(),
            "pageName" => "page"
        ]));
    }

    public static function paginator($items, $total, $perPage, $currentPage, $options)
    {
        return(Container::getInstance()->makeWith(LengthAwarePaginator::class, compact(
            "items", "total", "perPage", "currentPage", "options"
        )));
    }
}
