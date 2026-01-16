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
        Schema::create('loans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->restrictOnDelete();
            $table->string('borrower_name');
            $table->string('code')->unique();
            $table->string('proof_image')->nullable();
            $table->text('purpose');
            $table->dateTime('loan_date');
            $table->dateTime('due_date');
            $table->dateTime('returned_date')->nullable();
            $table->string('status')->default('pending')->index();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['status', 'due_date']);
            $table->index(['user_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('loans');
    }
};
