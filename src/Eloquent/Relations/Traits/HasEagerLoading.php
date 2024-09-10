<?php

namespace Staudenmeir\EloquentHasManyDeep\Eloquent\Relations\Traits;

use Illuminate\Database\Eloquent\Collection;

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
                $this->query->where(
                    $this->throughParent->qualifyColumn($this->foreignKeys[0][0]),
                    '=',
                    $this->farParent->getMorphClass()
                );
            }
        }
    }

    /** @inheritDoc */
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

        return parent::match($models, $results, $relation);
    }
}
