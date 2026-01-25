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
        Schema::create('kits', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('kit_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kit_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained();
            $table->foreignId('location_id')->nullable()->constrained();
            $table->integer('quantity')->default(1);
            $table->string('notes')->nullable();
            $table->timestamps();

            $table->unique(['kit_id', 'product_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kit_items');
        Schema::dropIfExists('kits');
    }
};
