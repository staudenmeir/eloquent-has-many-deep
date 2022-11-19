<?php

namespace Tests\Concatenation\EloquentJsonRelations\Models;

use Staudenmeir\EloquentHasManyDeep\HasManyDeep;
use Staudenmeir\EloquentHasManyDeep\HasRelationships;
use Staudenmeir\EloquentJsonRelations\JsonKey;

class Role extends Model
{
    use HasRelationships;

    public function projects(): HasManyDeep
    {
        return $this->hasManyThroughJson(Project::class, User::class, new JsonKey('options->role_ids'));
    }

    public function projects2(): HasManyDeep
    {
        return $this->hasManyThroughJson(Project::class, User::class, new JsonKey('options->roles[]->role->id'));
    }
}
