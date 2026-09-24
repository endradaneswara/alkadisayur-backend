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
        Schema::create('barang', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kategori_id')->constrained('kategori')->onDelete('cascade');
            $table->string('KodeItem', 50);
            $table->string('Barcode', 50)->unique();
            $table->string('SKU', 50)->unique();
            $table->string('NamaItem', 100);
            $table->string('Merek')->nullable();
            $table->unsignedInteger('Stok')->default(0);
            $table->string('Rak', 50)->nullable();
            $table->string('TipeItem', 100)->nullable();
            $table->decimal('HargaBeli', 12, 2);
            $table->decimal('HargaJual', 12, 2);
            $table->text('Keterangan')->nullable();
            $table->string('foto')->nullable();
            $table->enum('status', ['tersedia', 'tidak tersedia'])->default('tersedia');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('barang');
    }
};
