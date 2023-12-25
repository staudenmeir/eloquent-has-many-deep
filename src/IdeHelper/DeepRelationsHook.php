<?php

namespace Staudenmeir\EloquentHasManyDeep\IdeHelper;

use Barryvdh\LaravelIdeHelper\Console\ModelsCommand;
use Barryvdh\LaravelIdeHelper\Contracts\ModelHookInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Str;
use ReflectionClass;
use ReflectionMethod;
use Staudenmeir\EloquentHasManyDeep\HasManyDeep;
use Staudenmeir\EloquentHasManyDeep\HasOneDeep;
use Staudenmeir\EloquentHasManyDeep\HasRelationships;
use Throwable;

class DeepRelationsHook implements ModelHookInterface
{
    public function run(ModelsCommand $command, Model $model): void
    {
        $traits = class_uses_recursive($model);

        if (!in_array(HasRelationships::class, $traits)) {
            return; // @codeCoverageIgnore
        }

        $methods = (new ReflectionClass($model))->getMethods(ReflectionMethod::IS_PUBLIC);

        foreach ($methods as $method) {
            if ($method->isAbstract() || $method->isStatic() || !$method->isPublic()
                || $method->getNumberOfParameters() > 0 || $method->getDeclaringClass()->getName() === Model::class) {
                continue;
            }

            try {
                $relationship = $method->invoke($model);
            } catch (Throwable) { // @codeCoverageIgnore
                continue; // @codeCoverageIgnore
            }

            if ($relationship instanceof HasManyDeep) {
                $this->addRelationship($command, $method, $relationship);
            }
        }
    }

    protected function addRelationship(ModelsCommand $command, ReflectionMethod $method, Relation $relationship): void
    {
        $manyRelation = !$relationship instanceof HasOneDeep;

        $type = $manyRelation
            ? '\\' . Collection::class . '|\\' . $relationship->getRelated()::class . '[]'
            : '\\' . $relationship->getRelated()::class;

        $command->setProperty(
            $method->getName(),
            $type,
            true,
            false,
            '',
            !$manyRelation
        );

        if ($manyRelation) {
            $command->setProperty(
                Str::snake($method->getName()) . '_count',
                'int',
                true,
                false,
                null,
                true
            );
        }
    }
}
