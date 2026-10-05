<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('admin_login_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('admin_id')->nullable()->constrained('admins')->nullOnDelete();
            $table->string('ip_address', 45)->nullable();
            $table->string('device', 30)->default('Desktop');
            $table->string('browser', 60)->nullable();
            $table->string('platform', 60)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('logged_in_at')->index();
            $table->timestamps();

            $table->index(['admin_id', 'logged_in_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admin_login_activities');
    }
};
