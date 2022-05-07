<?php

namespace Staudenmeir\EloquentHasManyDeep;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Support\Str;

trait RetrievesIntermediateTables
{
    /**
     * The intermediate tables to retrieve.
     *
     * @var array
     */
    protected array $intermediateTables = [];

    /**
     * Set the columns on an intermediate table to retrieve.
     *
     * @param string $class
     * @param array $columns
     * @param string|null $accessor
     * @return $this
     */
    public function withIntermediate(string $class, array $columns = ['*'], string|null $accessor = null): self
    {
        /** @var \Illuminate\Database\Eloquent\Model $instance */
        $instance = new $class();

        $accessor = $accessor ?: Str::snake(class_basename($class));

        return $this->withPivot($instance->getTable(), $columns, $class, $accessor);
    }

    /**
     * Set the columns on a pivot table to retrieve.
     *
     * @param string $table
     * @param array $columns
     * @param string $class
     * @param string|null $accessor
     * @return $this
     */
    public function withPivot(string $table, array $columns = ['*'], string $class = Pivot::class, string|null $accessor = null): self
    {
        if ($columns === ['*']) {
            $columns = $this->query->getConnection()->getSchemaBuilder()->getColumnListing($table);
        }

        $accessor = $accessor ?: $table;

        if (isset($this->intermediateTables[$accessor])) {
            $columns = array_merge($columns, $this->intermediateTables[$accessor]['columns']);
        }

        $this->intermediateTables[$accessor] = compact('table', 'columns', 'class');

        return $this;
    }

    /**
     * Get the intermediate columns for the relation.
     *
     * @return array
     */
    protected function intermediateColumns(): array
    {
        $columns = [];

        foreach ($this->intermediateTables as $accessor => $intermediateTable) {
            $prefix = $this->prefix($accessor);

            foreach ($intermediateTable['columns'] as $column) {
                $columns[] = $intermediateTable['table'].'.'.$column.' as '.$prefix.$column;
            }
        }

        return array_unique($columns);
    }

    /**
     * Hydrate the intermediate table relationship on the models.
     *
     * @param array $models
     * @return void
     */
    protected function hydrateIntermediateRelations(array $models): void
    {
        $intermediateTables = $this->intermediateTables;

        ksort($intermediateTables);

        foreach ($intermediateTables as $accessor => $intermediateTable) {
            $prefix = $this->prefix($accessor);

            if (Str::contains($accessor, '.')) {
                [$path, $key] = preg_split('/\.(?=[^.]*$)/', $accessor);
            } else {
                [$path, $key] = [null, $accessor];
            }

            foreach ($models as $model) {
                $relation = $this->intermediateRelation($model, $intermediateTable, $prefix);

                data_get($model, $path)->setRelation($key, $relation);
            }
        }
    }

    /**
     * Get the intermediate relationship from the query.
     *
     * @param \Illuminate\Database\Eloquent\Model $model
     * @param array $intermediateTable
     * @param string $prefix
     * @return \Illuminate\Database\Eloquent\Model
     */
    protected function intermediateRelation(Model $model, array $intermediateTable, string $prefix): \Illuminate\Database\Eloquent\Model
    {
        $attributes = $this->intermediateAttributes($model, $prefix);

        $class = $intermediateTable['class'];

        if ($class === Pivot::class) {
            return $class::fromAttributes($model, $attributes, $intermediateTable['table'], true);
        }

        if (is_subclass_of($class, Pivot::class)) {
            return $class::fromRawAttributes($model, $attributes, $intermediateTable['table'], true);
        }

        /** @var \Illuminate\Database\Eloquent\Model $instance */
        $instance = new $class();

        return $instance->newFromBuilder($attributes);
    }

    /**
     * Get the intermediate attributes from a model.
     *
     * @param \Illuminate\Database\Eloquent\Model $model
     * @param string $prefix
     * @return array
     */
    protected function intermediateAttributes(Model $model, string $prefix): array
    {
        $attributes = [];

        foreach ($model->getAttributes() as $key => $value) {
            if (str_starts_with($key, $prefix)) {
                $attributes[substr($key, strlen($prefix))] = $value;

                unset($model->$key);
            }
        }

        return $attributes;
    }

    /**
     * Get the intermediate column alias prefix.
     *
     * @param string $accessor
     * @return string
     */
    protected function prefix(string $accessor): string
    {
        return '__'.$accessor.'__';
    }
}
