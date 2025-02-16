<?php

namespace Tests\Models;

use Awobaz\Compoships\Compoships;
use Awobaz\Compoships\Database\Eloquent\Relations\BelongsTo as ComposhipsBelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Task extends Model
{
    // use Compoships; TODO[L12]

    public function employee(): ComposhipsBelongsTo
    {
        return $this->belongsTo(Employee::class, ['team_id', 'work_stream_id'], ['team_id', 'work_stream_id']);
    }

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }
}
