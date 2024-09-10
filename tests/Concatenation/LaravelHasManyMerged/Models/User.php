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

    /**
     * @return \Staudenmeir\EloquentHasManyDeep\HasManyDeep<\Tests\Concatenation\LaravelHasManyMerged\Models\Attachment, $this>
     */
    public function attachments(): HasManyDeep
    {
        return $this->hasManyDeepFromRelations($this->messages(), (new Message())->attachments());
    }

    /**
     * @return \Korridor\LaravelHasManyMerged\HasManyMerged<\Tests\Concatenation\LaravelHasManyMerged\Models\Message>
     */
    public function messages(): HasManyMerged
    {
        return $this->hasManyMerged(Message::class, ['sender_id', 'recipient_id']);
    }
}
