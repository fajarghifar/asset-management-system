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
        Schema::create('borrowings', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique(); // BRW-2025-001
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->text('purpose');
            // Indexing tanggal untuk performa filtering/reporting
            $table->dateTime('borrow_date')->index();
            $table->dateTime('expected_return_date')->index();
            $table->dateTime('actual_return_date')->nullable();
            // Status menggunakan string (yang akan dicasting ke Enum di Model)
            $table->string('status')->default('pending')->index();
            $table->text('notes')->nullable();
            $table->softDeletes();
            $table->timestamps();

            // Composite Index untuk query umum (Status + Tanggal)
            $table->index(['status', 'borrow_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('borrowings');
    }
};
