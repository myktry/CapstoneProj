<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $driver = DB::connection()->getDriverName();

        // Add a unique constraint for admin role to ensure only one admin exists
        // This uses a CASE/WHEN approach to make the constraint database-agnostic
        if ($driver === 'mysql') {
            DB::statement(
                "ALTER TABLE users ADD CONSTRAINT unique_admin_role UNIQUE (
                    CASE WHEN role = 'admin' THEN 1 ELSE NULL END
                )"
            );
        } elseif ($driver === 'pgsql') {
            // For PostgreSQL, create a partial unique index
            DB::statement(
                "CREATE UNIQUE INDEX unique_admin_role ON users(role) WHERE role = 'admin'"
            );
        } elseif ($driver === 'sqlite') {
            // SQLite doesn't support conditional unique constraints easily
            // The application-level check will be sufficient
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $driver = DB::connection()->getDriverName();

        if ($driver === 'mysql') {
            Schema::table('users', function (Blueprint $table) {
                $table->dropUnique('unique_admin_role');
            });
        } elseif ($driver === 'pgsql') {
            DB::statement('DROP INDEX IF EXISTS unique_admin_role');
        }
    }
};

