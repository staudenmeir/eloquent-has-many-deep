<?php

namespace Tests\Models;

use Illuminate\Database\Eloquent\Model as Base;
use Staudenmeir\EloquentHasManyDeep\HasRelationships;

abstract class Model extends Base
{
    use HasRelationships;

    public $incrementing = false;

    public $timestamps = false;
}
