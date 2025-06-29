<?php

namespace Staudenmeir\EloquentHasManyDeep;

use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Concerns\SupportsDefaultModels;
use Illuminate\Database\Eloquent\Relations\HasOneThrough;
use Staudenmeir\EloquentHasManyDeep\Eloquent\Relations\Interfaces\DeepRelation;
use Staudenmeir\EloquentHasManyDeep\Eloquent\Relations\Traits\IsOneOrManyDeepRelation;
use Staudenmeir\EloquentHasManyDeepContracts\Interfaces\ConcatenableRelation;

/**
 * @template TRelatedModel of \Illuminate\Database\Eloquent\Model
 * @template TDeclaringModel of \Illuminate\Database\Eloquent\Model
 *
 * @extends \Illuminate\Database\Eloquent\Relations\HasOneThrough<TRelatedModel, \Illuminate\Database\Eloquent\Model, TDeclaringModel>
 * @implements \Staudenmeir\EloquentHasManyDeep\Eloquent\Relations\Interfaces\DeepRelation<TRelatedModel, TDeclaringModel>
 */
class HasOneDeep extends HasOneThrough implements ConcatenableRelation, DeepRelation
{
    /** @use \Staudenmeir\EloquentHasManyDeep\Eloquent\Relations\Traits\IsOneOrManyDeepRelation<TRelatedModel, TDeclaringModel> */
    use IsOneOrManyDeepRelation;
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
