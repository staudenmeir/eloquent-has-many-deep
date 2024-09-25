<?php

namespace Staudenmeir\EloquentHasManyDeep\Eloquent\Relations\Traits;

use Illuminate\Database\Eloquent\Relations\Pivot;

trait IsConcatenable
{
    /**
     * Append the relation's through parents, foreign and local keys to a deep relationship.
     *
     * @param non-empty-list<string> $through
     * @param non-empty-list<array{0: string, 1: string}|callable|string|\Staudenmeir\EloquentHasManyDeep\Eloquent\CompositeKey|null> $foreignKeys
     * @param non-empty-list<array{0: string, 1: string}|callable|string|\Staudenmeir\EloquentHasManyDeep\Eloquent\CompositeKey|null> $localKeys
     * @param int $position
     * @return array{0: non-empty-list<string>,
     *     1: non-empty-list<array{0: string, 1: string}|callable|string|\Staudenmeir\EloquentHasManyDeep\Eloquent\CompositeKey|null>,
     *     2: non-empty-list<array{0: string, 1: string}|callable|string|\Staudenmeir\EloquentHasManyDeep\Eloquent\CompositeKey|null>}
     */
    public function appendToDeepRelationship(array $through, array $foreignKeys, array $localKeys, int $position): array
    {
        foreach ($this->throughParents as $throughParent) {
            $segments = explode(' as ', $throughParent->getTable());

            $class = get_class($throughParent);

            if (isset($segments[1])) {
                $class .= ' as '.$segments[1];
            } elseif ($throughParent instanceof Pivot) {
                $class = $throughParent->getTable();
            }

            $through[] = $class;
        }

        $foreignKeys = array_merge($foreignKeys, $this->foreignKeys);

        $localKeys = array_merge($localKeys, $this->localKeys);

        return [$through, $foreignKeys, $localKeys];
    }
}
