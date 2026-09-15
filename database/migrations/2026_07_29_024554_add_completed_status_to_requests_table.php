<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            Schema::table('requests', function (Blueprint $table) {
                $table->enum('status', [
                    'pending',
                    'accepted',
                    'ongoing',
                    'completed',
                    'rejected',
                    'cancelled',
                ])->default('pending')->change();
            });

            return;
        }

        if (! in_array(DB::getDriverName(), ['mysql', 'mariadb'], true)) {
            return;
        }

        DB::statement("
            ALTER TABLE requests
            MODIFY status ENUM(
                'pending',
                'accepted',
                'ongoing',
                'completed',
                'rejected',
                'cancelled'
            ) NOT NULL DEFAULT 'pending'
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::getDriverName() === 'sqlite') {
            Schema::table('requests', function (Blueprint $table) {
                $table->enum('status', [
                    'pending',
                    'accepted',
                    'ongoing',
                    'rejected',
                    'cancelled',
                ])->default('pending')->change();
            });

            return;
        }

        if (! in_array(DB::getDriverName(), ['mysql', 'mariadb'], true)) {
            return;
        }

        DB::statement("
            ALTER TABLE requests
            MODIFY status ENUM(
                'pending',
                'accepted',
                'ongoing',
                'rejected',
                'cancelled'
            ) NOT NULL DEFAULT 'pending'
        ");
    }
};
