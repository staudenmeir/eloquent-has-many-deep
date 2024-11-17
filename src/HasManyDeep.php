<?php

namespace Staudenmeir\EloquentHasManyDeep;

use Closure;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Staudenmeir\EloquentHasManyDeep\Eloquent\CompositeKey;
use Staudenmeir\EloquentHasManyDeep\Eloquent\Relations\Traits\ExecutesQueries;
use Staudenmeir\EloquentHasManyDeep\Eloquent\Relations\Traits\HasEagerLoading;
use Staudenmeir\EloquentHasManyDeep\Eloquent\Relations\Traits\HasExistenceQueries;
use Staudenmeir\EloquentHasManyDeep\Eloquent\Relations\Traits\IsConcatenable;
use Staudenmeir\EloquentHasManyDeep\Eloquent\Relations\Traits\JoinsThroughParents;
use Staudenmeir\EloquentHasManyDeep\Eloquent\Relations\Traits\RetrievesIntermediateTables;
use Staudenmeir\EloquentHasManyDeep\Eloquent\Relations\Traits\SupportsCompositeKeys;
use Staudenmeir\EloquentHasManyDeep\Eloquent\Relations\Traits\IsCustomizable;
use Staudenmeir\EloquentHasManyDeepContracts\Interfaces\ConcatenableRelation;

/**
 * @template TRelatedModel of \Illuminate\Database\Eloquent\Model
 * @template TDeclaringModel of \Illuminate\Database\Eloquent\Model
 *
 * @extends \Illuminate\Database\Eloquent\Relations\HasManyThrough<TRelatedModel, \Illuminate\Database\Eloquent\Model, TDeclaringModel>
 */
class HasManyDeep extends HasManyThrough implements ConcatenableRelation
{
    /** @use \Staudenmeir\EloquentHasManyDeep\Eloquent\Relations\Traits\ExecutesQueries<TRelatedModel, TDeclaringModel> */
    use ExecutesQueries;
    /** @use \Staudenmeir\EloquentHasManyDeep\Eloquent\Relations\Traits\HasEagerLoading<TRelatedModel, TDeclaringModel> */
    use HasEagerLoading;
    use HasExistenceQueries;
    use IsConcatenable;
    /** @use \Staudenmeir\EloquentHasManyDeep\Eloquent\Relations\Traits\IsCustomizable<TRelatedModel, TDeclaringModel> */
    use IsCustomizable;
    use JoinsThroughParents;
    /** @use \Staudenmeir\EloquentHasManyDeep\Eloquent\Relations\Traits\RetrievesIntermediateTables<TRelatedModel, TDeclaringModel> */
    use RetrievesIntermediateTables;
    /** @use \Staudenmeir\EloquentHasManyDeep\Eloquent\Relations\Traits\SupportsCompositeKeys<TRelatedModel, TDeclaringModel> */
    use SupportsCompositeKeys;

    /**
     * The "through" parent model instances.
     *
     * @var non-empty-list<\Illuminate\Database\Eloquent\Model>
     */
    protected $throughParents;

    /**
     * The foreign keys on the relationship.
     *
     * @var non-empty-list<array{0: string, 1: string}|callable|string|\Staudenmeir\EloquentHasManyDeep\Eloquent\CompositeKey|null>
     */
    protected $foreignKeys;

    /**
     * The local keys on the relationship.
     *
     * @var non-empty-list<array{0: string, 1: string}|callable|string|\Staudenmeir\EloquentHasManyDeep\Eloquent\CompositeKey|null>
     */
    protected $localKeys;

    /**
     * Create a new has many deep relationship instance.
     *
     * @param \Illuminate\Database\Eloquent\Builder<TRelatedModel> $query
     * @param \Illuminate\Database\Eloquent\Model $farParent
     * @param non-empty-list<\Illuminate\Database\Eloquent\Model> $throughParents
     * @param non-empty-list<array{0: string, 1: string}|callable|string|\Staudenmeir\EloquentHasManyDeep\Eloquent\CompositeKey|null> $foreignKeys
     * @param non-empty-list<array{0: string, 1: string}|callable|string|\Staudenmeir\EloquentHasManyDeep\Eloquent\CompositeKey|null> $localKeys
     * @return void
     */
    public function __construct(Builder $query, Model $farParent, array $throughParents, array $foreignKeys, array $localKeys)
    {
        $this->throughParents = $throughParents;
        $this->foreignKeys = $foreignKeys;
        $this->localKeys = $localKeys;

        $firstKey = is_array($foreignKeys[0])
            ? $foreignKeys[0][1]
            : ($foreignKeys[0] instanceof CompositeKey ? $foreignKeys[0]->columns[0] : $foreignKeys[0]);

        $localKey = $localKeys[0] instanceof CompositeKey ? $localKeys[0]->columns[0] : $localKeys[0];

        // @phpstan-ignore-next-line
        parent::__construct($query, $farParent, $throughParents[0], $firstKey, $foreignKeys[1], $localKey, $localKeys[1]);
    }

