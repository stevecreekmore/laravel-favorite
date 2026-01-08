<?php

return [
    /*
     * Model class name for Favorite
     */
    'favorite_model' => \Stevecreekmore\LaravelFavorite\Favorite::class,

    /*
     * Table name for favorites
     */
    'favorites_table' => 'favorites',

    /*
     * Foreign key column names
     */
    'user_foreign_key' => 'user_id',
    'favoriteable_foreign_key' => 'favoriteable_id',
];
