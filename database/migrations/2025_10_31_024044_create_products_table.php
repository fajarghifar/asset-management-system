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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name')->index()->comment('Name of the product/item');
            $table->string('code')->unique()->comment('SKU or Master Code');
            $table->string('type')->index()->comment('Type: Asset or Consumable');
            $table->foreignId('category_id')->constrained()->restrictOnDelete()->comment('Product Category');
            $table->boolean('can_be_loaned')->default(true)->index()->comment('Flag if product is loanable');
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
