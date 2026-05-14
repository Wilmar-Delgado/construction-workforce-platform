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
        Schema::create('requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('mission_id')->constrained('missions')->cascadeOnDelete();
            $table->foreignId('requested_by')->constrained('users')->cascadeOnDelete(); // who initiated the request
            $table->foreignId('company_id')->nullable()->constrained('companies')->nullOnDelete(); // optional company making request
            $table->foreignId('worker_profile_id')->nullable()->constrained('worker_profiles')->nullOnDelete(); // worker being proposed / applying
            $table->string('type');
            $table->text('message')->nullable();
            $table->enum('status', ['pending', 'accepted', 'rejected', 'cancelled'])->default('pending');
            $table->foreignId('responded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('responded_at')->nullable();
            $table->timestamps();
            $table->index('status');
            $table->index('type');
            $table->index('requested_by');
            $table->index('company_id');
            $table->index(['mission_id', 'status']);
            $table->unique(
                ['mission_id', 'worker_profile_id', 'type'],
                'request_unique_per_worker'
            );
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('requests');
    }
};
