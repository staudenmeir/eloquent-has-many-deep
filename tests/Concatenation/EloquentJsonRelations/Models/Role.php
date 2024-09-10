<?php

namespace Tests\Concatenation\EloquentJsonRelations\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Staudenmeir\EloquentHasManyDeep\HasManyDeep;
use Staudenmeir\EloquentHasManyDeep\HasRelationships;
use Staudenmeir\EloquentJsonRelations\JsonKey;

/**
 * @property-read \Illuminate\Database\Eloquent\Relations\Pivot $pivot
 */
class Role extends Model
{
    use HasRelationships;

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<\Tests\Concatenation\EloquentJsonRelations\Models\Permission>
     */
    public function permissions(): HasMany
    {
        return $this->hasMany(Permission::class);
    }

    /**
     * @return \Staudenmeir\EloquentHasManyDeep\HasManyDeep<\Tests\Concatenation\EloquentJsonRelations\Models\Project, $this>
     */
    public function projects(): HasManyDeep
    {
        return $this->hasManyThroughJson(Project::class, User::class, new JsonKey('options->role_ids'));
    }

    /**
     * @return \Staudenmeir\EloquentHasManyDeep\HasManyDeep<\Tests\Concatenation\EloquentJsonRelations\Models\Project, $this>
     */
    public function projects2(): HasManyDeep
    {
        return $this->hasManyThroughJson(Project::class, User::class, new JsonKey('options->roles[]->role->id'));
    }
}
