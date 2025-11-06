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
        Schema::create('installed_item_location_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('instance_id')
                ->constrained('installed_item_instances')
                ->cascadeOnDelete();
            $table->foreignId('location_id')->constrained()->cascadeOnDelete();
            $table->date('installed_at');
            $table->date('removed_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['instance_id', 'installed_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('installed_item_location_histories');
    }
};
