<?php

namespace Stevecreekmore\LaravelFavorite\Traits;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Collection;
use Stevecreekmore\LaravelFavorite\Events\Favorited;
use Stevecreekmore\LaravelFavorite\Events\Unfavorited;

trait CanFavorite
{
    /**
     * Favorite a model.
     */
    public function favorite(Model $object): array
    {
        if (!$this->canBeFavorited($object)) {
            throw new \InvalidArgumentException('The model must use the CanBeFavorited trait.');
        }

        if ($this->isFavoriting($object)) {
            return [
                'attached' => false,
                'detached' => false,
            ];
        }

        $favorite = app(config('favorite.favorite_model'));
        $favorite->{config('favorite.user_foreign_key', 'user_id')} = $this->getKey();
        $favorite->favoriteable_id = $object->getKey();
        $favorite->favoriteable_type = $object->getMorphClass();
        $favorite->favorited_at = now();
        $favorite->save();

        event(new Favorited($object));

        return [
            'attached' => true,
            'detached' => false,
        ];
    }

    /**
     * Unfavorite a model.
     */
    public function unfavorite(Model $object): array
    {
        if (!$this->canBeFavorited($object)) {
            throw new \InvalidArgumentException('The model must use the CanBeFavorited trait.');
        }

        $relation = $this->favorites()
            ->where('favoriteable_id', $object->getKey())
            ->where('favoriteable_type', $object->getMorphClass())
            ->first();

        if ($relation) {
            $relation->delete();
            event(new Unfavorited($object));

            return [
                'attached' => false,
                'detached' => true,
            ];
        }

        return [
            'attached' => false,
            'detached' => false,
        ];
    }

    /**
     * Toggle favorite status.
     */
    public function toggleFavorite(Model $object): array
    {
        return $this->isFavoriting($object) ? $this->unfavorite($object) : $this->favorite($object);
    }

    /**
     * Check if the user is favoriting the given model.
     */
    public function isFavoriting(Model $object): bool
    {
        if (!$this->canBeFavorited($object)) {
            throw new \InvalidArgumentException('The model must use the CanBeFavorited trait.');
        }

        if ($this->relationLoaded('favorites')) {
            return $this->favorites
                ->where('favoriteable_id', $object->getKey())
                ->where('favoriteable_type', $object->getMorphClass())
                ->isNotEmpty();
        }

        return $this->favorites()
            ->where('favoriteable_id', $object->getKey())
            ->where('favoriteable_type', $object->getMorphClass())
            ->exists();
    }

    /**
     * Get all favorites.
     */
    public function favorites(): HasMany
    {
        return $this->hasMany(
            config('favorite.favorite_model'),
            config('favorite.user_foreign_key', 'user_id')
        );
    }

    /**
     * Get favorited items of a specific type.
     */
    public function getFavorited(string $model): Collection
    {
        return $this->favorites()
            ->where('favoriteable_type', (new $model)->getMorphClass())
            ->get()
            ->pluck('favoriteable');
    }

    /**
     * Attach favorite status to a collection of models.
     */
    public function attachFavoriteStatus($favoriteables, callable $resolver = null)
    {
        $favorites = $this->favorites()
            ->get()
            ->keyBy(function ($item) {
                return sprintf('%s-%s', $item->favoriteable_type, $item->favoriteable_id);
            });

        $attachStatus = function ($favoriteable) use ($favorites, $resolver) {
            $resolver = $resolver ?? fn($m) => $m;
            $favoriteable = $resolver($favoriteable);

            if ($favoriteable instanceof Model) {
                $key = sprintf('%s-%s', $favoriteable->getMorphClass(), $favoriteable->getKey());
                $favoriteable->setAttribute('has_favorited', $favorites->has($key));
                $favoriteable->setAttribute('favorited_at', $favorites->get($key)?->favorited_at);
            }
        };

        if ($favoriteables instanceof Model) {
            $attachStatus($favoriteables);
        } elseif ($favoriteables instanceof Collection || is_array($favoriteables)) {
            foreach ($favoriteables as $favoriteable) {
                $attachStatus($favoriteable);
            }
        }

        return $favoriteables;
    }

    /**
     * Check if the model can be favorited.
     */
    protected function canBeFavorited(Model $object): bool
    {
        return in_array(CanBeFavorited::class, class_uses_recursive($object));
    }
}
