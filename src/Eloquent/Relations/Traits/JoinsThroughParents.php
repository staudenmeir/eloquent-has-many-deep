<?php

namespace Staudenmeir\EloquentHasManyDeep\Eloquent\Relations\Traits;

use Closure;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Query\JoinClause;
use Staudenmeir\EloquentHasManyDeep\Eloquent\CompositeKey;

trait JoinsThroughParents
{
    /**
     * Join a through parent table.
     *
     * @param \Illuminate\Database\Eloquent\Builder<\Illuminate\Database\Eloquent\Model> $query
     * @param \Illuminate\Database\Eloquent\Model $throughParent
     * @param \Illuminate\Database\Eloquent\Model $predecessor
     * @param array{0: string,
     *     1: string}|callable|string|\Staudenmeir\EloquentHasManyDeep\Eloquent\CompositeKey|null $foreignKey
     * @param array{0: string,
     *     1: string}|callable|string|\Staudenmeir\EloquentHasManyDeep\Eloquent\CompositeKey|null $localKey
     * @param string $prefix
     * @return void
     */
    protected function joinThroughParent(Builder $query, Model $throughParent, Model $predecessor, $foreignKey, $localKey, $prefix)
    {
        $table = $throughParent->getTable();

        if ($foreignKey instanceof Closure) {
            $query->join(
                $table,
                fn (JoinClause $join) => $foreignKey($query, $join)
            );
        } else {
            $joins = $this->throughParentJoins($query, $throughParent, $predecessor, $foreignKey, $localKey);

            foreach ($joins as $i => [$first, $second]) {
                $joins[$i] = [
                    $throughParent->qualifyColumn($first),
                    $predecessor->qualifyColumn($prefix.$second),
                ];
            }

            $query->join(
                $table,
                function (JoinClause $join) use ($joins) {
                    foreach ($joins as [$first, $second]) {
                        $join->on($first, '=', $second);
                    }
                }
            );
        }

        if ($this->throughParentInstanceSoftDeletes($throughParent)
            && method_exists($throughParent, 'getQualifiedDeletedAtColumn')) {
            /** @var string $column */
            $column = $throughParent->getQualifiedDeletedAtColumn();

            $query->withGlobalScope(__CLASS__ . ":$column", function (Builder $query) use ($column) {
                $query->whereNull($column);
            });
        }
    }

    /**
     * Get the joins for a through parent table.
     *
     * @param \Illuminate\Database\Eloquent\Builder<\Illuminate\Database\Eloquent\Model> $query
     * @param \Illuminate\Database\Eloquent\Model $throughParent
     * @param \Illuminate\Database\Eloquent\Model $predecessor
     * @param array{0: string,
     *     1: string}|callable|string|\Staudenmeir\EloquentHasManyDeep\Eloquent\CompositeKey|null $foreignKey
     * @param array{0: string,
     *     1: string}|callable|string|\Staudenmeir\EloquentHasManyDeep\Eloquent\CompositeKey|null $localKey
     * @return list<array{0: string, 1: string}>
     */
    protected function throughParentJoins(Builder $query, Model $throughParent, Model $predecessor, $foreignKey, $localKey): array
    {
        $joins = [];

        if ($localKey instanceof CompositeKey && $foreignKey instanceof CompositeKey) {
            foreach ($localKey->columns as $i => $column) {
                $joins[] = [$column, $foreignKey->columns[$i]];
            }
        } else {
            /** @var array{0: string, 1: string}|string $localKey */
            /** @var array{0: string, 1: string}|string $foreignKey */

            if (is_array($localKey)) {
                $query->where($throughParent->qualifyColumn($localKey[0]), '=', $predecessor->getMorphClass());

                $localKey = $localKey[1];
            }

            if (is_array($foreignKey)) {
                $query->where($predecessor->qualifyColumn($foreignKey[0]), '=', $throughParent->getMorphClass());

                $foreignKey = $foreignKey[1];
            }

            $joins[] = [$localKey, $foreignKey];
        }

        return $joins;
    }

    /**
     * Determine whether a "through" parent instance of the relation uses SoftDeletes.
     *
     * @param \Illuminate\Database\Eloquent\Model $instance
     * @return bool
     */
    public function throughParentInstanceSoftDeletes(Model $instance)
    {
        return in_array(SoftDeletes::class, class_uses_recursive($instance));
    }
}
