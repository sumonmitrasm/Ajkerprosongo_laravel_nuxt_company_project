<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('poll_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('poll_id')->constrained()->cascadeOnDelete();
            $table->string('content', 150);
            $table->unsignedSmallInteger('position')->default(0);
            $table->timestamps();
            $table->unique(['poll_id', 'content']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('poll_options');
    }
};
