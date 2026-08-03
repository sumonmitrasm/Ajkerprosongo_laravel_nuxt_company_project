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
        Schema::create('admin_roles', function (Blueprint $table) {
            $table->id();
            $table->integer('admin_id');
            $table->string('module');
            $table->tinyInteger('view_access');
            $table->tinyInteger('edit_access');
            $table->tinyInteger('add_access');
            $table->tinyInteger('delete_access');
            $table->tinyInteger('no_access');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admin_roles');
    }
};
