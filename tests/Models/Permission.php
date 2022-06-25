<?php

namespace Tests\Models;

use Staudenmeir\EloquentHasManyDeep\HasRelationships;

class Permission extends Model
{
    use HasRelationships;

    public function countries()
    {
        return $this->hasManyDeepFromReverse(
            (new Country())->permissionsWithPivotAlias()
        );
    }
}
