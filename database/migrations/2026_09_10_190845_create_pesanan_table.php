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
        Schema::create('pesanan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('alamat_id')->nullable()->constrained('alamat')->onDelete('restrict');
            $table->foreignId('lokasi_toko_id')->nullable()->constrained('lokasi')->onDelete('restrict');
            $table->string('nomor_order')->unique();
            $table->decimal('total_harga', 12, 2);
            $table->decimal('biaya_pengiriman', 12, 2);
            $table->enum('metode', ['pesan_antar', 'ambil_ditoko']);
            $table->enum('status', ['pending', 'diproses', 'dikirim', 'selesai', 'dibatalkan'])->default('pending');
            $table->text('catatan')->nullable();
            $table->timestamp('tanggal_pemesanan')->useCurrent();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pesanan');
    }
};
