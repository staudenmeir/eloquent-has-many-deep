<?php

namespace Staudenmeir\EloquentHasManyDeep;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Concerns\SupportsDefaultModels;

/**
 * @template TRelatedModel of \Illuminate\Database\Eloquent\Model
 * @template TDeclaringModel of \Illuminate\Database\Eloquent\Model
 *
 * @extends \Staudenmeir\EloquentHasManyDeep\HasManyDeep<TRelatedModel, TDeclaringModel>
 */
class HasOneDeep extends HasManyDeep
{
    use SupportsDefaultModels;

    /**
     * Get the results of the relationship.
     *
     * @return \Illuminate\Database\Eloquent\Model|null
     */
    public function getResults()
    {
        return $this->first() ?: $this->getDefaultFor(end($this->throughParents));
    }

    /** @inheritDoc */
    public function initRelation(array $models, $relation)
    {
        foreach ($models as $model) {
            $model->setRelation($relation, $this->getDefaultFor($model));
        }

        return $models;
    }

    /** @inheritDoc */
    public function match(array $models, Collection $results, $relation)
    {
        if ($this->customEagerMatchingCallbacks) {
            foreach ($this->customEagerMatchingCallbacks as $callback) {
                $models = $callback($models, $results, $relation, 'one');
            }

            return $models;
        }

        $dictionary = $this->buildDictionary($results);

        foreach ($models as $model) {
            if (isset($dictionary[$key = $model->getAttribute($this->localKey)])) {
                $model->setRelation(
                    $relation,
                    reset($dictionary[$key])
                );
            }
        }

        return $models;
    }

    /**
     * Make a new related instance for the given model.
     *
     * @param TDeclaringModel $parent
     * @return TRelatedModel
     */
    public function newRelatedInstanceFor(Model $parent)
    {
        return $this->related->newInstance();
    }
}
