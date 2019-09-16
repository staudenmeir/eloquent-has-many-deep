<?php

namespace Tests\Models;

class Role extends Model
{
    public function permissions()
    {
        return $this->hasMany(Permission::class);
    }
}
