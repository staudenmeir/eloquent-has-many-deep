<?php

namespace Tests\Concatenation\EloquentJsonRelations\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Staudenmeir\EloquentHasManyDeep\HasManyDeep;
use Staudenmeir\EloquentHasManyDeep\HasRelationships;
use Staudenmeir\EloquentJsonRelations\JsonKey;

class Role extends Model
{
    use HasRelationships;

    public function permissions(): HasMany
    {
        return $this->hasMany(Permission::class);
    }

    public function projects(): HasManyDeep
    {
        return $this->hasManyThroughJson(Project::class, User::class, new JsonKey('options->role_ids'));
    }

    public function projects2(): HasManyDeep
    {
        return $this->hasManyThroughJson(Project::class, User::class, new JsonKey('options->roles[]->role->id'));
    }
}
