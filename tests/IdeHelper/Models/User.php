<?php

namespace Tests\IdeHelper\Models;

use Illuminate\Database\Eloquent\Model;
use Staudenmeir\EloquentHasManyDeep\HasManyDeep;
use Staudenmeir\EloquentHasManyDeep\HasOneDeep;
use Staudenmeir\EloquentHasManyDeep\HasRelationships;

class User extends Model
{
    use HasRelationships;

    public function comment(): HasOneDeep
    {
        return $this->hasOneDeep(Comment::class, [Post::class]);
    }

    public function comments(): HasManyDeep
    {
        return $this->hasManyDeep(Comment::class, [Post::class]);
    }
}
