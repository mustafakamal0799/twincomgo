# Rencana Perbaikan Masalah ID Gudang Tidak Terbaca (Versi 2)

## Masalah
- Gudang dengan ID tertentu (misal 52750) muncul di sales invoice tapi tidak ada di data stok ($stokNew).
- Hal ini karena filter gudang di fungsi getSalesOrderStockAjax membatasi gudang yang dimasukkan ke $stokNew.
- Akibatnya, pengecekan stok di fetchMatchingInvoices menghasilkan warning "Gudang ID tidak ditemukan di data stok".

## Rencana Perbaikan
1. Modifikasi fungsi getSalesOrderStockAjax:
   - Tambahkan semua gudang dari $detailWarehouse ke $stokNew tanpa filter terlebih dahulu.
   - Jika ingin tetap menggunakan filter, tambahkan logika untuk memasukkan gudang yang muncul di sales invoice tapi tidak ada di $stokNew.
2. Tambahkan logging untuk memantau gudang yang masuk ke $stokNew dan gudang yang muncul di sales invoice.
3. Uji coba dengan data nyata untuk memastikan warning tidak muncul lagi.
4. Jika perlu, buat opsi konfigurasi untuk mengaktifkan/menonaktifkan filter gudang.

## File yang Akan Diedit
- app/Http/Controllers/ItemController.php

## Langkah Selanjutnya
- Implementasi perubahan di fungsi getSalesOrderStockAjax.
- Tambahkan logging tambahan.
- Lakukan testing menyeluruh pada alur pengecekan stok dan sales invoice.
