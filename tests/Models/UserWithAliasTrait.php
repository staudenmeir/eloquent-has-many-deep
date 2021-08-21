<?php

namespace Tests\Models;

use Staudenmeir\EloquentHasManyDeep\HasTableAlias;

class UserWithAliasTrait extends User
{
    use HasTableAlias;

    protected $table = 'users';
}
