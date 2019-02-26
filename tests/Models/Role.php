<?php

namespace Tests\Models;

class Role extends Model
{
    protected $primaryKey = 'role_pk';

    public function permissions()
    {
        return $this->hasMany(Permission::class);
    }
}
