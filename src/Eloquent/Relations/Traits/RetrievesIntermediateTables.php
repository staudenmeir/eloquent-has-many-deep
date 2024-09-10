<?php

namespace Staudenmeir\EloquentHasManyDeep\Eloquent\Relations\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Pivot;
use Illuminate\Support\Str;

trait RetrievesIntermediateTables
{
    /**
     * The intermediate tables to retrieve.
     *
     * @var array<string, array{table: string, columns: list<string>, class: class-string<\Illuminate\Database\Eloquent\Model>, postProcessor: list<string>}>
     */
    protected $intermediateTables = [];

    /**
     * Set the columns on an intermediate table to retrieve.
     *
     * @param class-string<\Illuminate\Database\Eloquent\Model> $class
     * @param list<string> $columns
     * @param string|null $accessor
     * @return $this
     */
    public function withIntermediate($class, array $columns = ['*'], $accessor = null)
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
     * @param list<string> $columns
     * @param class-string<\Illuminate\Database\Eloquent\Model> $class
     * @param string|null $accessor
     * @param callable|null $postProcessor
     * @return $this
     */
    public function withPivot(
        $table,
        array $columns = ['*'],
        $class = Pivot::class,
        $accessor = null,
        ?callable $postProcessor = null
    ) {
        if ($columns === ['*']) {
            /** @var \Illuminate\Database\Connection $connection */
            $connection = $this->query->getConnection();

            $columns = $connection->getSchemaBuilder()->getColumnListing($table);
        }

        $accessor = $accessor ?: $table;

        if (isset($this->intermediateTables[$accessor])) {
            $columns = array_merge($columns, $this->intermediateTables[$accessor]['columns']);
        }

        $this->intermediateTables[$accessor] = compact('table', 'columns', 'class', 'postProcessor');

        return $this;
    }

    /**
     * Get the intermediate columns for the relation.
     *
     * @return list<string>
     */
    protected function intermediateColumns()
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
     * Hydrate the intermediate table relationships on the models.
     *
     * @param list<\Illuminate\Database\Eloquent\Model> $models
     * @return void
     */
    protected function hydrateIntermediateRelations(array $models)
    {
        $intermediateTables = $this->intermediateTables;

        ksort($intermediateTables);

        foreach ($intermediateTables as $accessor => $intermediateTable) {
            $prefix = $this->prefix($accessor);

            if (str_contains($accessor, '.')) {
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
     * @param array{table: string, columns: list<string>, class: class-string<\Illuminate\Database\Eloquent\Model>, postProcessor: list<string>} $intermediateTable
     * @param string $prefix
     * @return \Illuminate\Database\Eloquent\Model
     */
    protected function intermediateRelation(Model $model, array $intermediateTable, $prefix)
    {
        $attributes = $this->intermediateAttributes($model, $prefix);

        if ($intermediateTable['postProcessor']) {
            $attributes = $intermediateTable['postProcessor']($model, $attributes);
        }

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
     * @return array<string, mixed>
     */
    protected function intermediateAttributes(Model $model, $prefix)
    {
        $attributes = [];

        foreach ($model->getAttributes() as $key => $value) {
            if (strpos($key, $prefix) === 0) {
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
    protected function prefix($accessor)
    {
        return '__'.$accessor.'__';
    }

    /**
     * Get the intermediate tables.
     *
     * @return array<string, array{table: string, columns: list<string>, class: class-string<\Illuminate\Database\Eloquent\Model>, postProcessor: list<string>}>
     */
    public function getIntermediateTables(): array
    {
        return $this->intermediateTables;
    }
}
