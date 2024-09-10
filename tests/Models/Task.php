<?php

namespace Tests\Models;

use Awobaz\Compoships\Compoships;
use Awobaz\Compoships\Database\Eloquent\Relations\BelongsTo as ComposhipsBelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Task extends Model
{
    use Compoships;

    /**
     * @return \Awobaz\Compoships\Database\Eloquent\Relations\BelongsTo<\Tests\Models\Employee, self>
     */
    public function employee(): ComposhipsBelongsTo
    {
        return $this->belongsTo(Employee::class, ['team_id', 'work_stream_id'], ['team_id', 'work_stream_id']);
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo<\Tests\Models\Project, self>
     */
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }
}
