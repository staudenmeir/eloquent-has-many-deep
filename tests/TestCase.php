<?php

namespace Tests;

use Carbon\Carbon;
use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Blueprint;
use Tests\Models\Club;
use Tests\Models\Comment;
use Tests\Models\Country;
use Tests\Models\Post;
use Tests\Models\Team;
use Tests\Models\User;

abstract class TestCase extends \PHPUnit\Framework\TestCase
{
    protected function setUp()
    {
        parent::setUp();

        $capsule = new Capsule;
        $capsule->addConnection([
            'driver' => 'sqlite',
            'database' => ':memory:'
        ]);
        $capsule->setAsGlobal();
        $capsule->bootEloquent();

        Capsule::schema()->create('countries', function (Blueprint $table) {
            $table->increments('country_pk');
        });

        Capsule::schema()->create('users', function (Blueprint $table) {
            $table->increments('user_pk');
            $table->unsignedInteger('country_country_pk');
            $table->unsignedInteger('team_team_pk');
            $table->softDeletes();
        });

        Capsule::schema()->create('posts', function (Blueprint $table) {
            $table->increments('post_pk');
            $table->unsignedInteger('user_user_pk');
        });

        Capsule::schema()->create('comments', function (Blueprint $table) {
            $table->increments('comment_pk');
            $table->unsignedInteger('post_post_pk');
        });

        Capsule::schema()->create('clubs', function (Blueprint $table) {
            $table->increments('club_pk');
            $table->unsignedInteger('user_user_pk');
        });

        Capsule::schema()->create('teams', function (Blueprint $table) {
            $table->increments('team_pk');
            $table->unsignedInteger('club_club_pk');
        });

        Model::unguarded(function (){
            Country::create();
            Country::create();

            User::create(['country_country_pk' => 1, 'team_team_pk' => 1, 'deleted_at' => null]);
            User::create(['country_country_pk' => 1, 'team_team_pk' => 1, 'deleted_at' => null]);
            User::create(['country_country_pk' => 1, 'team_team_pk' => 2, 'deleted_at' => Carbon::yesterday()]);

            Post::create(['user_user_pk' => 1]);
            Post::create(['user_user_pk' => 2]);
            Post::create(['user_user_pk' => 3]);

            Comment::create(['post_post_pk' => 1]);
            Comment::create(['post_post_pk' => 2]);
            Comment::create(['post_post_pk' => 3]);

            Club::create(['user_user_pk' => 1]);
            Club::create(['user_user_pk' => 2]);
            Club::create(['user_user_pk' => 3]);

            Team::create(['club_club_pk' => 1]);
            Team::create(['club_club_pk' => 2]);
            Team::create(['club_club_pk' => 3]);
        });

        Capsule::enableQueryLog();
    }
}
