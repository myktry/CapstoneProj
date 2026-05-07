<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * Enforces a single admin account at the database level.
     */
    public function up(): void
    {
        $driver = DB::connection()->getDriverName();

        if ($driver === 'mysql') {
            Schema::table('users', function (Blueprint $table) {
                $table->unsignedTinyInteger('admin_guard')
                    ->nullable()
                    ->storedAs("case when role = 'admin' then 1 else null end")
                    ->after('role');
            });

            DB::statement('ALTER TABLE users ADD UNIQUE INDEX unique_admin_role (admin_guard)');

            return;
        }

        if ($driver === 'pgsql') {
            DB::statement("CREATE UNIQUE INDEX IF NOT EXISTS unique_admin_role ON users ((CASE WHEN role = 'admin' THEN 1 ELSE NULL END))");
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
                $table->dropColumn('admin_guard');
            });

            return;
        }

        if ($driver === 'pgsql') {
            DB::statement('DROP INDEX IF EXISTS unique_admin_role');
        }
    }
};

