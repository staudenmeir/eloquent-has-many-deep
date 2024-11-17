<?php

namespace Staudenmeir\EloquentHasManyDeep\Eloquent\Traits;

use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Staudenmeir\EloquentHasManyDeep\Eloquent\Relations\ThirdParty\LaravelHasManyMerged\HasManyMerged;
use Staudenmeir\EloquentHasManyDeep\HasManyDeep;
use Staudenmeir\EloquentHasManyDeep\HasOneDeep;
use Staudenmeir\EloquentHasManyDeepContracts\Interfaces\ConcatenableRelation;

/**
 * @phpstan-ignore trait.unused
 */
trait ConcatenatesRelationships
{
    use ConcatenatesNativeRelationships;

    /**
     * Define a has-many-deep relationship from existing relationships.
     *
     * @param callable|\Illuminate\Database\Eloquent\Relations\Relation<*, *, *> ...$relations
     * @return \Staudenmeir\EloquentHasManyDeep\HasManyDeep<\Illuminate\Database\Eloquent\Model, $this>
     */
    public function hasManyDeepFromRelations(...$relations)
    {
        [
            $related,
            $through,
            $foreignKeys,
            $localKeys,
            $postGetCallbacks,
            $customThroughKeyCallback,
            $customEagerConstraintsCallback,
            $customEagerMatchingCallback
        ] =
            $this->hasOneOrManyDeepFromRelations($relations);

        $relation = $this->hasManyDeep($related, $through, $foreignKeys, $localKeys);

        $this->customizeHasOneOrManyDeepRelationship(
            $relation,
            $postGetCallbacks,
            $customThroughKeyCallback,
            $customEagerConstraintsCallback,
            $customEagerMatchingCallback
        );

        return $relation;
    }

    /**
     * Define a has-one-deep relationship from existing relationships.
     *
     * @param callable|\Illuminate\Database\Eloquent\Relations\Relation<*, *, *> ...$relations
     * @return \Staudenmeir\EloquentHasManyDeep\HasOneDeep<\Illuminate\Database\Eloquent\Model, $this>
     */
    public function hasOneDeepFromRelations(...$relations)
    {
        [
            $related,
            $through,
            $foreignKeys,
            $localKeys,
            $postGetCallbacks,
            $customThroughKeyCallback,
            $customEagerConstraintsCallback,
            $customEagerMatchingCallback
        ] = $this->hasOneOrManyDeepFromRelations($relations);

        $relation = $this->hasOneDeep($related, $through, $foreignKeys, $localKeys);

        $this->customizeHasOneOrManyDeepRelationship(
            $relation,
            $postGetCallbacks,
            $customThroughKeyCallback,
            $customEagerConstraintsCallback,
            $customEagerMatchingCallback
        );

        return $relation;
    }

    /**
     * Prepare a has-one-deep or has-many-deep relationship from existing relationships.
     *
     * @param list<callable|\Illuminate\Database\Eloquent\Relations\Relation<*, *, *>> $relations
     * @return array{0: class-string<\Illuminate\Database\Eloquent\Model>,
     *     1: list<string>,
     *     2: list<array{0: string, 1: string}|callable|string|\Staudenmeir\EloquentHasManyDeep\Eloquent\CompositeKey>,
     *     3: list<array{0: string, 1: string}|callable|string|\Staudenmeir\EloquentHasManyDeep\Eloquent\CompositeKey>,
     *     4: list<callable>,
     *     5: callable|null,
     *     6: callable|null,
     *     7: callable|null}
     */
    protected function hasOneOrManyDeepFromRelations(array $relations)
    {
        $relations = $this->normalizeVariadicRelations($relations);

        foreach ($relations as $i => $relation) {
            if (is_callable($relation)) {
                $relations[$i] = $relation();
            }
        }

        $related = null;
        $through = [];
        $foreignKeys = [];
        $localKeys = [];
        $postGetCallbacks = [];
        $customThroughKeyCallback = null;
        $customEagerConstraintsCallback = null;
        $customEagerMatchingCallback = null;

        foreach ($relations as $i => $relation) {
            // https://github.com/korridor/laravel-has-many-merged
            if (is_a($relation, 'Korridor\LaravelHasManyMerged\HasManyMerged', true)) {
                $relation = HasManyMerged::fromBaseRelation($relation);
            }

            if ($relation instanceof ConcatenableRelation) {
                [$through, $foreignKeys, $localKeys] = $relation->appendToDeepRelationship(
                    $through,
                    $foreignKeys,
                    $localKeys,
                    $i
                );

                if (method_exists($relation, 'postGetCallback')) {
                    $postGetCallbacks[] = [$relation, 'postGetCallback'];
                }

                if ($i === 0) {
                    if (method_exists($relation, 'getThroughKeyForDeepRelationships')) {
                        $customThroughKeyCallback = [$relation, 'getThroughKeyForDeepRelationships'];
                    }

                    if (method_exists($relation, 'addEagerConstraintsToDeepRelationship')) {
                        $customEagerConstraintsCallback = [$relation, 'addEagerConstraintsToDeepRelationship'];
                    }

                    if (method_exists($relation, 'matchResultsForDeepRelationship')) {
                        $customEagerMatchingCallback = [$relation, 'matchResultsForDeepRelationship'];
                    }
                }
            } else {
                $method = $this->hasOneOrManyDeepRelationMethod($relation);

                [$through, $foreignKeys, $localKeys] = $this->$method($relation, $through, $foreignKeys, $localKeys);
            }

            if ($i === count($relations) - 1) {
                $related = get_class($relation->getRelated());

                if ((new $related())->getTable() !== $relation->getRelated()->getTable()) {
                    $related .= ' from ' . $relation->getRelated()->getTable();
                }
            } else {
                $through[] = $this->hasOneOrManyThroughParent($relation, $relations[$i + 1]);
            }
        }

        return [
            $related,
            $through,
            $foreignKeys,
            $localKeys,
            $postGetCallbacks,
            $customThroughKeyCallback,
            $customEagerConstraintsCallback,
            $customEagerMatchingCallback
        ];
    }

