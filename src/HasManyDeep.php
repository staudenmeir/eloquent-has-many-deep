<?php

namespace Staudenmeir\EloquentHasManyDeep;

use Closure;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;
use Staudenmeir\EloquentHasManyDeep\Eloquent\Relations\Interfaces\DeepRelation;
use Staudenmeir\EloquentHasManyDeep\Eloquent\Relations\Traits\IsOneOrManyDeepRelation;
use Staudenmeir\EloquentHasManyDeepContracts\Interfaces\ConcatenableRelation;

/**
 * @template TRelatedModel of \Illuminate\Database\Eloquent\Model
 * @template TDeclaringModel of \Illuminate\Database\Eloquent\Model
 *
 * @extends \Illuminate\Database\Eloquent\Relations\HasManyThrough<TRelatedModel, \Illuminate\Database\Eloquent\Model, TDeclaringModel>
 * @implements \Staudenmeir\EloquentHasManyDeep\Eloquent\Relations\Interfaces\DeepRelation<TRelatedModel, TDeclaringModel>
 */
class HasManyDeep extends HasManyThrough implements ConcatenableRelation, DeepRelation
{
    /** @use \Staudenmeir\EloquentHasManyDeep\Eloquent\Relations\Traits\IsOneOrManyDeepRelation<TRelatedModel, TDeclaringModel> */
    use IsOneOrManyDeepRelation;

    /** @inheritDoc */
    public function getResults()
    {
        if ($this->firstKey instanceof Closure || $this->localKey instanceof Closure) {
            return $this->get();
        }

        return parent::getResults();
    }

    /**
     * Match the eagerly loaded results to their parents.
     *
     * @param array<int, TDeclaringModel> $models
     * @param \Illuminate\Database\Eloquent\Collection<int, TRelatedModel> $results
     * @param string $relation
     * @return array<int, TDeclaringModel>
     */
    public function match(array $models, Collection $results, $relation)
    {
        if ($this->customEagerMatchingCallbacks) {
            foreach ($this->customEagerMatchingCallbacks as $callback) {
                $models = $callback($models, $results, $relation);
            }

            return $models;
        }

        if ($this->hasLeadingCompositeKey()) {
            return $this->matchWithCompositeKey($models, $results, $relation);
        }

        /** @var array<int, TDeclaringModel> $parentModels */
        $parentModels = parent::match($models, $results, $relation);

        return $parentModels;
    }

    /**
     * Convert the relationship to a "has one deep" relationship.
     *
     * @return \Staudenmeir\EloquentHasManyDeep\HasOneDeep<TRelatedModel, TDeclaringModel>
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
