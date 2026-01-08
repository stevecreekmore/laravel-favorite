<?php

namespace stevecreekmore\LaravelFavorite\Events;

use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class Favorited
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    public $favoriteable;

    /**
     * Create a new event instance.
     */
    public function __construct($favoriteable)
    {
        $this->favoriteable = $favoriteable;
    }
}
