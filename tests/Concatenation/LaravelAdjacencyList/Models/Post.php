<?php

namespace Tests\Concatenation\LaravelAdjacencyList\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Staudenmeir\LaravelCte\Eloquent\QueriesExpressions;

class Post extends Model
{
    use QueriesExpressions;
    use SoftDeletes;
}
