<?php

namespace Tests\Models;

use Awobaz\Compoships\Compoships;
use Awobaz\Compoships\Database\Eloquent\Relations\HasMany;
use Staudenmeir\EloquentHasManyDeep\Eloquent\CompositeKey;
use Staudenmeir\EloquentHasManyDeep\HasManyDeep;
use Staudenmeir\EloquentHasManyDeep\HasRelationships;

class Employee extends Model
{
    use Compoships;
    use HasRelationships;

    /**
     * @return \Staudenmeir\EloquentHasManyDeep\HasManyDeep<\Tests\Models\Project, $this>
     */
    public function projects(): HasManyDeep
    {
        return $this->hasManyDeep(
            Project::class,
            [Task::class],
            [new CompositeKey('team_id', 'work_stream_id'), 'id'],
            [new CompositeKey('team_id', 'work_stream_id'), 'project_id']
        );
    }

    /**
     * @return \Staudenmeir\EloquentHasManyDeep\HasManyDeep<\Tests\Models\Project, $this>
     */
    public function projectsFromRelations(): HasManyDeep
    {
        return $this->hasManyDeepFromRelations($this->tasks(), (new Task())->project());
    }

    /**
     * @return \Awobaz\Compoships\Database\Eloquent\Relations\HasMany<\Tests\Models\Task>
     */
    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class, ['team_id', 'work_stream_id'], ['team_id', 'work_stream_id']);
    }
}
