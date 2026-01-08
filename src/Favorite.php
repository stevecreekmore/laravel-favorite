<?php

namespace Stevecreekmore\LaravelFavorite;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Favorite extends Model
{
    protected $fillable = [
        'favorited_at',
    ];

    protected $casts = [
        'favorited_at' => 'datetime',
    ];

    /**
     * Get the table associated with the model.
     */
    public function getTable(): string
    {
        return config('favorite.favorites_table', 'favorites');
    }

    /**
     * Get the favoriteable entity.
     */
    public function favoriteable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * Get the user who favorited.
     */
    public function user()
    {
        return $this->belongsTo(
            config('auth.providers.users.model'),
            config('favorite.user_foreign_key', 'user_id')
        );
    }
}
