<?php

namespace Tests\Concatenation\LaravelHasManyMerged\Models;

use Illuminate\Database\Eloquent\Model;
use Korridor\LaravelHasManyMerged\HasManyMerged;
use Korridor\LaravelHasManyMerged\HasManyMergedRelation;
use Staudenmeir\EloquentHasManyDeep\HasManyDeep;
use Staudenmeir\EloquentHasManyDeep\HasRelationships;

class User extends Model
{
    use HasManyMergedRelation;
    use HasRelationships;

    public function attachments(): HasManyDeep
    {
        return $this->hasManyDeepFromRelations($this->messages(), (new Message())->attachments());
    }

    public function messages(): HasManyMerged
    {
        return $this->hasManyMerged(Message::class, ['sender_id', 'recipient_id']);
    }
}
