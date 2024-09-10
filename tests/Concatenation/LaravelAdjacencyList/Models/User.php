<?php

namespace Tests\Concatenation\LaravelAdjacencyList\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Staudenmeir\EloquentHasManyDeep\HasManyDeep;
use Staudenmeir\EloquentHasManyDeep\HasOneDeep;
use Staudenmeir\EloquentHasManyDeep\HasRelationships;
use Staudenmeir\EloquentHasManyDeep\HasTableAlias;
use Staudenmeir\LaravelAdjacencyList\Eloquent\HasRecursiveRelationships;
use Staudenmeir\LaravelAdjacencyList\Eloquent\Relations\HasManyOfDescendants;

/**
 * @property-read \Tests\Concatenation\LaravelAdjacencyList\Models\Post $ancestorPost
 * @property-read \Tests\Concatenation\LaravelAdjacencyList\Models\Post $descendantPost
 */
class User extends Model
{
    use HasRelationships;
    use HasRecursiveRelationships;
    use HasTableAlias;
    use SoftDeletes;

    /**
     * @return \Staudenmeir\EloquentHasManyDeep\HasOneDeep<\Tests\Concatenation\LaravelAdjacencyList\Models\Post, $this>
     */
    public function ancestorPost(): HasOneDeep
    {
        return $this->hasOneDeepFromRelations(
            $this->ancestors(),
            (new static())->posts()
        );
    }

    /**
     * @return \Staudenmeir\EloquentHasManyDeep\HasManyDeep<\Tests\Concatenation\LaravelAdjacencyList\Models\Post, $this>
     */
    public function ancestorPosts(): HasManyDeep
    {
        return $this->hasManyDeepFromRelations(
            $this->ancestors(),
            (new static())->posts()
        );
    }

    /**
     * @return \Staudenmeir\EloquentHasManyDeep\HasManyDeep<\Tests\Concatenation\LaravelAdjacencyList\Models\Post, $this>
     */
    public function ancestorAndSelfPosts(): HasManyDeep
    {
        return $this->hasManyDeepFromRelations(
            $this->ancestorsAndSelf(),
            (new static())->posts()
        );
    }

    /**
     * @return \Staudenmeir\EloquentHasManyDeep\HasManyDeep<\Tests\Concatenation\LaravelAdjacencyList\Models\Post, $this>
     */
    public function bloodlinePosts(): HasManyDeep
    {
        return $this->hasManyDeepFromRelations(
            $this->bloodline(),
            (new static())->posts()
        );
    }

    /**
     * @return \Staudenmeir\EloquentHasManyDeep\HasOneDeep<\Tests\Concatenation\LaravelAdjacencyList\Models\Post, $this>
     */
    public function descendantPost(): HasOneDeep
    {
        return $this->hasOneDeepFromRelations(
            $this->descendants(),
            (new static())->posts()
        );
    }

    /**
     * @return \Staudenmeir\EloquentHasManyDeep\HasManyDeep<\Tests\Concatenation\LaravelAdjacencyList\Models\Post, $this>
     */
    public function descendantPosts(): HasManyDeep
    {
        return $this->hasManyDeepFromRelations(
            $this->descendants(),
            (new static())->posts()
        );
    }

    /**
     * @return \Staudenmeir\EloquentHasManyDeep\HasManyDeep<\Tests\Concatenation\LaravelAdjacencyList\Models\Post, $this>
     */
    public function descendantPostsAndSelf(): HasManyDeep
    {
        return $this->hasManyDeepFromRelations(
            $this->descendantsAndSelf(),
            (new static())->posts()
        );
    }

    /**
     * @return \Staudenmeir\LaravelAdjacencyList\Eloquent\Relations\HasManyOfDescendants<\Tests\Concatenation\LaravelAdjacencyList\Models\Post>
     */
    public function posts(): HasManyOfDescendants
    {
        return $this->hasManyOfDescendants(Post::class);
    }
}
