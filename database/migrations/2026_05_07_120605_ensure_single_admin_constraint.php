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
        if ($driver === 'mysql') {
            // For MySQL: Add a generated column that stores 1 only for admin role,
            // then add a unique constraint on it. Since NULL values are not unique,
            // this allows multiple non-admin users but only one admin.
            Schema::table('users', function (Blueprint $table) {
                $table->unsignedTinyInteger('is_admin_generated')->storedAs(
                    "CASE WHEN role = 'admin' THEN 1 ELSE NULL END"
                )->nullable()->after('role');
            });

            DB::statement('ALTER TABLE users ADD CONSTRAINT unique_admin_role UNIQUE (is_admin_generated)');
        } elseif ($driver === 'pgsql') {
            // For PostgreSQL, create a partial unique index
            DB::statement(
                "CREATE UNIQUE INDEX unique_admin_role ON users(role) WHERE role = 'admin'"
            );
        } elseif ($driver === 'sqlite') {
            // SQLite doesn't support conditional unique constraints easily
            // The application-level check in admin-register.blade.php will be sufficient
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
                $table->dropColumn('is_admin_generated');
            });
        } elseif ($driver === 'pgsql') {
            DB::statement('DROP INDEX IF EXISTS unique_admin_role');
        }
    }
};

