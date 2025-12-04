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
            $table->string('code', 30)->unique();
            $table->string('borrower_name');
            $table->string('proof_image')->nullable();
            $table->text('purpose');
            $table->dateTime('borrow_date');
            $table->dateTime('expected_return_date');
            $table->dateTime('actual_return_date')->nullable();
            $table->string('status')->default('pending');
            $table->text('notes')->nullable();
            $table->softDeletes();
            $table->timestamps();

            // Indexes
            $table->index('borrower_name');
            $table->index('borrow_date');
            $table->index('expected_return_date');
            $table->index('status');
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
