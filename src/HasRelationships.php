<?php

namespace Staudenmeir\EloquentHasManyDeep;

use Closure;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOneOrMany;
use Illuminate\Database\Eloquent\Relations\MorphOneOrMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Str;

trait HasRelationships
{
    /**
     * Define a has-many-deep relationship.
     *
     * @param  string  $related
     * @param  array  $through
     * @param  array  $foreignKeys
     * @param  array  $localKeys
     * @return \Staudenmeir\EloquentHasManyDeep\HasManyDeep
     */
    public function hasManyDeep($related, array $through, array $foreignKeys = [], array $localKeys = [])
    {
        return $this->newHasManyDeep(...$this->hasOneOrManyDeep($related, $through, $foreignKeys, $localKeys));
    }

    /**
     * Define a has-many-deep relationship from existing relationships.
     *
     * @param  \Illuminate\Database\Eloquent\Relations\Relation ...$relations
     * @return \Staudenmeir\EloquentHasManyDeep\HasManyDeep
     */
    public function hasManyDeepFromRelations(...$relations)
    {
        return $this->hasManyDeep(...$this->hasOneOrManyDeepFromRelations($relations));
    }

    /**
     * Define a has-one-deep relationship.
     *
     * @param  string  $related
     * @param  array  $through
     * @param  array  $foreignKeys
     * @param  array  $localKeys
     * @return \Staudenmeir\EloquentHasManyDeep\HasOneDeep
     */
    public function hasOneDeep($related, array $through, array $foreignKeys = [], array $localKeys = [])
    {
        return $this->newHasOneDeep(...$this->hasOneOrManyDeep($related, $through, $foreignKeys, $localKeys));
    }

    /**
     * Define a has-one-deep relationship from existing relationships.
     *
     * @param  \Illuminate\Database\Eloquent\Relations\Relation ...$relations
     * @return \Staudenmeir\EloquentHasManyDeep\HasOneDeep
     */
    public function hasOneDeepFromRelations(...$relations)
    {
        return $this->hasOneDeep(...$this->hasOneOrManyDeepFromRelations($relations));
    }

    /**
     * Prepare a has-one-deep or has-many-deep relationship.
     *
     * @param  string  $related
     * @param  array  $through
     * @param  array  $foreignKeys
     * @param  array  $localKeys
     * @return array
     */
    protected function hasOneOrManyDeep($related, array $through, array $foreignKeys, array $localKeys)
    {
        $relatedInstance = $this->newRelatedInstance($related);

        $throughParents = array_map(function ($class) {
            $segments = preg_split('/\s+as\s+/i', $class);

            $instance = Str::contains($segments[0], '\\') ? new $segments[0] : (new Pivot)->setTable($segments[0]);

            if (isset($segments[1])) {
                $instance->setTable($instance->getTable().' as '.$segments[1]);
            }

            return $instance;
        }, $through);

        foreach (array_merge([$this], $throughParents) as $i => $instance) {
            if (! isset($foreignKeys[$i])) {
                if ($instance instanceof Pivot) {
                    $foreignKeys[$i] = ($throughParents[$i] ?? $relatedInstance)->getKeyName();
                } else {
                    $foreignKeys[$i] = $instance->getForeignKey();
                }
            }

            if (! isset($localKeys[$i])) {
                if ($instance instanceof Pivot) {
                    $localKeys[$i] = ($throughParents[$i] ?? $relatedInstance)->getForeignKey();
                } else {
                    $localKeys[$i] = $instance->getKeyName();
                }
            }
        }

        return [$relatedInstance->newQuery(), $this, $throughParents, $foreignKeys, $localKeys];
    }

