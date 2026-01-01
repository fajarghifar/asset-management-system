<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('loans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->string('borrower_name');
            $table->string('code')->unique();
            $table->string('proof_image')->nullable();
            $table->text('purpose')->nullable();
            $table->dateTime('loan_date');
            $table->dateTime('due_date')->nullable();
            $table->dateTime('returned_date')->nullable();
            $table->string('status')->default('pending')->index();
            $table->text('notes')->nullable();
            $table->timestamps();

            // Indexes
            $table->index('borrower_name');
            $table->index('loan_date');
            $table->index('due_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('loans');
    }
};
