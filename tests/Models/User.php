<?php

namespace Tests\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Staudenmeir\EloquentHasManyDeep\HasTableAlias;

class User extends Model
{
    use HasTableAlias, SoftDeletes;

    protected $primaryKey = 'user_pk';

    public function likes()
    {
        return $this->hasManyDeep(Like::class, [Post::class], [null, ['likeable_type', 'likeable_id']]);
    }

    public function permissions()
    {
        return $this->hasManyDeep(Permission::class, ['role_user', Role::class]);
    }

    public function players()
    {
        return $this->hasManyDeep(self::class, [Club::class, Team::class]);
    }
}
