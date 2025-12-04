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
        Schema::create('borrowing_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('borrowing_id')->constrained()->cascadeOnDelete();
            $table->foreignId('item_id')->constrained()->restrictOnDelete();
            $table->foreignId('fixed_instance_id')
                ->nullable()
                ->constrained('fixed_item_instances')
                ->nullOnDelete();
            $table->foreignId('location_id')
                ->nullable()
                ->constrained('locations')
                ->nullOnDelete();
            $table->integer('quantity')->default(1);
            $table->integer('returned_quantity')->default(0);
            $table->string('status')->default('borrowed');
            $table->dateTime('returned_at')->nullable();
            $table->timestamps();

            // Indexes
            $table->unique(['borrowing_id', 'item_id', 'fixed_instance_id']);
            $table->index('item_id');
            $table->index('fixed_instance_id');
            $table->index('location_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('borrowing_items');
    }
};
