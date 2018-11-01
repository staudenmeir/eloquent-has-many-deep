<?php

namespace Staudenmeir\EloquentHasManyDeep;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

class HasManyDeep extends HasManyThrough
{
    /**
     * The "through" parent model instances.
     *
     * @var \Illuminate\Database\Eloquent\Model[]
     */
    protected $throughParents;

    /**
     * The foreign keys on the relationship.
     *
     * @var array
     */
    protected $foreignKeys;

    /**
     * The local keys on the relationship.
     *
     * @var array
     */
    protected $localKeys;

    /**
     * The intermediate tables to retrieve.
     *
     * @var array
     */
    protected $intermediateTables = [];

    /**
     * Create a new has many deep relationship instance.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  \Illuminate\Database\Eloquent\Model  $farParent
     * @param  \Illuminate\Database\Eloquent\Model[]  $throughParents
     * @param  array  $foreignKeys
     * @param  array  $localKeys
     * @return void
     */
    public function __construct(Builder $query, Model $farParent, array $throughParents, array $foreignKeys, array $localKeys)
    {
        $this->throughParents = $throughParents;
        $this->foreignKeys = $foreignKeys;
        $this->localKeys = $localKeys;

        $firstKey = is_array($foreignKeys[0]) ? $foreignKeys[0][1] : $foreignKeys[0];

        parent::__construct($query, $farParent, $throughParents[0], $firstKey, $foreignKeys[1], $localKeys[0], $localKeys[1]);
    }

    /**
     * Set the base constraints on the relation query.
     *
     * @return void
     */
    public function addConstraints()
    {
        parent::addConstraints();

        if (static::$constraints) {
            if (is_array($this->foreignKeys[0])) {
                $column = $this->throughParent->qualifyColumn($this->foreignKeys[0][0]);

                $this->query->where($column, '=', $this->farParent->getMorphClass());
            }
        }
    }

    /**
     * Set the join clause on the query.
     *
     * @param  \Illuminate\Database\Eloquent\Builder|null  $query
     * @return void
     */
    protected function performJoin(Builder $query = null)
    {
        $query = $query ?: $this->query;

        $throughParents = array_reverse($this->throughParents);
        $foreignKeys = array_reverse($this->foreignKeys);
        $localKeys = array_reverse($this->localKeys);

        $segments = explode(' as ', $query->getQuery()->from);

        $alias = count($segments) > 1 ? $segments[1] : null;

        foreach ($throughParents as $i => $throughParent) {
            $predecessor = $i > 0 ? $throughParents[$i - 1] : $this->related;

            $localKey = $localKeys[$i];

            if (is_array($localKey)) {
                $query->where($throughParent->qualifyColumn($localKey[0]), '=', $predecessor->getMorphClass());

                $localKey = $localKey[1];
            }

            $first = $throughParent->qualifyColumn($localKey);

            $foreignKey = $foreignKeys[$i];

            if (is_array($foreignKey)) {
                $query->where($predecessor->qualifyColumn($foreignKey[0]), '=', $throughParent->getMorphClass());

                $foreignKey = $foreignKey[1];
            }

            $foreignKey = ($i === 0 && $alias ? $alias.'.' : '').$foreignKey;

            $second = $predecessor->qualifyColumn($foreignKey);

            $query->join($throughParent->getTable(), $first, '=', $second);

            if ($this->throughParentInstanceSoftDeletes($throughParent)) {
                $query->whereNull($throughParent->getQualifiedDeletedAtColumn());
            }
        }
    }

    /**
     * Determine whether a "through" parent instance of the relation uses Soft Deletes.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $instance
     * @return bool
     */
    public function throughParentInstanceSoftDeletes(Model $instance)
    {
        return in_array(SoftDeletes::class, class_uses_recursive($instance));
    }

    /**
     * Set the constraints for an eager load of the relation.
     *
     * @param  array  $models
     * @return void
     */
    public function addEagerConstraints(array $models)
    {
        parent::addEagerConstraints($models);

        if (is_array($this->foreignKeys[0])) {
            $column = $this->throughParent->qualifyColumn($this->foreignKeys[0][0]);

            $this->query->where($column, '=', $this->farParent->getMorphClass());
        }
    }

    /**
     * Execute the query as a "select" statement.
     *
     * @param  array  $columns
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function get($columns = ['*'])
    {
        $models = parent::get($columns);

        $this->hydrateIntermediateRelations($models->all());

        return $models;
    }

    /**
     * Get a paginator for the "select" statement.
     *
     * @param  int  $perPage
     * @param  array  $columns
     * @param  string  $pageName
     * @param  int  $page
     * @return \Illuminate\Contracts\Pagination\LengthAwarePaginator
     */
    public function paginate($perPage = null, $columns = ['*'], $pageName = 'page', $page = null)
    {
        $columns = $this->shouldSelect($columns);

        unset($columns[array_search($this->getQualifiedFirstKeyName(), $columns)]);

        $this->query->addSelect($columns);

        return tap($this->query->paginate($perPage, $columns, $pageName, $page), function ($paginator) {
            $this->hydrateIntermediateRelations($paginator->items());
        });
    }

    /**
     * Paginate the given query into a simple paginator.
     *
     * @param  int  $perPage
     * @param  array  $columns
     * @param  string  $pageName
     * @param  int|null  $page
     * @return \Illuminate\Contracts\Pagination\Paginator
     */
    public function simplePaginate($perPage = null, $columns = ['*'], $pageName = 'page', $page = null)
    {
        $columns = $this->shouldSelect($columns);

        unset($columns[array_search($this->getQualifiedFirstKeyName(), $columns)]);

        $this->query->addSelect($columns);

        return tap($this->query->simplePaginate($perPage, $columns, $pageName, $page), function ($paginator) {
            $this->hydrateIntermediateRelations($paginator->items());
        });
    }

