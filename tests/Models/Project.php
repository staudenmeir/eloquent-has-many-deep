<?php

namespace Tests\Models;

use Illuminate\Database\Eloquent\Relations\HasMany;
use Staudenmeir\EloquentHasManyDeep\Eloquent\CompositeKey;
use Staudenmeir\EloquentHasManyDeep\HasManyDeep;
use Staudenmeir\EloquentHasManyDeep\HasRelationships;

class Project extends Model
{
    use HasRelationships;

    /**
     * @return \Staudenmeir\EloquentHasManyDeep\HasManyDeep<\Tests\Models\Employee, $this>
     */
    public function employees(): HasManyDeep
    {
        return $this->hasManyDeep(
            Employee::class,
            [Task::class],
            [null, new CompositeKey('team_id', 'work_stream_id')],
            [null, new CompositeKey('team_id', 'work_stream_id')]
        );
    }

    /**
     * @return \Staudenmeir\EloquentHasManyDeep\HasManyDeep<\Tests\Models\Employee, $this>
     */
    public function employeesFromRelations(): HasManyDeep
    {
        return $this->hasManyDeepFromRelations($this->tasks(), (new Task())->employee());
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany<\Tests\Models\Task>
     */
    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }
}
