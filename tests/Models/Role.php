<?php

namespace Tests\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;

class Role extends Model
{
    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<\Tests\Models\Permission>
     */
    public function permissions(): HasMany
    {
        return $this->hasMany(Permission::class);
    }
}
