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
        Schema::table('availabilities', function (Blueprint $table) {

            $table->date('date')->after('worker_profile_id');
            $table->time('start_time')->after('date');
            $table->time('end_time')->after('start_time');

            $table->dropColumn([
                'start_datetime',
                'end_datetime'
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('availabilities', function (Blueprint $table) {

            $table->dateTime('start_datetime');
            $table->dateTime('end_datetime');

            $table->dropColumn([
                'date',
                'start_time',
                'end_time'
            ]);
        });
    }
};
