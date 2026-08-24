<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Create the chronological publishing workflow history.
     */
    public function up(): void
    {
        Schema::create('post_status_histories', function (Blueprint $table) {
            $table->id();

            // Record each status transition and the responsible admin.
            $table->foreignId('post_id')->constrained('posts')->cascadeOnDelete();
            $table->foreignId('changed_by')->nullable()->constrained('admins')->nullOnDelete();
            $table->string('from_status', 30)->nullable();
            $table->string('to_status', 30);
            $table->text('note')->nullable();
            $table->timestamp('created_at')->useCurrent();

            // Show the workflow timeline of one post efficiently.
            $table->index(['post_id', 'created_at']);
        });
    }

    /**
     * Remove the post status history table.
     */
    public function down(): void
    {
        Schema::dropIfExists('post_status_histories');
    }
};