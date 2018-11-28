<?php

namespace Tests\Models;

class Role extends Model
{
    public $timestamps = true;

    protected $primaryKey = 'role_pk';

    public function permissions()
    {
        return $this->hasMany(Permission::class);
    }
}
