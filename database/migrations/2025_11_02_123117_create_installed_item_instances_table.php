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
            $table->foreignId('installed_location_id')->constrained('locations');
            $table->date('installed_at');
            $table->text('notes')->nullable();
            $table->softDeletes();
            $table->timestamps();

            $table->index('item_id');
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
