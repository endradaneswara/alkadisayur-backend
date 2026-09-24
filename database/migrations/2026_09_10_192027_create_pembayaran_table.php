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
        Schema::create('pembayaran', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pesanan_id')->unique()->constrained('pesanan')->onDelete('cascade');
            $table->enum('metode_pembayaran', ['QRIS', 'Transfer', 'COD'])->default('QRIS');
            $table->enum('status', ['pending', 'berhasil', 'gagal', 'expired'])->default('pending');
            $table->decimal('jumlah', 12, 2);
            $table->string('transaksi_id');
            $table->timestamp('tanggal_pembayaran')->useCurrent();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pembayaran');
    }
};
