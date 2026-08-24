<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Create the gallery images attached to posts.
     */
    public function up(): void
    {
        Schema::create('post_images', function (Blueprint $table) {
            $table->id();

            // Deleting a post also removes its gallery database records.
            $table->foreignId('post_id')->constrained('posts')->cascadeOnDelete();

            // Store image details and their display order.
            $table->string('image');
            $table->text('caption')->nullable();
            $table->string('alt_text')->nullable();
            $table->string('source')->nullable();
            $table->string('photographer')->nullable();
            $table->unsignedInteger('position')->default(0);
            $table->boolean('is_featured')->default(false);
            $table->timestamps();

            // Fetch an article gallery in its intended order efficiently.
            $table->index(['post_id', 'position']);
        });
    }

    /**
     * Remove the post image table.
     */
    public function down(): void
    {
        Schema::dropIfExists('post_images');
    }
};