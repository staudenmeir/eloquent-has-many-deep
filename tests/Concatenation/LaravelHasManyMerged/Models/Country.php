<?php

namespace Tests\Concatenation\LaravelHasManyMerged\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Staudenmeir\EloquentHasManyDeep\HasManyDeep;
use Staudenmeir\EloquentHasManyDeep\HasRelationships;

class Country extends Model
{
    use HasRelationships;

    /**
     * @return \Staudenmeir\EloquentHasManyDeep\HasManyDeep<\Tests\Concatenation\LaravelHasManyMerged\Models\Attachment, $this>
     */
    public function attachments(): HasManyDeep
    {
        return $this->hasManyDeepFromRelations(
            $this->users(),
            (new User())->messages(),
            (new Message())->attachments()
        );
    }

    /**
     * @return \Staudenmeir\EloquentHasManyDeep\HasManyDeep<\Tests\Concatenation\LaravelHasManyMerged\Models\Message, $this>
     */
    public function messages(): HasManyDeep
    {
        return $this->hasManyDeepFromRelations($this->users(), (new User())->messages());
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<\Tests\Concatenation\LaravelHasManyMerged\Models\User>
     */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }
}
