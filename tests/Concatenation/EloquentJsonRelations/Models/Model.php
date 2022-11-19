<?php

namespace Tests\Concatenation\EloquentJsonRelations\Models;

use Illuminate\Database\Eloquent\Model as Base;
use Staudenmeir\EloquentJsonRelations\HasJsonRelationships;

abstract class Model extends Base
{
    use HasJsonRelationships;

    public $timestamps = false;
}
