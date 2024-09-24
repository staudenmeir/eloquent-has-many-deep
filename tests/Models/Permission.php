<?php

namespace Tests\Models;

use Staudenmeir\EloquentHasManyDeep\HasManyDeep;
use Staudenmeir\EloquentHasManyDeep\HasRelationships;

class Permission extends Model
{
    use HasRelationships;

    public function countries(): HasManyDeep
    {
        return $this->hasManyDeepFromReverse(
            (new Country())->permissionsWithPivotAlias()
        );
    }
}
