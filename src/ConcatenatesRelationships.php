<?php

namespace Staudenmeir\EloquentHasManyDeep;

use Closure;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Illuminate\Database\Eloquent\Relations\HasOneOrMany;
use Illuminate\Database\Eloquent\Relations\MorphOneOrMany;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Database\Eloquent\Relations\Relation;
use RuntimeException;

trait ConcatenatesRelationships
{
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
            $method = $this->hasOneOrManyDeepRelationMethod($relation);

            list($through, $foreignKeys, $localKeys) = $this->$method($relation, $through, $foreignKeys, $localKeys);

            if ($i === count($relations) - 1) {
                $related = get_class($relation->getRelated());
            } else {
                $through[] = get_class($relation->getRelated());
            }
        }

        return [$related, $through, $foreignKeys, $localKeys];
    }

    /**
     * Prepare a has-one-deep or has-many-deep relationship from an existing belongs-to relationship.
     *
     * @param  \Illuminate\Database\Eloquent\Relations\BelongsTo  $relation
     * @param  \Illuminate\Database\Eloquent\Model[]  $through
     * @param  array  $foreignKeys
     * @param  array  $localKeys
     * @return array
     */
    protected function hasOneOrManyDeepFromBelongsTo(BelongsTo $relation, array $through, array $foreignKeys, array $localKeys)
    {
        $foreignKeys[] = $relation->getOwnerKey();

        $localKeys[] = $relation->getForeignKey();

        return [$through, $foreignKeys, $localKeys];
    }

    /**
     * Prepare a has-one-deep or has-many-deep relationship from an existing belongs-to-many relationship.
     *
     * @param  \Illuminate\Database\Eloquent\Relations\BelongsToMany  $relation
     * @param  \Illuminate\Database\Eloquent\Model[]  $through
     * @param  array  $foreignKeys
     * @param  array  $localKeys
     * @return array
     */
    protected function hasOneOrManyDeepFromBelongsToMany(BelongsToMany $relation, array $through, array $foreignKeys, array $localKeys)
    {
        $through[] = $relation->getTable();

        $foreignKeys[] = $relation->getForeignPivotKeyName();
        $foreignKeys[] = $this->getProtectedProperty($relation, 'relatedKey');

        $localKeys[] = $this->getProtectedProperty($relation, 'parentKey');
        $localKeys[] = $relation->getRelatedPivotKeyName();

        return [$through, $foreignKeys, $localKeys];
    }

    /**
     * Prepare a has-one-deep or has-many-deep relationship from an existing has-one or has-many relationship.
     *
     * @param  \Illuminate\Database\Eloquent\Relations\HasOneOrMany  $relation
     * @param  \Illuminate\Database\Eloquent\Model[]  $through
     * @param  array  $foreignKeys
     * @param  array  $localKeys
     * @return array
     */
    protected function hasOneOrManyDeepFromHasOneOrMany(HasOneOrMany $relation, array $through, array $foreignKeys, array $localKeys)
    {
        $foreignKeys[] = $relation->getQualifiedForeignKeyName();

        $localKeys[] = $this->getProtectedProperty($relation, 'localKey');

        return [$through, $foreignKeys, $localKeys];
    }

    /**
     * Prepare a has-one-deep or has-many-deep relationship from an existing has-many-through relationship.
     *
     * @param  \Illuminate\Database\Eloquent\Relations\HasManyThrough  $relation
     * @param  \Illuminate\Database\Eloquent\Model[]  $through
     * @param  array  $foreignKeys
     * @param  array  $localKeys
     * @return array
     */
    protected function hasOneOrManyDeepFromHasManyThrough(HasManyThrough $relation, array $through, array $foreignKeys, array $localKeys)
    {
        $through[] = get_class($relation->getParent());

        $foreignKeys[] = $this->getProtectedProperty($relation, 'firstKey');
        $foreignKeys[] = $this->getProtectedProperty($relation, 'secondKey');

        $localKeys[] = $this->getProtectedProperty($relation, 'localKey');
        $localKeys[] = $this->getProtectedProperty($relation, 'secondLocalKey');

        return [$through, $foreignKeys, $localKeys];
    }

    /**
     * Prepare a has-one-deep or has-many-deep relationship from an existing has-many-deep relationship.
     *
     * @param  \Staudenmeir\EloquentHasManyDeep\HasManyDeep  $relation
     * @param  \Illuminate\Database\Eloquent\Model[]  $through
     * @param  array  $foreignKeys
     * @param  array  $localKeys
     * @return array
     */
    protected function hasOneOrManyDeepFromHasManyDeep(HasManyDeep $relation, array $through, array $foreignKeys, array $localKeys)
    {
        foreach ($relation->getThroughParents() as $throughParent) {
            $segments = explode(' as ', $throughParent->getTable());

            $class = get_class($throughParent);

            if (isset($segments[1])) {
                $class .= ' as '.$segments[1];
            } elseif ($throughParent instanceof Pivot) {
                $class = $throughParent->getTable();
            }

            $through[] = $class;
        }

        $foreignKeys = array_merge($foreignKeys, $relation->getForeignKeys());

        $localKeys = array_merge($localKeys, $relation->getLocalKeys());

        return [$through, $foreignKeys, $localKeys];
    }

    /**
     * Prepare a has-one-deep or has-many-deep relationship from an existing morph-one or morph-many relationship.
     *
     * @param  \Illuminate\Database\Eloquent\Relations\MorphOneOrMany  $relation
     * @param  \Illuminate\Database\Eloquent\Model[]  $through
     * @param  array  $foreignKeys
     * @param  array  $localKeys
     * @return array
     */
    protected function hasOneOrManyDeepFromMorphOneOrMany(MorphOneOrMany $relation, array $through, array $foreignKeys, array $localKeys)
    {
        $foreignKeys[] = [$relation->getQualifiedMorphType(), $relation->getQualifiedForeignKeyName()];

        $localKeys[] = $this->getProtectedProperty($relation, 'localKey');

        return [$through, $foreignKeys, $localKeys];
    }

    /**
     * Prepare a has-one-deep or has-many-deep relationship from an existing morph-to-many relationship.
     *
     * @param  \Illuminate\Database\Eloquent\Relations\MorphToMany  $relation
     * @param  \Illuminate\Database\Eloquent\Model[]  $through
     * @param  array  $foreignKeys
     * @param  array  $localKeys
     * @return array
     */
    protected function hasOneOrManyDeepFromMorphToMany(MorphToMany $relation, array $through, array $foreignKeys, array $localKeys)
    {
        $through[] = $relation->getTable();

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

        return [$through, $foreignKeys, $localKeys];
    }

    /**
     * Get the relationship method name.
     *
     * @param  \Illuminate\Database\Eloquent\Relations\Relation  $relation
     * @return string
     */
    protected function hasOneOrManyDeepRelationMethod(Relation $relation)
    {
        $classes = [
            BelongsTo::class,
            HasManyDeep::class,
            HasManyThrough::class,
            MorphOneOrMany::class,
            HasOneOrMany::class,
            MorphToMany::class,
            BelongsToMany::class,
        ];

        foreach ($classes as $class) {
            if ($relation instanceof $class) {
                return 'hasOneOrManyDeepFrom'.class_basename($class);
            }
        }

        throw new RuntimeException('This relationship is not supported.'); // @codeCoverageIgnore
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
