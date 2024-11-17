<?php

namespace Staudenmeir\EloquentHasManyDeep\Eloquent\Relations\Traits;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection as BaseCollection;
use Staudenmeir\EloquentHasManyDeep\Eloquent\CompositeKey;

/**
 * @template TRelatedModel of \Illuminate\Database\Eloquent\Model
 * @template TDeclaringModel of \Illuminate\Database\Eloquent\Model
 */
trait SupportsCompositeKeys
{
    /**
     * Determine whether the relationship starts with a composite key.
     *
     * @return bool
     */
    protected function hasLeadingCompositeKey(): bool
    {
        return $this->localKeys[0] instanceof CompositeKey;
    }

    /**
     * Set the base constraints on the relation query for a leading composite key.
     *
     * @return void
     */
    protected function addConstraintsWithCompositeKey(): void
    {
        /** @var \Staudenmeir\EloquentHasManyDeep\Eloquent\CompositeKey $foreignKey */
        $foreignKey = $this->foreignKeys[0];

        /** @var \Staudenmeir\EloquentHasManyDeep\Eloquent\CompositeKey $localKey */
        $localKey = $this->localKeys[0];

        $columns = array_slice($foreignKey->columns, 1, null, true);

        foreach ($columns as $i => $column) {
            $this->query->where(
                $this->throughParent->qualifyColumn($column),
                '=',
                $this->farParent[$localKey->columns[$i]]
            );
        }
    }

    /**
     * Set the constraints for an eager load of the relation for a leading composite key.
     *
     * @param array<int, \Illuminate\Database\Eloquent\Model> $models
     * @return void
     */
    protected function addEagerConstraintsWithCompositeKey(array $models): void
    {
        /** @var \Staudenmeir\EloquentHasManyDeep\Eloquent\CompositeKey $foreignKey */
        $foreignKey = $this->foreignKeys[0];

        /** @var \Staudenmeir\EloquentHasManyDeep\Eloquent\CompositeKey $localKey */
        $localKey = $this->localKeys[0];

        $keys = (new BaseCollection($models))->map(
            function (Model $model) use ($localKey) {
                return array_map(
                    fn (string $column) => $model[$column],
                    $localKey->columns
                );
            }
        )->values()->unique(null, true)->all();

        $this->query->where(
            function (Builder $query) use ($foreignKey, $keys) {
                foreach ($keys as $key) {
                    $query->orWhere(
                        function (Builder $query) use ($foreignKey, $key) {
                            foreach ($foreignKey->columns as $i => $column) {
                                $query->where(
                                    $this->throughParent->qualifyColumn($column),
                                    '=',
                                    $key[$i]
                                );
                            }
                        }
                    );
                }
            }
        );
    }

    /**
     * Match the eagerly loaded results to their parents for a leading composite key.
     *
     * @param array<int, TDeclaringModel> $models
     * @param \Illuminate\Database\Eloquent\Collection<int, TRelatedModel> $results
     * @param string $relation
     * @return array<int, TDeclaringModel>
     */
    protected function matchWithCompositeKey(array $models, Collection $results, string $relation): array
    {
        $dictionary = $this->buildDictionaryWithCompositeKey($results);

        foreach ($models as $model) {
            $values = [];
            /** @var \Staudenmeir\EloquentHasManyDeep\Eloquent\CompositeKey $localKey */
            $localKey = $this->localKeys[0];

            foreach ($localKey->columns as $column) {
                $values[] = $this->getDictionaryKey(
                    $model->getAttribute($column)
                );
            }

            $key = implode("\0", $values);

            if (isset($dictionary[$key])) {
                $model->setRelation(
                    $relation,
                    $this->related->newCollection($dictionary[$key])
                );
            }
        }

        return $models;
    }

    /**
     * Build model dictionary keyed by the relation's composite foreign key.
     *
     * @param \Illuminate\Database\Eloquent\Collection<int, \Illuminate\Database\Eloquent\Model> $results
     * @return array<string, list<\Illuminate\Database\Eloquent\Model>>
     */
    protected function buildDictionaryWithCompositeKey(Collection $results): array
    {
        $dictionary = [];

        /** @var \Staudenmeir\EloquentHasManyDeep\Eloquent\CompositeKey $foreignKey */
        $foreignKey = $this->foreignKeys[0];

        foreach ($results as $result) {
            $values = [];

            foreach ($foreignKey->columns as $i => $column) {
                $alias = 'laravel_through_key' . ($i > 0 ? "_$i" : '');

                $values[] = $result->$alias;
            }

            $values = implode("\0", $values);

            $dictionary[$values][] = $result;
        }

        return $dictionary;
    }

    /**
     * Get the columns to select for a leading composite key.
     *
     * @return array<int, string>
     */
    protected function shouldSelectWithCompositeKey(): array
    {
        /** @var \Staudenmeir\EloquentHasManyDeep\Eloquent\CompositeKey $foreignKey */
        $foreignKey = $this->foreignKeys[0];

        $columns = array_slice($foreignKey->columns, 1, null, true);

        return array_map(
            fn ($column, $i) => $this->throughParent->qualifyColumn($column) . " as laravel_through_key_$i",
            $columns,
            array_keys($columns)
        );
    }

    /**
     * Add the constraints for a relationship query for a leading composite key.
     *
     * @param \Illuminate\Database\Eloquent\Builder<\Illuminate\Database\Eloquent\Model> $query
     * @return void
     */
    public function getRelationExistenceQueryWithCompositeKey(Builder $query): void
    {
        /** @var \Staudenmeir\EloquentHasManyDeep\Eloquent\CompositeKey $foreignKey */
        $foreignKey = $this->foreignKeys[0];

        /** @var \Staudenmeir\EloquentHasManyDeep\Eloquent\CompositeKey $localKey */
        $localKey = $this->localKeys[0];

        $columns = array_slice($localKey->columns, 1, null, true);

        foreach ($columns as $i => $column) {
            $query->whereColumn(
                $this->farParent->qualifyColumn($column),
                '=',
                $this->throughParent->qualifyColumn($foreignKey->columns[$i])
            );
        }
    }
}
