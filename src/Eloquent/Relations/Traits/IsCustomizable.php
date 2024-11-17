<?php

namespace Staudenmeir\EloquentHasManyDeep\Eloquent\Relations\Traits;

/**
 * @template TRelatedModel of \Illuminate\Database\Eloquent\Model
 * @template TDeclaringModel of \Illuminate\Database\Eloquent\Model
 */
trait IsCustomizable
{
    /**
     * The custom callbacks to run at the end of the get() method.
     *
     * @var list<callable(\Illuminate\Database\Eloquent\Collection<int, TRelatedModel>): void>
     */
    protected array $postGetCallbacks = [];

    /**
     * The custom through key callback for an eager load of the relation.
     *
     * @var callable(string): string
     */
    protected $customThroughKeyCallback = null;

    /**
     * The custom constraints callback for an eager load of the relation.
     *
     * @var callable(\Illuminate\Database\Eloquent\Builder<TRelatedModel>, array<int, \Illuminate\Database\Eloquent\Model>): void
     */
    protected $customEagerConstraintsCallback = null;

    /**
     * The custom matching callbacks for the eagerly loaded results.
     *
     * @var list<callable(array<int, TDeclaringModel>, \Illuminate\Database\Eloquent\Collection<int, TRelatedModel>, string, string=): array<int, TDeclaringModel>>
     */
    protected array $customEagerMatchingCallbacks = [];

    /**
     * Set custom callbacks to run at the end of the get() method.
     *
     * @param list<callable(\Illuminate\Database\Eloquent\Collection<int, TRelatedModel>): void> $callbacks
     * @return $this
     */
    public function withPostGetCallbacks(array $callbacks): static
    {
        $this->postGetCallbacks = array_merge($this->postGetCallbacks, $callbacks);

        return $this;
    }

    /**
     * Set the custom through key callback for an eager load of the relation.
     *
     * @param callable(string): string $callback
     * @return $this
     */
    public function withCustomThroughKeyCallback(callable $callback): static
    {
        $this->customThroughKeyCallback = $callback;

        return $this;
    }

    /**
     * Set the custom constraints callback for an eager load of the relation.
     *
     * @param callable(\Illuminate\Database\Eloquent\Builder<TRelatedModel>, array<int, \Illuminate\Database\Eloquent\Model>): void $callback
     * @return $this
     */
    public function withCustomEagerConstraintsCallback(callable $callback): static
    {
        $this->customEagerConstraintsCallback = $callback;

        return $this;
    }

    /**
     * Set a custom matching callback for the eagerly loaded results.
     *
     * @param callable(array<int, TDeclaringModel>, \Illuminate\Database\Eloquent\Collection<int, TRelatedModel>, string, string=): array<int, TDeclaringModel> $callback
     * @return $this
     */
    public function withCustomEagerMatchingCallback(callable $callback): static
    {
        $this->customEagerMatchingCallbacks[] = $callback;

        return $this;
    }
}
