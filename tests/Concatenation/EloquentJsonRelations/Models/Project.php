<?php

namespace Tests\Concatenation\EloquentJsonRelations\Models;

use Staudenmeir\EloquentHasManyDeep\HasManyDeep;
use Staudenmeir\EloquentHasManyDeep\HasRelationships;
use Staudenmeir\EloquentJsonRelations\JsonKey;

/**
 * @property-read \Illuminate\Database\Eloquent\Relations\Pivot $pivot
 */
class Project extends Model
{
    use HasRelationships;

    public function roles(): HasManyDeep
    {
        return $this->hasManyThroughJson(
            Role::class,
            User::class,
            'id',
            'id',
            'user_id',
            new JsonKey('options->role_ids')
        );
    }

    public function roles2(): HasManyDeep
    {
        return $this->hasManyThroughJson(
            Role::class,
            User::class,
            'id',
            'id',
            'user_id',
            new JsonKey('options->roles[]->role->id')
        );
    }
}