    /**
     * Prepare the through parent class from an existing relationship and its successor.
     *
     * @param \Illuminate\Database\Eloquent\Relations\Relation<*, *, *> $relation
     * @param \Illuminate\Database\Eloquent\Relations\Relation<*, *, *> $successor
     * @return string
     */
    protected function hasOneOrManyThroughParent(Relation $relation, Relation $successor)
    {
        $through = get_class($relation->getRelated());

        if ($relation instanceof ConcatenableRelation && method_exists($relation, 'getTableForDeepRelationship')) {
            return $through . ' from ' . $relation->getTableForDeepRelationship();
        }

        if ((new $through())->getTable() !== $relation->getRelated()->getTable()) {
            $through .= ' from ' . $relation->getRelated()->getTable();
        }

        if (get_class($relation->getRelated()) === get_class($successor->getParent())) {
            $table = $successor->getParent()->getTable();

            $segments = explode(' as ', $table);

            if (isset($segments[1])) {
                $through .= ' as ' . $segments[1];
            }
        }

        return $through;
    }

    /**
     * Customize a has-one-deep or has-many-deep relationship.
     *
     * @template TRelatedModel of \Illuminate\Database\Eloquent\Model
     * @template TDeclaringModel of \Illuminate\Database\Eloquent\Model
     *
     * @param \Staudenmeir\EloquentHasManyDeep\HasManyDeep<TRelatedModel, TDeclaringModel> $relation
     * @param list<callable> $postGetCallbacks
     * @param callable|null $customThroughKeyCallback
     * @param callable|null $customEagerConstraintsCallback
     * @param callable|null $customEagerMatchingCallback
     * @return \Staudenmeir\EloquentHasManyDeep\HasManyDeep<TRelatedModel, TDeclaringModel>|\Staudenmeir\EloquentHasManyDeep\HasOneDeep<TRelatedModel, TDeclaringModel>
     */
    protected function customizeHasOneOrManyDeepRelationship(
        HasManyDeep $relation,
        array $postGetCallbacks,
        ?callable $customThroughKeyCallback,
        ?callable $customEagerConstraintsCallback,
        ?callable $customEagerMatchingCallback
    ): HasManyDeep|HasOneDeep {
        $relation->withPostGetCallbacks($postGetCallbacks);

        if ($customThroughKeyCallback) {
            $relation->withCustomThroughKeyCallback($customThroughKeyCallback);
        }

        if ($customEagerConstraintsCallback) {
            $relation->withCustomEagerConstraintsCallback($customEagerConstraintsCallback);
        }

        if ($customEagerMatchingCallback) {
            $relation->withCustomEagerMatchingCallback($customEagerMatchingCallback);
        }

        return $relation;
    }

    /**
     * Define a has-many-deep relationship with constraints from existing relationships.
     *
     * @param callable ...$relations
     * @return \Staudenmeir\EloquentHasManyDeep\HasManyDeep<\Illuminate\Database\Eloquent\Model, $this>
     */
    public function hasManyDeepFromRelationsWithConstraints(...$relations): HasManyDeep
    {
        $hasManyDeep = $this->hasManyDeepFromRelations(...$relations);

        $this->addConstraintsToHasOneOrManyDeepRelationship($hasManyDeep, $relations);

        return $hasManyDeep;
    }

