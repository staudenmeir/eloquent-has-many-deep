<?php

namespace Staudenmeir\EloquentHasManyDeep;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\SoftDeletes;

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

        parent::__construct($query, $farParent, $throughParents[0], $foreignKeys[0], $foreignKeys[1], $localKeys[0], $localKeys[1]);
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
            $first = $throughParent->qualifyColumn($localKeys[$i]);

            $predecessor = $i > 0 ? $throughParents[$i - 1] : $this->related;

            $foreignKey = ($i == 0 && $alias ? $alias.'.' : '').$foreignKeys[$i];

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
        if ($columns == ['*']) {
            $columns = [$this->related->getTable() . '.*'];
        }

        $this->query->addSelect($columns);

        return $this->query->paginate($perPage, $columns, $pageName, $page);
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
        if ($columns == ['*']) {
            $columns = [$this->related->getTable() . '.*'];
        }

        $this->query->addSelect($columns);

        return $this->query->simplePaginate($perPage, $columns, $pageName, $page);
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
}
