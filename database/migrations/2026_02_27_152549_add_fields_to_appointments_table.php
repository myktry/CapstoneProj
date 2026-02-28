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
        Schema::table('appointments', function (Blueprint $table) {
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('service_id')->nullable()->constrained()->nullOnDelete();
            $table->date('appointment_date');
            $table->string('appointment_time');
            $table->string('customer_name');
            $table->string('customer_email');
            $table->string('customer_phone');
            $table->string('status')->default('pending'); // pending | paid | cancelled
            $table->string('stripe_session_id')->nullable();
            $table->unsignedInteger('amount_paid')->default(0); // in centavos
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table) {
            $table->dropForeign(['user_id']);
            $table->dropForeign(['service_id']);
            $table->dropColumn([
                'user_id', 'service_id', 'appointment_date', 'appointment_time',
                'customer_name', 'customer_email', 'customer_phone',
                'status', 'stripe_session_id', 'amount_paid',
            ]);
        });
    }
};
