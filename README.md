# Laravel Favorite

[![Latest Version on Packagist](https://img.shields.io/packagist/v/stevecreekmore/laravel-favorite.svg?style=flat-square)](https://packagist.org/packages/stevecreekmore/laravel-favorite)
[![Total Downloads](https://img.shields.io/packagist/dt/stevecreekmore/laravel-favorite.svg?style=flat-square)](https://packagist.org/packages/stevecreekmore/favorite)

User favorite system for Laravel.

## Installation

You can install the package via composer:

```bash
composer require Stevecreekmore/laravel-favorite
```

## Configuration

Publish the configuration file and migrations:

```bash
php artisan vendor:publish --provider="Stevecreekmore\LaravelFavorite\FavoriteServiceProvider"
```

Or publish them individually:

```bash
# Publish config only
php artisan vendor:publish --tag=favorite-config

# Publish migrations only
php artisan vendor:publish --tag=favorite-migrations
```

Run the migrations:

```bash
php artisan migrate
```

## Usage

### Setup Models

Add the `CanFavorite` trait to your User model (or any model that can favorite):

```php
use Stevecreekmore\LaravelFavorite\Traits\CanFavorite;

class User extends Authenticatable
{
    use CanFavorite;
}
```

Add the `CanBeFavorited` trait to models that can be favorited:

```php
use Stevecreekmore\LaravelFavorite\Traits\CanBeFavorited;

class User extends Authenticatable
{
    use CanBeFavorited;
}

// Or any other model
class Post extends Model
{
    use CanBeFavorited;
}
```

### Basic Operations

#### Favorite a User

```php
$user = User::find(1);
$target = User::find(2);

$user->favorite($target);
```

#### Unfavorite a User

```php
$user->unfavorite($target);
```

#### Toggle Favorite Status

```php
$user->toggleFavorite($target);
```

#### Check if Favoriting

```php
if ($user->isFavoriting($target)) {
    // User is favoriting target
}
```

#### Check if Favorited By

```php
if ($target->isFavoritedBy($user)) {
    // Target is favorited by user
}
```

### Retrieving Favorites

#### Get All Favorites

```php
// Get all favorite records
$favorites = $user->favorites;

// Get favorited users
$favoritedUsers = $user->getFavorited(User::class);

// Get favorited posts
$favoritedPosts = $user->getFavorited(Post::class);
```

#### Get Users Who Favorited

```php
$post = Post::find(1);
$favoriters = $post->favoriters;
```

#### Get Favorites Count

```php
$count = $post->favoritersCount();

// Or using withCount
$posts = Post::withCount('favorites')->get();
foreach ($posts as $post) {
    echo $post->favorites_count;
}
```

### Attaching Favorite Status

You can attach favorite status to models for the current user:

```php
$user = auth()->user();
$posts = Post::all();

// Attach status to collection
$user->attachFavoriteStatus($posts);

foreach ($posts as $post) {
    if ($post->has_favorited) {
        echo "Favorited at: " . $post->favorited_at;
    }
}

// Attach status to single model
$post = Post::find(1);
$user->attachFavoriteStatus($post);
```

### Query Scopes

#### Get Models Favorited By User

```php
$favoritedPosts = Post::favoritedBy($user)->get();
```

#### Order By Favorites Count

```php
// Most favorited first
$posts = Post::orderByFavoritesCount()->get();

// Least favorited first
$posts = Post::orderByFavoritesCount('asc')->get();
```

### Events

The package dispatches events when favorites are created or removed:

- `Stevecreekmore\LaravelFavorite\Events\Favorited`
- `Stevecreekmore\LaravelFavorite\Events\Unfavorited`

You can listen to these events in your `EventServiceProvider`:

```php
use Stevecreekmore\LaravelFavorite\Events\Favorited;
use Stevecreekmore\LaravelFavorite\Events\Unfavorited;

protected $listen = [
    Favorited::class => [
        SendFavoriteNotification::class,
    ],
    Unfavorited::class => [
        RemoveFavoriteNotification::class,
    ],
];
```

## Configuration Options

The `config/favorite.php` file contains the following options:

```php
return [
    // Model class name for Favorite
    'favorite_model' => \Stevecreekmore\LaravelFavorite\Favorite::class,

    // Table name for favorites
    'favorites_table' => 'favorites',

    // Foreign key column names
    'user_foreign_key' => 'user_id',
    'favoriteable_foreign_key' => 'favoriteable_id',
];
```

## API Methods

### CanFavorite Trait

| Method | Description |
|--------|-------------|
| `favorite($model)` | Favorite a model |
| `unfavorite($model)` | Unfavorite a model |
| `toggleFavorite($model)` | Toggle favorite status |
| `isFavoriting($model)` | Check if favoriting a model |
| `favorites()` | Get all favorite records |
| `getFavorited($modelClass)` | Get favorited models of a specific type |
| `attachFavoriteStatus($models)` | Attach favorite status to models |

### CanBeFavorited Trait

| Method | Description |
|--------|-------------|
| `favorites()` | Get all favorite records for this model |
| `favoriters()` | Get users who favorited this model |
| `isFavoritedBy($user)` | Check if favorited by a user |
| `favoritersCount()` | Get count of favoriters |
| `scopeFavoritedBy($user)` | Query scope for favorited by user |
| `scopeOrderByFavoritesCount($direction)` | Query scope to order by favorites count |

## License

MIT
