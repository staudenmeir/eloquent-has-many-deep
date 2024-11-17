<?php

namespace Staudenmeir\EloquentHasManyDeep\Eloquent\Relations\Traits;

use Illuminate\Database\Eloquent\Collection;

/**
 * @template TRelatedModel of \Illuminate\Database\Eloquent\Model
 * @template TDeclaringModel of \Illuminate\Database\Eloquent\Model
 */
trait HasEagerLoading
{
    /** @inheritDoc */
    public function addEagerConstraints(array $models)
    {
        if ($this->customEagerConstraintsCallback) {
            ($this->customEagerConstraintsCallback)($this->query, $models);
            return;
        }

        if ($this->hasLeadingCompositeKey()) {
            $this->addEagerConstraintsWithCompositeKey($models);
        } else {
            parent::addEagerConstraints($models);

            if (is_array($this->foreignKeys[0])) {
                /** @var string $foreignKey */
                $foreignKey = $this->foreignKeys[0][0];

                $this->query->where(
                    $this->throughParent->qualifyColumn($foreignKey),
                    '=',
                    $this->farParent->getMorphClass()
                );
            }
        }
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
}
