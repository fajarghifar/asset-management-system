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
        Schema::create('loan_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('loan_id')->constrained('loans')->cascadeOnDelete();
            $table->string('type');
            $table->foreignId('asset_id')->nullable()->constrained('assets')->restrictOnDelete();
            $table->foreignId('consumable_stock_id')->nullable()->constrained('consumable_stocks')->restrictOnDelete();
            $table->integer('quantity_borrowed');
            $table->integer('quantity_returned')->default(0);
            $table->dateTime('returned_at')->nullable();
            $table->timestamps();

            $table->index(['loan_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('loan_items');
    }
};
