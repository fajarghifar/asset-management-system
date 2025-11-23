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
        Schema::create('installed_item_instances', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->foreignId('item_id')->constrained()->cascadeOnDelete();
            $table->string('serial_number')->nullable()->unique();
            $table->foreignId('current_location_id')->constrained('locations');
            $table->date('installed_at');
            $table->text('notes')->nullable();
            $table->softDeletes();
            $table->timestamps();

            // Indexes
            $table->index(['current_location_id', 'deleted_at']);
            $table->index(['item_id', 'deleted_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('installed_item_instances');
    }
};
