<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('receipt_records', function (Blueprint $table) {
            $table->id();
            $table->string('transaction_id')->unique();
            $table->string('filename');
            $table->string('relative_path');
            $table->char('sha256_hash', 64);
            $table->string('mime_type', 80);
            $table->unsignedBigInteger('size_bytes')->nullable();
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['is_active', 'transaction_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('receipt_records');
    }
};
