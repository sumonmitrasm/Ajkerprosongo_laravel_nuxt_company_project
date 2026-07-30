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
        Schema::create('categories', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('parent_id')->default(0);
            $table->foreignId('section_id')->constrained('sections')->onDelete('cascade');
            $table->string('category_name');
            $table->text('description')->nullable();
            $table->string('image')->nullable();
            $table->integer('position')->nullable()->default(0);
            $table->string('url')->nullable();
            $table->string('url_structure')->nullable();
            $table->string('heading_tag')->nullable();
            $table->text('schema_markup')->nullable();
            $table->string('meta_title')->nullable();
            $table->text('meta_data')->nullable();
            $table->text('meta_description')->nullable();
            $table->string('meta_keywords')->nullable();
            $table->string('meta_robot')->nullable();
            $table->tinyInteger('status')->default(1);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('categories');
    }
};
