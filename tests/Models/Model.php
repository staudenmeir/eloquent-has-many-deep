<?php

namespace Tests\Models;

use Staudenmeir\EloquentHasManyDeep\HasRelationships;

abstract class Model extends \Illuminate\Database\Eloquent\Model
{
    use HasRelationships;

    public $timestamps = false;
}