    /**
     * Prepare a has-one-deep or has-many-deep relationship from existing relationships.
     *
     * @param  \Illuminate\Database\Eloquent\Relations\Relation[]  $relations
     * @return array
     */
    protected function hasOneOrManyDeepFromRelations(array $relations)
    {
        if (is_array($relations[0])) {
            $relations = $relations[0];
        }

        $related = null;
        $through = [];
        $foreignKeys = [];
        $localKeys = [];

        foreach ($relations as $i => $relation) {
            if ($relation instanceof BelongsTo) {
                $foreignKeys[] = $relation->getOwnerKey();
                $localKeys[] = $relation->getForeignKey();
            }

            if ($relation instanceof BelongsToMany) {
                $through[] = $relation->getTable();

                if ($relation instanceof MorphToMany) {
                    if ($this->getProtectedProperty($relation, 'inverse')) {
                        $foreignKeys[] = $relation->getForeignPivotKeyName();
                        $foreignKeys[] = $this->getProtectedProperty($relation, 'relatedKey');
                        $localKeys[] = $this->getProtectedProperty($relation, 'parentKey');
                        $localKeys[] = [$relation->getMorphType(), $relation->getRelatedPivotKeyName()];
                    } else {
                        $foreignKeys[] = [$relation->getMorphType(), $relation->getForeignPivotKeyName()];
                        $foreignKeys[] = $this->getProtectedProperty($relation, 'relatedKey');
                        $localKeys[] = $this->getProtectedProperty($relation, 'parentKey');
                        $localKeys[] = $relation->getRelatedPivotKeyName();
                    }
                } else {
                    $foreignKeys[] = $relation->getForeignPivotKeyName();
                    $foreignKeys[] = $this->getProtectedProperty($relation, 'relatedKey');
                    $localKeys[] = $this->getProtectedProperty($relation, 'parentKey');
                    $localKeys[] = $relation->getRelatedPivotKeyName();
                }
            }

            if ($relation instanceof HasOneOrMany) {
                if ($relation instanceof MorphOneOrMany) {
                    $foreignKeys[] = [$relation->getQualifiedMorphType(), $relation->getQualifiedForeignKeyName()];
                } else {
                    $foreignKeys[] = $relation->getQualifiedForeignKeyName();
                }

                $localKeys[] = $this->getProtectedProperty($relation, 'localKey');
            }

            if ($relation instanceof HasManyThrough) {
                $through[] = get_class($relation->getParent());
                $foreignKeys[] = $this->getProtectedProperty($relation, 'firstKey');
                $foreignKeys[] = $this->getProtectedProperty($relation, 'secondKey');
                $localKeys[] = $this->getProtectedProperty($relation, 'localKey');
                $localKeys[] = $this->getProtectedProperty($relation, 'secondLocalKey');
            }

            if ($i === count($relations) - 1) {
                $related = get_class($relation->getRelated());
            } else {
                $through[] = get_class($relation->getRelated());
            }
        }

        return [$related, $through, $foreignKeys, $localKeys];
    }

    /**
     * Instantiate a new HasManyDeep relationship.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  \Illuminate\Database\Eloquent\Model  $farParent
     * @param  \Illuminate\Database\Eloquent\Model[]  $throughParents
     * @param  array  $foreignKeys
     * @param  array  $localKeys
     * @return \Staudenmeir\EloquentHasManyDeep\HasManyDeep
     */
    protected function newHasManyDeep(Builder $query, Model $farParent, array $throughParents, array $foreignKeys, array $localKeys)
    {
        return new HasManyDeep($query, $farParent, $throughParents, $foreignKeys, $localKeys);
    }

    /**
     * Instantiate a new HasOneDeep relationship.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  \Illuminate\Database\Eloquent\Model  $farParent
     * @param  \Illuminate\Database\Eloquent\Model[]  $throughParents
     * @param  array  $foreignKeys
     * @param  array  $localKeys
     * @return \Staudenmeir\EloquentHasManyDeep\HasOneDeep
     */
    protected function newHasOneDeep(Builder $query, Model $farParent, array $throughParents, array $foreignKeys, array $localKeys)
    {
        return new HasOneDeep($query, $farParent, $throughParents, $foreignKeys, $localKeys);
    }

    /**
     * Get a protected property from a relationship instance.
     *
     * @param  \Illuminate\Database\Eloquent\Relations\Relation  $relation
     * @param  string  $property
     * @return \Staudenmeir\EloquentHasManyDeep\HasOneDeep
     */
    protected function getProtectedProperty(Relation $relation, $property)
    {
        $closure = Closure::bind(function (Relation $relation) use ($property) {
            return $relation->$property;
        }, null, $relation);

        return $closure($relation);
    }
}
