<?php

namespace Staudenmeir\EloquentHasManyDeep\Types;

use Staudenmeir\EloquentHasManyDeep\Types\Models\Comment;
use Staudenmeir\EloquentHasManyDeep\Types\Models\Country;
use Staudenmeir\EloquentHasManyDeep\Types\Models\Post;
use Staudenmeir\EloquentHasManyDeep\Types\Models\User;

use function PHPStan\Testing\assertType;

function test(Country $country): void
{
    assertType(
        'Staudenmeir\EloquentHasManyDeep\HasManyDeep<Staudenmeir\EloquentHasManyDeep\Types\Models\Comment, Staudenmeir\EloquentHasManyDeep\Types\Models\Country>',
        $country->hasManyDeep(Comment::class, [User::class, Post::class])
    );

    assertType(
        'Staudenmeir\EloquentHasManyDeep\HasOneDeep<Staudenmeir\EloquentHasManyDeep\Types\Models\Comment, Staudenmeir\EloquentHasManyDeep\Types\Models\Country>',
        $country->hasOneDeep(Comment::class, [User::class, Post::class])
    );

    assertType(
        'Staudenmeir\EloquentHasManyDeep\HasManyDeep<Illuminate\Database\Eloquent\Model, Staudenmeir\EloquentHasManyDeep\Types\Models\Country>',
        $country->hasManyDeepFromRelations(
            (new Country())->hasManyThrough(Post::class, User::class),
            (new Post())->hasMany(Comment::class)
        )
    );

    assertType(
        'Staudenmeir\EloquentHasManyDeep\HasOneDeep<Illuminate\Database\Eloquent\Model, Staudenmeir\EloquentHasManyDeep\Types\Models\Country>',
        $country->hasOneDeepFromRelations(
            (new Country())->hasManyThrough(Post::class, User::class),
            (new Post())->hasMany(Comment::class)
        )
    );

    assertType(
        'Staudenmeir\EloquentHasManyDeep\HasManyDeep<Staudenmeir\EloquentHasManyDeep\Types\Models\Country, Staudenmeir\EloquentHasManyDeep\Types\Models\Comment>',
        $country->hasManyDeepFromReverse((new Country())->hasManyDeep(Comment::class, [User::class, Post::class]))
    );

    assertType(
        'Staudenmeir\EloquentHasManyDeep\HasOneDeep<Staudenmeir\EloquentHasManyDeep\Types\Models\Country, Staudenmeir\EloquentHasManyDeep\Types\Models\Comment>',
        $country->hasOneDeepFromReverse((new Country())->hasManyDeep(Comment::class, [User::class, Post::class]))
    );
}
