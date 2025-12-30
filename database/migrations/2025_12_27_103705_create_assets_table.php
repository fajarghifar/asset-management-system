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
        Schema::create('assets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained('products')->restrictOnDelete();
            $table->foreignId('location_id')->constrained()->restrictOnDelete();
            $table->string('asset_tag', 50)->unique()->comment('Kode Inventaris Unik (Ex: BT-LPT-001)');
            $table->string('serial_number')->nullable()->unique()->comment('SN dari Pabrik');
            $table->string('status')->default('in_stock')->index();
            $table->date('purchase_date')->nullable();
            $table->unsignedBigInteger('purchase_price')->default(0);
            $table->string('supplier_name')->nullable();
            $table->string('order_number')->nullable();
            $table->string('image_path')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('assets');
    }
};
