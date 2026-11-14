# Laporan Perubahan

Tanggal: 14 November 2026

## Ringkasan

Menyesuaikan judul tab browser dengan label menu sidebar terbaru, serta menghapus empat card ringkasan pada halaman Rekap Laporan agar konsisten antara role admin dan kepala dinas.

## Perubahan

### Judul tab browser

- `resources/views/admin/supervision/index.blade.php`
  - `Pengawasan` menjadi `Kondisi Pasar`.
- `resources/views/admin/reports/index.blade.php`
  - `Ekspor Laporan` menjadi `Rekap Laporan`.
- `resources/views/kepala-dinas/distribution/index.blade.php`
  - `Laporan` menjadi `Penyaluran`.

### Card ringkasan Rekap Laporan

- `resources/views/partials/report-recap-content.blade.php`
  - Menghapus empat card ringkasan:
    - Laporan Penyaluran
    - Jumlah Penyaluran
    - Laporan Kondisi Pasar
    - Perlu Perhatian
  - Menghapus parameter `showSummary`.
  - Baris tab dan tombol ekspor langsung mengisi ruang setelah filter dengan jarak `mt-4`.
- `resources/views/admin/reports/index.blade.php`
  - Menghapus pengiriman parameter `showSummary => false`.
- Halaman kepala dinas otomatis tidak lagi menampilkan card ringkasan karena card dihapus dari partial bersama.

### Test

- `tests/Feature/DashboardTest.php`
  - Menyesuaikan urutan dan label menu sidebar terbaru.
- `tests/Feature/Admin/NavigationTest.php`
  - Menyesuaikan label dan judul halaman `Kondisi Pasar` serta `Rekap Laporan`.
- `tests/Feature/Admin/SidebarToggleTest.php`
  - Menyesuaikan daftar label menu sidebar.
- `tests/Feature/KepalaDinas/KepalaDinasAreaTest.php`
  - Menyesuaikan judul tab `Penyaluran`.
  - Memastikan card `Jumlah Penyaluran` tidak lagi tampil pada halaman Rekap Laporan.

## Verifikasi

- `vendor/bin/pint --dirty --format agent`: lulus.
- Seluruh test suite: **390 tests, 1663 assertions** lulus.
- Tidak ada perubahan dependency maupun aset Vite.
