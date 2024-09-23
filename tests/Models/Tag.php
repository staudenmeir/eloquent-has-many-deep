<?php

namespace Tests\Models;

use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Staudenmeir\EloquentHasManyDeep\HasManyDeep;

class Tag extends Model
{
    public function comments(): HasManyDeep
    {
        return $this->hasManyDeep(
            Comment::class,
            ['taggables', Post::class],
            [null, 'id'],
            [null, ['taggable_type', 'taggable_id']]
        );
    }

    public function commentsFromRelations(): HasManyDeep
    {
        return $this->hasManyDeepFromRelations($this->posts(), (new Post())->comments());
    }

    public function posts(): MorphToMany
    {
        return $this->morphedByMany(Post::class, 'taggable');
    }
}