    /**
     * Set the select clause for the relation query.
     *
     * @param  array  $columns
     * @return array
     */
    protected function shouldSelect(array $columns = ['*'])
    {
        return array_merge(parent::shouldSelect($columns), $this->intermediateColumns());
    }

    /**
     * Chunk the results of the query.
     *
     * @param  int  $count
     * @param  callable  $callback
     * @return bool
     */
    public function chunk($count, callable $callback)
    {
        return $this->prepareQueryBuilder()->chunk($count, function ($results) use ($callback) {
            $this->hydrateIntermediateRelations($results->all());

            return $callback($results);
        });
    }

    /**
     * Hydrate the intermediate table relationship on the models.
     *
     * @param  array  $models
     * @return void
     */
    protected function hydrateIntermediateRelations(array $models)
    {
        $intermediateTables = $this->intermediateTables;

        ksort($intermediateTables);

        foreach ($intermediateTables as $accessor => $intermediateTable) {
            $prefix = $this->prefix($accessor);

            if (Str::contains($accessor, '.')) {
                list($path, $key) = preg_split('/\.(?=[^.]*$)/', $accessor);
            } else {
                list($path, $key) = [null, $accessor];
            }

            foreach ($models as $model) {
                $relation = $this->intermediateRelation($model, $intermediateTable, $prefix);

                data_get($model, $path)->setRelation($key, $relation);
            }
        }
    }

    /**
     * Get the intermediate relationship from the query.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @param  array  $intermediateTable
     * @param  string  $prefix
     * @return \Illuminate\Database\Eloquent\Model
     */
    protected function intermediateRelation(Model $model, array $intermediateTable, $prefix)
    {
        $attributes = $this->intermediateAttributes($model, $prefix);

        $class = $intermediateTable['class'];

        if ($class === Pivot::class) {
            return $class::fromAttributes($model, $attributes, $intermediateTable['table'], true);
        }

        if (is_subclass_of($class, Pivot::class)) {
            return $class::fromRawAttributes($model, $attributes, $intermediateTable['table'], true);
        }

        return (new $class)->newFromBuilder($attributes);
    }

    /**
     * Get the intermediate attributes from a model.
     *
     * @param  \Illuminate\Database\Eloquent\Model  $model
     * @param  string  $prefix
     * @return array
     */
    protected function intermediateAttributes(Model $model, $prefix)
    {
        $attributes = [];

        foreach ($model->getAttributes() as $key => $value) {
            if (strpos($key, $prefix) === 0) {
                $attributes[substr($key, strlen($prefix))] = $value;

                unset($model->$key);
            }
        }

        return $attributes;
    }

    /**
     * Add the constraints for a relationship query.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  \Illuminate\Database\Eloquent\Builder  $parentQuery
     * @param  array|mixed  $columns
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function getRelationExistenceQuery(Builder $query, Builder $parentQuery, $columns = ['*'])
    {
        $query = parent::getRelationExistenceQuery($query, $parentQuery, $columns);

        if (is_array($this->foreignKeys[0])) {
            $column = $this->throughParent->qualifyColumn($this->foreignKeys[0][0]);

            $query->where($column, '=', $this->farParent->getMorphClass());
        }

        return $query;
    }

    /**
     * Add the constraints for a relationship query on the same table.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  \Illuminate\Database\Eloquent\Builder  $parentQuery
     * @param  array|mixed  $columns
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function getRelationExistenceQueryForSelfRelation(Builder $query, Builder $parentQuery, $columns = ['*'])
    {
        $query->from($query->getModel()->getTable().' as '.$hash = $this->getRelationCountHash());

        $this->performJoin($query);

        $query->getModel()->setTable($hash);

        return $query->select($columns)->whereColumn(
            $parentQuery->getQuery()->from.'.'.$query->getModel()->getKeyName(), '=', $this->getQualifiedFirstKeyName()
        );
    }

    /**
     * Set the columns on an intermediate table to retrieve.
     *
     * @param  string  $class
     * @param  array  $columns
     * @param  string|null  $accessor
     * @return $this
     */
    public function withIntermediate($class, array $columns = ['*'], $accessor = null)
    {
        $table = (new $class)->getTable();

        $accessor = $accessor ?: snake_case(class_basename($class));

        return $this->withPivot($table, $columns, $class, $accessor);
    }

    /**
     * Set the columns on a pivot table to retrieve.
     *
     * @param  string  $table
     * @param  array  $columns
     * @param  string  $class
     * @param  string|null  $accessor
     * @return $this
     */
    public function withPivot($table, array $columns = ['*'], $class = Pivot::class, $accessor = null)
    {
        if ($columns === ['*']) {
            $columns = $this->query->getConnection()->getSchemaBuilder()->getColumnListing($table);
        }

        $accessor = $accessor ?: $table;

        if (isset($this->intermediateTables[$accessor])) {
            $columns = array_merge($columns, $this->intermediateTables[$accessor]['columns']);
        }

        $this->intermediateTables[$accessor] = compact('table', 'columns', 'class');

        return $this;
    }

    /**
     * Get the intermediate columns for the relation.
     *
     * @return array
     */
    protected function intermediateColumns()
    {
        $columns = [];

        foreach ($this->intermediateTables as $accessor => $intermediateTable) {
            $prefix = $this->prefix($accessor);

            foreach ($intermediateTable['columns'] as $column) {
                $columns[] = $intermediateTable['table'].'.'.$column.' as '.$prefix.$column;
            }
        }

        return array_unique($columns);
    }

    /**
     * Get the intermediate column alias prefix.
     *
     * @param  string  $accessor
     * @return string
     */
    protected function prefix($accessor)
    {
        return '__'.$accessor.'__';
    }
}
