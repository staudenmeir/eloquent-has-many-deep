<?php

namespace Tests\Concatenation\LaravelAdjacencyList\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Staudenmeir\EloquentHasManyDeep\HasManyDeep;
use Staudenmeir\EloquentHasManyDeep\HasOneDeep;
use Staudenmeir\EloquentHasManyDeep\HasRelationships;
use Staudenmeir\EloquentHasManyDeep\HasTableAlias;
use Staudenmeir\LaravelAdjacencyList\Eloquent\HasRecursiveRelationships;

class User extends Model
{
    use HasRelationships;
    use HasRecursiveRelationships;
    use HasTableAlias;
    use SoftDeletes;

    public function ancestorPost(): HasOneDeep
    {
        return $this->hasOneDeepFromRelations(
            $this->ancestors(),
            (new self())->posts()
        );
    }

    public function ancestorPosts(): HasManyDeep
    {
        return $this->hasManyDeepFromRelations(
            $this->ancestors(),
            (new self())->posts()
        );
    }

    public function ancestorAndSelfPosts(): HasManyDeep
    {
        return $this->hasManyDeepFromRelations(
            $this->ancestorsAndSelf(),
            (new self())->posts()
        );
    }

    public function bloodlinePosts(): HasManyDeep
    {
        return $this->hasManyDeepFromRelations(
            $this->bloodline(),
            (new self())->posts()
        );
    }

    public function descendantPost(): HasOneDeep
    {
        return $this->hasOneDeepFromRelations(
            $this->descendants(),
            (new self())->posts()
        );
    }

    public function descendantPosts(): HasManyDeep
    {
        return $this->hasManyDeepFromRelations(
            $this->descendants(),
            (new self())->posts()
        );
    }

    public function descendantPostsAndSelf(): HasManyDeep
    {
        return $this->hasManyDeepFromRelations(
            $this->descendantsAndSelf(),
            (new self())->posts()
        );
    }

    public function posts()
    {
        return $this->hasManyOfDescendants(Post::class);
    }
}
