# Eloquent HasManyDeep

[![CI](https://github.com/staudenmeir/eloquent-has-many-deep/actions/workflows/ci.yml/badge.svg)](https://github.com/staudenmeir/eloquent-has-many-deep/actions/workflows/ci.yml)
[![Code Coverage](https://codecov.io/gh/staudenmeir/eloquent-has-many-deep/graph/badge.svg?token=H59fIf4mG6)](https://codecov.io/gh/staudenmeir/eloquent-has-many-deep)
[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/staudenmeir/eloquent-has-many-deep/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/staudenmeir/eloquent-has-many-deep/?branch=master)
[![Latest Stable Version](https://poser.pugx.org/staudenmeir/eloquent-has-many-deep/v/stable)](https://packagist.org/packages/staudenmeir/eloquent-has-many-deep)
[![Total Downloads](https://poser.pugx.org/staudenmeir/eloquent-has-many-deep/downloads)](https://packagist.org/packages/staudenmeir/eloquent-has-many-deep/stats)
[![License](https://poser.pugx.org/staudenmeir/eloquent-has-many-deep/license)](https://github.com/staudenmeir/eloquent-has-many-deep/blob/master/LICENSE)

This extended version of `HasManyThrough` allows relationships with unlimited intermediate models.  
It supports [many-to-many](#manytomany) and [polymorphic](#morphmany) relationships and all their possible combinations.
It also supports some [third-party packages](#third-party-packages).

Supports Laravel 5.5+.

## Installation

    composer require staudenmeir/eloquent-has-many-deep:"^1.7"

Use this command if you are in PowerShell on Windows (e.g. in VS Code):

    composer require staudenmeir/eloquent-has-many-deep:"^^^^1.7"

## Versions

| Laravel | Package |
|:--------|:--------|
| 11.x    | 1.20    |
| 10.x    | 1.18    |
| 9.x     | 1.17    |
| 8.x     | 1.14    |
| 7.x     | 1.12    |
| 6.x     | 1.11    |
| 5.8     | 1.8     |
| 5.5–5.7 | 1.7     |

## Usage

The package offers two ways of defining deep relationships:  
You can concatenate [existing relationships](#concatenating-existing-relationships) or specify the intermediate models,
foreign and local keys [manually](#defining-relationships-manually).

- [Concatenating Existing Relationships](#concatenating-existing-relationships)
    - [Constraints](#constraints)
    - [Third-Party Packages](#third-party-packages)
- [Defining Relationships Manually](#defining-relationships-manually)
    - [HasMany](#hasmany)
    - [ManyToMany](#manytomany)
    - [MorphMany](#morphmany)
    - [MorphToMany](#morphtomany)
    - [MorphedByMany](#morphedbymany)
    - [BelongsTo](#belongsto)
    - [HasOneDeep](#hasonedeep)
    - [Composite Keys](#composite-keys)
- [Intermediate and Pivot Data](#intermediate-and-pivot-data)
- [Intermediate and Pivot Constraints](#intermediate-and-pivot-constraints)
- [Table Aliases](#table-aliases)
- [Soft Deleting](#soft-deleting)
- [Getting Unique Results](#getting-unique-results)
- [Reversing Relationships](#reversing-relationships)
- [IDE Helper](#ide-helper)

### Concatenating Existing Relationships

Consider this [example](https://laravel.com/docs/eloquent-relationships#has-many-through) from the Laravel documentation
with an additional level:  
`Country` → has many → `User` → has many → `Post` → has many → `Comment`

You can define a `HasManyDeep` relationship by concatenating existing relationships:

```php
class Country extends Model
{
    use \Staudenmeir\EloquentHasManyDeep\HasRelationships;

    public function comments()
    {
        return $this->hasManyDeepFromRelations($this->posts(), (new Post())->comments());
    }

    public function posts()
    {
        return $this->hasManyThrough(Post::class, User::class);
    }
}

class Post extends Model
{
    public function comments()
    {
        return $this->hasMany(Comment::class);
    }
}
```

Define a `HasOneDeep` relationship with `hasOneDeepFromRelations()` if you only want to retrieve a single related
instance.

#### Constraints

By default, constraints from the concatenated relationships are not transferred to the new deep relationship.
Use `hasManyDeepFromRelationsWithConstraints()` with the relationships as callable arrays to apply these constraints:

```php
class Country extends Model
{
    use \Staudenmeir\EloquentHasManyDeep\HasRelationships;

    public function comments()
    {
        return $this->hasManyDeepFromRelationsWithConstraints([$this, 'posts'], [new Post(), 'comments']);
    }

    public function posts()
    {
        return $this->hasManyThrough(Post::class, User::class)->where('posts.published', true);
    }
}

class Post extends Model
{
    public function comments()
    {
        return $this->hasMany(Comment::class)->withTrashed();
    }
}
```

Make sure to qualify the constraints' column names if they appear in multiple tables:  
`->where('posts.published', true)` instead of `->where('published', true)`

#### Third-Party Packages

Besides native Laravel relationships, you can also concatenate relationships from these third-party packages:

- https://github.com/korridor/laravel-has-many-merged: `HasManyMerged`
- https://github.com/staudenmeir/eloquent-json-relations: `BelongsToJson`, `HasManyJson`, `HasManyThroughJson`
- https://github.com/staudenmeir/laravel-adjacency-list: [Tree](https://github.com/staudenmeir/laravel-adjacency-list?tab=readme-ov-file#deep-relationship-concatenation)
  & [Graph](https://github.com/staudenmeir/laravel-adjacency-list?tab=readme-ov-file#graphs-deep-relationship-concatenation)
  relationships
- https://github.com/topclaudy/compoships: `BelongsTo`, `HasMany`, `HasOne`

### Defining Relationships Manually

If you don't have all the necessary existing relationships to concatenate them, you can also define a deep relationship
manually by specifying the intermediate models, foreign and local keys.

#### HasMany

Consider this [example](https://laravel.com/docs/eloquent-relationships#has-many-through) from the Laravel documentation
with an additional level:  
`Country` → has many → `User` → has many → `Post` → has many → `Comment`

```php
class Country extends Model
{
    use \Staudenmeir\EloquentHasManyDeep\HasRelationships;

    public function comments()
    {
        return $this->hasManyDeep(Comment::class, [User::class, Post::class]);
    }
}
```

Just like with `hasManyThrough()`, the first argument of `hasManyDeep()` is the related model. The second argument is an
array of intermediate models, from the far parent (the model where the relationship is defined) to the related model.

By default, `hasManyDeep()` uses the Eloquent conventions for foreign and local keys. You can also specify custom
foreign keys as the third argument and custom local keys as the fourth argument:

```php
class Country extends Model
{
    use \Staudenmeir\EloquentHasManyDeep\HasRelationships;

    public function comments()
    {
        return $this->hasManyDeep(
            Comment::class,
            [User::class, Post::class], // Intermediate models, beginning at the far parent (Country).
            [
               'country_id', // Foreign key on the "users" table.
               'user_id',    // Foreign key on the "posts" table.
               'post_id'     // Foreign key on the "comments" table.
            ],
            [
              'id', // Local key on the "countries" table.
              'id', // Local key on the "users" table.
              'id'  // Local key on the "posts" table.
            ]
        );
    }
}
```

You can use `null` placeholders for default keys:

```php
class Country extends Model
{
    use \Staudenmeir\EloquentHasManyDeep\HasRelationships;

    public function comments()
    {
        return $this->hasManyDeep(Comment::class, [User::class, Post::class], [null, 'custom_user_id']);
    }
}
```

#### ManyToMany

You can include `ManyToMany` relationships in the intermediate path.

##### ManyToMany → HasMany

Consider this [example](https://laravel.com/docs/eloquent-relationships#many-to-many) from the Laravel documentation
with an additional `HasMany` level:  
`User` → many to many → `Role` → has many → `Permission`

Add the pivot tables to the intermediate models:

```php
class User extends Model
{
    use \Staudenmeir\EloquentHasManyDeep\HasRelationships;

    public function permissions()
    {
        return $this->hasManyDeep(Permission::class, ['role_user', Role::class]);
    }
}
```

If you specify custom keys, remember to swap the foreign and local key on the "right" side of the pivot table:

```php
class User extends Model
{
    use \Staudenmeir\EloquentHasManyDeep\HasRelationships;

    public function permissions()
    {
        return $this->hasManyDeep(
            Permission::class,
            ['role_user', Role::class], // Intermediate models and tables, beginning at the far parent (User).
            [           
               'user_id', // Foreign key on the "role_user" table.
               'id',      // Foreign key on the "roles" table (local key).
               'role_id'  // Foreign key on the "permissions" table.
            ],
            [          
              'id',      // Local key on the "users" table.
              'role_id', // Local key on the "role_user" table (foreign key).
              'id'       // Local key on the "roles" table.
            ]
        );
    }
}
```

##### ManyToMany → ManyToMany

Consider this [example](https://laravel.com/docs/eloquent-relationships#many-to-many) from the Laravel documentation
with an additional `ManyToMany` level:  
`User` → many to many → `Role` → many to many → `Permission`

Add the pivot table to the intermediate models:

```php
class User extends Model
{
    use \Staudenmeir\EloquentHasManyDeep\HasRelationships;

    public function permissions()
    {
        return $this->hasManyDeep(Permission::class, ['role_user', Role::class, 'permission_role']);
    }
}
```

#### MorphMany

You can include `MorphMany` relationships in the intermediate path.

Consider this [example](https://laravel.com/docs/eloquent-relationships#polymorphic-relations) from the Laravel
documentation with an additional level:  
`User` → has many → `Post` → morph many → `Comment`

Specify the polymorphic foreign keys as an array, starting with the `*_type` column:

```php
class User extends Model
{
    use \Staudenmeir\EloquentHasManyDeep\HasRelationships;

    public function postComments()
    {
        return $this->hasManyDeep(
            Comment::class,
            [Post::class],
            [null, ['commentable_type', 'commentable_id']]
        );
    }
}
```

#### MorphToMany

You can include `MorphToMany` relationships in the intermediate path.

Consider this [example](https://laravel.com/docs/eloquent-relationships#many-to-many-polymorphic-relations) from the
Laravel documentation with an additional level:    
`User` → has many → `Post` → morph to many → `Tag`

Add the pivot table to the intermediate models and specify the polymorphic foreign keys as an array, starting with
the `*_type` column:

```php
class User extends Model
{
    use \Staudenmeir\EloquentHasManyDeep\HasRelationships;

    public function postTags()
    {
        return $this->hasManyDeep(
            Tag::class,
            [Post::class, 'taggables'],
            [null, ['taggable_type', 'taggable_id'], 'id'],
            [null, null, 'tag_id']
        );
    }
}
```

Remember to swap the foreign and local key on the "right" side of the pivot table:

#### MorphedByMany

You can include `MorphedByMany` relationships in the intermediate path.

Consider this [example](https://laravel.com/docs/eloquent-relationships#many-to-many-polymorphic-relations) from the
Laravel documentation with an additional level:  
`Tag` → morphed by many → `Post` → has many → `Comment`

Add the pivot table to the intermediate models and specify the polymorphic local keys as an array, starting with
the `*_type` column:

```php
class Tag extends Model
{
    use \Staudenmeir\EloquentHasManyDeep\HasRelationships;

    public function postComments()
    {
        return $this->hasManyDeep(
            Comment::class,
            ['taggables', Post::class],
            [null, 'id'],
            [null, ['taggable_type', 'taggable_id']]
        );
    }
}
```

#### BelongsTo

You can include `BelongsTo` relationships in the intermediate path:  
`Tag` → morphed by many → `Post` → belongs to → `User`

Swap the foreign and local key:

```php
class Tag extends Model
{
    use \Staudenmeir\EloquentHasManyDeep\HasRelationships;

    public function postAuthors()
    {
        return $this->hasManyDeep(
            User::class,
            ['taggables', Post::class],
            [null, 'id', 'id'],
            [null, ['taggable_type', 'taggable_id'], 'user_id']
        );
    }
}
```

#### HasOneDeep

Define a `HasOneDeep` relationship if you only want to retrieve a single related instance:

```php
class Country extends Model
{
    use \Staudenmeir\EloquentHasManyDeep\HasRelationships;

    public function latestComment()
    {
        return $this->hasOneDeep(Comment::class, [User::class, Post::class])
            ->latest('comments.created_at');
    }
}
```

#### Composite Keys

If multiple columns need to match between two tables, you can define a composite key with the `CompositeKey` class.

Consider this [example](https://github.com/topclaudy/compoships#example) from the `compoships` documentation with an
additional level:  
`User` → has many (matching `team_id` & `category_id`) → `Task` → belongs to → `Project`

```php
use Staudenmeir\EloquentHasManyDeep\Eloquent\CompositeKey;

class User extends Model
{
    use \Staudenmeir\EloquentHasManyDeep\HasRelationships;

    public function projects()
    {
        return $this->hasManyDeep(
            Project::class,
            [Task::class],
            [new CompositeKey('team_id', 'category_id'), 'id'],
            [new CompositeKey('team_id', 'category_id'), 'project_id']
        );
    }
}
```

### Intermediate and Pivot Data

Use `withIntermediate()` to retrieve attributes from intermediate tables:

```php
public function comments()
{
    return $this->hasManyDeep(Comment::class, [User::class, Post::class])
        ->withIntermediate(Post::class);
}

foreach ($country->comments as $comment) {
    // $comment->post->title
}
```

By default, this will retrieve all the table's columns. Be aware that this executes a separate query to get the list of
columns.

You can specify the selected columns as the second argument:

```php
public function comments()
{
    return $this->hasManyDeep(Comment::class, [User::class, Post::class])
        ->withIntermediate(Post::class, ['id', 'title']);
}
```

As the third argument, you can specify a custom accessor:

```php
public function comments()
{
    return $this->hasManyDeep(Comment::class, [User::class, Post::class])
        ->withIntermediate(Post::class, ['id', 'title'], 'accessor');
}

foreach ($country->comments as $comment) {
    // $comment->accessor->title
}
```

If you retrieve data from multiple tables, you can use nested accessors:

```php
public function comments()
{
    return $this->hasManyDeep(Comment::class, [User::class, Post::class])
        ->withIntermediate(Post::class)
        ->withIntermediate(User::class, ['*'], 'post.user');
}

foreach ($country->comments as $comment) {
    // $comment->post->title
    // $comment->post->user->name
}
```

Use `withPivot()` for the pivot tables of `BelongsToMany` and `MorphToMany`/`MorphedByMany` relationships:

```php
public function permissions()
{
    return $this->hasManyDeep(Permission::class, ['role_user', Role::class])
        ->withPivot('role_user', ['expires_at']);
}

foreach ($user->permissions as $permission) {
    // $permission->role_user->expires_at
}
```

You can specify a custom pivot model as the third argument and a custom accessor as the fourth:

```php
public function permissions()
{
    return $this->hasManyDeep(Permission::class, ['role_user', Role::class])
        ->withPivot('role_user', ['expires_at'], RoleUser::class, 'pivot');
}

foreach ($user->permissions as $permission) {
    // $permission->pivot->expires_at
}
```

### Intermediate and Pivot Constraints

You can apply constraints on intermediate and pivot tables:

```php
class Country extends Model
{
    use \Staudenmeir\EloquentHasManyDeep\HasRelationships;

    public function comments()
    {
        return $this->hasManyDeep(Comment::class, [User::class, Post::class]);
    }
}

$commentsFromActiveUsers = $country->comments()->where('users.active', true)->get();
```

### Table Aliases

If your relationship path contains the same model multiple times, you can specify a table alias:

```php
class Post extends Model
{
    use \Staudenmeir\EloquentHasManyDeep\HasRelationships;

    public function commentReplies()
    {
        return $this->hasManyDeep(Comment::class, ['App\Comment as alias'], [null, 'parent_id']);
    }
}
```

Use the `HasTableAlias` trait in the models you are aliasing:

```php
class Comment extends Model
{
    use \Staudenmeir\EloquentHasManyDeep\HasTableAlias;
}
```

For pivot tables, this requires custom models:

```php
class User extends Model
{
    use \Staudenmeir\EloquentHasManyDeep\HasRelationships;

    public function permissions()
    {
        return $this->hasManyDeep(Permission::class, ['App\RoleUser as alias', Role::class]);
    }
}

class RoleUser extends Pivot
{
    use \Staudenmeir\EloquentHasManyDeep\HasTableAlias;
}
```

Use `setAlias()` to specify a table alias when concatenating existing relationships:

```php
class Post extends Model
{
    use \Staudenmeir\EloquentHasManyDeep\HasRelationships;

    public function commentReplies()
    {
        return $this->hasManyDeepFromRelations(
            $this->comments(),
            (new Comment())->setAlias('alias')->replies()
        );
    }

    public function comments()
    {
        return $this->hasMany(Comment::class);
    }
}

class Comment extends Model
{
    use \Staudenmeir\EloquentHasManyDeep\HasTableAlias;

    public function replies()
    {
        return $this->hasMany(self::class, 'parent_id');
    }
}
```

### Soft Deleting

By default, soft-deleted intermediate models will be excluded from the result. Use `withTrashed()` to include them:

```php
class Country extends Model
{
    use \Staudenmeir\EloquentHasManyDeep\HasRelationships;

    public function comments()
    {
        return $this->hasManyDeep(Comment::class, [User::class, Post::class])
            ->withTrashed('users.deleted_at');
    }
}

class User extends Model
{
    use SoftDeletes;
}
```

### Getting Unique Results

Deep relationships with many-to-many segments can contain duplicate models in their results. If you want to get unique
results, you can remove duplicates from the result collection:

```php
$uniqueComments = Country::find($id)->comments()->get()->unique();
```

If you need to remove duplicates in the query (e.g. for pagination), try adding `distinct()`:

```php
$uniqueComments = Country::find($id)->comments()->distinct()->get();
```

`distinct()` doesn't work for all cases. If it doesn't work for you, use `groupBy()` instead:

```php
$uniqueComments = Country::find($id)->comments()
    ->getQuery()             // Get the underlying query builder
    ->select('comments.*')   // Select only columns from the related table
    ->groupBy('comments.id') // Group by the related table's primary key 
    ->get();
```

### Reversing Relationships

You can define a `HasManyDeep`/`HasOneDeep` relationship by reversing an existing deep relationship
using `hasManyDeepFromReverse()`/`hasOneDeepFromReverse()`:

```php
class Country extends Model
{
    use \Staudenmeir\EloquentHasManyDeep\HasRelationships;

    public function comments()
    {
        return $this->hasManyDeep(Comment::class, [User::class, Post::class]);
    }
}

class Comment extends Model
{
    use \Staudenmeir\EloquentHasManyDeep\HasRelationships;

    public function country()
    {
        return $this->hasOneDeepFromReverse(
            (new Country())->comments()
        );
    }
}
```

### IDE Helper

If you are using [barryvdh/laravel-ide-helper](https://github.com/barryvdh/laravel-ide-helper), this package provides a model hook that will correctly add relations when generating the type hints. 
The model hook is **enabled by default** using [Package Discovery](https://laravel.com/docs/packages#package-discovery).

To enable it manually, add [model hook](https://github.com/barryvdh/laravel-ide-helper#model-hooks) to the model_hooks array.

```php
    // File: config/ide-helper.php

    /*
    |--------------------------------------------------------------------------
    | Models hooks
    |--------------------------------------------------------------------------
    |
    | Define which hook classes you want to run for models to add custom information
    |
    | Hooks should implement Barryvdh\LaravelIdeHelper\Contracts\ModelHookInterface.
    |
    */

    'model_hooks' => [
        \Staudenmeir\EloquentHasManyDeep\IdeHelper\DeepRelationsHook::class,
    ],
```

To disable the model hook you have 3 options:

 - [Disable using .env](#disable-using-env)
 - [Disable using config](#disable-using-config)
 - [Disable by option out of Package Discovery](#disable-by-opting-out-of-package-discovery)

#### Disable using .env

Update your `.env` file to include:

```dotenv
ELOQUENT_HAS_MANY_DEEP_IDE_HELPER_ENABLED=false
```

#### Disable using config

Publish the config and disable the setting directly:

```
php artisan vendor:publish --tag=eloquent-has-many-deep
```

```php
    // File: config/eloquent-has-many-deep.php
    
    /*
    |--------------------------------------------------------------------------
    | IDE Helper
    |--------------------------------------------------------------------------
    |
    | Automatically register the model hook to receive correct type hints
    |
    */
    'ide_helper_enabled' => false,
```

#### Disable by opting out of Package Discovery

Update your `composer.json` with the following:

```json
"extra": {
    "laravel": {
        "dont-discover": [
            "staudenmeir/eloquent-has-many-deep"
        ]
    }
},
```

## Contributing

Please see [CONTRIBUTING](.github/CONTRIBUTING.md) and [CODE OF CONDUCT](.github/CODE_OF_CONDUCT.md) for details.
