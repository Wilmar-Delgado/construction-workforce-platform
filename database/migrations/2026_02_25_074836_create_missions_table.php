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
        Schema::create('missions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('hiring_company_id')->constrained('companies')->cascadeOnDelete();
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->foreignId('lending_company_id')->nullable()->constrained('companies')->nullOnDelete();
            $table->foreignId('worker_profile_id')->nullable()->constrained('worker_profiles')->nullOnDelete();

            $table->string('title');
            $table->text('description')->nullable();
            
            $table->string('city');
            $table->string('province', 100);
            $table->string('country')->default('Canada');
            $table->string('address_line_1')->nullable();
            $table->string('address_line_2')->nullable();
            $table->string('postal_code')->nullable();
            $table->string('site_name')->nullable();
            $table->text('directions')->nullable();
            $table->decimal('latitude', 10, 7)->nullable();
            $table->decimal('longitude', 10, 7)->nullable();

            $table->string('job_type');
            $table->unsignedInteger('workers')->nullable();
            $table->date('start_date');
            $table->date('end_date');
            $table->decimal('hourly_rate', 10, 2)->nullable();
            $table->enum('status', [
                'draft',
                'open',
                'in_progress',
                'completed',
                'cancelled'
            ])->default('draft');
            $table->timestamps();

            $table->index(['job_type', 'status']);
            $table->index(['city', 'status']);
            $table->index(['province', 'status']);
            $table->index(['hiring_company_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('missions');
    }
};
