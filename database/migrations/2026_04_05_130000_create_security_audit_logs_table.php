<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('security_audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('admin_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('event');
            $table->string('status');
            $table->string('ip_address', 45)->nullable();
            $table->string('transaction_id')->nullable()->index();
            $table->string('message')->nullable();
            $table->json('context')->nullable();
            $table->timestamps();

            $table->index(['event', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('security_audit_logs');
    }
};
