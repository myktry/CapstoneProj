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
        Schema::table('appointments', function (Blueprint $table): void {
            $table->string('stripe_payment_intent_id')->nullable()->after('stripe_session_id');
            $table->string('refund_status')->nullable()->after('amount_paid');
            $table->unsignedInteger('refund_amount')->nullable()->after('refund_status');
            $table->unsignedInteger('refund_deduction_amount')->nullable()->after('refund_amount');
            $table->string('refund_reference')->nullable()->after('refund_deduction_amount');
            $table->timestamp('refund_processed_at')->nullable()->after('refund_reference');
            $table->string('cancelled_by')->nullable()->after('refund_processed_at');
            $table->timestamp('cancelled_at')->nullable()->after('cancelled_by');
            $table->text('cancellation_note')->nullable()->after('cancelled_at');

            $table->index(['status', 'cancelled_by'], 'appointments_status_cancelled_by_idx');
            $table->index('refund_status', 'appointments_refund_status_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('appointments', function (Blueprint $table): void {
            $table->dropIndex('appointments_status_cancelled_by_idx');
            $table->dropIndex('appointments_refund_status_idx');

            $table->dropColumn([
                'stripe_payment_intent_id',
                'refund_status',
                'refund_amount',
                'refund_deduction_amount',
                'refund_reference',
                'refund_processed_at',
                'cancelled_by',
                'cancelled_at',
                'cancellation_note',
            ]);
        });
    }
};
