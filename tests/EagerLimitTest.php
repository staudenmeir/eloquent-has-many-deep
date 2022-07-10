<?php

namespace Tests;

use Staudenmeir\EloquentHasManyDeep\HasManyDeep;
use Tests\Models\Country;

class EagerLimitTest extends TestCase
{
    public function testLazyLoading()
    {
        $comments = Country::find(1)->comments()->limit(1)->offset(1)->get();

        $this->assertEquals([32], $comments->pluck('id')->all());
    }

    public function testEagerLoading()
    {
        $countries = Country::with(['comments' => function (HasManyDeep $query) {
            $query->orderByDesc('comments.id')->limit(1);
        }])->get();

        $this->assertEquals([32], $countries[0]->comments->pluck('id')->all());
        $this->assertEquals([36], $countries[1]->comments->pluck('id')->all());
    }

    public function testLazyEagerLoading()
    {
        $countries = Country::all()->load(['comments' => function (HasManyDeep $query) {
            $query->orderByDesc('comments.id')->take(1);
        }]);

        $this->assertEquals([32], $countries[0]->comments->pluck('id')->all());
        $this->assertEquals([36], $countries[1]->comments->pluck('id')->all());
    }
}
