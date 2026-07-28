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
        Schema::create('admins', function (Blueprint $table) {
        $table->id();
        $table->integer('ap_id')->nullable();
        $table->string('name')->nullable();
        $table->string('slug')->nullable();
        $table->string('type')->nullable();
        $table->string('mobile')->nullable();
        $table->string('email')->unique();
        $table->string('password');
        $table->string('image')->nullable();
        $table->string('district')->nullable();
        $table->string('rank')->nullable();
        $table->string('position')->nullable();
        $table->boolean('status')->default(1); // 1 = Active, 0 = Inactive
        $table->text('description')->nullable();
        $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admins');
    }
};
