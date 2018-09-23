[![Build Status](https://travis-ci.org/staudenmeir/eloquent-has-many-deep.svg?branch=master)](https://travis-ci.org/staudenmeir/eloquent-has-many-deep)
[![Latest Stable Version](https://poser.pugx.org/staudenmeir/eloquent-has-many-deep/v/stable)](https://packagist.org/packages/staudenmeir/eloquent-has-many-deep)
[![Total Downloads](https://poser.pugx.org/staudenmeir/eloquent-has-many-deep/downloads)](https://packagist.org/packages/staudenmeir/eloquent-has-many-deep)
[![License](https://poser.pugx.org/staudenmeir/eloquent-has-many-deep/license)](https://packagist.org/packages/staudenmeir/eloquent-has-many-deep)

## Introduction
This extended version of `HasManyThrough` allows relationships with unlimited intermediate models and supports `BelongsToMany` relationships.  
Requires Laravel 5.5.29+.

## Installation

    composer require staudenmeir/eloquent-has-many-deep

## Usage

Using the  [documentation example](https://laravel.com/docs/eloquent-relationships#has-many-through) with an additional level:  
`Country` → has many → `User` → has many → `Post` → has many → `Comment`

```php
class Country extends Model
{
    use \Staudenmeir\EloquentHasManyDeep\HasRelationships;

    public function comments()
    {
        return $this->hasManyDeep('App\Comment', ['App\User', 'App\Post']);
    }
}
```

Just like with `hasManyThrough()`, the first parameter of `hasManyDeep()` is the related model. The second parameter is an array of intermediate models, from the far parent (the model where the relationship is defined) to the related model.

By default, `hasManyDeep()` uses the Eloquent conventions for foreign and local keys. You can also specify custom foreign keys as the third parameter and custom local keys as the fourth parameter: 

```php
class Country extends Model
{
    use \Staudenmeir\EloquentHasManyDeep\HasRelationships;

    public function comments()
    {
        return $this->hasManyDeep(
            'App\Comment',
            ['App\User', 'App\Post'], // Intermediate models, beginning at the far parent (Country).
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

You can use `null` placeholders for the default keys:

```php
class Country extends Model
{
    use \Staudenmeir\EloquentHasManyDeep\HasRelationships;

    public function comments()
    {
        return $this->hasManyDeep('App\Comment', ['App\User', 'App\Post'], [null, 'custom_user_id']);
    }
}
```

### BelongsToMany

You can also include `BelongsToMany` relationships in the intermediate path.

Using the [documentation example](https://laravel.com/docs/eloquent-relationships#many-to-many) with an additional level:  
`User` → belongs to many → `Role` → has many → `Permission`

Add the pivot table to the intermediate models:

```php
class User extends Model
{
    use \Staudenmeir\EloquentHasManyDeep\HasRelationships;

    public function permissions()
    {
        return $this->hasManyDeep('App\Permission', ['role_user', 'App\Role']);
    }
}
```

If you specify custom keys, remember to reverse the foreign and local key on the right side of the pivot table:

```php
class User extends Model
{
    use \Staudenmeir\EloquentHasManyDeep\HasRelationships;

    public function permissions()
    {
        return $this->hasManyDeep(
            'App\Permission',
            ['role_user', 'App\Role'], // Intermediate models and tables, beginning at the far parent (User).
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

### Intermediate and pivot data

Use `withIntermediate()` to retrieve attributes from intermediate tables:

```php
public function comments()
{
    return $this->hasManyDeep('App\Comment', ['App\User', 'App\Post'])
        ->withIntermediate('App\Post');
}

foreach ($country->comments as $comment) {
    // $comment->post->title
}
```

By default, this will retrieve all the table's columns. Be aware that this executes a separate query to get the list of columns.

You can specify the selected columns as the second argument:

```php
public function comments()
{
    return $this->hasManyDeep('App\Comment', ['App\User', 'App\Post'])
        ->withIntermediate('App\Post', ['id', 'title']);
}
```

As the third argument, you can specify a custom accessor:

```php
public function comments()
{
    return $this->hasManyDeep('App\Comment', ['App\User', 'App\Post'])
        ->withIntermediate('App\Post', ['id', 'title'], 'accessor');
}

foreach ($country->comments as $comment) {
    // $comment->accessor->title
}
```

If you retrieve data from multiple tables, you can use nested accessors:

```php
public function comments()
{
    return $this->hasManyDeep('App\Comment', ['App\User', 'App\Post'])
        ->withIntermediate('App\Post')
        ->withIntermediate('App\User', ['*'], 'post.user');
}

foreach ($country->comments as $comment) {
    // $comment->post->title
    // $comment->post->user->name
}
```

Use `withPivot()` for the pivot tables of `BelongsToMany` relationships:

```php
public function permissions()
{
    return $this->hasManyDeep('App\Permission', ['role_user', 'App\Role'])
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
    return $this->hasManyDeep('App\Permission', ['role_user', 'App\Role'])
        ->withPivot('role_user', ['expires_at'], 'App\RoleUserPivot', 'pivot');
}

foreach ($user->permissions as $permission) {
    // $permission->pivot->expires_at
}
```