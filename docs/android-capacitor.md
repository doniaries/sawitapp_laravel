# Android App dengan Capacitor

Project Android Capacitor sudah dibuat di folder `android`.

## Cara kerja

Capacitor memuat aplikasi mobile lokal dari folder `mobile`. UI mobile ini bisa dibuka tanpa jaringan, menyimpan input ke IndexedDB, lalu melakukan sync ke API Laravel saat online.

Filament tetap dipakai sebagai admin panel web. Android tidak membuka panel Filament langsung karena input offline membutuhkan storage lokal di perangkat.

## Development lokal

Android emulator tidak bisa mengakses `127.0.0.1` komputer host secara langsung. Untuk testing API lokal:

1. Jalankan Laravel:

    ```bash
    php artisan serve --host=0.0.0.0 --port=8000
    ```

2. Buka aplikasi Android, isi URL API:

    ```text
    http://10.0.2.2:8000
    ```

3. Sync Android:

    ```bash
    npx cap sync android
    ```

4. Buka di Android Studio:

    ```bash
    npx cap open android
    ```

Untuk perangkat Android fisik, ganti `10.0.2.2` dengan IP LAN komputer, misalnya `http://192.168.1.10:8000`.

Manifest Android saat ini mengizinkan cleartext traffic agar testing HTTP lokal bisa jalan. Untuk release publik, tetap gunakan HTTPS production.

## Production

Sebelum build release, pastikan:

- `APP_URL` Laravel juga memakai domain HTTPS yang sama.
- Session/cookie Laravel valid untuk domain tersebut.
- API Laravel bisa diakses dari Android.
- Android user memakai URL API production, misalnya `https://successmandiri.com`.

Lalu jalankan:

```bash
npx cap sync android
npx cap open android
```

Build APK/AAB release dilakukan dari Android Studio.

## Catatan Node

Capacitor CLI yang terpasang adalah versi 8 dan membutuhkan Node.js 22 atau lebih baru. Di mesin ini ada Node 24 di `C:\Program Files\nodejs\node.exe`, sedangkan npm Laragon memakai Node 18.
