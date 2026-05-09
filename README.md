# Sawit App Laravel

1. git clone https://github.com/doniaries/sawitapp_laravel.git
2. cd sawitapp_laravel
3. composer install
4. cp .env.example .env
5. php artisan key:generate
6. php artisan storage:link
7. php artisan migrate
8. php artisan db:seed
9. php artisan serve

## Production Setup

### 1. Scheduler (Wajib untuk Tutup Hari Otomatis)

Untuk menjalankan fitur **Tutup Hari Otomatis** pada jam 00:00, tambahkan baris berikut ke Crontab server Anda (`crontab -e`):

```bash
* * * * * cd /path-ke-aplikasi-anda && php artisan schedule:run >> /dev/null 2>&1
```

### 2. Queue Worker (Opsional - Jika ada Background Jobs)

Jika aplikasi menggunakan antrian (seperti kirim email atau proses berat lainnya), jalankan worker:

```bash
php artisan queue:work --daemon
```

_Sangat disarankan menggunakan **Supervisor** untuk menjaga agar worker tetap berjalan._

### 3. Optimasi Performa

Jalankan perintah ini saat pertama kali deploy atau setiap ada perubahan kode di production:

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan icons:cache
php artisan filament:cache-components
```

### 4. Permission Folder

Pastikan folder storage dan bootstrap/cache memiliki izin tulis:

```bash
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

### 5. Local Development (Scheduler Testing)

Untuk mengetes scheduler di lokal tanpa menunggu jam 00:00:

```bash
php artisan schedule:work
```

10.
