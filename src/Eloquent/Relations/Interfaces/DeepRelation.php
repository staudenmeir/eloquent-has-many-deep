<?php

namespace Staudenmeir\EloquentHasManyDeep\Eloquent\Relations\Interfaces;

use Illuminate\Database\Eloquent\Model;

/**
 * @template TRelatedModel of \Illuminate\Database\Eloquent\Model
 * @template TDeclaringModel of \Illuminate\Database\Eloquent\Model
 */
interface DeepRelation
{
    /**
     * Get the far parent model instance.
     *
     * @return TDeclaringModel
     */
    public function getFarParent(): Model;

    /**
     * Get the "through" parent model instances.
     *
     * @return list<\Illuminate\Database\Eloquent\Model>
     */
    public function getThroughParents(): array;

    /**
     * Get the foreign keys on the relationship.
     *
     * @return list<array{0: string, 1: string}|callable|string|\Staudenmeir\EloquentHasManyDeep\Eloquent\CompositeKey|null>
     */
    public function getForeignKeys(): array;

    /**
     * Get the local keys on the relationship.
     *
     * @return list<array{0: string, 1: string}|callable|string|\Staudenmeir\EloquentHasManyDeep\Eloquent\CompositeKey|null>
     */
    public function getLocalKeys(): array;
}
