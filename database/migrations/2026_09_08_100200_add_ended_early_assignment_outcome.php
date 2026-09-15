<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('requests', function (Blueprint $table) {
            $table->timestamp('ended_at')->nullable()->after('completed_at');
        });

        if (DB::getDriverName() === 'sqlite') {
            Schema::table('requests', function (Blueprint $table) {
                $table->enum('status', [
                    'pending',
                    'accepted',
                    'ongoing',
                    'completed',
                    'ended_early',
                    'rejected',
                    'cancelled',
                ])->default('pending')->change();
            });

            return;
        }

        if (in_array(DB::getDriverName(), ['mysql', 'mariadb'], true)) {
            DB::statement("
                ALTER TABLE requests
                MODIFY status ENUM(
                    'pending',
                    'accepted',
                    'ongoing',
                    'completed',
                    'ended_early',
                    'rejected',
                    'cancelled'
                ) NOT NULL DEFAULT 'pending'
            ");
        }
    }

    public function down(): void
    {
        if (DB::table('requests')->where('status', 'ended_early')->exists()) {
            throw new RuntimeException('Cannot remove the ended_early request status while ended-early assignments exist.');
        }

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
        } elseif (in_array(DB::getDriverName(), ['mysql', 'mariadb'], true)) {
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

        Schema::table('requests', function (Blueprint $table) {
            $table->dropColumn('ended_at');
        });
    }
};
