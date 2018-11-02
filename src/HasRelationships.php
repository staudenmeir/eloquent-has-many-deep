<?php

namespace Staudenmeir\EloquentHasManyDeep;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Pivot;
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
            return Str::contains($class, '\\') ? new $class : (new Pivot)->setTable($class);
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
}
