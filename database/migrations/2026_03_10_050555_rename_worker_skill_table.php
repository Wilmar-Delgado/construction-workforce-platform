<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::rename('worker_skill', 'skill_worker_profile');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::rename('skill_worker_profile', 'worker_skill');
    }
};