    /** @inheritDoc */
    public function addConstraints()
    {
        if ($this->firstKey instanceof Closure || $this->localKey instanceof Closure) {
            $this->performJoin();
        } else {
            parent::addConstraints();
        }

        if (static::$constraints) {
            if ($this->firstKey instanceof Closure) {
                ($this->firstKey)($this->query);
            } elseif ($this->localKey instanceof Closure) {
                ($this->localKey)($this->query);
            } elseif (is_array($this->foreignKeys[0])) {
                /** @var string $foreignKey */
                $foreignKey = $this->foreignKeys[0][0];

                $this->query->where(
                    $this->throughParent->qualifyColumn($foreignKey),
                    '=',
                    $this->farParent->getMorphClass()
                );
            } elseif ($this->hasLeadingCompositeKey()) {
                $this->addConstraintsWithCompositeKey();
            }
        }
    }

    /** @inheritDoc */
    protected function performJoin(?Builder $query = null)
    {
        $query = $query ?: $this->query;

        $throughParents = array_reverse($this->throughParents);
        $foreignKeys = array_reverse($this->foreignKeys);
        $localKeys = array_reverse($this->localKeys);

        /** @var string $from */
        $from = $query->getQuery()->from;

        $segments = explode(' as ', $from);

        $alias = $segments[1] ?? null;

        foreach ($throughParents as $i => $throughParent) {
            $predecessor = $throughParents[$i - 1] ?? $this->related;

            $prefix = $i === 0 && $alias ? $alias.'.' : '';

            $this->joinThroughParent($query, $throughParent, $predecessor, $foreignKeys[$i], $localKeys[$i], $prefix);
        }
    }

    /**
     * Set the select clause for the relation query.
     *
     * @param list<string> $columns
     * @return array<int, string>
     */
    protected function shouldSelect(array $columns = ['*'])
    {
        if ($columns == ['*']) {
            $columns = [$this->related->getTable().'.*'];
        }

        $alias = 'laravel_through_key';

        if ($this->customThroughKeyCallback) {
            $throughKey = ($this->customThroughKeyCallback)($alias);

            if (is_array($throughKey)) {
                $columns = array_merge($columns, $throughKey);
            } else {
                $columns[] = $throughKey;
            }
        } else {
            $columns[] = $this->getQualifiedFirstKeyName() . " as $alias";
        }

        if ($this->hasLeadingCompositeKey()) {
            $columns = array_merge(
                $columns,
                $this->shouldSelectWithCompositeKey()
            );
        }

        return array_merge($columns, $this->intermediateColumns());
    }

    /**
     * Restore soft-deleted models.
     *
     * @param string|list<string> ...$columns
     * @return $this
     */
    public function withTrashed(...$columns)
    {
        if (empty($columns)) {
            // @phpstan-ignore method.notFound
            $this->query->withTrashed();

            return $this;
        }

        if (is_array($columns[0])) {
            $columns = $columns[0];
        }

        /** @var list<string> $columns */

        foreach ($columns as $column) {
            $this->query->withoutGlobalScope(__CLASS__ . ":$column");
        }

        return $this;
    }

    /**
     * Get the far parent model instance.
     *
     * @return \Illuminate\Database\Eloquent\Model
     */
    public function getFarParent(): Model
    {
        return $this->farParent;
    }

    /**
     * Get the "through" parent model instances.
     *
     * @return list<\Illuminate\Database\Eloquent\Model>
     */
    public function getThroughParents()
    {
        return $this->throughParents;
    }

    /**
     * Get the foreign keys on the relationship.
     *
     * @return list<array{0: string, 1: string}|callable|string|\Staudenmeir\EloquentHasManyDeep\Eloquent\CompositeKey|null>
     */
    public function getForeignKeys()
    {
        return $this->foreignKeys;
    }

    /**
     * Get the local keys on the relationship.
     *
     * @return list<array{0: string, 1: string}|callable|string|\Staudenmeir\EloquentHasManyDeep\Eloquent\CompositeKey|null>
     */
    public function getLocalKeys()
    {
        return $this->localKeys;
    }

    /**
     * Convert the relationship to a "has one deep" relationship.
     *
     * @return \Staudenmeir\EloquentHasManyDeep\HasOneDeep<TRelatedModel, TDeclaringModel>
     * @phpstan-ignore method.childReturnType
     */
    public function one()
    {
        $query = $this->getQuery();

        $query->getQuery()->joins = [];

        /** @var \Staudenmeir\EloquentHasManyDeep\HasOneDeep<TRelatedModel, TDeclaringModel> $hasOneDeep */
        $hasOneDeep = HasOneDeep::noConstraints(
            fn () => new HasOneDeep(
                $query,
                $this->farParent,
                $this->throughParents,
                $this->foreignKeys,
                $this->localKeys
            )
        );

        return $hasOneDeep;
    }
}
