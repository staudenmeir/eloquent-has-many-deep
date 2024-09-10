<?php

namespace Tests\Concatenation\EloquentJsonRelations\Models;

use Staudenmeir\EloquentHasManyDeep\HasManyDeep;
use Staudenmeir\EloquentHasManyDeep\HasRelationships;
use Staudenmeir\EloquentJsonRelations\Relations\BelongsToJson;

class User extends Model
{
    use HasRelationships;

    protected $casts = [
        'options' => 'json',
    ];

    /**
     * @return \Staudenmeir\EloquentHasManyDeep\HasManyDeep<\Tests\Concatenation\EloquentJsonRelations\Models\Permission, $this>
     */
    public function permissions(): HasManyDeep
    {
        return $this->hasManyDeepFromRelations($this->roles(), (new Role())->permissions());
    }

    /**
     * @return \Staudenmeir\EloquentHasManyDeep\HasManyDeep<\Tests\Concatenation\EloquentJsonRelations\Models\Permission, $this>
     */
    public function permissions2(): HasManyDeep
    {
        return $this->hasManyDeepFromRelations($this->roles2(), (new Role())->permissions());
    }

    public function roles(): BelongsToJson
    {
        return $this->belongsToJson(Role::class, 'options->role_ids');
    }

    public function roles2(): BelongsToJson
    {
        return $this->belongsToJson(Role::class, 'options->roles[]->role->id');
    }
}
