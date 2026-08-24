<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Create restorable snapshots of post edits.
     */
    public function up(): void
    {
        Schema::create('post_revisions', function (Blueprint $table) {
            $table->id();

            // Record which post was edited and which admin edited it.
            $table->foreignId('post_id')->constrained('posts')->cascadeOnDelete();
            $table->foreignId('editor_id')->nullable()->constrained('admins')->nullOnDelete();
            $table->unsignedInteger('version');

            // Keep a snapshot of the important content fields.
            $table->string('title');
            $table->string('special_title')->nullable();
            $table->text('summary')->nullable();
            $table->longText('description');
            $table->string('featured_image')->nullable();
            $table->text('image_caption')->nullable();
            $table->text('change_note')->nullable();
            $table->timestamp('created_at')->useCurrent();

            // Every version number must be unique inside its post.
            $table->unique(['post_id', 'version']);
            $table->index(['post_id', 'created_at']);
        });
    }

    /**
     * Remove all saved post revisions.
     */
    public function down(): void
    {
        Schema::dropIfExists('post_revisions');
    }
};