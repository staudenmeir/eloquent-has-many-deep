<?php

namespace Staudenmeir\EloquentHasManyDeep\Eloquent\Relations\Traits;

use Closure;
use Illuminate\Support\Collection;

/**
 * @template TRelatedModel of \Illuminate\Database\Eloquent\Model
 * @template TDeclaringModel of \Illuminate\Database\Eloquent\Model
 */
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

    /**
     * Execute the query as a "select" statement.
     *
     * @param list<string> $columns
     * @return \Illuminate\Database\Eloquent\Collection<int, TRelatedModel>
     */
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
     * @param int|\Closure|null $perPage
     * @param list<string>|string $columns
     * @param string $pageName
     * @param int|null $page
     * @param int|null|\Closure $total
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator<int, TRelatedModel>
     *
     * @throws \InvalidArgumentException
     */
    public function paginate($perPage = null, $columns = ['*'], $pageName = 'page', $page = null, $total = null)
    {
        $columns = array_filter(
            $this->shouldSelect((array) $columns),
            fn ($column) => !str_contains($column, 'laravel_through_key')
        );

        $this->query->addSelect($columns);

        /** @var \Illuminate\Contracts\Pagination\LengthAwarePaginator<int, TRelatedModel> $paginator */
        $paginator = $this->query->paginate($perPage, $columns, $pageName, $page, $total);

        $this->hydrateIntermediateRelations(
            $paginator->items()
        );

        return $paginator;
    }

    /**
     * Paginate the given query into a simple paginator.
     *
     * @param int $perPage
     * @param list<string> $columns
     * @param string $pageName
     * @param int|null $page
     * @return \Illuminate\Contracts\Pagination\Paginator<int, TRelatedModel>
     */
    public function simplePaginate($perPage = null, $columns = ['*'], $pageName = 'page', $page = null)
    {
        $columns = array_filter(
            $this->shouldSelect($columns),
            fn ($column) => !str_contains($column, 'laravel_through_key')
        );

        $this->query->addSelect($columns);

        /** @var \Illuminate\Contracts\Pagination\Paginator<int, TRelatedModel> $paginator */
        $paginator = $this->query->simplePaginate($perPage, $columns, $pageName, $page);

        $this->hydrateIntermediateRelations(
            $paginator->items()
        );

        return $paginator;
    }

    /**
     * Paginate the given query into a cursor paginator.
     *
     * @param int|null $perPage
     * @param list<string> $columns
     * @param string $cursorName
     * @param string|null $cursor
     * @return \Illuminate\Contracts\Pagination\CursorPaginator<int, TRelatedModel>
     */
    public function cursorPaginate($perPage = null, $columns = ['*'], $cursorName = 'cursor', $cursor = null)
    {
        $columns = array_filter(
            $this->shouldSelect($columns),
            fn ($column) => !str_contains($column, 'laravel_through_key')
        );

        $this->query->addSelect($columns);

        /** @var \Illuminate\Contracts\Pagination\CursorPaginator<int, TRelatedModel> $paginator */
        $paginator = $this->query->cursorPaginate($perPage, $columns, $cursorName, $cursor);

        $this->hydrateIntermediateRelations(
            $paginator->items()
        );

        return $paginator;
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
