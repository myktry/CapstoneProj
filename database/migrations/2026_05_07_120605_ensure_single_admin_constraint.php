<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Creates a database-level constraint to ensure only one admin exists.
     * Relies primarily on application-level validation in admin-register.blade.php
     */
    public function up(): void
    {
        $driver = DB::connection()->getDriverName();

        if ($driver === 'pgsql') {
            // For PostgreSQL, create a partial unique index
            // This ensures only one row can have role = 'admin'
            DB::statement(
                "CREATE UNIQUE INDEX IF NOT EXISTS unique_admin_role ON users(role) WHERE role = 'admin'"
            );
        }
        
        // MySQL: Using application-level validation is sufficient
        // SQLite: Using application-level validation is sufficient
        // Both rely on the abort_if() checks in admin-register.blade.php and admin-bootstrap.blade.php
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $driver = DB::connection()->getDriverName();

        if ($driver === 'pgsql') {
            DB::statement('DROP INDEX IF EXISTS unique_admin_role');
        }
    }
};

