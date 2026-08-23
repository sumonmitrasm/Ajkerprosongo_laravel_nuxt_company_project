<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('polls', function (Blueprint $table) {
            $table->foreignId('correct_option_id')->nullable()->after('poll_type')->constrained('poll_options')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('polls', function (Blueprint $table) {
            $table->dropConstrainedForeignId('correct_option_id');
        });
    }
};
