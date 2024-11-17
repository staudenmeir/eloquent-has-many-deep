<?php

namespace Staudenmeir\EloquentHasManyDeep\Eloquent\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Staudenmeir\EloquentHasManyDeep\HasManyDeep;
use Staudenmeir\EloquentHasManyDeep\HasOneDeep;

/**
 * @phpstan-ignore trait.unused
 */
trait ReversesRelationships
{
    /**
     * Define a has-many-deep relationship by reversing an existing deep relationship.
     *
     * @template TRelatedModel of \Illuminate\Database\Eloquent\Model
     * @template TDeclaringModel of \Illuminate\Database\Eloquent\Model
     *
     * @param \Staudenmeir\EloquentHasManyDeep\HasManyDeep<TDeclaringModel, TRelatedModel> $relation
     * @return \Staudenmeir\EloquentHasManyDeep\HasManyDeep<TRelatedModel, TDeclaringModel>
     */
    public function hasManyDeepFromReverse(HasManyDeep $relation): HasManyDeep
    {
        return $this->hasManyDeep(
            ...$this->hasOneOrManyDeepFromReverse($relation)
        );
    }

    /**
     * Define a has-one-deep relationship by reversing an existing deep relationship.
     *
     * @template TRelatedModel of \Illuminate\Database\Eloquent\Model
     * @template TDeclaringModel of \Illuminate\Database\Eloquent\Model
     *
     * @param \Staudenmeir\EloquentHasManyDeep\HasManyDeep<TDeclaringModel, TRelatedModel> $relation
     * @return \Staudenmeir\EloquentHasManyDeep\HasOneDeep<TRelatedModel, TDeclaringModel>
     */
    public function hasOneDeepFromReverse(HasManyDeep $relation): HasOneDeep
    {
        return $this->hasOneDeep(
            ...$this->hasOneOrManyDeepFromReverse($relation)
        );
    }

    /**
     * Prepare a has-one-deep or has-many-deep relationship by reversing an existing deep relationship.
     *
     * @template TRelatedModel of \Illuminate\Database\Eloquent\Model
     * @template TDeclaringModel of \Illuminate\Database\Eloquent\Model
     *
     * @param \Staudenmeir\EloquentHasManyDeep\HasManyDeep<TRelatedModel, TDeclaringModel> $relation
     * @return array{0: class-string<TDeclaringModel>,
     *     1: list<string>,
     *     2: list<callable|string|\Staudenmeir\EloquentHasManyDeep\Eloquent\CompositeKey>,
     *     3: list<callable|string|\Staudenmeir\EloquentHasManyDeep\Eloquent\CompositeKey>}
     */
    protected function hasOneOrManyDeepFromReverse(HasManyDeep $relation): array
    {
        $related = $relation->getFarParent()::class;

        $through = [];

        foreach (array_reverse($relation->getThroughParents()) as $throughParent) {
            $through[] = $this->hasOneOrManyDeepFromReverseThroughClass($throughParent);
        }

        $foreignKeys = array_reverse(
            $relation->getLocalKeys()
        );

        $localKeys = array_reverse(
            $relation->getForeignKeys()
        );

        return [$related, $through, $foreignKeys, $localKeys];
    }

    /**
     * Prepare a has-one-deep or has-many-deep relationship through class.
     *
     * @param \Illuminate\Database\Eloquent\Model $throughParent
     * @return string
     */
    protected function hasOneOrManyDeepFromReverseThroughClass(Model $throughParent): string
    {
        $table = $throughParent->getTable();

        $segments = preg_split('/\s+as\s+/i', $table);

        if ($throughParent instanceof Pivot) {
            if (isset($segments[1])) {
                $class = $throughParent::class . " as $segments[1]";
            } else {
                $class = $table;
            }
        } else {
            $class = $throughParent::class;

            if (isset($segments[1])) {
                $class .= " as $segments[1]";
            } elseif ($table !== (new $throughParent())->getTable()) {
                $class .= " from $table";
            }
        }

        return $class;
    }
}
