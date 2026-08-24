<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Create the posts table for the current version of every news item.
     */
    public function up(): void
    {
        Schema::create('posts', function (Blueprint $table) {
            $table->id();

            // Connect the post with its newsroom classification.
            $table->foreignId('section_id')->nullable()->constrained('sections')->nullOnDelete();
            $table->foreignId('category_id')->nullable()->constrained('categories')->nullOnDelete();

            // Connect local news with the imported Bangladesh location tables.
            $table->foreignId('division_id')->nullable()->constrained('divisions')->nullOnDelete();
            $table->foreignId('district_id')->nullable()->constrained('districts')->nullOnDelete();
            $table->foreignId('upazila_id')->nullable()->constrained('upazilas')->nullOnDelete();

            // Keep author and editorial audit responsibilities separate.
            $table->foreignId('author_id')->nullable()->constrained('admins')->nullOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('admins')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('admins')->nullOnDelete();
            $table->foreignId('reviewed_by')->nullable()->constrained('admins')->nullOnDelete();
            $table->foreignId('published_by')->nullable()->constrained('admins')->nullOnDelete();
            $table->foreignId('deleted_by')->nullable()->constrained('admins')->nullOnDelete();

            // Store the current, publicly usable version of the news content.
            $table->string('post_type', 30)->default('news');
            $table->string('title');
            $table->string('slug')->unique();
            $table->string('special_title')->nullable();
            $table->text('summary')->nullable();
            $table->longText('description');
            $table->text('reporter_details')->nullable();
            $table->string('source')->nullable();

            // Store primary media; additional gallery images live in post_images.
            $table->string('featured_image')->nullable();
            $table->text('image_caption')->nullable();
            $table->string('image_alt')->nullable();
            $table->text('video_url')->nullable();
            $table->string('video_type', 30)->nullable();

            // Control the complete newsroom publishing workflow.
            $table->string('status', 30)->default('draft');
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->timestamp('last_edited_at')->nullable();

            // Configure homepage placement and reader-facing behaviour.
            $table->boolean('is_breaking')->default(false);
            $table->boolean('is_featured')->default(false);
            $table->boolean('is_pinned')->default(false);
            $table->boolean('allow_comments')->default(true);
            $table->json('featured_positions')->nullable();

            // Keep search-engine and social-sharing metadata with the post.
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->text('meta_keywords')->nullable();
            $table->string('meta_image')->nullable();
            $table->string('meta_robots', 50)->default('index,follow');
            $table->text('canonical_url')->nullable();
            $table->json('schema_markup')->nullable();

            // Store summary statistics; detailed analytics can be added later.
            $table->unsignedBigInteger('views_count')->default(0);
            $table->unsignedSmallInteger('reading_time')->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Speed up the queries most frequently used by a news portal.
            $table->index(['status', 'published_at'], 'posts_status_published_index');
            $table->index(['category_id', 'status', 'published_at'], 'posts_category_published_index');
            $table->index(['section_id', 'status', 'published_at'], 'posts_section_published_index');
            $table->index(['is_breaking', 'status', 'published_at'], 'posts_breaking_published_index');
            $table->index(['is_featured', 'status', 'published_at'], 'posts_featured_published_index');
        });
    }

    /**
     * Remove the posts table.
     */
    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};