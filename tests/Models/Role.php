<?php

namespace Tests\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;

class Role extends Model
{
    public function permissions(): HasMany
    {
        return $this->hasMany(Permission::class);
    }
}
