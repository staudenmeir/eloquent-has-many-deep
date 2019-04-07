<?php

namespace Tests\Models;

use Illuminate\Database\Eloquent\Relations\Pivot;
use Staudenmeir\EloquentHasManyDeep\HasTableAlias;

class RoleUser extends Pivot
{
    use HasTableAlias;
}
