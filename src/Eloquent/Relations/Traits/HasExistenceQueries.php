<?php

namespace Staudenmeir\EloquentHasManyDeep\Eloquent\Relations\Traits;

use Closure;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Staudenmeir\EloquentHasManyDeep\HasTableAlias;

trait HasExistenceQueries
{
    /** @inheritDoc */
    public function getRelationExistenceQuery(Builder $query, Builder $parentQuery, $columns = ['*'])
    {
        $this->setRelationExistenceQueryAlias($parentQuery);

        if ($this->firstKey instanceof Closure || $this->localKey instanceof Closure) {
            $this->performJoin($query);

            /** @var callable $closureKey */
            $closureKey = $this->firstKey instanceof Closure ? $this->firstKey : $this->localKey;

            $closureKey($query, $parentQuery);

            $query->select($columns);

            return $query;
        }

        $query = parent::getRelationExistenceQuery($query, $parentQuery, $columns);

        if (is_array($this->foreignKeys[0])) {
            /** @var string $foreignKey */
            $foreignKey = $this->foreignKeys[0][0];

            $column = $this->throughParent->qualifyColumn($foreignKey);

            $query->where($column, '=', $this->farParent->getMorphClass());
        } elseif ($this->hasLeadingCompositeKey()) {
            $this->getRelationExistenceQueryWithCompositeKey($query);
        }

        return $query;
    }

    /** @inheritDoc */
    public function getRelationExistenceQueryForSelfRelation(Builder $query, Builder $parentQuery, $columns = ['*'])
    {
        $hash = $this->getRelationCountHash();

        $query->from($query->getModel()->getTable().' as '.$hash);

        $this->performJoin($query);

        $query->getModel()->setTable($hash);

        /** @var string $parentFrom */
        $parentFrom = $parentQuery->getQuery()->from;

        $query->select($columns)->whereColumn(
            "$parentFrom.$this->localKey",
            '=',
            $this->getQualifiedFirstKeyName()
        );

        return $query;
    }

    /**
     * Set the table alias for a relation existence query if necessary.
     *
     * @param \Illuminate\Database\Eloquent\Builder<\Illuminate\Database\Eloquent\Model> $parentQuery
     * @return void
     */
    protected function setRelationExistenceQueryAlias(Builder $parentQuery): void
    {
        foreach ($this->throughParents as $throughParent) {
            if ($throughParent->getTable() === $parentQuery->getQuery()->from) {
                if (!in_array(HasTableAlias::class, class_uses_recursive($throughParent))) {
                    $traitClass = HasTableAlias::class;
                    $parentClass = get_class($throughParent);

                    throw new Exception(
                        <<<EOT
This query requires an additional trait. Please add the $traitClass trait to $parentClass.
See https://github.com/staudenmeir/eloquent-has-many-deep/issues/137 for details.
EOT
                    );
                }

                $table = $throughParent->getTable() . ' as ' . $this->getRelationCountHash();

                $throughParent->setTable($table);

                break;
            }
        }
    }
}
