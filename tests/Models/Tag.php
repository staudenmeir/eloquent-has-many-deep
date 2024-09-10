<?php

namespace Tests\Models;

use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Staudenmeir\EloquentHasManyDeep\HasManyDeep;

class Tag extends Model
{
    /**
     * @return \Staudenmeir\EloquentHasManyDeep\HasManyDeep<\Tests\Models\Comment, $this>
     */
    public function comments(): HasManyDeep
    {
        return $this->hasManyDeep(
            Comment::class,
            ['taggables', Post::class],
            [null, 'id'],
            [null, ['taggable_type', 'taggable_id']]
        );
    }

    /**
     * @return \Staudenmeir\EloquentHasManyDeep\HasManyDeep<\Tests\Models\Comment, $this>
     */
    public function commentsFromRelations(): HasManyDeep
    {
        return $this->hasManyDeepFromRelations($this->posts(), (new Post())->comments());
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphToMany<\Tests\Models\Post>
     */
    public function posts(): MorphToMany
    {
        return $this->morphedByMany(Post::class, 'taggable');
    }
}