    /**
     * Define a has-one-deep relationship with constraints from existing relationships.
     *
     * @param callable ...$relations
     * @return \Staudenmeir\EloquentHasManyDeep\HasOneDeep<\Illuminate\Database\Eloquent\Model, $this>
     */
    public function hasOneDeepFromRelationsWithConstraints(...$relations): HasOneDeep
    {
        $hasOneDeep = $this->hasOneDeepFromRelations(...$relations);

        $this->addConstraintsToHasOneOrManyDeepRelationship($hasOneDeep, $relations);

        return $hasOneDeep;
    }

    /**
     * Add the constraints from existing relationships to a has-one-deep or has-many-deep relationship.
     *
     * @template TRelatedModel of \Illuminate\Database\Eloquent\Model
     * @template TDeclaringModel of \Illuminate\Database\Eloquent\Model
     *
     * @param \Staudenmeir\EloquentHasManyDeep\HasManyDeep<TRelatedModel, TDeclaringModel> $deepRelation
     * @param list<callable> $relations
     * @return \Staudenmeir\EloquentHasManyDeep\HasManyDeep<TRelatedModel, TDeclaringModel>|\Staudenmeir\EloquentHasManyDeep\HasOneDeep<TRelatedModel, TDeclaringModel>
     */
    protected function addConstraintsToHasOneOrManyDeepRelationship(
        HasManyDeep $deepRelation,
        array $relations
    ): HasManyDeep|HasOneDeep {
        $relations = $this->normalizeVariadicRelations($relations);

        foreach ($relations as $i => $relation) {
            $relationWithoutConstraints = Relation::noConstraints(function () use ($relation) {
                return $relation();
            });

            $deepRelation->getQuery()->mergeWheres(
                $relationWithoutConstraints->getQuery()->getQuery()->wheres,
                $relationWithoutConstraints->getQuery()->getQuery()->getRawBindings()['where'] ?? []
            );

            $isLast = $i === count($relations) - 1;

            $this->addRemovedScopesToHasOneOrManyDeepRelationship($deepRelation, $relationWithoutConstraints, $isLast);
        }

        return $deepRelation;
    }

    /**
     * Add the removed scopes from an existing relationship to a has-one-deep or has-many-deep relationship.
     *
     * @template TRelatedModel of \Illuminate\Database\Eloquent\Model
     * @template TDeclaringModel of \Illuminate\Database\Eloquent\Model
     *
     * @param \Staudenmeir\EloquentHasManyDeep\HasManyDeep<TRelatedModel, TDeclaringModel> $deepRelation
     * @param \Illuminate\Database\Eloquent\Relations\Relation<*, *, *> $relation
     * @param bool $isLastRelation
     * @return void
     */
    protected function addRemovedScopesToHasOneOrManyDeepRelationship(
        HasManyDeep $deepRelation,
        Relation $relation,
        bool $isLastRelation
    ): void {
        $removedScopes = $relation->getQuery()->removedScopes();

        foreach ($removedScopes as $scope) {
            if ($scope === SoftDeletingScope::class) {
                if ($isLastRelation) {
                    $deepRelation->withTrashed();
                } elseif (method_exists($relation->getRelated(), 'getQualifiedDeletedAtColumn')) {
                    $deletedAtColumn = $relation->getRelated()->getQualifiedDeletedAtColumn();

                    $deepRelation->withTrashed($deletedAtColumn);
                }
            }

            if ($scope === 'SoftDeletableHasManyThrough'
                && method_exists($relation->getParent(), 'getQualifiedDeletedAtColumn')) {
                $deletedAtColumn = $relation->getParent()->getQualifiedDeletedAtColumn();

                $deepRelation->withTrashed($deletedAtColumn);
            }

            if (str_starts_with($scope, HasManyDeep::class . ':')) {
                $deletedAtColumn = explode(':', $scope)[1];

                $deepRelation->withTrashed($deletedAtColumn);
            }
        }
    }

    /**
     * Normalize the relations from a variadic parameter.
     *
     * @param list<callable|list<callable|\Illuminate\Database\Eloquent\Relations\Relation<*, *, *>>|\Illuminate\Database\Eloquent\Relations\Relation<*, *, *>> $relations
     * @return list<callable|\Illuminate\Database\Eloquent\Relations\Relation<*, *, *>>
     */
    protected function normalizeVariadicRelations(array $relations): array
    {
        return is_array($relations[0]) && !is_callable($relations[0]) ? $relations[0] : $relations;
    }
}
