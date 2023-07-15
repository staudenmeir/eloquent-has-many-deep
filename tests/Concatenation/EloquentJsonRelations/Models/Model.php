<?php

namespace Tests\Concatenation\EloquentJsonRelations\Models;

use Illuminate\Database\Eloquent\Model as Base;
use Staudenmeir\EloquentJsonRelations\HasJsonRelationships;

/**
 * @method static static create(array $attributes = [])
 * @method static static find(mixed $id, array $columns = ['*'])
 * @method static static first(array $columns = ['*'])
 * @method static \Illuminate\Database\Eloquent\Builder has(mixed $relation, string $operator = '>=', int $count = 1, string $boolean = 'and', \Closure $callback = null)
 * @method static \Illuminate\Database\Eloquent\Builder withCount(mixed $relations)
 */
abstract class Model extends Base
{
    use HasJsonRelationships;

    public $timestamps = false;
}
