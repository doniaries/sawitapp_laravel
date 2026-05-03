# Android App dengan Capacitor

Project Android Capacitor sudah dibuat di folder `android`.

## Cara kerja

Aplikasi Filament ini adalah aplikasi Laravel server-rendered, jadi Capacitor dipakai sebagai Android WebView shell yang membuka URL panel Filament:

```text
https://successmandiri.com/admin
```

URL tersebut diatur di `capacitor.config.json`.

## Development lokal

Android emulator tidak bisa mengakses `127.0.0.1` komputer host secara langsung. Untuk testing lokal:

1. Jalankan Laravel:

    ```bash
    php artisan serve --host=0.0.0.0 --port=8000
    ```

2. Ubah sementara `capacitor.config.json`:

    ```json
    {
        "server": {
            "url": "http://10.0.2.2:8000/admin",
            "cleartext": true
        }
    }
    ```

3. Sync Android:

    ```bash
    npx cap sync android
    ```

4. Buka di Android Studio:

    ```bash
    npx cap open android
    ```

Untuk perangkat Android fisik, ganti `10.0.2.2` dengan IP LAN komputer, misalnya `http://192.168.1.10:8000/admin`.

## Production

Sebelum build release, pastikan:

- `capacitor.config.json` memakai URL HTTPS production.
- `APP_URL` Laravel juga memakai domain HTTPS yang sama.
- Session/cookie Laravel valid untuk domain tersebut.
- Asset Filament sudah ter-publish dan bisa diakses dari domain production.

Lalu jalankan:

```bash
npx cap sync android
npx cap open android
```

Build APK/AAB release dilakukan dari Android Studio.

## Catatan Node

Capacitor CLI yang terpasang adalah versi 8 dan membutuhkan Node.js 22 atau lebih baru. Di mesin ini ada Node 24 di `C:\Program Files\nodejs\node.exe`, sedangkan npm Laragon memakai Node 18.
