<?php

namespace Staudenmeir\EloquentHasManyDeep\Eloquent\Relations\Traits;

use Closure;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Pagination\CursorPaginator;

trait ExecutesQueries
{
    /**
     * Get the results of the relationship.
     *
     * @return mixed
     */
    public function getResults()
    {
        if ($this->firstKey instanceof Closure || $this->localKey instanceof Closure) {
            return $this->get();
        }

        return parent::getResults();
    }

    /** @inheritDoc */
    public function get($columns = ['*'])
    {
        $models = parent::get($columns);

        $this->hydrateIntermediateRelations($models->all());

        foreach ($this->postGetCallbacks as $postGetCallback) {
            $postGetCallback($models);
        }

        return $models;
    }

    /**
     * Get a paginator for the "select" statement.
     *
     * @param int $perPage
     * @param list<string> $columns
     * @param string $pageName
     * @param int $page
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator<\Illuminate\Database\Eloquent\Model>
     */
    public function paginate($perPage = null, $columns = ['*'], $pageName = 'page', $page = null)
    {
        $columns = array_filter(
            $this->shouldSelect($columns),
            fn ($column) => !str_contains($column, 'laravel_through_key')
        );

        $this->query->addSelect($columns);

        return tap($this->query->paginate($perPage, $columns, $pageName, $page), function (Paginator $paginator) {
            $this->hydrateIntermediateRelations($paginator->items());
        });
    }

    /**
     * Paginate the given query into a simple paginator.
     *
     * @param int $perPage
     * @param list<string> $columns
     * @param string $pageName
     * @param int|null $page
     * @return \Illuminate\Contracts\Pagination\Paginator<\Illuminate\Database\Eloquent\Model>
     */
    public function simplePaginate($perPage = null, $columns = ['*'], $pageName = 'page', $page = null)
    {
        $columns = array_filter(
            $this->shouldSelect($columns),
            fn ($column) => !str_contains($column, 'laravel_through_key')
        );

        $this->query->addSelect($columns);

        return tap($this->query->simplePaginate($perPage, $columns, $pageName, $page), function (Paginator $paginator) {
            $this->hydrateIntermediateRelations($paginator->items());
        });
    }

    /**
     * Paginate the given query into a cursor paginator.
     *
     * @param int|null $perPage
     * @param list<string> $columns
     * @param string $cursorName
     * @param string|null $cursor
     * @return \Illuminate\Contracts\Pagination\CursorPaginator<\Illuminate\Database\Eloquent\Model>
     */
    public function cursorPaginate($perPage = null, $columns = ['*'], $cursorName = 'cursor', $cursor = null)
    {
        $columns = array_filter(
            $this->shouldSelect($columns),
            fn ($column) => !str_contains($column, 'laravel_through_key')
        );

        $this->query->addSelect($columns);

        return tap($this->query->cursorPaginate($perPage, $columns, $cursorName, $cursor), function (CursorPaginator $paginator) {
            $this->hydrateIntermediateRelations($paginator->items());
        });
    }

    /** @inheritDoc */
    public function chunk($count, callable $callback)
    {
        return $this->prepareQueryBuilder()->chunk($count, function (Collection $results) use ($callback) {
            $this->hydrateIntermediateRelations($results->all());

            return $callback($results);
        });
    }
}
