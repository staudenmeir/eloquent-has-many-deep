<?php

namespace Tests\Concatenation\LaravelHasManyMerged\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Staudenmeir\EloquentHasManyDeep\HasManyDeep;
use Staudenmeir\EloquentHasManyDeep\HasRelationships;

class Country extends Model
{
    use HasRelationships;

    public function attachments(): HasManyDeep
    {
        return $this->hasManyDeepFromRelations(
            $this->users(),
            (new User())->messages(),
            (new Message())->attachments()
        );
    }

    public function messages(): HasManyDeep
    {
        return $this->hasManyDeepFromRelations($this->users(), (new User())->messages());
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
}
