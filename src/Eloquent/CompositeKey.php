<?php

namespace Staudenmeir\EloquentHasManyDeep\Eloquent;

class CompositeKey
{
    /**
     * @var list<string> $columns
     */
    public array $columns;

    /**
     * @param string ...$columns
     */
    public function __construct(...$columns)
    {
        $this->columns = $columns;
    }
}
