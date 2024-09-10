<?php

namespace Tests\IdeHelper\Models;

use Illuminate\Database\Eloquent\Model;
use Staudenmeir\EloquentHasManyDeep\HasManyDeep;
use Staudenmeir\EloquentHasManyDeep\HasOneDeep;
use Staudenmeir\EloquentHasManyDeep\HasRelationships;

class User extends Model
{
    use HasRelationships;

    /**
     * @return \Staudenmeir\EloquentHasManyDeep\HasOneDeep<\Tests\IdeHelper\Models\Comment, $this>
     */
    public function comment(): HasOneDeep
    {
        return $this->hasOneDeep(Comment::class, [Post::class]);
    }

    /**
     * @return \Staudenmeir\EloquentHasManyDeep\HasManyDeep<\Tests\IdeHelper\Models\Comment, $this>
     */
    public function comments(): HasManyDeep
    {
        return $this->hasManyDeep(Comment::class, [Post::class]);
    }
}
