<?php

namespace Staudenmeir\EloquentHasManyDeep;

/**
 * @phpstan-ignore trait.unused
 */
trait HasTableAlias
{
    /** @inheritDoc */
    public function qualifyColumn($column)
    {
        if (str_contains($column, '.')) {
            return $column;
        }

        $table = $this->getTable();

        if (str_contains($table, ' as ')) {
            $table = explode(' as ', $table)[1];
        }

        return $table.'.'.$column;
    }

    /**
     * Set an alias for the model's table.
     *
     * @param string $alias
     * @return $this
     */
    public function setAlias($alias)
    {
        return $this->setTable($this->getTable().' as '.$alias);
    }
}
