<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Fitur "Tahan Transaksi" (park sale) — untuk kasus antrian di mana
     * pembeli pertama masih bingung/lama mikir, sedangkan pembeli kedua
     * di belakangnya cuma beli 1 barang dan mau cepat.
     *
     * Kasir bisa menyimpan keranjang pembeli pertama sebagai DRAFT
     * (belum jadi transaksi resmi, stok belum berkurang), lalu langsung
     * layani pembeli kedua sampai selesai. Begitu pembeli pertama sudah
     * tahu mau beli apa, kasir tinggal buka draft-nya lagi dan lanjutkan.
     *
     * SENGAJA dibuat sebagai tabel TERPISAH dari `sales`, bukan
     * menambah kolom status ke tabel `sales` yang sudah ada — supaya:
     *   1. Semua query laporan/dashboard yang sudah ada (yang menghitung
     *      dari tabel `sales`) tidak perlu diubah sama sekali dan tidak
     *      berisiko ikut menghitung draft yang belum dibayar.
     *   2. Draft yang belum dibayar TIDAK PERNAH mengurangi stok —
     *      pengurangan stok baru terjadi saat draft benar-benar
     *      dikonversi jadi transaksi (Sale) lewat SaleController::store().
     */
    public function up(): void
    {
        Schema::create('draft_sales', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();

            // Catatan bebas dari kasir, opsional — misal ciri-ciri
            // pembeli, biar gampang dikenali pas mau dilanjutkan lagi.
            $table->string('note', 255)->nullable();

            $table->timestamps();
        });

        Schema::create('draft_sale_details', function (Blueprint $table) {
            $table->id();

            $table->foreignId('draft_sale_id')
                ->constrained('draft_sales')
                ->cascadeOnDelete();

            $table->string('kode_produk', 10);
            $table->foreign('kode_produk')
                ->references('kode_produk')
                ->on('products')
                ->restrictOnDelete();

            $table->integer('quantity');

            // Harga disimpan saat draft dibuat, murni sebagai referensi
            // tampilan (perkiraan total). Harga FINAL tetap selalu
            // diambil ulang dari products.selling_price saat draft
            // benar-benar dibayar — sama seperti alur checkout normal.
            $table->decimal('unit_price', 15, 2);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('draft_sale_details');
        Schema::dropIfExists('draft_sales');
    }
};