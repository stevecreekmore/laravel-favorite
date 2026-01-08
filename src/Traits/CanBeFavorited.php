<?php

namespace stevecreekmore\LaravelFavorite\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphMany;

trait CanBeFavorited
{
    /**
     * Get all favorites for this model.
     */
    public function favoritesReceived(): MorphMany
    {
        return $this->morphMany(
            config('favorite.favorite_model'),
            'favoriteable'
        );
    }

    /**
     * Get users who have favorited this model.
     */
    public function favoriters()
    {
        return $this->belongsToManyThrough(
            config('auth.providers.users.model'),
            config('favorite.favorite_model'),
            'favoriteable_id',
            'id',
            'id',
            config('favorite.user_foreign_key', 'user_id')
        )->where('favoriteable_type', $this->getMorphClass());
    }

    /**
     * Check if this model is favorited by the given user.
     */
    public function isFavoritedBy(Model $user): bool
    {
        if (!$this->userCanFavorite($user)) {
            return false;
        }

        if ($this->relationLoaded('favoritesReceived')) {
            return $this->favoritesReceived
                ->where(config('favorite.user_foreign_key', 'user_id'), $user->getKey())
                ->isNotEmpty();
        }

        return $this->favoritesReceived()
            ->where(config('favorite.user_foreign_key', 'user_id'), $user->getKey())
            ->exists();
    }

    /**
     * Get the count of users who favorited this model.
     */
    public function favoritersCount(): int
    {
        return $this->favoritesReceived()->count();
    }

    /**
     * Scope to get only models favorited by a specific user.
     */
    public function scopeFavoritedBy($query, Model $user)
    {
        return $query->whereHas('favoritesReceived', function ($q) use ($user) {
            $q->where(config('favorite.user_foreign_key', 'user_id'), $user->getKey());
        });
    }

    /**
     * Scope to order by favorites count.
     */
    public function scopeOrderByFavoritesCount($query, string $direction = 'desc')
    {
        return $query->withCount('favoritesReceived')
            ->orderBy('favorites_received_count', $direction);
    }

    /**
     * Check if the user can favorite.
     */
    protected function userCanFavorite(Model $user): bool
    {
        return in_array(CanFavorite::class, class_uses_recursive($user));
    }

    /**
     * Custom implementation for belongsToManyThrough relationship.
     */
    protected function belongsToManyThrough(
        string $related,
        string $through,
        string $firstKey,
        string $secondKey,
        string $localKey,
        string $secondLocalKey
    ) {
        $instance = $this->newRelatedInstance($related);
        $throughInstance = new $through;

        return $instance->newQuery()
            ->select($instance->getTable() . '.*')
            ->join(
                $throughInstance->getTable(),
                $instance->getTable() . '.id',
                '=',
                $throughInstance->getTable() . '.' . $secondLocalKey
            )
            ->where($throughInstance->getTable() . '.favoriteable_type', $this->getMorphClass())
            ->where($throughInstance->getTable() . '.favoriteable_id', $this->getKey());
    }
}
