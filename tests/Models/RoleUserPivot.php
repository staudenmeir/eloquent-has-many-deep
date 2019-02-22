<?php

namespace Tests\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;
use Staudenmeir\EloquentHasManyDeep\HasTableAlias;

class RoleUserPivot extends Pivot
{
    use HasTableAlias;

    protected $table = 'role_user';
}
