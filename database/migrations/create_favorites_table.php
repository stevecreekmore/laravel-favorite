<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create(config('favorite.favorites_table', 'favorites'), function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger(config('favorite.user_foreign_key', 'user_id'))->index();
            $table->morphs('favoriteable');
            $table->timestamp('favorited_at')->nullable();
            $table->timestamps();

            $table->unique([
                config('favorite.user_foreign_key', 'user_id'),
                'favoriteable_type',
                'favoriteable_id',
            ], 'favorites_user_favoriteable_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists(config('favorite.favorites_table', 'favorites'));
    }
};
