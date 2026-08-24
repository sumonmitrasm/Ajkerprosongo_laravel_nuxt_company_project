<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Create the many-to-many connection between posts and tags.
     */
    public function up(): void
    {
        Schema::create('post_tag', function (Blueprint $table) {
            $table->id();

            // Removing either parent automatically cleans up this connection.
            $table->foreignId('post_id')->constrained('posts')->cascadeOnDelete();
            $table->foreignId('tag_id')->constrained('tags')->cascadeOnDelete();
            $table->timestamps();

            // Prevent the same tag from being attached to a post twice.
            $table->unique(['post_id', 'tag_id']);
        });
    }

    /**
     * Remove the post and tag connection table.
     */
    public function down(): void
    {
        Schema::dropIfExists('post_tag');
    }
};